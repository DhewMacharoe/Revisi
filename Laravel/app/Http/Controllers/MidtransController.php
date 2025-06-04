<?php

namespace App\Http\Controllers;

use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransController extends Controller
{
    public function __construct()
    {
        // Set your Midtrans server key
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        // Set sanitization on (default)
        Config::$isSanitized = true;
        // Set 3DS transaction for credit card to true
        Config::$is3ds = true;
    }

    public function createTransaction(Request $request)
{
    $request->validate([
        'order_id' => 'required|string',
        'gross_amount' => 'required|numeric',
        'first_name' => 'required|string',
        'last_name' => 'string',
        'email' => 'required|email',
        'items' => 'required|array',
        'items.*.id' => 'required|string',
        'items.*.name' => 'required|string',
        'items.*.price' => 'required|numeric',
        'items.*.quantity' => 'required|numeric',
    ]);

    $params = [
        'transaction_details' => [
            'order_id' => $request->order_id,
            'gross_amount' => $request->gross_amount,
        ],
        'customer_details' => [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
        ],
        'item_details' => $request->items,
        'expiry' => [
            'start_time' => date("Y-m-d H:i:s O"),
            'unit' => 'minute',
            'duration' => 15,
        ],
    ];

    try {
        $snapToken = Snap::getSnapToken($params);
        $redirectUrl = Snap::getSnapUrl($params);

        return response()->json([
            'status' => 'success',
            'snap_token' => $snapToken,
            'redirect_url' => $redirectUrl,
            'order_id' => $request->order_id
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}


    public function checkStatus($orderId)
    {
        try {
            $status = Transaction::status($orderId);
            return response()->json($status);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function handleNotification(Request $request)
    {
        $notificationBody = json_decode($request->getContent(), true);
        $transactionStatus = $notificationBody['transaction_status'];
        $orderId = $notificationBody['order_id'];

        // Handle the transaction status accordingly
        // Update your database based on the transaction status

        return response()->json(['status' => 'success']);
    }
}
