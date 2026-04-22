<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{

    public function status(Order $order)
    {
        if (!$this->customerOwnsOrder($order)) {
            return redirect()->route('orders.my')
                ->with('error', 'Bạn không có quyền truy cập đơn hàng này.');
        }

        return redirect()->route('orders.detail', $order->id);
    }

    public function momo($orderId)
    {
        $order = Order::findOrFail($orderId);

        if (!$this->customerOwnsOrder($order)) {
            return redirect()->route('orders.my')
                ->with('error', 'Bạn không có quyền truy cập đơn hàng này.');
        }

        return view('pages.payment.momo', compact('order'));
    }


    public function momoProcess($orderId)
    {
        $order = Order::findOrFail($orderId);

        if (!$this->customerOwnsOrder($order)) {
            return redirect()->route('orders.my')
                ->with('error', 'Bạn không có quyền truy cập đơn hàng này.');
        }

        $endpoint = env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create');

        $partnerCode = env('MOMO_PARTNER_CODE', 'MOMOBKUN20180529');
        $accessKey = env('MOMO_ACCESS_KEY', 'klm05TvNBzhg7h7j');
        $secretKey = env('MOMO_SECRET_KEY', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa');

        // Validate amount
        $amount = (int)$order->total_amount;
        if ($amount <= 0) {
            return back()->with('error', 'Số tiền thanh toán không hợp lệ');
        }

        // Use real order ID - must be consistent
        $orderIdMomo = (string)$order->id;
        $requestId = (string)time();

        // Simple order info without Vietnamese characters
        $orderInfo = "DonHang" . $order->id;

        // URL trả về sau khi thanh toán
        $redirectUrl = route('momo.return');
        $ipnUrl = route('momo.return');

        // Extra data - plain base64 of order ID only
        $extraData = base64_encode((string)$order->id);

        // Request type - use captureWallet for payment
        $requestType = "captureWallet";

        // Create signature - parameters must be in alphabetical order
        // IMPORTANT: Do NOT encode URLs in rawHash
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
            "partnerName" => "OcopShop",
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

        if (!$result) {
            return back()->with('error', 'Không nhận được phản hồi từ MoMo. Vui lòng thử lại sau.');
        }

        $jsonResult = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->with('error', 'Lỗi xử lý phản hồi MoMo: ' . json_last_error_msg());
        }

        if (!$jsonResult) {
            return back()->with('error', 'Phản hồi MoMo không hợp lệ');
        }

        $resultCode = $jsonResult['resultCode'] ?? null;

        if ($resultCode == 0 && !empty($jsonResult['payUrl'])) {
            return redirect($jsonResult['payUrl']);
        }

        $errorMsg = $jsonResult['message'] ?? 'Không tạo được thanh toán MoMo';
        $errorCode = $jsonResult['resultCode'] ?? 'unknown';

        // Log error
        \Log::error('MoMo Payment Error', [
            'order_id' => $order->id,
            'error_code' => $errorCode,
            'error_msg' => $errorMsg,
            'response' => $jsonResult
        ]);

        $message = ($errorCode == 98)
            ? "Lỗi MoMo ($errorCode): QR Code tạo không thành công. Vui lòng thử lại sau hoặc chọn phương thức thanh toán khác."
            : "Lỗi MoMo ($errorCode): $errorMsg. Vui lòng thử lại sau.";

        return back()->with('error', $message);
    }

    public function momoTest($orderId)
    {
        return $this->momo($orderId);
    }

    public function momoTestProcess($orderId)
    {
        return $this->momoProcess($orderId);
    }


    private function execPostRequest($url, $data)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($curlError) {
            \Log::error('CURL Error', [
                'url' => $url,
                'error' => $curlError,
                'http_code' => $httpCode
            ]);
        }

        return $result;
    }

    public function momoReturn(Request $request)
    {
        // Decode order ID from extraData
        $orderId = base64_decode($request->extraData ?? '');

        if (!$orderId || !is_numeric($orderId)) {
            \Log::warning('Invalid orderId in momoReturn', [
                'extraData' => $request->extraData ?? 'empty',
                'decoded_id' => $orderId ?? 'null'
            ]);
            return redirect()->route('pages.home')->with('error', 'Đơn hàng không tồn tại');
        }

        $order = Order::find($orderId);
        $payment = null;

        if (!$order) {
            \Log::warning('Order not found in momoReturn', ['order_id' => $orderId]);
            return redirect()->route('pages.home')->with('error', 'Đơn hàng không tồn tại');
        }

        $payment = Payment::where('order_id', $order->id)->first();

        if ($request->resultCode == 0) {
            // Check if this transaction already processed
            $existingPayment = Payment::where('order_id', $order->id)
                ->where('transaction_code', $request->transId)
                ->where('status', 'paid')
                ->first();

            if ($existingPayment) {
                return redirect()->route('orders.detail', $order->id)
                    ->with('order_success', $order->id)
                    ->with('success', 'Thanh toán MoMo thành công');
            }

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

            $order->update(['status' => 'confirmed']);

            $this->createOrderSuccessNotifications($order);

            return redirect()->route('orders.detail', $order->id)
                ->with('order_success', $order->id)
                ->with('success', 'Thanh toán MoMo thành công');
        } else {
            // Payment failed
            if ($payment) {
                $payment->update(['status' => 'failed']);
            } else {
                Payment::create([
                    'order_id' => $order->id,
                    'method' => 'MOMO',
                    'amount' => $request->amount ?? $order->total_amount,
                    'transaction_code' => $request->transId ?? null,
                    'status' => 'failed'
                ]);
            }

            \Log::warning('Payment failed', [
                'order_id' => $order->id,
                'result_code' => $request->resultCode,
                'trans_id' => $request->transId ?? null
            ]);

            return redirect()->route('orders.detail', $order->id)
                ->with('error', 'Thanh toán MoMo thất bại. Vui lòng thử lại.');
        }
    }

    public function vnpay($orderId)
    {
        try {
            $order = Order::findOrFail($orderId);

            if (!$this->customerOwnsOrder($order)) {
                return redirect()->route('orders.my')
                    ->with('error', 'Bạn không có quyền truy cập đơn hàng này.');
            }

            $vnp_TmnCode = env('VNP_TMN_CODE');
            $vnp_HashSecret = env('VNP_HASH_SECRET');

            if (!$vnp_TmnCode || !$vnp_HashSecret) {
                return redirect()->route('orders.detail', $order->id)
                    ->with('error', 'Cấu hình VNPAY không đúng. Vui lòng liên hệ hỗ trợ hoặc chọn phương thức thanh toán khác.');
            }

            $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
            $vnp_Returnurl = route('vnpay.return');

            $vnp_TxnRef = (string)$order->id;
            $vnp_Amount = (int)($order->total_amount * 100);

            if ($vnp_Amount <= 0) {
                return back()->with('error', 'Số tiền thanh toán không hợp lệ');
            }

            $vnp_IpAddr = request()->ip() ?? "127.0.0.1";
            $startTime = date("YmdHis");
            $expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));

            // Build input data theo đúng format VNPay
            $inputData = [
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => $startTime,
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => "vi",
                "vnp_OrderInfo" => "Order",
                "vnp_OrderType" => "billpayment",
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
                "vnp_ExpireDate" => $expire
            ];

            ksort($inputData);

            // Build hash data
            $hashData = "";
            foreach ($inputData as $key => $value) {
                $hashData .= urlencode($key) . "=" . urlencode($value) . "&";
            }
            $hashData = rtrim($hashData, "&");

            $vnp_SecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

            // Build redirect URL
            $query = "";
            foreach ($inputData as $key => $value) {
                $query .= urlencode($key) . "=" . urlencode($value) . "&";
            }
            $query = rtrim($query, "&");

            $vnp_Url = $vnp_Url . "?" . $query . "&vnp_SecureHash=" . $vnp_SecureHash;

            return redirect()->away($vnp_Url);
        } catch (\Exception $e) {
            return redirect()->route('orders.my')
                ->with('error', 'Lỗi thanh toán: ' . $e->getMessage());
        }
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

            $orderId = (int)$request->vnp_TxnRef;
            $order = Order::find($orderId);

            if (!$order) {
                return redirect()->route('pages.home')
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
            } else {
                $data['status'] = 'failed';
            }

            if ($payment) {
                $payment->update($data);
            } else {
                Payment::create(array_merge(['order_id' => $order->id], $data));
            }

            if ($request->vnp_ResponseCode == '00') {
                $order->update(['status' => 'confirmed']);

                $this->createOrderSuccessNotifications($order);

                return redirect()->route('orders.detail', $orderId)
                    ->with('order_success', $orderId)
                    ->with('success', 'Thanh toán VNPAY thành công');
            } else {
                return redirect()->route('orders.detail', $orderId)
                    ->with('error', 'Thanh toán VNPAY thất bại: ' . $this->getVNPayErrorMessage($request->vnp_ResponseCode));
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

        Notification::firstOrCreate(
            [
                'user_id' => $customerUserId,
                'type' => 'order_payment_success',
                'related_id' => $order->id,
            ],
            [
                'title' => 'Thanh toán đơn hàng thành công',
                'content' => 'Thanh toán đơn hàng #' . $order->id . ' thành công',
                'is_read' => false,
            ]
        );
    }

    private function getVNPayErrorMessage($responseCode): string
    {
        $errorMessages = [
            '01' => 'Yêu cầu ngân hàng bị từ chối',
            '02' => 'Thẻ/Tài khoản bị khóa',
            '03' => 'Đã hết hạn xác thực',
            '04' => 'Giao dịch bị từ chối',
            '05' => 'Tài khoản không đủ tiền',
            '06' => 'Ngân hàng không hỗ trợ giao dịch này',
            '07' => 'Giao dịch bị từ chối do vượt quá giới hạn',
            '08' => 'Khách hàng hủy giao dịch',
            '09' => 'Giao dịch không hợp lệ',
            '10' => 'Ngân hàng không phản hồi',
            '24' => 'Mã giao dịch không tồn tại hoặc đã hết hạn',
        ];

        return $errorMessages[$responseCode] ?? "Lỗi thanh toán (mã: $responseCode)";
    }

    private function customerOwnsOrder(Order $order): bool
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'customer') {
            return false;
        }

        return (int) $order->customer_id === (int) $user->id;
    }
}
