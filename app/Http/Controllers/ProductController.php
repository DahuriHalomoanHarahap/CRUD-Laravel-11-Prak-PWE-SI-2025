<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->paginate(10);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $suppliers = Supplier::all();
        return view('products.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'image'         => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'title'         => 'required|min:5',
            'description'   => 'required|min:10',
            'price'         => 'required|numeric',
            'stock'         => 'required|numeric',
            'category_id'   => 'required|exists:categories,id',
            'supplier_id'   => 'required|exists:suppliers,id'
        ]);

        $image = $request->file('image');
        $image->storeAs('public/products', $image->hashName());

        Product::create([
            'image'         => $image->hashName(),
            'title'         => $request->title,
            'description'   => $request->description,
            'price'         => $request->price,
            'stock'         => $request->stock,
            'category_id'   => $request->category_id,
            'supplier_id'   => $request->supplier_id   
        ]);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $suppliers = Supplier::all();
        return view('products.edit', compact('product'), compact('categories', 'suppliers'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'image'         => 'image|mimes:jpeg,jpg,png|max:2048',
            'title'         => 'required|min:5',
            'description'   => 'required|min:10',
            'price'         => 'required|numeric',
            'stock'         => 'required|numeric',
            'category_id'   => 'required|exists:categories,id',
            'supplier_id'   => 'required|exists:suppliers,id'
        ]);

        $product = Product::find($id);

        if($request->hasFile('image')) {
            $image = $request->file('image');
            $image->storeAs('public/products', $image->hashName());
        
            Storage::delete('public/products/' . $product->image);

            $product->update([
                'image'         => $image->hashName(),
                'title'         => $request->title,
                'description'   => $request->description,
                'price'         => $request->price,
                'stock'         => $request->stock,
                'category_id'   => $request->category_id,
                'supplier_id'   => $request->supplier_id
            ]);
        } else {
            $product->update([
                'title'         => $request->title,
                'description'   => $request->description,
                'price'         => $request->price,
                'stock'         => $request->stock,
                'category_id'   => $request->category_id,
                'supplier_id'   => $request->supplier_id
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        Storage::delete('public/products/' . $product->image);
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
