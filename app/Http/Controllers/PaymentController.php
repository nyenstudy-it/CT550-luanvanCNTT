<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Notification;

class PaymentController extends Controller
{

    public function momo($orderId)
    {
        $order = Order::findOrFail($orderId);

        return view('pages.payment.momo', compact('order'));
    }


    public function momoProcess($orderId)
    {
        $order = Order::findOrFail($orderId);

        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";

        $partnerCode = "MOMOBKUN20180529";
        $accessKey = "klm05TvNBzhg7h7j";
        $secretKey = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa";

        $orderInfo = "Thanh toán đơn hàng #" . $order->id;
        $amount = (int)$order->total_amount;

        $orderIdMomo = time() . "";
        $requestId = time() . "";

        // URL trả về sau khi thanh toán
        $redirectUrl = route('momo.return');
        $ipnUrl = route('momo.return');

        $extraData = base64_encode($order->id);

        // ATM MOMO
        $requestType = "payWithATM";

        $rawHash =
            "accessKey=" . $accessKey .
            "&amount=" . $amount .
            "&extraData=" . $extraData .
            "&ipnUrl=" . $ipnUrl .
            "&orderId=" . $orderIdMomo .
            "&orderInfo=" . $orderInfo .
            "&partnerCode=" . $partnerCode .
            "&redirectUrl=" . $redirectUrl .
            "&requestId=" . $requestId .
            "&requestType=" . $requestType;

        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = [
            "partnerCode" => $partnerCode,
            "partnerName" => "Test",
            "storeId" => "MomoTestStore",
            "requestId" => $requestId,
            "amount" => $amount,
            "orderId" => $orderIdMomo,
            "orderInfo" => $orderInfo,
            "redirectUrl" => $redirectUrl,
            "ipnUrl" => $ipnUrl,
            "lang" => "vi",
            "extraData" => $extraData,
            "requestType" => $requestType,
            "signature" => $signature
        ];

        $result = $this->execPostRequest($endpoint, json_encode($data));

        $jsonResult = json_decode($result, true);

        if (!empty($jsonResult['payUrl'])) {
            return redirect($jsonResult['payUrl']);
        }

        return back()->with('error', 'Không tạo được thanh toán MOMO');
    }


    private function execPostRequest($url, $data)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
    }

    public function momoReturn(Request $request)
    {
        $orderId = base64_decode($request->extraData);
        $order = Order::find($orderId);

        if (!$order) {
            return redirect()->route('orders.my')->with('error', 'Đơn hàng không tồn tại');
        }
        $payment = Payment::where('order_id', $order->id)->first();

        if ($request->resultCode == 0) {

            if ($payment) {
                $payment->update([
                    'method' => 'MOMO',
                    'amount' => $request->amount,
                    'transaction_code' => $request->transId,
                    'status' => 'paid',
                    'paid_at' => now()
                ]);
            } else {
                Payment::create([
                    'order_id' => $order->id,
                    'method' => 'MOMO',
                    'amount' => $request->amount,
                    'transaction_code' => $request->transId,
                    'status' => 'paid',
                    'paid_at' => now()
                ]);
            }

            $this->createOrderSuccessNotifications($order);

            return redirect()->route('orders.detail', $order->id)
                ->with('success', 'Thanh toán MoMo thành công');
        } else {

            if ($payment) {
                $payment->update([
                    'status' => 'failed'
                ]);
            }

            return redirect()->route('orders.detail', $order->id)
                ->with('error', 'Thanh toán thất bại');
        }
    }
    public function vnpay($orderId)
    {
        $order = Order::findOrFail($orderId);

        $vnp_TmnCode = env('VNP_TMN_CODE');
        $vnp_HashSecret = env('VNP_HASH_SECRET');

        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = route('vnpay.return');
        $vnp_apiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
        $apiUrl = "https://sandbox.vnpayment.vn/merchant_webapi/api/transaction";

        $vnp_TxnRef = $order->id . "_" . time();
        $vnp_OrderInfo = "Thanh toan don hang #" . $order->id;
        $vnp_OrderType = "billpayment";
        $vnp_Amount = $order->total_amount * 100;
        $vnp_Locale = "vn";
        $vnp_IpAddr = request()->ip();

        $startTime = date("YmdHis");
        $expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => "Thanh toan GD:" . $vnp_TxnRef,
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $expire
        );


        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);

        $query = "";
        $hashdata = "";

        foreach ($inputData as $key => $value) {
            $hashdata .= urlencode($key) . "=" . urlencode($value) . "&";
            $query .= urlencode($key) . "=" . urlencode($value) . "&";
        }

        $hashdata = rtrim($hashdata, '&');

        $vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

        $vnp_Url = $vnp_Url . "?" . $query . "vnp_SecureHash=" . $vnp_SecureHash;

        return redirect($vnp_Url);
    }


    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = env('VNP_HASH_SECRET');

        $inputData = $request->all();
        $vnp_SecureHash = $inputData['vnp_SecureHash'];

        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);

        ksort($inputData);

        $hashData = "";
        foreach ($inputData as $key => $value) {
            $hashData .= urlencode($key) . "=" . urlencode($value) . "&";
        }

        $hashData = rtrim($hashData, '&');

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash == $vnp_SecureHash) {

            $orderId = explode("_", $request->vnp_TxnRef)[0];
            $order = Order::find($orderId);

            if (!$order) {
                return redirect()->route('orders.my')
                    ->with('error', 'Đơn hàng không tồn tại');
            }

            $payment = Payment::where('order_id', $order->id)->first();

            $data = [
                'method' => 'VNPAY',
                'amount' => $request->vnp_Amount / 100,
                'transaction_code' => $request->vnp_TransactionNo ?? null,
                'status' => ($request->vnp_ResponseCode == '00') ? 'paid' : 'failed',
                'paid_at' => ($request->vnp_ResponseCode == '00') ? now() : null
            ];

            if ($request->vnp_ResponseCode == '00') {
                $data['status'] = 'paid';

                $order->status = 'pending';
                $order->save();
            } else {
                $data['status'] = 'failed';
            }

            if ($payment) {
                $payment->update($data);
            } else {
                Payment::create(array_merge(['order_id' => $order->id], $data));
            }

            if ($request->vnp_ResponseCode == '00') {
                $this->createOrderSuccessNotifications($order);

                return redirect()->route('orders.detail', $orderId)
                    ->with('success', 'Thanh toán VNPAY thành công');
            } else {
                return redirect()->route('orders.detail', $orderId)
                    ->with('error', 'Thanh toán VNPAY thất bại');
            }
        }

        return redirect()->route('orders.my')
            ->with('error', 'Sai chữ ký VNPAY');
    }

    private function createOrderSuccessNotifications(Order $order): void
    {
        if (!$order->customer || !$order->customer->user_id) {
            return;
        }

        $customerUserId = $order->customer->user_id;

        $existsCustomer = Notification::where('type', 'order_success')
            ->where('related_id', $order->id)
            ->where('user_id', $customerUserId)
            ->exists();

        if (!$existsCustomer) {
            Notification::create([
                'user_id' => $customerUserId,
                'type' => 'order_success',
                'title' => 'Đặt hàng thành công',
                'content' => 'Đơn #' . $order->id . ' đã được tạo',
                'related_id' => $order->id,
                'is_read' => false,
            ]);
        }

        $newOrderRecipients = Notification::recipientIdsForGroups(['admin', 'order_staff']);
        Notification::createForRecipients($newOrderRecipients, [
            'type' => 'new_order',
            'title' => 'Có đơn hàng mới',
            'content' => 'Đơn #' . $order->id . ' vừa được tạo',
            'related_id' => $order->id,
        ]);

        $cashierRecipients = Notification::recipientIdsForGroups(['admin', 'cashier']);
        Notification::createForRecipients($cashierRecipients, [
            'type' => 'cashier_stats_update',
            'title' => 'Cập nhật dữ liệu thống kê',
            'content' => 'Đơn #' . $order->id . ' vừa phát sinh và đã cập nhật số liệu bán hàng.',
            'related_id' => $order->id,
        ]);
    }
}
