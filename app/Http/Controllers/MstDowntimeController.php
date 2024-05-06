<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MstDowntimeCause;
use App\Models\Dropdown;

class MstDowntimeController extends Controller
{
    public function index(){
        $item = MstDowntimeCause::get();
        $category = Dropdown::where('category','Downtime')->get();
        return view('master.downtime.index',compact('item','category'));
    }

        public function store(Request $request)
    {
        // Validate the incoming request if necessary

        // Extract the category and pic values from the request
        $category = $request->shop_id;
        $pic = $request->pic;

        // Check if the category and pic combination already exists
        $existingCause = MstDowntimeCause::where('category', $category)->where('pic', $pic)->first();

        // If the cause already exists, return an error response
        if ($existingCause) {
            return redirect()->back()->with('failed', 'Downtime cause already exists.');
        }

        // Create a new instance of MstDowntimeCause model
        $cause = new MstDowntimeCause();
        $cause->category = $category;
        $cause->pic = $pic;
        $cause->save();

        // Redirect back or return a response as needed
        // For example:
        return redirect()->back()->with('status', 'Downtime cause stored successfully.');
    }


public function update(Request $request)
{
    // Validate the incoming request if necessary

    // Find the downtime cause by its ID
    $cause = MstDowntimeCause::findOrFail($request->id);

    // Extract the new category and pic values from the request
    $newCategory = $request->shop_id;
    $newPic = $request->pic;

    // Check if any changes were made
    if ($cause->category != $newCategory || $cause->pic != $newPic) {
        // Update the cause attributes
        $cause->category = $newCategory;
        $cause->pic = $newPic;

        // Save the changes to the database
        $cause->save();

        // Redirect back or return a response as needed
        // For example:
        return redirect()->back()->with('status', 'Downtime cause updated successfully.');
    } else {
        // If no changes were made, redirect back without performing any action
        // For example:
        return redirect()->back()->with('failed', 'No changes were made.');
    }
}

}
