<?php

namespace App\Http\Controllers\Admin;

use Str;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Models\ChildCategory;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    function index(): View
    {
        $products = Product::latest()->get();
        return view('admin.product.index', compact('products'));
    }

    public function create(): View
    {
        $categories = Category::latest()->get();
        $product = Product::latest()->first();
        $brands = Brand::latest()->get();
        $childs = ChildCategory::latest()->get();
        return view('admin.product.create', compact('categories', 'brands', 'childs', 'product'));
    }

    public function store(Request $request): RedirectResponse
    {
        $product = Product::latest()->first();
        $request->validate([
            'name' => 'required|unique:products',
            'featured_image' => 'image|mimes:jpg,png,jpeg|max:2096',
            'short_description' => 'required',
            'description' => 'required',
            'tags' => 'required',
            // 'specifications' => 'required',
            'meta_keyword' => 'nullable',
            'meta_description' => 'nullable|string',
            'current_price' => 'required',
            'previous_price' => '',
            'cat_id' => 'required|exists:categories,id',
            'sub_cat_id' => 'required|exists:sub_categories,id',
            'child_cat_id' => 'required|exists:child_categories,id',
            'brand_id' => 'required|exists:brands,id',
            'total_stock' => 'required',
        ]);

        // Check and handle the featured image
        if ($request->hasFile('featured_image')) {
            // Delete the old image if it exists and is not the default image
            if ($product->featured_image && Storage::disk('public')->exists('product/' . $product->featured_image)) {
                Storage::disk('public')->delete('product/' . $product->featured_image);
            }

            // Generate a unique filename
            $newFilename = time() . '_' . $request->file('featured_image')->getClientOriginalName();

            // Store the new image in 'uploads/product' and save the filename
            $request->file('featured_image')->storeAs('product', $newFilename, 'public');

            $filename = $newFilename;
        }

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->featured_image = $filename; // Simpan nama file gambar
        $product->images = json_encode(['', '']);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->tags = $request->tags;
        $product->specifications = json_encode($request->specifications);
        $product->meta_keyword = json_encode($request->meta_keyword);
        $product->meta_description = $request->meta_description;
        $product->current_price = $request->current_price;
        $product->previous_price = $request->previous_price;
        $product->cat_id = $request->cat_id;
        $product->sub_cat_id = $request->sub_cat_id;
        $product->child_cat_id = $request->child_cat_id;
        $product->brand_id = $request->brand_id;
        $product->total_stock = $request->total_stock;
        $product->save();

        return redirect()->route('admin.product.index')->with('success', 'Product Add successfully');
    }
    function edit($id): View
    {
        $product = Product::findOrFail($id);
        $categories = Category::latest()->get();
        $subchilds = SubCategory::latest()->get();
        $childs = ChildCategory::latest()->get();
        $brands = Brand::latest()->get();
        return view('admin.product.update', compact('product', 'brands', 'categories', 'childs', 'subchilds'));
    }

    function update(Request $request, $id): RedirectResponse
    {
        $product = Product::findOrFail($id);
        $request->validate([
            'name' => 'required',
            'featured_image' => 'image|mimes:jpg,png,jpeg|max:2096',
            'short_description' => 'required',
            'description' => 'required',
            'tags' => 'required',
            // 'specifications' => 'required',
            'meta_keyword' => 'nullable',
            'meta_description' => 'nullable|string',
            'current_price' => 'required',
            'previous_price' => '',
            'cat_id' => 'required|exists:categories,id',
            'sub_cat_id' => 'required|exists:sub_categories,id',
            'child_cat_id' => 'required|exists:child_categories,id',
            'brand_id' => 'required|exists:brands,id',
            'total_stock' => 'required',
        ]);

        // Check and handle the featured image
        if ($request->file('featured_image')) {
            // Delete the old image if it exists and is not the default image
            if ($product->featured_image && Storage::disk('public')->exists('product/' . $product->featured_image)) {
                Storage::disk('public')->delete('product/' . $product->featured_image);
            }

            // Generate a unique filename
            $newFilename = time() . '_' . $request->file('featured_image')->getClientOriginalName();

            // Store the new image in 'product' folder under 'public' disk
            $request->file('featured_image')->storeAs('product', $newFilename, 'public');

            // Update filename in database
            $product->featured_image = $newFilename;
        } else {
            // Keep the existing image filename if no new image is uploaded
            $newFilename = $product->featured_image;
        }


        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        // $product->featured_image = $newFilename;
        $product->images = json_encode(['', '']);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->tags = $request->tags;
        $product->specifications = json_encode($request->specifications);
        $product->meta_keyword = json_encode($request->meta_keyword);
        $product->meta_description = $request->meta_description;
        $product->current_price = $request->current_price;
        $product->previous_price = $request->previous_price;
        $product->cat_id = $request->cat_id;
        $product->sub_cat_id = $request->sub_cat_id;
        $product->child_cat_id = $request->child_cat_id;
        $product->brand_id = $request->brand_id;
        $product->total_stock = $request->total_stock;
        $product->save();

        return redirect()->route('admin.product.index')->with('success', 'Product Update successfully');
    }

    function delete($id): RedirectResponse
    {
        $product = Product::findOrFail($id);
        $featured_image = public_path('storage\\' . $product->featured_image);
        foreach ($product->images as $img) {
            $images = public_path('storage\\' . $img);
            if (File::exists($images)) {
                File::delete($images);
            }
        }
        if (File::exists($featured_image)) {
            File::delete($featured_image);
        }
        $product->delete();
        return redirect()->route('admin.product.index')->with('success', 'Product delete successfully');
    }

    function get_sub_category(Request $request)
    {
        $sub_categories = SubCategory::where('cat_id', $request->cat_id)->latest()->get();
        $output = "";
        if (count($sub_categories) > 0) {
            foreach ($sub_categories as $category) {
                $output .= "<option value='{$category->id}'>{$category->name}</option>";
            }
        } else {
            $output .= "<option value=''>Record not found</option>";
        }

        echo $output;
    }


    function get_child_category(Request $request)
    {
        $child_categories = ChildCategory::where('sub_cat_id', $request->sub_cat_id)->latest()->get();
        $output = "";
        if (count($child_categories) > 0) {
            foreach ($child_categories as $category) {
                $output .= "<option value='{$category->id}'>{$category->name}</option>";
            }
        } else {
            $output .= "<option value=''>Record not found</option>";
        }

        echo $output;
    }

    function update_status($id): RedirectResponse
    {
        $product = Product::findOrFail($id);
        if ($product->status == 1) {
            $product->status = 0;
            $product->save();

            return redirect()->route('admin.product.index')->with('success', 'Product Status un-active successfully');
        } else {
            $product->status = 1;
            $product->save();
            return redirect()->route('admin.product.index')->with('success', 'Product Status active successfully');
        }
    }
}
