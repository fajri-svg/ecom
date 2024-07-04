<?php

namespace App\Http\Controllers\User\Auth;

use App\Models\User;
use App\Events\UserUpdated;
use Illuminate\Http\Request;
use App\Models\BillingAddress;
use App\Models\ShippingAddress;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    function index(): View
    {
        return view('user.user-dashboard.profile');
    }

    // public function update_profile(Request $request): RedirectResponse
    // {
    //     $request->validate([
    //         'first_name' => 'required',
    //         'last_name' => 'required',
    //         'name' => 'required',
    //         'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    //         'email' => 'required|unique:users,email,' . auth()->id(),
    //         'phone' => 'required|unique:users,phone,' . auth()->id(),
    //     ]);

    //     $user = User::findOrFail(auth()->id());

    //     // Handle image upload if a new file is provided
    //     if ($request->hasFile('photo')) {
    //         // Delete the old photo if it exists and is not the default photo
    //         if ($user->photo && Storage::disk('public')->exists('profile/' . $user->photo)) {
    //             Storage::disk('public')->delete('profile/' . $user->photo);
    //         }

    //         // Get the original filename
    //         $originalFilename = $request->file('photo')->getClientOriginalName();

    //         // Generate filename with user ID prefix and original filename
    //         $newFilename = time() . '_' . $originalFilename;

    //         // Store the new photo in 'profile' folder under 'public' disk with the new filename
    //         $request->file('photo')->storeAs('profile', $newFilename, 'public');

    //         // Update the user's photo field with just the filename
    //         $user->photo = $newFilename;
    //     }

    //     // Update user data
    //     $user->name = $request->name;
    //     $user->first_name = $request->first_name;
    //     $user->last_name = $request->last_name;
    //     if ($request->password) {
    //         $user->password = Hash::make($request->password);
    //     }
    //     $user->save();

    //     return redirect()->route('user.dashboard.profile')->with('success', 'Profile updated successfully');
    // }


    function billing_address(): View
    {
        $billing_address = BillingAddress::whereUserId(auth()->id())->first();
        return view('user.user-dashboard.billing-address', compact('billing_address'));
    }

    function update_billing_address(Request $request): RedirectResponse
    {
        $request->validate([
            'address1' => 'required',
            'city' => 'required',
            'zip_code' => 'required',
        ]);
        $billing_address = BillingAddress::whereUserId(auth()->id())->first();

        $billing_address->address1 = $request->address1;
        $billing_address->address2 = $request->address2;
        $billing_address->city = $request->city;
        $billing_address->zip_code = $request->zip_code;
        $billing_address->company = $request->company;
        $billing_address->save();
        return redirect()->route('user.dashboard.billing-address')->with('success', 'Billing address update successfully');
    }

    function shipping_address(): View
    {
        $shipping_address = ShippingAddress::whereUserId(auth()->id())->first();
        return view('user.user-dashboard.billing-address', compact('shipping_address'));
    }

    function update_shipping_address(Request $request): RedirectResponse
    {
        $request->validate([
            'address1' => 'required',
            'city' => 'required',
            'zip_code' => 'required',
        ]);
        $billing_address = ShippingAddress::whereUserId(auth()->id())->first();

        $billing_address->address1 = $request->address1;
        $billing_address->address2 = $request->address2;
        $billing_address->city = $request->city;
        $billing_address->zip_code = $request->zip_code;
        $billing_address->company = $request->company;
        $billing_address->save();
        return redirect()->route('user.dashboard.billing-address')->with('success', 'Shipping address update successfully');
    }

    function logout(): RedirectResponse
    {
        Auth::logout();
        return redirect()->route('user.auth.login')->with('success', 'Logout successfully');
    }

    function deleteAccount(): RedirectResponse
    {
        User::findOrFail(auth()->id())->delete();
        return redirect()->route('user.auth.login')->with('success', 'Account delete success your successfully');
    }
}
