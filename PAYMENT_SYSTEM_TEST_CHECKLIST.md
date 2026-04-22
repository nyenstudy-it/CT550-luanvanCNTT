# 🧪 Payment System Test Checklist - April 22, 2026

## 📋 Before Testing

- [ ] ENV variables configured correctly:
    ```
    MOMO_ENDPOINT=https://test-payment.momo.vn/v2/gateway/api/create
    MOMO_PARTNER_CODE=MOMOBKUN20180529
    MOMO_ACCESS_KEY=klm05TvNBzhg7h7j
    MOMO_SECRET_KEY=at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa
    VNP_TMN_CODE=<your_tmn_code>
    VNP_HASH_SECRET=<your_hash_secret>
    ```
- [ ] Database ready (migrations run)
- [ ] Server running (php artisan serve)
- [ ] Browser ready for testing

---

## 🛒 TEST 1: CHECKOUT FORM

### 1.1 Load Checkout Page

- [ ] Click "TIẾN HÀNH THANH TOÁN" from cart
- [ ] Checkout page loads at `/checkout`
- [ ] Form displays correctly with:
    - [ ] Receiver name input
    - [ ] Phone number input
    - [ ] Shipping address textarea
    - [ ] Note textarea
    - [ ] Cart items summary (products, qty, price)
    - [ ] Total amount displayed
    - [ ] Shipping fee displayed
    - [ ] Discount code (if applicable)
    - [ ] Final total calculated correctly

### 1.2 Payment Methods Displayed

- [ ] Radio button: "💵 Thanh toán khi nhận hàng (COD)"
- [ ] Radio button: "🏦 Thanh toán qua VNPAY"
- [ ] Radio button: "📱 Thanh toán ví MoMo"
- [ ] COD is selected by default

### 1.3 Validation

- [ ] Try submit without receiver name → Error
- [ ] Try submit without phone → Error
- [ ] Try submit without address → Error
- [ ] Try submit without agreeing to policy → Error
- [ ] Tick policy checkbox → "XÁC NHẬN ĐẶT HÀNG" button clickable

### 1.4 Order Creation

- [ ] Select payment method (any)
- [ ] Fill all required fields
- [ ] Agree to policy
- [ ] Click "XÁC NHẬN ĐẶT HÀNG"
- [ ] Form submits to POST `/checkout/store`
- [ ] Check database:
    ```sql
    SELECT id, customer_id, status, total_amount FROM orders ORDER BY id DESC LIMIT 1;
    -- Should see: id, customer_id set, status='pending', total_amount correct
    ```

---

## 💳 TEST 2: VNPAY PAYMENT

### 2.1 Checkout with VNPAY

- [ ] Go back to checkout page
- [ ] Add items to cart again
- [ ] Select "🏦 Thanh toán qua VNPAY"
- [ ] Fill form completely
- [ ] Click "XÁC NHẬN ĐẶT HÀNG"
- [ ] Redirects to `/payment/vnpay/{orderId}`
- [ ] Check database for Payment record:
    ```sql
    SELECT id, order_id, method, status FROM payments ORDER BY id DESC LIMIT 1;
    -- Should see: method='VNPAY', status='pending'
    ```

### 2.2 VNPAY Redirect to Sandbox

- [ ] Page loads and builds request
- [ ] Browser redirects to `https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?...`
    - [ ] Check URL contains: vnp_Amount, vnp_TmnCode, vnp_OrderInfo, vnp_SecureHash
    - [ ] URL is EXTERNAL (sandbox.vnpayment.vn)
- [ ] VNPAY Sandbox page loads successfully
- [ ] Display order info:
    - [ ] Amount (with proper formatting)
    - [ ] Order reference
    - [ ] Merchant name "OcopShop" visible

### 2.3 Payment on Sandbox

- [ ] On sandbox, fill test card info:
    - Bank code: ONLINE
    - Card number: 9704198526191432198 (test)
    - Exp: 07/15
    - CVV: 123
- [ ] Complete payment
- [ ] Get success confirmation on sandbox

### 2.4 Callback & Return

- [ ] Browser redirects back to `/payment/vnpay-return?vnp_Amount=...&vnp_ResponseCode=00&...`
- [ ] Page shows "Thanh toán VNPAY thành công"
- [ ] Redirects to order detail page
- [ ] Check database:
    ```sql
    SELECT id, order_id, status, transaction_code FROM payments
    WHERE method='VNPAY' ORDER BY id DESC LIMIT 1;
    -- Should see: status='paid', transaction_code set
    ```
    ```sql
    SELECT id, status FROM orders WHERE id=<orderId>;
    -- Should see: status='confirmed'
    ```
- [ ] Order detail page shows:
    - [ ] Payment status: "Đã thanh toán"
    - [ ] Payment method: "VNPay"
    - [ ] Transaction code displayed
    - [ ] Amount displayed

### 2.5 Test Payment Failure

- [ ] Create another order, select VNPAY
- [ ] On sandbox, reject the payment
- [ ] Browser returns with `vnp_ResponseCode != 00`
- [ ] Page shows "Thanh toán VNPAY thất bại"
- [ ] Database shows Payment.status = 'failed'
- [ ] Order status remains 'pending'

---

## 📱 TEST 3: MOMO PAYMENT

### 3.1 Checkout with MOMO

- [ ] Go back to checkout page
- [ ] Add items to cart again
- [ ] Select "📱 Thanh toán ví MoMo"
- [ ] Fill form completely
- [ ] Click "XÁC NHẬN ĐẶT HÀNG"
- [ ] Redirects to `/payment/momo/{orderId}`
- [ ] Check database for Payment record:
    ```sql
    SELECT id, order_id, method, status FROM payments ORDER BY id DESC LIMIT 1;
    -- Should see: method='MOMO', status='pending'
    ```

### 3.2 MoMo Payment Page

- [ ] Page displays:
    - [ ] Order ID: #<id>
    - [ ] Amount: <total_amount> VND
    - [ ] Button: "Thanh toán với MoMo"
- [ ] Button visible and clickable

### 3.3 MoMo Process & API Call

- [ ] Click "Thanh toán với MoMo"
- [ ] POST to `/payment/momo-process/{orderId}`
- [ ] Backend:
    - [ ] Builds MOMO request JSON
    - [ ] Calculates HMAC-SHA256 signature
    - [ ] CURL POST to `https://test-payment.momo.vn/v2/gateway/api/create`
- [ ] Receives response with `resultCode=0` and `payUrl`
- [ ] Redirects to `payUrl` (on test-payment.momo.vn)

### 3.4 MoMo Sandbox - QR Code

- [ ] MoMo Sandbox page loads
- [ ] Display:
    - [ ] QR Code for payment
    - [ ] Amount
    - [ ] Merchant: "OcopShop"
    - [ ] Store: "MomoTestStore"
    - [ ] Order ID
- [ ] QR Code is scannable (can open in phone simulator)

### 3.5 MoMo Sandbox - Payment Confirmation

- [ ] Scan QR or use direct payment method
- [ ] On phone simulator/test environment:
    - [ ] Select payment method (wallet, card, etc.)
    - [ ] Confirm payment
- [ ] Success confirmation
- [ ] Redirect back to `/payment/momo-return?resultCode=0&transId=...&amount=...`

### 3.6 Callback & Return

- [ ] Page shows "Thanh toán MoMo thành công"
- [ ] Redirects to order detail page
- [ ] Check database:
    ```sql
    SELECT id, order_id, status, transaction_code, method FROM payments
    WHERE method='MOMO' ORDER BY id DESC LIMIT 1;
    -- Should see: status='paid', transaction_code set
    ```
    ```sql
    SELECT id, status FROM orders WHERE id=<orderId>;
    -- Should see: status='confirmed'
    ```
- [ ] Order detail page shows:
    - [ ] Payment status: "Đã thanh toán"
    - [ ] Payment method: "Ví MoMo"
    - [ ] Transaction code displayed
    - [ ] Amount displayed
    - [ ] Paid time displayed

### 3.7 Duplicate Payment Prevention

- [ ] Manually navigate back to `/payment/momo-return?resultCode=0&transId=...` (same params)
- [ ] Check if payment is processed again (should NOT be)
- [ ] Database should show only ONE paid payment for this transaction_code
- [ ] Page shows success but doesn't duplicate

### 3.8 Test Payment Failure

- [ ] Create another order, select MOMO
- [ ] On sandbox, cancel/reject the payment
- [ ] Browser returns with `resultCode != 0`
- [ ] Page shows "Thanh toán MoMo thất bại"
- [ ] Database shows Payment.status = 'failed'
- [ ] Order status remains 'pending'

---

## 💰 TEST 4: COD (CASH ON DELIVERY)

### 4.1 Checkout with COD

- [ ] Go back to checkout page
- [ ] Add items to cart again
- [ ] Select "💵 Thanh toán khi nhận hàng (COD)" (default)
- [ ] Fill form completely
- [ ] Click "XÁC NHẬN ĐẶT HÀNG"
- [ ] Redirects directly to `/order/{orderId}` (order detail page)
- [ ] Check database:
    ```sql
    SELECT id, order_id, method, status FROM payments ORDER BY id DESC LIMIT 1;
    -- Should see: method='COD', status='pending'
    ```

### 4.2 Order Detail Page

- [ ] Display:
    - [ ] Order ID
    - [ ] Status: "Chờ xử lý" (pending)
    - [ ] Payment method: "Thanh toán khi nhận hàng"
    - [ ] Payment status: "Chưa thanh toán"
    - [ ] Order items with details
    - [ ] Total amount
    - [ ] Shipping address
    - [ ] Receiver info

### 4.3 Manual Admin Confirmation

- [ ] Admin (in admin panel) confirms payment when receives money
- [ ] Order status updates to 'confirmed'
- [ ] Payment status updates to 'paid'
- [ ] Notifications sent to customer

---

## 🔄 TEST 5: RE-PAYMENT FLOW

### 5.1 Setup

- [ ] Create order with VNPAY or MOMO
- [ ] Simulate payment failure (payment.status = 'failed')
- [ ] Order status = 'pending'

### 5.2 View Order Detail

- [ ] Order detail page shows payment failed
- [ ] Display button: "Thanh toán lại VNPAY" or "Thanh toán lại MOMO"
- [ ] Button is visible and clickable

### 5.3 Retry Payment

- [ ] Click "Thanh toán lại..."
- [ ] Redirects to payment page again
- [ ] Follow same flow as original payment
- [ ] Complete payment successfully
- [ ] Check database shows 2 Payment records (one failed, one paid)
- [ ] Order updated to 'confirmed' with latest successful transaction

---

## 📊 TEST 6: DATABASE VERIFICATION

### 6.1 Orders Table

```sql
SELECT id, customer_id, status, total_amount, shipping_fee, discount_amount, payment_method
FROM orders WHERE id=<test_order_id>;
```

**Expected**: All fields populated, status='confirmed' after successful payment

### 6.2 Payments Table

```sql
SELECT id, order_id, method, amount, status, transaction_code, paid_at
FROM payments WHERE order_id=<test_order_id>;
```

**Expected**:

- method matches chosen (VNPAY/MOMO/COD)
- status='paid' for successful
- transaction_code populated for VNPAY/MOMO
- paid_at timestamp set

### 6.3 Order Items Table

```sql
SELECT id, order_id, product_variant_id, quantity, price, subtotal
FROM order_items WHERE order_id=<test_order_id>;
```

**Expected**: All items from cart in order_items

### 6.4 Notifications Table

```sql
SELECT id, user_id, type, title, is_read FROM notifications
WHERE related_id=<test_order_id> ORDER BY created_at DESC;
```

**Expected**: At least one notification with type='order_payment_success'

---

## 🐛 TEST 7: ERROR HANDLING

### 7.1 Network Error (VNPAY)

- [ ] Simulate server down (change endpoint to invalid URL)
- [ ] Try to checkout with VNPAY
- [ ] Error message: "Không nhận được phản hồi từ VNPay"
- [ ] Order still created (status='pending')
- [ ] Can retry payment later

### 7.2 Network Error (MOMO)

- [ ] Simulate MoMo API down
- [ ] Try to checkout with MOMO
- [ ] Process route returns curl error
- [ ] Error message: "Không nhận được phản hồi từ MoMo"
- [ ] Order still created
- [ ] Can retry payment later

### 7.3 Invalid Signature

- [ ] (For developers) Modify signature in return URL
- [ ] vnpayReturn() or momoReturn() should detect tampering
- [ ] Show error: "Sai chữ ký" or similar
- [ ] Payment NOT created/updated
- [ ] Order status unchanged

### 7.4 Invalid Order ID

- [ ] Manually call `/payment/momo-return?extraData=invalid_base64`
- [ ] Error: "Đơn hàng không tồn tại"
- [ ] Redirect to home page
- [ ] No database changes

---

## 🎯 SUMMARY CHECKLIST

### Functionality

- [ ] All 3 payment methods accessible from checkout
- [ ] Order created before redirecting to payment
- [ ] VNPAY redirects to sandbox successfully
- [ ] MOMO displays page, then redirects to sandbox
- [ ] COD skips payment, goes directly to order detail
- [ ] All callbacks update database correctly
- [ ] No duplicate payments processed

### Database Integrity

- [ ] Orders table: All fields correct
- [ ] Payments table: Status and transaction codes correct
- [ ] OrderItems table: Cart items properly stored
- [ ] Inventory: Decremented correctly
- [ ] Notifications: Created for successful payments

### User Experience

- [ ] Clear payment method selection
- [ ] Sandbox redirects seamless (external URLs)
- [ ] Success/error messages displayed
- [ ] Order detail page shows correct status
- [ ] Can retry failed payments

### Security

- [ ] Signatures verified (VNPAY/MOMO)
- [ ] Customer can only access own orders
- [ ] Tampered callbacks rejected
- [ ] ENV secrets not exposed in frontend

---

## ✅ FINAL APPROVAL

- [ ] All tests passed
- [ ] No critical bugs found
- [ ] System ready for customer use
- [ ] Documentation complete

**Tested By**: ******\_\_\_\_******  
**Date**: ******\_\_\_\_******  
**Status**: ☐ PASS ☐ FAIL ☐ NEEDS FIX
