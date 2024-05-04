<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MstChecksheetSection;

class MstSectionController extends Controller
{
    public function index(){
        $item = MstChecksheetSection::get();
        return view('master.section.index',compact('item'));
    }

    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'section' => 'required|string|max:255',
        ]);

        // Check if the section already exists in the database
        $existingSection = MstChecksheetSection::where('section_name', $request->section)->exists();

        if ($existingSection) {
            return redirect()->back()->withErrors(['section' => 'The section already exists.'])->withInput();
        }

        // Create a new instance of MstChecksheetSection model and fill it with request data
        $section = new MstChecksheetSection();
        $section->section_name = $request->section;

        // Save the new section to the database
        $section->save();

        // Redirect back or return a response as needed
        // For example:
        return redirect()->back()->with('success', 'Section created successfully.');
    }

    public function update(Request $request){
        dd($request->all());
    }
}
