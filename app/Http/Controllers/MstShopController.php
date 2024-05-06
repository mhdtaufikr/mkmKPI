<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MstShop;
use App\Models\MstChecksheetSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MstShopController extends Controller
{
    public function index(){
        $item = MstShop::get();
        $sectionName = MstChecksheetSection::get();
        return view('master.shop.index',compact('item','sectionName'));
    }

    public function store(Request $request)
    {
        // Validate the incoming request if necessary

        // Extract the section_id from the request
        $sectionId = $request->section_id;

        // Extract the mechine values from the request
        $mechineNames = $request->mechine;

        try {
            // Start a transaction
            DB::beginTransaction();

            // Loop through each mechine name and store it in the mst_shops table
            foreach ($mechineNames as $mechineName) {
                // Check if the shop_name already exists
                $existingShop = MstShop::where('shop_name', $mechineName)->first();

                if ($existingShop) {
                    // If the shop_name already exists, rollback the transaction
                    DB::rollBack();
                    throw ValidationException::withMessages(['shop_name' => 'Shop name already exists.']);
                }

                // Create a new instance of MstShop model
                $shop = new MstShop();
                $shop->shop_name = $mechineName;
                $shop->section_id = $sectionId;
                $shop->save();
            }

            // If everything is successful, commit the transaction
            DB::commit();

            // Redirect back or return a response as needed
            // For example:
            return redirect()->back()->with('status', 'Shops stored successfully.');
        } catch (\Exception $e) {
            // If an error occurs, rollback the transaction
            DB::rollBack();

            // Redirect back with an error message
            return redirect()->back()->withErrors(['failed' => $e->getMessage()]);
        }
    }

    public function update(Request $request){
        // Validate the incoming request if necessary

        // Find the shop by its ID
        $shop = MstShop::findOrFail($request->id);

        // Update the shop name if it has been changed
        if ($shop->shop_name !== $request->shop) {
            $shop->shop_name = $request->shop;
        }

        // Update the section ID if it has been changed
        if ($shop->section_id != $request->section_id) {
            $shop->section_id = $request->section_id;
        }

        // Check if any changes have been made to the model attributes
        if ($shop->isDirty()) {
            // Save the changes
            $shop->save();

            // Redirect back or return a response as needed
            // For example:
            return redirect()->back()->with('status', 'Shop updated successfully.');
        } else {
            // No changes were made
            // Redirect back or return a response indicating that no updates were performed
            // For example:
            return redirect()->back()->with('failed', 'No changes were made.');
        }
    }


}
