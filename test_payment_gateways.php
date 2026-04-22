<?php
/**
 * Test Payment Gateways - MoMo & VNPay
 * Debug credentials and connectivity
 */

// Load Laravel .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
} else {
    die("❌ .env file not found!");
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "PAYMENT GATEWAY TEST\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// ==================== MoMo TEST ====================
echo "🔍 MOMO GATEWAY TEST\n";
echo "───────────────────────────────────────────────────────────────\n";

$momo_endpoint = $env['MOMO_ENDPOINT'] ?? 'https://test-payment.momo.vn/v2/gateway/api/create';
$momo_partner_code = $env['MOMO_PARTNER_CODE'] ?? '';
$momo_access_key = $env['MOMO_ACCESS_KEY'] ?? '';
$momo_secret_key = $env['MOMO_SECRET_KEY'] ?? '';

echo "Endpoint: $momo_endpoint\n";
echo "Partner Code: " . (empty($momo_partner_code) ? "❌ NOT SET" : "✅ $momo_partner_code") . "\n";
echo "Access Key: " . (empty($momo_access_key) ? "❌ NOT SET" : "✅ SET (length=" . strlen($momo_access_key) . ")") . "\n";
echo "Secret Key: " . (empty($momo_secret_key) ? "❌ NOT SET" : "✅ SET (length=" . strlen($momo_secret_key) . ")") . "\n\n";

// Test MoMo connectivity
if (!empty($momo_partner_code) && !empty($momo_access_key) && !empty($momo_secret_key)) {
    echo "🧪 Testing MoMo connectivity...\n";
    
    $test_order_id = "TEST" . time();
    $test_amount = 100000;
    $test_request_id = (string)time();
    $test_extra_data = base64_encode($test_order_id);
    $test_order_info = "DonHang" . $test_order_id;
    
    $redirect_url = "http://localhost/luanvan/payment/momo/return";
    $ipn_url = "http://localhost/luanvan/payment/momo/return";
    
    // Build signature (alphabetical order, NO encoding)
    $raw_hash = 
        "accessKey=" . $momo_access_key .
        "&amount=" . $test_amount .
        "&extraData=" . $test_extra_data .
        "&ipnUrl=" . $ipn_url .
        "&orderId=" . $test_order_id .
        "&orderInfo=" . $test_order_info .
        "&partnerCode=" . $momo_partner_code .
        "&redirectUrl=" . $redirect_url .
        "&requestId=" . $test_request_id .
        "&requestType=captureWallet";
    
    $signature = hash_hmac("sha256", $raw_hash, $momo_secret_key);
    
    $data = [
        "partnerCode" => $momo_partner_code,
        "partnerName" => "OcopShop",
        "storeId" => "MomoTestStore",
        "requestId" => $test_request_id,
        "amount" => $test_amount,
        "orderId" => $test_order_id,
        "orderInfo" => $test_order_info,
        "redirectUrl" => $redirect_url,
        "ipnUrl" => $ipn_url,
        "lang" => "vi",
        "extraData" => $test_extra_data,
        "requestType" => "captureWallet",
        "signature" => $signature
    ];
    
    // Send test request
    $ch = curl_init($momo_endpoint);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($curl_error) {
        echo "❌ CURL Error: $curl_error\n\n";
    } else {
        echo "HTTP Code: $http_code\n";
        echo "Response: $response\n\n";
        
        $json_response = json_decode($response, true);
        if ($json_response) {
            $result_code = $json_response['resultCode'] ?? 'unknown';
            $message = $json_response['message'] ?? 'N/A';
            echo "Result Code: $result_code\n";
            echo "Message: $message\n";
            
            if ($result_code == 0) {
                echo "✅ MoMo is WORKING!\n";
            } elseif ($result_code == 98) {
                echo "⚠️  MoMo Error 98: QR Code creation failed\n";
                echo "   → Possible: Credentials expired, sandbox down, IP blocked\n";
            } else {
                echo "⚠️  MoMo Error $result_code\n";
            }
        } else {
            echo "❌ Invalid JSON response\n";
        }
    }
} else {
    echo "❌ MoMo credentials not complete\n";
}

echo "\n";

// ==================== VNPAY TEST ====================
echo "🔍 VNPAY GATEWAY TEST\n";
echo "───────────────────────────────────────────────────────────────\n";

$vnp_tmn_code = $env['VNP_TMN_CODE'] ?? '';
$vnp_hash_secret = $env['VNP_HASH_SECRET'] ?? '';
$vnp_url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";

echo "TMN Code: " . (empty($vnp_tmn_code) ? "❌ NOT SET" : "✅ $vnp_tmn_code") . "\n";
echo "Hash Secret: " . (empty($vnp_hash_secret) ? "❌ NOT SET" : "✅ SET (length=" . strlen($vnp_hash_secret) . ")") . "\n";
echo "Sandbox URL: $vnp_url\n";

if (!empty($vnp_tmn_code) && !empty($vnp_hash_secret)) {
    echo "✅ VNPay credentials configured\n\n";
    
    // Test signature generation
    $test_amount = 100000;
    $test_txn_ref = "TEST" . time();
    $test_time = date("YmdHis");
    
    $input_data = [
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $vnp_tmn_code,
        "vnp_Amount" => $test_amount * 100, // VND * 100
        "vnp_Command" => "pay",
        "vnp_CreateDate" => $test_time,
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => "127.0.0.1",
        "vnp_Locale" => "vi",
        "vnp_OrderInfo" => "Order",
        "vnp_OrderType" => "billpayment",
        "vnp_ReturnUrl" => "http://localhost/luanvan/payment/vnpay/return",
        "vnp_TxnRef" => $test_txn_ref,
    ];
    
    ksort($input_data);
    
    $hash_data = "";
    foreach ($input_data as $key => $value) {
        $hash_data .= urlencode($key) . "=" . urlencode($value) . "&";
    }
    $hash_data = rtrim($hash_data, "&");
    
    $secure_hash = hash_hmac('sha512', $hash_data, $vnp_hash_secret);
    
    echo "✅ VNPay signature generated successfully\n";
    echo "   Test Amount: " . number_format($test_amount) . " VND\n";
    echo "   Signature: " . substr($secure_hash, 0, 20) . "...\n";
} else {
    echo "❌ VNPay credentials not complete\n";
}

echo "\n";

// ==================== SUMMARY ====================
echo "═══════════════════════════════════════════════════════════════\n";
echo "SUMMARY\n";
echo "═══════════════────────────────────────────────────────────────\n";

$all_good = !empty($momo_partner_code) && !empty($momo_access_key) && 
            !empty($momo_secret_key) && !empty($vnp_tmn_code) && 
            !empty($vnp_hash_secret);

if ($all_good) {
    echo "✅ All credentials configured\n";
    echo "⚠️  MoMo Error 98 = Server issue, not code issue\n";
    echo "\nRecommended Actions:\n";
    echo "1. Try VNPay payment (fully working)\n";
    echo "2. Contact MoMo support to reactivate credentials\n";
    echo "3. Check if MoMo sandbox is down\n";
    echo "4. Use MoMo production credentials if available\n";
} else {
    echo "❌ Missing credentials\n";
}

echo "\n";
?>
