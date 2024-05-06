<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MstShop;
use App\Models\MstModel;
use Illuminate\Support\Facades\DB;

class MstModelController extends Controller
{
    public function index(){
        $item = MstModel::get();
        $shopName = MstShop::get();
        return view('master.model.index',compact('item','shopName'));
    }

    public function store(Request $request)
    {
        // Validate the incoming request if necessary

        // Extract the section_id from the request
        $shopId = $request->shop_id;

        // Extract the models array from the request
        $modelNames = $request->model;

        // If shop ID is not found, handle the situation accordingly

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Loop through each model name
            foreach ($modelNames as $modelName) {
                // Check if the model_name already exists within the same shop
                $existingModel = MstModel::where('model_name', $modelName)
                                        ->where('shop_id', $shopId)
                                        ->first();

                // If the model_name already exists within the same shop, rollback the transaction and return a response
                if ($existingModel) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Model "' . $modelName . '" already exists within the same shop.');
                }
                // Create a new instance of MstModel model
                $model = new MstModel();
                $model->model_name = $modelName;
                $model->shop_id = $shopId;
                $model->save();
            }

            // Commit the transaction if all models were saved successfully
            DB::commit();

            // Redirect back or return a response as needed
            // For example:
            return redirect()->back()->with('status', 'Models stored successfully.');
        } catch (\Exception $e) {
            dd($e);
            // If an exception occurs, rollback the transaction and return an error response
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to store models.');
        }
    }

        public function update(Request $request)
    {
        // Find the model instance by its ID
        $model = MstModel::findOrFail($request->id);

        // Update the model_name and shop_id fields if they are different from the request data
        if ($model->model_name != $request->shop) {
            $model->model_name = $request->shop;
        }

        if ($model->shop_id != $request->shop_id) {
            $model->shop_id = $request->shop_id;
        }

        // Check if any changes were made using isDirty() method
        if ($model->isDirty()) {
            // Save the changes
            $model->save();
            return redirect()->back()->with('status', 'Model updated successfully.');
        } else {
            // No changes were made
            return redirect()->back()->with('failed', 'No changes were made.');
        }
    }


}
