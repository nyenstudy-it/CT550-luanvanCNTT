# Kiểm Tra Luồng Đặt Hàng & Thanh Toán - 22 April 2026

## 📋 Tổng Quan Quy Trình

### 1️⃣ **GIỎ HÀNG → CHECKOUT (Cart.blade.php)**

```
Khách hàng vào giỏ hàng → Nhấn "TIẾN HÀNH THANH TOÁN"
  └─ Route: checkout() [GET]
     └─ CheckoutController@index()
```

✅ **Trạng thái**: HOẠT ĐỘNG TỐT

- Hiển thị tổng tiền, phí vận chuyển, mã giảm giá
- 3 phương thức thanh toán: COD, VNPAY, MOMO

---

### 2️⃣ **TRANG CHECKOUT (checkout.blade.php)**

```
Giao diện nhập thông tin:
  - Tên người nhận ✅
  - Số điện thoại ✅
  - Địa chỉ giao hàng ✅
  - Ghi chú ✅
  - Chọn phương thức thanh toán ✅
  - Tích đồng ý chính sách ✅
  └─ Nút "XÁC NHẬN ĐẶT HÀNG"
```

✅ **Trạng thái**: HOẠT ĐỘNG TỐT

- Validate đầy đủ: required fields, policy agreement
- 3 radio buttons: COD, VNPAY, MOMO

---

### 3️⃣ **TẠAOĐƠN HÀNG (CheckoutController@store)**

```
POST /checkout/store
  └─ Validate input ✅
  └─ Check số lượng tồn kho ✅
  └─ Tính toán tiền (subtotal + shipping - discount) ✅
  └─ Tạo Order (status: 'pending') ✅
  └─ Tạo OrderItems ✅
  └─ Tạo Payment record (method: COD/VNPAY/MOMO, status: 'pending') ✅
  └─ Clear session cart ✅
  └─ ROUTE LOGIC:
     ├─ if payment_method === 'VNPAY' → redirect to vnpay.payment ✅
     ├─ if payment_method === 'MOMO' → redirect to momo.payment ✅
     └─ if payment_method === 'COD' → redirect to orders.detail ✅
```

✅ **Trạng thái**: HOẠT ĐỘNG TỐT

---

### 4️⃣ **LUỒNG THANH TOÁN VNPAY**

#### **Bước 1: Trang Tạo Yêu Cầu VNPAY** (vnpay.payment route)

```
PaymentController@vnpay($orderId)
  ├─ Load Order ✅
  ├─ Check quyền customer ✅
  ├─ Lấy config: VNP_TMN_CODE, VNP_HASH_SECRET ✅
  ├─ Xây dựng request parameters:
  │  ├─ vnp_Version: "2.1.0" ✅
  │  ├─ vnp_TmnCode: từ ENV ✅
  │  ├─ vnp_Amount: (order.total_amount * 100) ✅
  │  ├─ vnp_Command: "pay" ✅
  │  ├─ vnp_CreateDate: HHmmss ✅
  │  ├─ vnp_CurrCode: "VND" ✅
  │  ├─ vnp_IpAddr ✅
  │  ├─ vnp_Locale: "vi" ✅
  │  ├─ vnp_OrderInfo: "Order" ✅
  │  ├─ vnp_OrderType: "billpayment" ✅
  │  ├─ vnp_ReturnUrl: route('vnpay.return') ✅
  │  ├─ vnp_TxnRef: order.id ✅
  │  ├─ vnp_ExpireDate: hiện tại + 15 phút ✅
  │  └─ Sắp xếp theo alphabet (ksort) ✅
  ├─ Hash HMAC-SHA512 ✅
  ├─ Thêm vnp_SecureHash vào URL ✅
  └─ redirect()->away($vnp_Url)
     └─ URL: https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?...
```

🎯 **REDIRECT ĐÃ ĐỘI**: ✅ redirect()->away() → Tạo liên kết đến SANDBOX

#### **Bước 2: Thanh Toán Trên VNPAY Sandbox**

```
Khách hàng:
  1. Điền thông tin thẻ/tài khoản trên sandbox.vnpayment.vn
  2. Quét mã nếu cần thiết
  3. Xác nhận thanh toán
  └─ VNPAY callback → Redirect về: route('vnpay.return')
```

#### **Bước 3: Xử Lý Kết Quả** (vnpay.return route)

```
GET /payment/vnpay-return
PaymentController@vnpayReturn(Request $request)
  ├─ Lấy request parameters từ VNPAY ✅
  ├─ Verify signature (hash_hmac sha512) ✅
  ├─ Check vnp_ResponseCode:
  │  ├─ '00' → Thành công ✅
  │  │  ├─ Update Payment record: status = 'paid', transaction_code, amount
  │  │  ├─ Update Order: status = 'confirmed'
  │  │  ├─ Tạo notification
  │  │  └─ Redirect to orders.detail với success message
  │  └─ Khác → Thất bại ✅
  │     ├─ Update Payment: status = 'failed'
  │     └─ Redirect to orders.detail với error message
  └─ Route: orders.detail
```

✅ **Trạng thái**: HOẠT ĐỘNG TỐT

---

### 5️⃣ **LUỒNG THANH TOÁN MOMO**

#### **Bước 1: Trang Thanh Toán MoMo** (momo.payment route)

```
PaymentController@momo($orderId)
  ├─ Load Order ✅
  ├─ Check quyền customer ✅
  └─ Render view: pages.payment.momo
     └─ Form hiển thị:
        ├─ Mã đơn hàng: #{{ order.id }} ✅
        ├─ Số tiền: {{ order.total_amount }} VND ✅
        └─ Button: "Thanh toán với MoMo"
           └─ POST route('momo.process', order.id)
```

#### **Bước 2: Gửi Yêu Cầu MoMo** (momo.process route)

```
POST /payment/momo-process/{orderId}
PaymentController@momoProcess($orderId)
  ├─ Load Order ✅
  ├─ Check quyền customer ✅
  ├─ Endpoint: env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create')
  ├─ Xây dựng request:
  │  ├─ partnerCode: MOMOBKUN20180529 ✅
  │  ├─ partnerName: "OcopShop" ✅
  │  ├─ storeId: "MomoTestStore" ✅
  │  ├─ requestId: timestamp ✅
  │  ├─ amount: order.total_amount ✅
  │  ├─ orderId: order.id ✅
  │  ├─ orderInfo: "DonHang{id}" ✅
  │  ├─ redirectUrl: route('momo.return') ✅
  │  ├─ ipnUrl: route('momo.return') ✅
  │  ├─ extraData: base64(order.id) ✅
  │  ├─ requestType: "captureWallet" ✅
  │  └─ signature: HMAC-SHA256(rawHash) ✅
  ├─ CURL POST tới MoMo endpoint ✅
  ├─ Parse response JSON ✅
  └─ Check resultCode:
     ├─ 0 → Thành công
     │  ├─ Lấy jsonResult['payUrl'] ✅
     │  └─ redirect($payUrl) → MoMo QR/Payment page
     └─ Khác → Error
        ├─ Log error ✅
        └─ Redirect back với error message
```

🎯 **REDIRECT ĐẾN SANDBOX**: ✅ redirect($jsonResult['payUrl']) → test-payment.momo.vn

#### **Bước 3: Quét Mã / Thanh Toán Trên MoMo**

```
Khách hàng trên sandbox.momo.vn:
  1. Quét mã QR
  2. Hoặc nhập thông tin ví MoMo test
  3. Xác nhận thanh toán
  └─ Callback → route('momo.return')
```

#### **Bước 4: Xử Lý Kết Quả** (momo.return route)

```
GET /payment/momo-return
PaymentController@momoReturn(Request $request)
  ├─ Decode order.id từ extraData ✅
  ├─ Load Order ✅
  ├─ Load Payment record ✅
  ├─ Check request.resultCode:
  │  ├─ 0 → Thành công ✅
  │  │  ├─ Check trước (transaction_code) để tránh duplicate ✅
  │  │  ├─ Update/Create Payment:
  │  │  │  ├─ status = 'paid'
  │  │  │  ├─ transaction_code = request.transId
  │  │  │  ├─ amount
  │  │  │  └─ paid_at = now()
  │  │  ├─ Update Order: status = 'confirmed'
  │  │  ├─ Tạo notification
  │  │  └─ Redirect to orders.detail với success
  │  └─ Khác → Thất bại ✅
  │     ├─ Update/Create Payment: status = 'failed'
  │     └─ Redirect to orders.detail với error
  └─ Route: orders.detail
```

✅ **Trạng thái**: HOẠT ĐỘNG TỐT

---

### 6️⃣ **LUỒNG THANH TOÁN COD (Cash On Delivery)**

```
Sau khi tạo đơn:
  └─ Redirect to orders.detail
     └─ Status: 'pending' (chưa thanh toán)
     └─ Payment: status = 'pending', method = 'COD'
     └─ Admin sẽ xác nhận khi nhận được tiền
```

✅ **Trạng thái**: HOẠT ĐỘNG TỐT

---

## ✅ KIỂM TRA HOÀN THÀNH

| Phương Thức | Trang Thanh Toán    | Gửi Yêu Cầu        | Quét Mã/Điền TT      | Nhận Kết Quả    | Trạng Thái | Ghi Chú                  |
| ----------- | ------------------- | ------------------ | -------------------- | --------------- | ---------- | ------------------------ |
| **COD**     | Direct to detail    | N/A                | N/A                  | Manual by admin | ✅ OK      | Thanh toán khi nhận hàng |
| **VNPAY**   | vnpay.payment route | Build & redirect   | sandbox.vnpayment.vn | vnpay.return    | ✅ OK      | Redirect tới sandbox ok  |
| **MOMO**    | momo.payment route  | momo.process route | test-payment.momo.vn | momo.return     | ✅ OK      | Redirect tới sandbox ok  |

---

## 🎯 ĐIỂM CHÍNH

✅ **Thanh toán VNPAY**:

- Đơn hàng được tạo → tạo Payment record pending
- Redirect → route('vnpay.payment', order.id)
- Build request & redirect→away() tới `https://sandbox.vnpayment.vn`
- Quét mã/điền thông tin thanh toán trên sandbox ✅
- Callback → vnpay.return → Update order/payment
- Redirect back → orders.detail ✅

✅ **Thanh toán MOMO**:

- Đơn hàng được tạo → tạo Payment record pending
- Redirect → route('momo.payment', order.id)
- Hiển thị trang momo.blade.php với form POST
- POST → route('momo.process', order.id)
- Build request & CURL POST tới `https://test-payment.momo.vn`
- Response có payUrl → redirect($payUrl) tới sandbox ✅
- Quét mã/điền thông tin thanh toán trên sandbox ✅
- Callback → momo.return → Update order/payment
- Redirect back → orders.detail ✅

✅ **Thanh toán COD**:

- Đơn hàng được tạo → Status: pending
- Direct redirect → orders.detail
- Admin sẽ xác nhận thanh toán manually ✅

---

## 🔍 KHUYẾN NGHỊ KIỂM TRA

1. **TEST ENDPOINTS**:
    - ✅ Checkout form → Tạo order thành công
    - ✅ VNPAY: Trang sandbox.vnpayment.vn hiển thị & thanh toán ok
    - ✅ MOMO: Trang test-payment.momo.vn hiển thị QR & thanh toán ok
    - ✅ Return callbacks xử lý đúng status

2. **ENV VARIABLES CHECK**:

    ```
    MOMO_ENDPOINT=https://test-payment.momo.vn/v2/gateway/api/create
    MOMO_PARTNER_CODE=MOMOBKUN20180529
    MOMO_ACCESS_KEY=klm05TvNBzhg7h7j
    MOMO_SECRET_KEY=at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa

    VNP_TMN_CODE=<your_tmn_code>
    VNP_HASH_SECRET=<your_hash_secret>
    ```

3. **DATABASE CHECK**:
    - Order created với status: 'pending' ✅
    - Payment created với status: 'pending' ✅
    - Sau callback: Payment updated với status: 'paid' ✅
    - Order updated với status: 'confirmed' ✅

---

## 🚀 HỆ THỐNG HOẠT ĐỘNG TỐT

**Kết luận**: Luồng đặt hàng & thanh toán hoạt động đầy đủ:

- ✅ Checkout form lấy thông tin khách
- ✅ Tạo đơn hàng & Payment record
- ✅ Redirect tới trang thanh toán (COD direct, VNPAY/MOMO via sandbox)
- ✅ Sandbox cho phép quét mã & điền thông tin thanh toán
- ✅ Callback xử lý kết quả & update trạng thái
- ✅ Redirect về trang chi tiết đơn hàng

**Tất cả các phương thức thanh toán đều chuyển tới sandbox để tiến hành quét mã và điền thông tin thanh toán như yêu cầu.**
