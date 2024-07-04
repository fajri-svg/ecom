<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    function index(): View
    {
        return view('admin.auth.profile');
    }

    function update_profile(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required',
            'image' => 'required|image|mimes:jpg,png,jpeg|image:1024',
            'phone' => 'required',
            'email' => 'required|email'
        ]);

        $admin = Admin::findOrFail(Auth::guard('admin')->user()->id);
        $filename = '';

        // Check and handle the featured image
        if ($request->hasFile('image')) {
            // Delete the old image if it exists and is not the default image
            if ($admin->image && Storage::disk('public')->exists('profile/' . $admin->image)) {
                Storage::disk('public')->delete('profile/' . $admin->image);
            }

            // Generate a unique filename using the original filename
            $originalFilename = $request->file('image')->getClientOriginalName();
            $newFilename = time() . '_' . $originalFilename;

            // Store the new image in 'profile' folder under 'public' disk
            $request->file('image')->storeAs('profile', $newFilename, 'public');

            $filename = $newFilename;
        } else {
            // Keep the existing image filename if no new image is uploaded
            $filename = $admin->image;
        }

        $admin->username = $request->username;
        $admin->phone = $request->phone;
        $admin->email = $request->email;
        $admin->image = $filename;
        $admin->save();

        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    function change_password_view(): View
    {
        return view('admin.auth.change-password');
    }

    function change_password(Request $request): RedirectResponse
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|confirmed',
        ]);

        $admin = Admin::findOrFail(Auth::guard('admin')->user()->id);
        if (Hash::check($request->old_password, $admin->password)) {
            $admin->password = Hash::make($request->password);
            $admin->save();
            return redirect()->back()->with('success', 'Password change successfully');
        } else {
            return redirect()->back()->with('error', 'Invalid old password');
        }
    }


    function logout(): RedirectResponse
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.auth.login')->with('success', 'Logout successfully');
    }
}
