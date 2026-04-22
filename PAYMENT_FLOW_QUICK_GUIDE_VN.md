# 📱 Hướng Dẫn Luồng Thanh Toán OcopShop - Nhanh Gọn

## 🚀 QUY TRÌNH TỰA HÀNH (5 bước)

### **Bước 1️⃣**: Khách chọn "TIẾN HÀNH THANH TOÁN" từ giỏ hàng

```
Route: GET /checkout
Controller: CheckoutController@index()
Kết quả: Hiển thị form checkout
```

### **Bước 2️⃣**: Khách điền thông tin + chọn phương thức thanh toán

```
Form input:
- Tên người nhận (required)
- Số điện thoại (required)
- Địa chỉ giao hàng (required)
- Ghi chú (optional)
- Phương thức: COD / VNPAY / MOMO
- Tích đồng ý chính sách
```

### **Bước 3️⃣**: Nhấn "XÁC NHẬN ĐẶT HÀNG"

```
Route: POST /checkout/store
Controller: CheckoutController@store()

Backend xử lý:
├─ Validate input
├─ Tạo Order (status: 'pending')
├─ Tạo OrderItems (chi tiết sản phẩm)
├─ Tạo Payment record (status: 'pending')
├─ Deduct inventory
├─ Xóa session cart
└─ Redirect logic:
   ├─ VNPAY → /payment/vnpay/{orderId}
   ├─ MOMO → /payment/momo/{orderId}
   └─ COD → /order/{orderId} (chi tiết đơn)
```

### **Bước 4️⃣**: Thanh toán (tùy PTTT)

#### **🏦 Nếu VNPAY**:

```
Route: GET /payment/vnpay/{orderId}
Action:
1. Build request VNPAY (parameters + hash)
2. redirect()->away(https://sandbox.vnpayment.vn)
3. Khách quét mã / điền thông tin thanh toán
4. Sandbox callback → /payment/vnpay-return
5. Update Payment.status = 'paid', Order.status = 'confirmed'
6. Redirect → /order/{orderId} (chi tiết đơn)
```

#### **📱 Nếu MOMO**:

```
Route: GET /payment/momo/{orderId}
Action:
1. Render form momo.blade.php (hiển thị button)
2. POST /payment/momo-process/{orderId}
3. Build request MOMO (JSON + hash)
4. CURL POST to https://test-payment.momo.vn API
5. Response có payUrl → redirect($payUrl)
6. Khách quét mã / điền thông tin thanh toán
7. Sandbox callback → /payment/momo-return
8. Update Payment.status = 'paid', Order.status = 'confirmed'
9. Redirect → /order/{orderId}
```

#### **💵 Nếu COD**:

```
Action:
1. Direct redirect → /order/{orderId}
2. Order.status = 'pending' (chờ thanh toán)
3. Payment.status = 'pending' (admin xác nhận sau)
```

### **Bước 5️⃣**: Xem kết quả trên trang chi tiết đơn hàng

```
Route: GET /order/{orderId}
Display:
- Mã đơn
- Thông tin sản phẩm
- Địa chỉ giao hàng
- Tổng tiền
- Phương thức thanh toán ✅
- Trạng thái thanh toán ✅
- Nút "Thanh toán lại" nếu thất bại
```

---

## ✅ CÁC ĐIỂM CHÍNH

| Phương Thức | Trang Thanh Toán | Gửi Yêu Cầu        | Quét Mã              | Trạng Thái     |
| ----------- | ---------------- | ------------------ | -------------------- | -------------- |
| **COD**     | N/A              | N/A                | N/A                  | Direct pending |
| **VNPAY**   | Tạo request      | redirect()->away() | sandbox.vnpayment.vn | ✅ Hoạt động   |
| **MOMO**    | momo.blade.php   | momo.process route | test-payment.momo.vn | ✅ Hoạt động   |

---

## 🗄️ DATABASE CHANGES

### Sau bước 3 (Tạo đơn):

```sql
-- Orders
INSERT INTO orders (customer_id, status, total_amount, ...)
VALUES (..., 'pending', ...);

-- OrderItems
INSERT INTO order_items (order_id, product_variant_id, quantity, ...)
VALUES (...);

-- Payments
INSERT INTO payments (order_id, method, status, amount)
VALUES (..., 'VNPAY'|'MOMO'|'COD', 'pending', ...);
```

### Sau thanh toán thành công (Bước 5):

```sql
-- Payments
UPDATE payments SET status='paid', transaction_code=?, paid_at=NOW()
WHERE order_id=?;

-- Orders
UPDATE orders SET status='confirmed' WHERE id=?;

-- Notifications
INSERT INTO notifications (user_id, type, title, ...)
VALUES (..., 'order_payment_success', ...);
```

---

## 🔍 KIỂM TRA NHANH

### ✅ VNPAY

- [ ] Tạo đơn chọn VNPAY
- [ ] Redirect tới sandbox.vnpayment.vn
- [ ] Quét mã / điền thông tin
- [ ] Callback, order.status = 'confirmed'

### ✅ MOMO

- [ ] Tạo đơn chọn MOMO
- [ ] Trang momo.blade.php hiển thị
- [ ] Click button → POST to momo.process
- [ ] Redirect tới test-payment.momo.vn
- [ ] Quét mã / điền thông tin
- [ ] Callback, order.status = 'confirmed'

### ✅ COD

- [ ] Tạo đơn chọn COD
- [ ] Direct → order detail, status = 'pending'
- [ ] Admin xác nhận → status = 'confirmed'

---

## 🐛 COMMON ISSUES & FIX

| Vấn đề                  | Nguyên Nhân           | Cách Fix                                 |
| ----------------------- | --------------------- | ---------------------------------------- |
| VNPAY không redirect    | ENV không set         | Check `VNP_TMN_CODE`, `VNP_HASH_SECRET`  |
| MOMO error 98 (QR fail) | API down / config sai | Check endpoint, credentials              |
| Duplicate payment       | Callback gọi 2 lần    | Kiểm tra `transaction_code` trước update |
| Order không tạo         | Validation fail       | Check required fields                    |
| Inventory âm            | FIFO lỗi              | Run batch recovery logic                 |

---

## 📞 ROUTES QUICK REF

```
[PUBLIC]
GET  /checkout                          → Checkout form
POST /checkout/store                    → Create order + payment

[CUSTOMER ONLY]
GET  /payment/vnpay/{orderId}           → Build & redirect VNPAY
GET  /payment/vnpay-return              → VNPAY callback
GET  /payment/momo/{orderId}            → MOMO form
POST /payment/momo-process/{orderId}    → MOMO API call
GET  /payment/momo-return               → MOMO callback
GET  /order/{orderId}                   → Order detail
POST /order/{orderId}/cancel            → Cancel order
```

---

## 🎯 SUMMARY

✅ **Luồng HOÀN CHỈNH**:

1. Checkout form → Tạo đơn hàng
2. VNPAY / MOMO → Redirect to sandbox
3. Quét mã / Điền thông tin trên sandbox
4. Callback → Update order status
5. Order detail → Xem kết quả

**Tất cả phương thức thanh toán đều chuyển tới sandbox để tiến hành quét mã và điền thông tin thanh toán như yêu cầu.** ✅

**Hệ thống hoạt động TỐT!** 🚀
