<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\BillingAddress;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class DashboardController extends Controller
{
    function index()
    {
        $pending_orders = Order::whereUserIdAndOrderStatus(auth()->id(), 'pending')->count();
        $progress_orders = Order::whereUserIdAndOrderStatus(auth()->id(), 'progress')->count();
        $delivered_orders = Order::whereUserIdAndOrderStatus(auth()->id(), 'delivered')->count();
        $canceled_orders = Order::whereUserIdAndOrderStatus(auth()->id(), 'canceled')->count();
        $all_orders = Order::whereUserId(auth()->id())->count();
        return view('user.auth.dashboard', compact(
            'pending_orders',
            'progress_orders',
            'all_orders',
            'canceled_orders',
            'delivered_orders'
        ));
    }

    function show_profile()
    {
        return view('user.auth.profile');
    }

    public function update_profile(Request $request)
    {
        $user = User::findOrFail(auth()->id());

        // Handle image upload if a new file is provided
        if ($request->hasFile('photo')) {
            // Get the original filename
            $originalFilename = $request->file('photo')->getClientOriginalName();

            // Generate filename with user ID prefix and original filename
            $newFilename = auth()->id() . '_' . $originalFilename;

            // Store the new photo in 'profile' folder under 'public' disk with the new filename
            $request->file('photo')->storeAs('profile', $newFilename, 'public');

            // Delete the old photo if it exists and is not the default photo
            if ($user->photo && Storage::disk('public')->exists('profile/' . $user->photo)) {
                Storage::disk('public')->delete('profile/' . $user->photo);
            }

            // Update the user's photo field with just the filename
            $user->photo = $newFilename;
        }

        // Update user data
        $user->name = $request->first_name . ' ' . $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return redirect()->route('user.dashboard.profile')->with('success', 'User updated successfully');
    }



    function show_address()
    {
        $address = BillingAddress::whereUserId(auth()->id())->first();
        return view('user.auth.address', compact('address'));
    }

    function update_address(Request $request)
    {
        $request->validate([
            'address1' => 'required|string|max:255',
            'address2' => 'nullable|string|max:255',
            'zip_code' => 'required|string|max:10',
            'city' => 'required|string|max:255',
        ]);
        $address = BillingAddress::whereUserId(auth()->id())->first();
        $address->address1 = $request->address1;
        $address->address2 = $request->address2;
        $address->zip_code = $request->zip_code;
        $address->city = $request->city;
        $address->company = $request->company;
        $address->save();
        return redirect()->route('user.dashboard.address')->with('success', 'User update successfully');
    }

    function logout()
    {
        Auth::logout();
        return redirect()->route('user.login');
    }
}
