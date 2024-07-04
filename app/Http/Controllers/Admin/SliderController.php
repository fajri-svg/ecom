<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SliderController extends Controller
{
    function index(): View
    {
        $sliders = Slider::latest()->get();
        return view('admin.slider.index', compact('sliders'));
    }
    function create(): View
    {
        return view('admin.slider.create');
    }
    function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required',
            'details' => 'required',
            'url' => 'required',
            'image' => 'required|image|mimes:jpg,png,jpeg|max:2096',
        ]);

        $filename = '';
        // Memeriksa apakah ada file yang diunggah
        if ($request->hasFile('image')) {
            // Mendapatkan nama file asli
            $originalFilename = $request->file('image')->getClientOriginalName();

            // Menambahkan timestamp untuk menghindari nama file yang sama
            $newFilename = time() . '_' . $originalFilename;

            // Menyimpan file dengan nama file asli di folder 'service' di dalam disk 'public'
            $request->file('image')->storeAs('slider', $newFilename, 'public');

            $filename = $newFilename;
        }

        $slider = new Slider();
        $slider->image = $filename;
        $slider->url = $request->url;
        $slider->title = $request->title;
        $slider->details = $request->details;
        $slider->save();
        return redirect()->route('admin.slider.index')->with('success', 'Slider add successfully');
    }
    function edit($id): View
    {
        $slider = Slider::findOrFail($id);
        return view('admin.slider.update', compact('slider'));
    }
    function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'title' => 'required',
            'details' => 'required',
            'url' => 'required',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2096',
        ]);

        $slider = Slider::findOrFail($id);
        // Memeriksa apakah ada file yang diunggah
        if ($request->hasFile('image')) {
            // Mendapatkan nama file asli
            $originalFilename = $request->file('image')->getClientOriginalName();

            // Menambahkan timestamp untuk menghindari nama file yang sama
            $newFilename = time() . '_' . $originalFilename;

            // Menyimpan file dengan nama file asli di folder 'service' di dalam disk 'public'
            $request->file('image')->storeAs('slider', $newFilename, 'public');

            $filename = $newFilename;
        }

        $slider->image = $filename;
        $slider->url = $request->url;
        $slider->title = $request->title;
        $slider->details = $request->details;
        $slider->save();
        return redirect()->route('admin.slider.index')->with('success', 'Slider Update successfully');
    }
    function delete($id): RedirectResponse
    {
        $slider = Slider::findOrFail($id);
        $path = public_path('storage\\' . $slider->image);
        if (File::exists($path)) {
            File::delete($path);
        }
        $slider->delete();
        return redirect()->route('admin.slider.index')->with('success', 'Slider Delete successfully');
    }
}
