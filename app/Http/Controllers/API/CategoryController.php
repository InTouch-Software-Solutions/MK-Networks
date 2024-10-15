<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function index()
    {
        $data = Category::all();
        return response()->json($data, 200);
    }
     public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'required', // Added more validation rules for images
        ]);

        $category = new Category();
        $category->name = $request->name;

        if ($request->hasFile('image')) {
            $file1 = $request->file('image');
            $ext = $file1->getClientOriginalExtension();
            $filename1 = time() . '_1.' . $ext;
            $file1->move('CategoryImage/', $filename1);
            $category->image = $filename1;
        }

        $category->save();

        return response()->json($category, 200);
    }

    // Update an existing category
   public function update(Request $request, $id)
    {

        $category = Category::find($id);
        $category->name = $request->name;

        if ($request->hasFile('image')) {
            $file1 = $request->file('image');
            $ext = $file1->getClientOriginalExtension();
            $filename1 = time() . '_1.' . $ext;
            $file1->move('CategoryImage/', $file1);
            $category->image = $filename1;
        }

        $category->save();

        return response()->json($category, 200);
    }


    // Delete a category
    public function destroy($id)
    {
        $category = Category::find($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully'], 200);
    }
    
    
    
    
}
