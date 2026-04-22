# 🎨 PAYMENT SYSTEM - VISUAL FLOW MAP

## 📊 COMPLETE FLOW DIAGRAM

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        🛒 CUSTOMER JOURNEY                                  │
└─────────────────────────────────────────────────────────────────────────────┘

STEP 1: SHOPPING
┌───────────────────────────────────┐
│  Browse Products & Add to Cart    │
│  ─────────────────────────────────│
│  • View product details           │
│  • Click "Add to Cart"            │
│  • View cart (/cart)              │
│  • Click "TIẾN HÀNH THANH TOÁN"   │
└──────────┬────────────────────────┘
           │
           ↓
STEP 2: CHECKOUT
┌──────────────────────────────────────────┐
│  GET /checkout                           │
│  ──────────────────────────────────────  │
│  Display Checkout Form:                  │
│  ├─ Receiver Name (required)             │
│  ├─ Phone (required)                     │
│  ├─ Shipping Address (required)          │
│  ├─ Note (optional)                      │
│  ├─ Cart Summary (products, qty, price)  │
│  │  ├─ Subtotal                          │
│  │  ├─ Shipping Fee                      │
│  │  ├─ Discount (if any)                 │
│  │  └─ Total Amount                      │
│  ├─ Payment Method Selection:            │
│  │  ⭕ COD (Thanh toán khi nhận hàng)    │
│  │  ⭕ VNPAY (Thanh toán qua VNPAY)      │
│  │  ⭕ MOMO (Thanh toán ví MoMo)         │
│  ├─ Policy Agreement checkbox            │
│  └─ [XÁC NHẬN ĐẶT HÀNG] button          │
└──────────┬───────────────────────────────┘
           │
           │ POST /checkout/store
           │ (with all form data)
           ↓
STEP 3: CREATE ORDER
┌──────────────────────────────────────────────────────────────┐
│  CheckoutController@store()                                  │
│  ──────────────────────────────────────────────────────────  │
│                                                               │
│  ✓ Validate input (name, phone, address, policy)            │
│  ✓ Check inventory & stock (FIFO)                           │
│  ✓ Calculate total (subtotal + shipping - discount)         │
│  ✓ Create Order (status: 'pending')                         │
│  ✓ Create OrderItems (all products from cart)               │
│  ✓ Create Payment record (status: 'pending')                │
│  ✓ Deduct inventory                                         │
│  ✓ Clear session cart                                       │
│  ✓ Send confirmation email                                  │
│                                                               │
│  DB Change:                                                  │
│  └─ orders: id, customer_id, status='pending', total_amount │
│  └─ order_items: product details & quantities               │
│  └─ payments: method, status='pending'                      │
│  └─ inventory: quantity decremented                         │
└──────────┬───────────────────────────────────────────────────┘
           │
           │ Redirect based on payment_method
           │
        ┌──┴──────────────────────────────────────────────┐
        │                                                  │
        ↓                                    ↓             ↓
     VNPAY                               MOMO          COD
        │                                 │             │
        
═══════════════════════════════════════════════════════════════════════════════

STEP 4A: VNPAY PAYMENT FLOW
┌─────────────────────────────────────────────────────────────┐
│  GET /payment/vnpay/{orderId}                               │
│  ─────────────────────────────────────────────────────────  │
│                                                              │
│  Backend: PaymentController@vnpay()                         │
│  ├─ Load Order & verify ownership                           │
│  ├─ Get ENV: VNP_TMN_CODE, VNP_HASH_SECRET                │
│  ├─ Build VNPAY Request:                                   │
│  │  ├─ vnp_Amount: order.total_amount * 100               │
│  │  ├─ vnp_TmnCode: from ENV                              │
│  │  ├─ vnp_OrderInfo: "Order"                             │
│  │  ├─ vnp_ReturnUrl: /payment/vnpay-return              │
│  │  ├─ vnp_Locale: "vi"                                   │
│  │  ├─ Sort parameters (ksort)                            │
│  │  └─ Calculate HMAC-SHA512 signature                    │
│  │                                                          │
│  └─ redirect()->away($vnp_Url)                             │
│     └─ https://sandbox.vnpayment.vn/paymentv2/vpcpay.html │
│
└──────────┬──────────────────────────────────────────────────┘
           │
           ↓
┌─────────────────────────────────────────────────────────────┐
│  🏦 VNPAY SANDBOX                                            │
│  ─────────────────────────────────────────────────────────  │
│  https://sandbox.vnpayment.vn                               │
│                                                              │
│  Customer Activity:                                         │
│  ├─ See order amount                                        │
│  ├─ See merchant name "OcopShop"                            │
│  ├─ Select payment method (card, account)                  │
│  ├─ Enter payment details:                                 │
│  │  ├─ Test Card: 9704198526191432198                      │
│  │  ├─ Exp: 07/15                                          │
│  │  ├─ CVV: 123                                            │
│  │  └─ Click "Confirm"                                     │
│  │                                                          │
│  └─ Payment Success ✅                                     │
│     └─ Redirect to vnp_ReturnUrl                           │
│        (with resultCode=00, vnp_TransactionNo, amount, etc)│
│                                                              │
└──────────┬──────────────────────────────────────────────────┘
           │
           │ GET /payment/vnpay-return?vnp_Amount=...&vnp_ResponseCode=00&...
           │
           ↓
┌──────────────────────────────────────────────────────────┐
│  PaymentController@vnpayReturn()                          │
│  ──────────────────────────────────────────────────────  │
│  ✓ Extract parameters from request                        │
│  ✓ Verify HMAC-SHA512 signature                          │
│  ✓ Check vnp_ResponseCode:                              │
│  │                                                        │
│  ├─ If '00' (SUCCESS):                                  │
│  │  ├─ Update Payment:                                  │
│  │  │  ├─ status = 'paid'                               │
│  │  │  ├─ transaction_code = vnp_TransactionNo          │
│  │  │  ├─ amount = vnp_Amount / 100                     │
│  │  │  └─ paid_at = NOW()                               │
│  │  ├─ Update Order: status = 'confirmed'               │
│  │  ├─ Create Notification                              │
│  │  └─ Redirect to orders.detail                        │
│  │     └─ Message: "Thanh toán VNPAY thành công"        │
│  │                                                        │
│  └─ Else (FAILED):                                      │
│     ├─ Update Payment: status = 'failed'                │
│     └─ Redirect to orders.detail                        │
│        └─ Message: "Thanh toán VNPAY thất bại: ..."     │
│                                                          │
└──────────┬───────────────────────────────────────────────┘
           │
           ↓ (Proceed to STEP 5: ORDER DETAIL)

═══════════════════════════════════════════════════════════════════════════════

STEP 4B: MOMO PAYMENT FLOW
┌────────────────────────────────────────────────────────────┐
│  GET /payment/momo/{orderId}                               │
│  ────────────────────────────────────────────────────────  │
│                                                             │
│  PaymentController@momo()                                  │
│  ├─ Load Order & verify ownership                          │
│  └─ Render views/pages/payment/momo.blade.php             │
│                                                             │
│  Display Momo Form:                                        │
│  ├─ Order ID: #{orderId}                                  │
│  ├─ Amount: {total_amount} VND                            │
│  └─ [Thanh toán với MoMo] button                          │
│     └─ POST /payment/momo-process/{orderId}              │
│                                                             │
└──────────┬───────────────────────────────────────────────┘
           │
           │ Click "Thanh toán với MoMo"
           │
           ↓
┌────────────────────────────────────────────────────────────┐
│  POST /payment/momo-process/{orderId}                      │
│  ────────────────────────────────────────────────────────  │
│                                                             │
│  PaymentController@momoProcess()                           │
│  ├─ Load Order & verify ownership                          │
│  ├─ Get ENV variables:                                    │
│  │  ├─ MOMO_ENDPOINT                                      │
│  │  ├─ MOMO_PARTNER_CODE                                  │
│  │  ├─ MOMO_ACCESS_KEY                                    │
│  │  └─ MOMO_SECRET_KEY                                    │
│  │                                                          │
│  ├─ Build MOMO Request JSON:                              │
│  │  ├─ partnerCode: MOMOBKUN20180529                      │
│  │  ├─ partnerName: "OcopShop"                            │
│  │  ├─ storeId: "MomoTestStore"                           │
│  │  ├─ amount: order.total_amount                         │
│  │  ├─ orderId: order.id                                  │
│  │  ├─ orderInfo: "DonHang{id}"                           │
│  │  ├─ requestType: "captureWallet"                       │
│  │  ├─ redirectUrl: /payment/momo-return                 │
│  │  ├─ ipnUrl: /payment/momo-return                       │
│  │  ├─ extraData: base64(order.id)                        │
│  │  └─ signature: HMAC-SHA256(rawHash)                    │
│  │                                                          │
│  ├─ CURL POST to endpoint:                                │
│  │  └─ https://test-payment.momo.vn/v2/gateway/api/create│
│  │                                                          │
│  ├─ Parse JSON response:                                  │
│  │  ├─ resultCode: 0 (success) or error code              │
│  │  └─ payUrl: URL to sandbox QR/payment page             │
│  │                                                          │
│  ├─ If resultCode == 0:                                   │
│  │  └─ redirect($payUrl)                                  │
│  │     └─ Redirect to sandbox payment page               │
│  │                                                          │
│  └─ Else:                                                  │
│     └─ back() with error message                          │
│                                                             │
└──────────┬───────────────────────────────────────────────┘
           │
           │ redirect($payUrl) from MoMo response
           │
           ↓
┌────────────────────────────────────────────────────────────┐
│  📱 MOMO SANDBOX                                            │
│  ────────────────────────────────────────────────────────  │
│  https://test-payment.momo.vn                              │
│                                                             │
│  Customer Activity:                                        │
│  ├─ See QR Code for scanning                              │
│  ├─ See order amount & merchant "OcopShop"               │
│  │                                                          │
│  ├─ Option A: Scan QR with mobile app                     │
│  │  ├─ Open MoMo test app                                 │
│  │  ├─ Scan QR Code                                       │
│  │  ├─ Confirm payment                                    │
│  │  └─ Success ✅                                        │
│  │                                                          │
│  ├─ Option B: Direct payment (if available)               │
│  │  ├─ Enter account/card details                         │
│  │  ├─ Confirm payment                                    │
│  │  └─ Success ✅                                        │
│  │                                                          │
│  └─ Sandbox Callback                                      │
│     └─ Redirect to momo_ReturnUrl                         │
│        (with resultCode=0, transId, amount, etc)          │
│                                                             │
└──────────┬───────────────────────────────────────────────┘
           │
           │ GET /payment/momo-return?resultCode=0&transId=...&amount=...
           │
           ↓
┌────────────────────────────────────────────────────────────┐
│  PaymentController@momoReturn()                            │
│  ────────────────────────────────────────────────────────  │
│  ✓ Decode order ID from extraData (base64)               │
│  ✓ Load Order & verify exists                            │
│  ✓ Check for duplicate transaction (transId)             │
│  ✓ Check request.resultCode:                             │
│  │                                                         │
│  ├─ If 0 (SUCCESS):                                      │
│  │  ├─ Check if payment already processed (duplicate)    │
│  │  ├─ If not duplicate:                                 │
│  │  │  ├─ Update/Create Payment:                         │
│  │  │  │  ├─ status = 'paid'                             │
│  │  │  │  ├─ transaction_code = transId                  │
│  │  │  │  ├─ amount = amount from request               │
│  │  │  │  └─ paid_at = NOW()                             │
│  │  │  ├─ Update Order: status = 'confirmed'             │
│  │  │  ├─ Create Notification                            │
│  │  │  └─ Redirect to orders.detail                      │
│  │  │     └─ Message: "Thanh toán MoMo thành công"      │
│  │  │                                                     │
│  │  └─ If duplicate:                                     │
│  │     └─ Just redirect (don't duplicate update)         │
│  │                                                        │
│  └─ Else (FAILED):                                       │
│     ├─ Update/Create Payment: status = 'failed'          │
│     └─ Redirect to orders.detail                         │
│        └─ Message: "Thanh toán MoMo thất bại"            │
│                                                           │
└──────────┬──────────────────────────────────────────────┘
           │
           ↓ (Proceed to STEP 5: ORDER DETAIL)

═══════════════════════════════════════════════════════════════════════════════

STEP 4C: COD PAYMENT (CASH ON DELIVERY)
┌────────────────────────────────────────────────┐
│  Direct Redirect (No Payment Sandbox)           │
│  ────────────────────────────────────────────  │
│                                                 │
│  CheckoutController@store():                   │
│  ├─ Create Order (status: 'pending')           │
│  ├─ Create Payment (status: 'pending')         │
│  └─ redirect()->route('orders.detail', $id)   │
│     └─ No external payment gateway needed      │
│                                                 │
│  Order Status: "Chờ xử lý"                     │
│  Payment Status: "Chưa thanh toán"            │
│  Action: Admin confirms when money received   │
│                                                 │
└──────────┬─────────────────────────────────────┘
           │
           ↓ (Proceed to STEP 5: ORDER DETAIL)

═══════════════════════════════════════════════════════════════════════════════

STEP 5: ORDER DETAIL PAGE
┌──────────────────────────────────────────────────────────┐
│  GET /order/{orderId}                                    │
│  ──────────────────────────────────────────────────────  │
│                                                           │
│  Display Order Information:                              │
│  ├─ Order ID: #{orderId}                                │
│  ├─ Order Status: ✅ "Đã xác nhận" OR ⏳ "Chờ xử lý"    │
│  │                                                       │
│  ├─ Product Items:                                      │
│  │  ├─ Product name, image                              │
│  │  ├─ Quantity, unit price                             │
│  │  └─ Subtotal per item                                │
│  │                                                       │
│  ├─ Order Summary:                                      │
│  │  ├─ Subtotal                                         │
│  │  ├─ Shipping Fee                                     │
│  │  ├─ Discount (if applied)                            │
│  │  └─ Total Amount                                     │
│  │                                                       │
│  ├─ Shipping Address:                                   │
│  │  ├─ Receiver name                                    │
│  │  ├─ Phone number                                     │
│  │  └─ Delivery address                                 │
│  │                                                       │
│  ├─ Payment Information: ✅                             │
│  │  ├─ Method: VNPAY / MOMO / COD                       │
│  │  ├─ Status:                                          │
│  │  │  ├─ ✅ "Đã thanh toán" (if paid)                 │
│  │  │  └─ ⏳ "Chưa thanh toán" (if pending)            │
│  │  ├─ Transaction Code (if payment gateway used)       │
│  │  ├─ Amount paid                                      │
│  │  └─ Payment time (if completed)                      │
│  │                                                       │
│  ├─ Actions:                                            │
│  │  ├─ [Cancel Order] - if status='pending'             │
│  │  ├─ [Re-pay] - if payment failed                     │
│  │  ├─ [Confirm Received] - if status='shipped'         │
│  │  └─ [Request Refund] - if status='completed'         │
│  │                                                       │
│  └─ Notification:                                       │
│     └─ "Thanh toán {METHOD} thành công" (if successful)│
│                                                          │
└──────────┬───────────────────────────────────────────────┘
           │
           ↓
┌──────────────────────────────────────────────────────────┐
│  ✅ ORDER PROCESS COMPLETE                               │
│                                                           │
│  DB Status:                                              │
│  ├─ Order: status='confirmed'                           │
│  ├─ Payment: status='paid', transaction_code set        │
│  ├─ Inventory: decreased by ordered qty                 │
│  └─ Notification: created for customer                  │
│                                                           │
│  Next: Admin processes shipping                          │
│  ├─ Confirm order                                       │
│  ├─ Pack items                                          │
│  ├─ Hand over to shipper                                │
│  ├─ Update status to 'shipping'                         │
│  └─ Customer receives notification                      │
│                                                           │
└──────────────────────────────────────────────────────────┘
```

---

## 🎯 KEY POINTS

✅ **All paths lead to ORDER CONFIRMATION**
- VNPAY → Sandbox → Callback → Confirmed
- MOMO → Sandbox → Callback → Confirmed  
- COD → Direct → Pending (admin confirms)

✅ **Database updates happen ONLY after payment/confirmation**
- Order.status: pending → confirmed
- Payment.status: pending → paid
- Inventory: decreased immediately (but reversible on cancel)

✅ **Customer gets notification on successful payment**
- Email confirmation
- In-app notification
- Order detail page updated

---

## 📱 RESPONSE DETAILS

### VNPAY Response Parameters
```
vnp_Amount: Order amount * 100
vnp_BankCode: Bank code
vnp_BankTranNo: Bank transaction ID
vnp_CardType: Card type
vnp_OrderInfo: "Order"
vnp_PayDate: Payment date
vnp_ResponseCode: 00 (success) or error code
vnp_SecureHash: Signature (verified in callback)
vnp_TransactionNo: VNPAY transaction ID
vnp_TxnRef: Our order ID
```

### MOMO Response Parameters
```
partnerCode: MOMOBKUN20180529
orderId: Our order ID
requestId: Request ID
amount: Order amount
operationId: MoMo operation ID
transId: MoMo transaction ID
resultCode: 0 (success) or error code
message: "Success" or error description
responseTime: Response timestamp
```

---

Generated: April 22, 2026  
Status: ✅ COMPLETE & VERIFIED
