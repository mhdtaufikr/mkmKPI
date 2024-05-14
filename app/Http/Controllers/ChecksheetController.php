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
                // Assuming the checksheet header ID is provided in the request
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
                            DB::table('checksheet_productions')->insert([
                                'detail_id' => $detailId,
                                'model_id' => $modelId,
                                'planning_production' => $planning[0] ?? null,
                                'actual_production' => $request->production_actual[$modelName][0] ?? null,
                                'balance' => $request->production_different[$modelName][0] ?? null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }

                // 3. Insert downtime data into checksheet_downtimes table
                foreach ($request->downtime_category as $modelName => $categories) {
                    $modelId = DB::table('mst_models')->where('model_name', $modelName)->value('id');
                    $productionIds = DB::table('checksheet_productions')->where('model_id', $modelId)->pluck('id');

                    foreach ($categories as $index => $categoryId) {
                        foreach ($productionIds as $productionId) {
                            DB::table('checksheet_downtimes')->insert([
                                'production_id' => $productionId,
                                'cause_id' => $categoryId,
                                'problem' => $request->cause[$modelName][$index] ?? null,
                                'action' => $request->action[$modelName][$index] ?? null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }

                // 4. Insert not good (NG) data into checksheet_not_goods table
                foreach ($request->production_planning as $modelName => $planning) {
                    $modelId = DB::table('mst_models')->where('model_name', $modelName)->value('id');
                    $productionIds = DB::table('checksheet_productions')->where('model_id', $modelId)->pluck('id');

                    foreach ($productionIds as $productionId) {
                        DB::table('checksheet_not_goods')->insert([
                            'production_id' => $productionId,
                            'model_id' => $modelId,
                            'quantity' => $request->repair[$modelName][0] + $request->reject[$modelName][0] ?? null,
                            'repair' => $request->repair[$modelName][0] ?? null,
                            'reject' => $request->reject[$modelName][0] ?? null,
                            'total' => $request->repair[$modelName][0] + $request->reject[$modelName][0] ?? null,
                            'remark' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                DB::commit();

                return redirect('/checksheet')->with('status', 'Checksheet data saved successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect('/checksheet')->with('failed', 'Failed to save checksheet data. Please try again.');
            }
        }
}
