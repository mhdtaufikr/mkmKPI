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
    // Extract data from the request
    $shopIds = array_flip($request->shop); // Flip the array to make it easier to get shop ids
    $modelIds = array_flip($request->model); // Flip the array to make it easier to get model ids
    $downtimeCategories = $request->downtime_category;
    $planningManpower = $request->man_power_planning;
    $actualManpower = $request->man_power_actual;
    $planningProduction = $request->production_planning;

    $actualProduction = $request->production_actual;
    $causes = $request->cause;
    $actions = $request->action;

    // Retrieve model ids from the mst_models table based on the model names
    $models = array_keys($modelIds);
    $models = MstModel::whereIn('model_name', $models)->get();

    // Create a mapping of model names to their corresponding ids
    $modelIds = $models->pluck('id', 'model_name')->toArray();

    // Retrieve the shop IDs based on the shop names
    $shopNames = array_keys($shopIds);
    $shops = MstShop::whereIn('shop_name', $shopNames)->get();

    $shopIds = $shops->pluck('id', 'shop_name')->toArray();

    // Start database transaction
    DB::beginTransaction();

    try {
        // Insert data into the checksheet_details table
        foreach ($shopIds as $shopName => $shopId) {
            foreach ($modelIds as $modelName => $modelId) {
                $detail = new ChecksheetDetail();
                $detail->header_id = $request->id;
                $detail->shop_id = $shopId;
                $detail->model_id = $modelId;
                $detail->planning_manpower = $planningManpower[$shopName][0];
                $detail->actual_manpower = $actualManpower[$shopName][0];
                $detail->planning_production = $planningProduction[0];
                $detail->actual_production = $actualProduction[0];
                $detail->balance = $planningProduction[0] - $actualProduction[0];
                $detail->save();

               // Insert data into the checksheet_downtimes table
                if (isset($downtimeCategories[$modelName])) {
                    foreach ($downtimeCategories[$modelName] as $downtimeCategory) {
                        $downtime = new ChecksheetDowntime();
                        $downtime->detail_id = $detail->id;
                        $downtime->cause_id = $downtimeCategory; // Assuming $downtimeCategory contains the cause_id

                        // Access the cause and action using the correct keys
                        $modelKey = array_search($modelName, array_keys($modelIds));
                        $downtime->problem = $causes[$modelKey]; // Use the model key to access the cause
                        $downtime->action = $actions[$modelKey]; // Use the model key to access the action

                        $downtime->save();
                    }
                }
            }
        }

        // Commit transaction
        DB::commit();
        dd('berhasil');
        // Redirect or return a response indicating success
        return redirect()->back()->with('success', 'Checksheet data saved successfully.');
    } catch (\Exception $e) {
        dd($e);
        // Rollback transaction in case of an error
        DB::rollback();

        // Log the error or return an error response
        return redirect()->back()->with('error', 'Failed to save checksheet data. Please try again.');
    }
}






}
