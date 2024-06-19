<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChecksheetHeader;
use App\Models\MstChecksheetSection;
use App\Models\MstDowntimeCause;
use App\Models\Dropdown;
use App\Models\ChecksheetDowntime;
use App\Models\ChecksheetDetail;
use App\Models\MstShop;
use App\Models\MstModel;
use App\Models\ChecksheetNotGood;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ChecksheetExport;


class ChecksheetController extends Controller
{
    public function index(){
        $item = ChecksheetHeader::get();
        $category = MstChecksheetSection::get();
        $dropdown = Dropdown::where('category','Shift')->get();

        return view('checksheet.index',compact('item','category','dropdown'));
    }

    public function storeMain(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'section_id' => 'required|exists:mst_checksheet_sections,id',
            'shift' => 'required|string|max:255',
        ]);

        // Retrieve section data based on the provided section_id
        $section = MstChecksheetSection::findOrFail($request->section_id);

        // Create a new instance of the ChecksheetHeader model
        $checksheetHeader = new ChecksheetHeader();

        // Populate the model instance with the retrieved data and the provided shift
        $checksheetHeader->section_id = $section->id;
        $checksheetHeader->department = $section->dept;
        $checksheetHeader->sub_section = $section->section;
        $checksheetHeader->date = now()->toDateString(); // You can change this if needed
        $checksheetHeader->revision = 0; // Set revision to 0
        $checksheetHeader->status = 0;
        $checksheetHeader->document_no = $section->no_document;
        $checksheetHeader->shift = $request->shift;
        $checksheetHeader->created_by = auth()->user()->name; // Example: Get the current user's name

        // Save the new ChecksheetHeader record to the database
        $checksheetHeader->save();

        // Redirect back or return a response as needed
        // For example:
        $encryptedId = encrypt($checksheetHeader->id);
        return redirect()->route('form.checksheet', ['id' => $encryptedId])->with('status', 'Checksheet header created successfully.');
    }

    public function formChecksheet($id) {
        $id = decrypt($id);
        $item = ChecksheetHeader::where('id', $id)->first();
        $downtimeCategory = MstDowntimeCause::get();
        // Check if $item exists (optional)
        if (!$item) {
          // Handle case where ChecksheetHeader record is not found
          return abort(404); // Or redirect to an error page
        }

        // Fetch section details (assuming section_id is in $item)
        $section_name = DB::table('mst_checksheet_sections')
          ->where('id', $item->section_id)
          ->value('section_name');  // Get only the section_name

        // Get shops associated with the section
        $shops = DB::table('mst_shops')
        ->where('section_id', $item->section_id)
        ->get();

        // Extract shop IDs from the $shops collection
        $shopIds = $shops->pluck('id')->toArray();

        // Fetch models based on the shop IDs
        $models = DB::table('mst_models')
        ->whereIn('shop_id', $shopIds)
        ->get();


        // Prepare the output data structure
        $formatted_data = [];
        foreach ($models as $model) {

          // Find shop name using shop_id (assuming shop_id exists in model)
          if (isset($shops)) {
            foreach ($shops as $shop) {  // Typo fix: $shop_name -> $shop
              if ($shop->id === $model->shop_id) {
                $shop_name = $shop->shop_name;
                break; // Exit inner loop once shop is found
              }
            }
          }

          $formatted_data[] = [
            'section_name' => $section_name,
            'shop_name' => $shop_name,
            'model_name' => $model->model_name,
          ];
        }
        // Return the view with the formatted data
        return view('checksheet.form', compact('formatted_data','item','id','downtimeCategory'));
      }

      public function storeForm(Request $request)
        {
            DB::beginTransaction();

            try {
                $headerId = $request->id;

                // 1. Insert data into checksheet_details table
                foreach ($request->shop as $shop) {
                    $shopId = DB::table('mst_shops')->where('shop_name', $shop)->value('id');

                    $detailId = DB::table('checksheet_details')->insertGetId([
                        'header_id' => $headerId,
                        'shop_id' => $shopId,
                        'planning_manpower' => $request->man_power_planning[$shop][0],
                        'actual_manpower' => $request->man_power_actual[$shop][0],
                        'pic' => $request->pic[$shop][0],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // 2. Insert data into checksheet_productions table for each model in the shop
                    foreach ($request->production_planning as $modelName => $planning) {
                        $modelId = DB::table('mst_models')->where('model_name', $modelName)->value('id');
                        $modelShopId = DB::table('mst_models')->where('model_name', $modelName)->value('shop_id');

                        if ($modelShopId === $shopId) {
                            $productionId = DB::table('checksheet_productions')->insertGetId([
                                'detail_id' => $detailId,
                                'model_id' => $modelId,
                                'planning_production' => $planning[0] ?? null,
                                'actual_production' => $request->production_actual[$modelName][0] ?? null,
                                'balance' => $request->production_different[$modelName][0] ?? null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            // 3. Insert downtime data into checksheet_downtimes table
                            if (isset($request->downtime_category[$modelName])) {
                                foreach ($request->downtime_category[$modelName] as $index => $categoryId) {
                                    // Ensure cause_id is not null
                                    if ($categoryId !== null) {
                                        DB::table('checksheet_downtimes')->insert([
                                            'production_id' => $productionId,
                                            'cause_id' => $categoryId,
                                            'problem' => $request->cause[$modelName][$index] ?? null,
                                            'action' => $request->action[$modelName][$index] ?? null,
                                            'time_from' => $request->time_from[$modelName][$index] ?? null,
                                            'time_to' => $request->time_until[$modelName][$index] ?? null,
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ]);
                                    } else {
                                        // Insert placeholder data with default values
                                        DB::table('checksheet_downtimes')->insert([
                                            'production_id' => $productionId,
                                            'cause_id' => null, // Placeholder for cause_id
                                            'problem' => null,
                                            'action' => null,
                                            'time_from' => null,
                                            'time_to' => null,
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ]);
                                    }
                                }
                            }


                            // 4. Insert not good (NG) data into checksheet_not_goods table
                            DB::table('checksheet_not_goods')->insert([
                                'production_id' => $productionId,
                                'model_id' => $modelId,
                                'quantity' => ($request->repair[$modelName][0] ?? 0) + ($request->reject[$modelName][0] ?? 0),
                                'repair' => $request->repair[$modelName][0] ?? null,
                                'reject' => $request->reject[$modelName][0] ?? null,
                                'total' => ($request->repair[$modelName][0] ?? 0) + ($request->reject[$modelName][0] ?? 0),
                                'remark' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }

                DB::commit();
                return redirect('/checksheet')->with('status', 'Checksheet data saved successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect('/checksheet')->with('failed', 'Failed to save checksheet data. Please try again. Error: ' . $e->getMessage());
            }
        }

        public function showDetail($id)
        {
            $id = decrypt($id);

            // Fetch checksheet header data
            $header = DB::table('checksheet_headers')->where('id', $id)->first();

            $downtimeCategory = MstDowntimeCause::get();
            // Fetch related checksheet details
            $details = DB::table('checksheet_details')
                ->where('header_id', $id)
                ->get();

            // Fetch related production, downtime, and not good data
            $productions = DB::table('checksheet_productions')
                ->whereIn('detail_id', $details->pluck('id'))
                ->get();

            $downtimes = DB::table('checksheet_downtimes')
                ->whereIn('production_id', $productions->pluck('id'))
                ->get();

            $notGoods = DB::table('checksheet_not_goods')
                ->whereIn('production_id', $productions->pluck('id'))
                ->get();

            // Format the data for the view
            $formattedData = [];
            foreach ($details as $detail) {
                $shop = DB::table('mst_shops')->where('id', $detail->shop_id)->first();
                $models = $productions->where('detail_id', $detail->id);

                $formattedData[] = [
                    'shop_name' => $shop->shop_name,
                    'planning_manpower' => $detail->planning_manpower,
                    'actual_manpower' => $detail->actual_manpower,
                    'pic' => $detail->pic,
                    'models' => $models->map(function ($model) use ($downtimes, $notGoods) {
                        return [
                            'model_id' => $model->model_id,
                            'planning_production' => $model->planning_production,
                            'actual_production' => $model->actual_production,
                            'balance' => $model->balance,
                            'downtimes' => $downtimes->where('production_id', $model->id),
                            'not_goods' => $notGoods->where('production_id', $model->id),
                        ];
                    }),
                ];
            }
            // dd($formattedData,$downtimes,$notGoods,$downtimeCategory);
            return view('checksheet.show', compact('header', 'formattedData', 'downtimes', 'notGoods','id','downtimeCategory'));
        }

public function updateDetail($id)
{
    $id = decrypt($id);

    // Fetch checksheet header data
    $header = DB::table('checksheet_headers')->where('id', $id)->first();

    $downtimeCategory = MstDowntimeCause::get();

    // Fetch related checksheet details
    $details = DB::table('checksheet_details')
        ->where('header_id', $id)
        ->get();

    // Fetch related production, downtime, and not good data
    $productions = DB::table('checksheet_productions')
        ->whereIn('detail_id', $details->pluck('id'))
        ->get();

    $downtimes = DB::table('checksheet_downtimes')
        ->whereIn('production_id', $productions->pluck('id'))
        ->get();

    $notGoods = DB::table('checksheet_not_goods')
        ->whereIn('production_id', $productions->pluck('id'))
        ->get();

    // Format the data for the view
    $formattedData = [];
    foreach ($details as $detail) {
        $shop = DB::table('mst_shops')->where('id', $detail->shop_id)->first();
        $models = $productions->where('detail_id', $detail->id);

        $formattedData[] = [
            'shop_name' => $shop->shop_name,
            'planning_manpower' => $detail->planning_manpower,
            'actual_manpower' => $detail->actual_manpower,
            'pic' => $detail->pic,
            'models' => $models->map(function ($model) use ($downtimes, $notGoods) {
                $downtimeIds = $downtimes->where('production_id', $model->id)->pluck('id')->toArray();
                $notGoodIds = $notGoods->where('production_id', $model->id)->pluck('id')->toArray();
                $modelName = DB::table('mst_models')->where('id', $model->model_id)->value('model_name'); // Fetch model name

                return [
                    'model_id' => $model->model_id,
                    'model_name' => $modelName, // Include model name
                    'id_checksheet_downtimes' => $downtimeIds,
                    'id_checksheet_not_goods' => $notGoodIds,
                    'id_checksheet_productions' => $model->id,
                    'planning_production' => $model->planning_production,
                    'actual_production' => $model->actual_production,
                    'balance' => $model->balance,
                    'downtimes' => $downtimes->where('production_id', $model->id)->map(function ($downtime) use ($modelName) {
                        return [
                            'id' => $downtime->id,
                            'model_name' => $modelName, // Include model name
                            'production_id' => $downtime->production_id,
                            'cause_id' => $downtime->cause_id,
                            'problem' => $downtime->problem,
                            'action' => $downtime->action,
                            'time_from' => $downtime->time_from,
                            'time_to' => $downtime->time_to,
                            'created_at' => $downtime->created_at,
                            'updated_at' => $downtime->updated_at,
                        ];
                    }),
                    'not_goods' => $notGoods->where('production_id', $model->id),
                ];
            }),
        ];

    }
    return view('checksheet.update', compact('header', 'formattedData', 'downtimes', 'notGoods', 'id', 'downtimeCategory'));
}


        public function updateForm(Request $request)
        {

            DB::beginTransaction();

            try {
                $headerId = $request->id;

                // 1. Update checksheet_details
                foreach ($request->shop as $shop) {
                    $shopId = DB::table('mst_shops')->where('shop_name', $shop)->value('id');

                    $detail = DB::table('checksheet_details')
                        ->where('header_id', $headerId)
                        ->where('shop_id', $shopId)
                        ->first();

                    if ($detail) {
                        DB::table('checksheet_details')
                            ->where('id', $detail->id)
                            ->update([
                                'planning_manpower' => $request->man_power_planning[$shop][0],
                                'actual_manpower' => $request->man_power_actual[$shop][0],
                                'pic' => $request->pic[$shop][0],
                                'updated_at' => now(),
                            ]);

                        $detailId = $detail->id;
                    } else {
                        $detailId = DB::table('checksheet_details')->insertGetId([
                            'header_id' => $headerId,
                            'shop_id' => $shopId,
                            'planning_manpower' => $request->man_power_planning[$shop][0],
                            'actual_manpower' => $request->man_power_actual[$shop][0],
                            'pic' => $request->pic[$shop][0],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    // 2. Update checksheet_productions
                    foreach ($request->production_planning as $productionID => $planning) {
                        // Query the checksheet_productions where id is $productionID
                        $production = DB::table('checksheet_productions')
                            ->where('id', $productionID)
                            ->first();

                        // Check if the production record exists
                        if ($production) {
                            $modelId = $production->model_id;

                            DB::table('checksheet_productions')
                                ->where('id', $productionID)
                                ->update([
                                    'planning_production' => $planning[0] ?? null,
                                    'actual_production' => $request->production_actual[$productionID][0] ?? null,
                                    'balance' => $request->production_different[$productionID][0] ?? null,
                                    'updated_at' => now(),
                                ]);

                            $productionId = $productionID;
                        } else {
                            // If the production record does not exist, you may need to determine the modelId from another source
                            // For example, assuming $request contains a model_id field
                            $modelId = $request->model_id[$productionID];

                            $productionId = DB::table('checksheet_productions')->insertGetId([
                                'detail_id' => $detailId,
                                'model_id' => $modelId,
                                'planning_production' => $planning[0] ?? null,
                                'actual_production' => $request->production_actual[$productionID][0] ?? null,
                                'balance' => $request->production_different[$productionID][0] ?? null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                        // 3. Update or insert checksheet_downtimes

foreach ($request->downtime_category as $downtimeId => $categoryIdArray) {
    if ($downtimeId !== null && $categoryIdArray !== null && isset($categoryIdArray[0])) {
        $categoryId = $categoryIdArray[0];

        // Check if downtime record exists
        $existingDowntime = DB::table('checksheet_downtimes')->where('id', $downtimeId)->first();

        if ($existingDowntime) {
            // Update existing downtime record
            DB::table('checksheet_downtimes')
                ->where('id', $downtimeId)
                ->update([
                    'cause_id' => $categoryId,
                    'problem' => $request->cause[$downtimeId][0] ?? null,
                    'action' => $request->action[$downtimeId][0] ?? null,
                    'time_from' => $request->time_from[$downtimeId][0] ?? null,
                    'time_to' => $request->time_until[$downtimeId][0] ?? null,
                    'updated_at' => now(),
                ]);

        } else {
            // Insert new downtime record
            $newDowntimeId = DB::table('checksheet_downtimes')->insertGetId([
                'id' => $downtimeId, // Use downtime ID as production ID
                'production_id' => $downtimeId, // Use downtime ID as production ID
                'cause_id' => $categoryId,
                'problem' => $request->cause[$downtimeId][0] ?? null,
                'action' => $request->action[$downtimeId][0] ?? null,
                'time_from' => $request->time_from[$downtimeId][0] ?? null,
                'time_to' => $request->time_until[$downtimeId][0] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        }
    }
}
        }


                      // Get the common keys between repair and reject arrays
                        $recordIds = array_keys($request->repair);

                        // Loop through the common keys
                        foreach ($recordIds as $recordId) {
                            $repairData = $request->repair[$recordId];
                            $rejectData = $request->reject[$recordId] ?? [0]; // Assuming a default value of 0 if not set

                            $ng = DB::table('checksheet_not_goods')
                                ->where('id', $recordId)
                                ->first();

                            $quantity = ($repairData[0] ?? 0) + ($rejectData[0] ?? 0);

                            if ($ng) {
                                DB::table('checksheet_not_goods')
                                    ->where('id', $recordId)
                                    ->update([
                                        'quantity' => $quantity,
                                        'repair' => $repairData[0] ?? null,
                                        'reject' => $rejectData[0] ?? null,
                                        'total' => $quantity,
                                        'remark' => null,
                                        'updated_at' => now(),
                                    ]);
                            } else {
                                dd('patek');
                                DB::table('checksheet_not_goods')->insert([
                                    'id' => $recordId, // Assuming the ID is provided in the request
                                    'quantity' => $quantity,
                                    'repair' => $repairData[0] ?? null,
                                    'reject' => $rejectData[0] ?? null,
                                    'total' => $quantity,
                                    'remark' => null,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }





                }

                DB::commit();
                return redirect('/checksheet')->with('status', 'Checksheet data updated successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect('/checksheet')->with('failed', 'Failed to update checksheet data. Please try again. Error: ' . $e->getMessage());
            }
        }

        public function exportExcel(Request $request){
             $month = $request->input('month');
            return Excel::download(new ChecksheetExport($month), 'checksheet_export.xlsx');
        }



}
