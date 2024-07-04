<?php

namespace App\Http\Controllers\User;

use Stripe;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\BillingAddress;
use Barryvdh\DomPDF\Facade\pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    function index()
    {
        if (Cart::whereUserId(auth()->id())->count() <= 0) {
            return redirect()->route('user.shop')->with('error', 'Your cart is empty');
        }
        $billing_address = BillingAddress::whereUserId(auth()->id())->first();
        if (!$billing_address) {
            BillingAddress::create([
                'user_id' => auth()->id(),
                'address1' =>  ' ',
                'address2' => ' ',
                'zip_code' => ' ',
                'company' => ' ',
                'city' => ' ',
                'phone' => ' ',
            ]);
            return view('user.checkout', compact('billing_address'));
        }
        return view('user.checkout', compact('billing_address'));
    }

    function update_billing_address(Request $request)
    {

        $validate = $request->validate([
            'address1' => 'required',
            'address2' => 'required',
            'zip_code' => 'required',
            'city' => 'required',
            'phone' => 'required',
        ]);

        $billing_address = BillingAddress::whereUserId(auth()->id())->first();
        if ($billing_address) {
            BillingAddress::where('user_id', auth()->id())->update([
                'user_id' => auth()->id(),
                'address1' =>  $request->address1,
                'address2' => $request->address2,
                'zip_code' => $request->zip_code,
                'company' => $request->company ?? ' ',
                'city' => $request->city,
                'phone' => $request->phone,
            ]);
        }
        return redirect()->route('user.payment')->with('success', 'billing address add successfully');
    }

    function payment()
    {
        if (Cart::whereUserId(auth()->id())->count() <= 0) {
            return redirect()->route('user.shop')->with('error', 'Your cart is empty');
        }
        $billing_address = BillingAddress::whereUserId(auth()->id())->first();
        return view('user.payment', compact('billing_address'));
    }

    public function redirectToMidtrans(Request $request)
    {
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        // dd(Config::$serverKey, Config::$isProduction, Config::$isSanitized, Config::$is3ds);

        // Ambil data dari keranjang belanja
        $total_amount = Cart::whereUserId(auth()->id())->sum('sub_total');
        $product_ids = Cart::whereUserId(auth()->id())->pluck('product_id');
        $quantities = Cart::whereUserId(auth()->id())->pluck('qty');

        // Generate unique order ID
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $randomString = substr(str_shuffle($characters), 0, 10);

        // Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Detail transaksi untuk dikirim ke Midtrans
        $transaction_details = [
            'order_id' => $randomString,
            'gross_amount' => $total_amount,
        ];

        $transaction = [
            'transaction_details' => $transaction_details,
        ];

        try {
            // Dapatkan Snap Token dari Midtrans
            // $snapToken = Snap::getSnapToken($transaction);

            // Simpan order dan transaksi ke dalam database lokal
            $order = new Order();
            $order->uuid = $randomString;
            $order->transaction_id = $randomString;
            $order->user_id = auth()->id();
            $order->total_amount = $total_amount;
            $order->payment_status = 'unpaid';
            $order->order_status = 'pending';
            $order->product_id = json_encode($product_ids);
            $order->payment_method = 'Midtrans'; // Pastikan payment_method sudah ditangkap sebelumnya
            $order->qty = json_encode($quantities);
            $order->save();

            $transaction = new Transaction();
            $transaction->order_id = $order->uuid;
            $transaction->user_id = auth()->id();
            $transaction->payment_status = 'unpaid';
            $transaction->order_status = 'pending';
            $transaction->total_amount = $total_amount;
            $transaction->save();

            // Redirect ke halaman payment dengan membawa Snap Token
            return view('midtrans', compact('order', 'transaction'));
        } catch (\Exception $e) {
            // Tangani jika terjadi kesalahan
            return back()->with('error', $e->getMessage());
        }
    }


    public function checkoutMidtrans(Request $request)
    {
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        $total_amount = Cart::whereUserId(auth()->id())->sum('sub_total');

        // Generate unique order ID
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $randomString = substr(str_shuffle($characters), 0, 10);

        $transaction_details = [
            'order_id' => $randomString,
            'gross_amount' => $total_amount,
        ];

        $transactions = [
            'transaction_details' => $transaction_details,
        ];

        try {
            $snapToken = Snap::getSnapToken($transactions);

            return response()->json([
                'snapToken' => $snapToken,
                'randomString' => $randomString,
                'total_amount' => $total_amount
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function saveTransaction(Request $request)
    {
        try {
            $productIds = Cart::whereUserId(auth()->id())->pluck('product_id');
            $quantities = Cart::whereUserId(auth()->id())->pluck('qty');

            $order = new Order();
            $order->uuid = $request->order_id;
            $order->transaction_id = json_decode($request->json_callback, true)['transaction_id'];
            $order->user_id = auth()->id();
            $order->total_amount = $request->total_amount;
            $order->payment_status = 'paid';
            $order->order_status = 'pending';
            $order->product_id = json_encode($productIds);
            $order->payment_method = $request->payment_method; // Ambil metode pembayaran dari form
            $order->qty = json_encode($quantities);
            $order->save();

            $transaction = new Transaction();
            $transaction->order_id = $order->uuid;
            $transaction->user_id = auth()->id();
            $transaction->payment_status = 'paid';
            $transaction->order_status = 'pending';
            $transaction->total_amount = $request->total_amount;
            $transaction->save();

            Cart::whereUserId(auth()->id())->delete();

            return redirect()->route('user.order')->with('success', 'Order placed successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }



    public function notificationHandler(Request $request)
    {
        $payload = $request->getContent();
        $notification = json_decode($payload);

        $order_id = $notification->order_id;
        $transaction_status = $notification->transaction_status;
        $fraud_status = $notification->fraud_status;

        $order = Order::where('uuid', $order_id)->first();
        $transaction = Transaction::where('order_id', $order_id)->first();

        if ($transaction_status == 'capture') {
            if ($fraud_status == 'challenge') {
                $order->payment_status = 'challenge';
                $transaction->payment_status = 'challenge';
            } else if ($fraud_status == 'accept') {
                $order->payment_status = 'paid';
                $transaction->payment_status = 'paid';
            }
        } else if ($transaction_status == 'settlement') {
            $order->payment_status = 'paid';
            $transaction->payment_status = 'paid';
        } else if ($transaction_status == 'cancel' || $transaction_status == 'deny' || $transaction_status == 'expire') {
            $order->payment_status = 'unpaid';
            $transaction->payment_status = 'unpaid';
        } else if ($transaction_status == 'pending') {
            $order->payment_status = 'pending';
            $transaction->payment_status = 'pending';
        }

        $order->order_status = $transaction_status;
        $transaction->order_status = $transaction_status;

        $order->save();
        $transaction->save();
    }

    public function complete(Request $request)
    {
        return redirect()->route('user.order')->with('success', 'Order placed successfully');
    }


    function checkout_submit_cash_on_delivery(Request $request)
    {
        $order = new Order();
        $transaction = new Transaction();
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $randomString = substr(str_shuffle($characters), 0, 10);

        $total_amount = Cart::whereUserId(auth()->id())->sum('sub_total');
        $product_ids = Cart::whereUserId(auth()->id())->pluck('product_id');
        $quantities = Cart::whereUserId(auth()->id())->pluck('qty');

        $order->uuid = $randomString;
        $order->transaction_id = 'null';
        $order->user_id = auth()->id();
        $order->total_amount = $total_amount;
        $order->payment_status = 'unpaid';
        $order->order_status = 'pending';
        $order->product_id = json_encode($product_ids);
        $order->payment_method = $request->payment_method;
        $order->qty = json_encode($quantities);

        $order->save();

        // var_dump($order);
        // die();

        $transaction->order_id = $order->uuid;
        $transaction->user_id = auth()->id();
        $transaction->payment_status = 'unpaid';
        $transaction->order_status = 'pending';
        $transaction->total_amount = $total_amount;
        $transaction->save();

        Cart::whereUserId(auth()->id())->delete();

        return redirect()->route('user.order')->with('success', 'Order place successfully');
    }

    function order()
    {
        $orders = Order::whereUserId(auth()->id())->latest()->get();
        return view('user.order', compact('orders'));
    }

    public function stripePost(Request $request)
    {
        $total_amount = Cart::whereUserId(auth()->id())->sum('sub_total');

        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $charge = Stripe\Charge::create([
            "amount" => 100 * $total_amount,
            "currency" => "idr",
            "source" => $request->stripeToken,
            "description" => "Payment Successfully From " . Auth::user()->name,
        ]);
        if ($charge->status) {

            $order = new Order();
            $transaction = new Transaction();
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            $randomString = substr(str_shuffle($characters), 0, 10);

            $product_ids = Cart::whereUserId(auth()->id())->pluck('product_id');
            $quantities = Cart::whereUserId(auth()->id())->pluck('qty');

            $order->uuid = $randomString;
            $order->transaction_id = $charge->id;
            $order->user_id = auth()->id();
            $order->total_amount = $total_amount;
            $order->payment_status = $charge->status == 'succeeded' ? 'paid' : 'unpaid';
            $order->order_status = 'pending';
            $order->product_id = json_encode($product_ids);
            $order->payment_method = $request->payment_method;
            $order->qty = json_encode($quantities);
            $order->save();

            $transaction->order_id = $order->uuid;
            $transaction->user_id = auth()->id();
            $transaction->payment_status = $charge->status == 'succeeded' ? 'paid' : 'unpaid';
            $transaction->order_status = 'pending';
            $transaction->total_amount = $total_amount;
            $transaction->save();

            Cart::whereUserId(auth()->id())->delete();

            return redirect()->route('user.order')->with('success', 'Order place successfully');
        }
    }



    function checkout_submit_back_transfer(Request $request)
    {
        $order = new Order();
        $transaction = new Transaction();
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $randomString = substr(str_shuffle($characters), 0, 10);

        $total_amount = Cart::whereUserId(auth()->id())->sum('sub_total');
        $product_ids = Cart::whereUserId(auth()->id())->pluck('product_id');
        $quantities = Cart::whereUserId(auth()->id())->pluck('qty');

        $order->uuid = $randomString;
        $order->transaction_id = $request->transaction;
        $order->user_id = auth()->id();
        $order->total_amount = $total_amount;
        $order->payment_status = 'unpaid';
        $order->order_status = 'pending';
        $order->product_id = json_encode($product_ids);
        $order->payment_method = $request->payment_method;
        $order->qty = json_encode($quantities);
        $order->save();

        $transaction->order_id = $order->uuid;
        $transaction->user_id = auth()->id();
        $transaction->payment_status = 'unpaid';
        $transaction->order_status = 'pending';
        $transaction->total_amount = $total_amount;
        $transaction->save();

        Cart::whereUserId(auth()->id())->delete();

        return redirect()->route('user.order')->with('success', 'Order place successfully');
    }
    public function invoice($uuid)
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();
        $user = $order->users;
        $products = json_decode($order->product_id);
        $billing_address = BillingAddress::whereUserId($user->id)->first();

        return view('user.invoice', compact('order', 'user', 'products', 'billing_address'));
    }

    public function downloadInvoicePDF($uuid)
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();
        $user = $order->users;
        $products = json_decode($order->product_id);

        // Log data for debugging
        Log::info('Order:', ['order' => $order]);
        Log::info('User:', ['user' => $user]);
        Log::info('Products:', ['products' => $products]);

        $pdf = pdf::loadView('user.invoice_pdf', compact('order', 'user', 'products'));
        // dd($pdf);
        // var_dump($pdf);
        // die();

        $filename = 'invoice_' . $uuid . '.pdf';
        // Save PDF to the public/invoices folder
        $pdf->save(public_path('invoices/' . $filename));

        // Download the saved PDF
        return response()->download(public_path('invoices/' . $filename))->deleteFileAfterSend(true);
    }

    public function cancel(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Hanya batalkan jika belum dibatalkan sebelumnya
        if ($order->order_status === 'pending' || $order->order_status === 'cancelled') {
            $order->update(['order_status' => 'cancelled']);
            return response()->json(['message' => 'Order cancelled successfully']);
        } else {
            return response()->json(['message' => 'Order cannot be cancelled.'], 400);
        }
    }
}
