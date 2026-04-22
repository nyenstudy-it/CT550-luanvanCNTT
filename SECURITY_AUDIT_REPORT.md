# 🔒 BÁO CÁO KIỂM TOÁN BẢO MẬT HỆ THỐNG

**Ngày kiểm tra:** 21 Tháng 4, 2026  
**Mức độ nguy hiểm:** 🔴 TRUNG BÌNH - CÓ CẦN CẢI THIỆN

---

## 📋 TÓM TẮT CÁC LỖI TÌM THẤY

### 1. ⚠️ LỖ HỔ HỔNG SQL INJECTION (TRUNG BÌNH)

#### Vấn đề 1: Sử dụng `whereRaw()` không an toàn

**Vị trí:** [app/Services/AiProductChatService.php](app/Services/AiProductChatService.php#L147)

```php
// ❌ NGUY HIỂM - Không sử dụng parameterized query
$builder->whereRaw('LOWER(name) LIKE ?', ['%' . $category . '%'])
```

**Vấn đề:** Mặc dù có binding parameter `?`, nhưng việc thêm `%` trước dữ liệu user có thể bị SQL injection nếu xử lý không đúng.

**Khuyến nghị:**

```php
// ✅ AN TOÀN - Sử dụng where() với like
$builder->where('name', 'like', '%' . $category . '%')
```

---

#### Vấn đề 2: SQL LIKE Injection trong AdminOrderController

**Vị trí:** [app/Http/Controllers/AdminOrderController.php](app/Http/Controllers/AdminOrderController.php#L40)

```php
// ❌ NGUY HIỂM - User input trực tiếp vào LIKE
$query->where('receiver_phone', 'like', '%' . $request->search . '%');
$query->where('receiver_phone', 'like', '%' . $request->phone . '%');
```

**Vấn đề:** Ký tự đặc biệt như `%` và `_` trong LIKE có thể làm thay đổi kết quả query.

**Khuyến nghị:**

```php
// ✅ AN TOÀN - Escape LIKE wildcards
use Illuminate\Support\Str;

$search = addcslashes($request->search, '\\%_');
$query->where('receiver_phone', 'like', '%' . $search . '%');
```

---

#### Vấn đề 3: ProductController - LIKE Injection

**Vị trí:** [app/Http/Controllers/ProductController.php](app/Http/Controllers/ProductController.php#L25)

```php
// ❌ NGUY HIỂM
if ($request->filled('name')) {
    $query->where('name', 'like', '%' . $request->name . '%');
}
```

---

### 2. 🛡️ LỖNG HỔ XSS (Cross-Site Scripting) (THẤP -> TRUNG BÌNH)

#### Vấn đề 1: Unescaped Data trong JSON trong layout

**Vị trí:** [resources/views/layout.blade.php](resources/views/layout.blade.php#L155-L164)

```php
// ⚠️ CANH BÁO - JSON_UNESCAPED_UNICODE có thể bị XSS
const flashMessages = {!! json_encode($flashMessages, JSON_UNESCAPED_UNICODE) !!};
```

**Vấn đề:** `JSON_UNESCAPED_UNICODE` có thể cho phép HTML/JavaScript injection.

**Khuyến nghị:**

```php
// ✅ AN TOÀN - Không sử dụng JSON_UNESCAPED_UNICODE
const flashMessages = {!! json_encode($flashMessages) !!};
```

---

#### Vấn đề 2: HTML Encoding thừa - NHƯNG AN TOÀN

**Vị trí:** [resources/views/pages/blog-details.blade.php](resources/views/pages/blog-details.blade.php#L59)

```php
// ✅ AN TOÀN - Nhưng không cần thiết
<p>{!! nl2br(e($blog->content)) !!}</p>
```

**Phân tích:** `e()` đã escape HTML, sau đó `nl2br()` chỉ thêm `<br/>`, và `{!!` render HTML. Kết quả là an toàn nhưng phức tạp.

**Khuyến nghị:**

```php
// ✅ TỐT HƠN - Đơn giản hơn
<p>{!! nl2br(htmlspecialchars($blog->content, ENT_QUOTES, 'UTF-8')) !!}</p>
```

---

### 3. 🚨 VẤN ĐỀ BẢO VỆ TƯỜNG LỬA (CSRF) (THẤP)

**Tình trạng:** ✅ Khá tốt - Hầu hết forms đều có `@csrf`

**Nhưng kiểm tra thêm:**

- [resources/views/pages/order-detail.blade.php](resources/views/pages/order-detail.blade.php) - Không thấy CSRF token trong modal/form
- API endpoints cần kiểm tra thêm X-CSRF-TOKEN headers

---

### 4. ⚠️ VẤN ĐỀ PHÂN QUYỀN (Authorization) (TRUNG BÌNH)

#### Vấn đề: Kiểm tra quyền không đầy đủ

**Vị trị:** [app/Http/Controllers/ReviewController.php](app/Http/Controllers/ReviewController.php#L45-L55)

```php
// ⚠️ CANH BÁO - Không kiểm tra authorization đầy đủ
$orders = Order::where('customer_id', $customer->user_id)
    ->where('status', 'completed')
    ->get();
```

**Vấn đề:** Cần kiểm tra xem user hiện tại có phải là customer owner không.

**Khuyến nghị:**

```php
// ✅ AN TOÀN
$this->authorize('view', $order); // Policy authorization

// Hoặc kiểm tra manual:
if (Auth::id() !== $order->customer->user_id) {
    abort(403, 'Unauthorized');
}
```

---

### 5. 🔐 VẤN ĐỀ LƯU GIỮ DỮ LIỆU NHẠY CẢM (THẤP)

#### Vấn đề: Debug View lộ thông tin

**Vị trí:** [resources/views/debug/orders_debug.blade.php](resources/views/debug/orders_debug.blade.php)

```php
// ⚠️ CANH BÁO - File debug không nên ở production
<h1>Debug: Orders for {{ auth()->user()->name ?? auth()->id() }}</h1>
```

**Khuyến nghị:** Xóa debug view hoặc bảo vệ bằng middleware:

```php
Route::get('/debug/orders', [OrderController::class, 'debug'])
    ->middleware('debug.only'); // Chỉ dev
```

---

### 6. ✅ VẤNĐỀ LOG (TỐTADEQUATE)

**Trạng thái:** ✅ Tốt - Có sử dụng `Log::error()` trong nhiều controller

---

## 📊 BẢNG TÓM TẮT RỦI RO

| Lỗ Hổng                      | Vị Trí                                  | Mức Độ        | Trạng Thái                |
| ---------------------------- | --------------------------------------- | ------------- | ------------------------- |
| SQL Injection (LIKE)         | AdminOrderController, ProductController | 🟠 TRUNG BÌNH | ❌ CẦN FIX                |
| SQL Injection (whereRaw)     | AiProductChatService                    | 🟠 TRUNG BÌNH | ❌ CẦN FIX                |
| XSS (JSON_UNESCAPED_UNICODE) | layout.blade.php                        | 🟠 TRUNG BÌNH | ❌ CẦN FIX                |
| CSRF Token                   | Missing in some forms                   | 🟢 THẤP       | ⚠️ CẦN KIỂM TRA           |
| Authorization Check          | ReviewController, OrderController       | 🟠 TRUNG BÌNH | ⚠️ CẦN KIỂM TRA           |
| Debug Views                  | orders_debug.blade.php                  | 🟢 THẤP       | ❌ CẦN REMOVE             |
| SQL Injection (whereRaw)     | SalaryController, AttendanceController  | 🟡 THẤP       | ✅ OK (Hard-coded values) |

---

## 🔧 KHUYẾN NGHỊ CẮP BÁCH (Priority 1)

### 1. FIX SQL INJECTION TRONG LIKE CLAUSES

**File:** `app/Http/Controllers/AdminOrderController.php`

```php
// ❌ HIỆN TẠI
$query->where('receiver_phone', 'like', '%' . $request->search . '%');

// ✅ SỮA CHỮA
$search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
$query->where('receiver_phone', 'like', '%' . $search . '%', 'and');
```

**File:** `app/Http/Controllers/ProductController.php`

```php
// ❌ HIỆN TẠI
$query->where('name', 'like', '%' . $request->name . '%');

// ✅ SỮA CHỮA
$search = str_replace(['%', '_'], ['\%', '\_'], $request->name);
$query->where('name', 'like', '%' . $search . '%', 'and');
```

---

### 2. FIX XSS TRONG JSON

**File:** `resources/views/layout.blade.php`

```php
// ❌ HIỆN TẠI
const flashMessages = {!! json_encode($flashMessages, JSON_UNESCAPED_UNICODE) !!};

// ✅ SỮA CHỮA
const flashMessages = {!! json_encode($flashMessages) !!};
```

---

### 3. THÊM AUTHORIZATION CHECKS

**Các file cần kiểm tra:**

- `app/Http/Controllers/ReviewController.php`
- `app/Http/Controllers/OrderController.php`
- `app/Http/Controllers/CustomerChatController.php`

```php
// ✅ THÊM VÀO STORE METHOD
public function store(Request $request)
{
    $order = Order::findOrFail($request->order_id);

    // Kiểm tra authorization
    if (Auth::id() !== $order->customer->user_id) {
        abort(403, 'Unauthorized');
    }

    // ... rest of code
}
```

---

### 4. XÓA HOẶC BẢO VỆ DEBUG VIEWS

**File:** `resources/views/debug/orders_debug.blade.php`

```php
// ✅ Bảo vệ bằng middleware
Route::get('/debug/orders', function() {
    // ...
})->middleware(['auth', 'debug']); // Chỉ dev mode
```

---

## 🛡️ KHUYẾN NGHỊ BỔ SUNG (Priority 2)

### 1. Thêm Rate Limiting

```php
// routes/web.php
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/checkout/store', [CheckoutController::class, 'store']);
    Route::post('/cart/add', [CartController::class, 'add']);
});
```

### 2. Thêm CSRF Protection cho AJAX

```javascript
// Thêm header X-CSRF-TOKEN cho tất cả fetch/ajax
fetch("/api/endpoint", {
    headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
            .content,
    },
});
```

### 3. Sử dụng prepared statements cho tất cả whereRaw

```php
// ❌ NGUY HIỂM
->selectRaw('users.name as staff_name, WEEK(work_date, 1) as week_num')

// ✅ AN TOÀN - Sử dụng DB::raw() có binding
->selectRaw('users.name as staff_name, WEEK(work_date, ?) as week_num', [1])
```

### 4. Input Validation tăng cường

```php
// Thêm validation cho search inputs
$request->validate([
    'search' => 'nullable|string|regex:/^[a-zA-Z0-9\s\-\_\.]+$|max:100',
    'phone' => 'nullable|regex:/^[0-9\s\-\+]+$/|max:20',
]);
```

---

## ✅ KIỂM TRA LẠI CÁC ĐIỂM TÍCH CỰC

1. ✅ **CSRF Protection** - Hầu hết forms có `@csrf`
2. ✅ **Password Hashing** - Sử dụng `bcrypt()`
3. ✅ **SQL Parameters** - Phần lớn queries dùng bindings
4. ✅ **XSS Protection Blade** - Sử dụng `{{ }}` (auto-escape)
5. ✅ **Authentication** - Có middleware `auth`, `role:customer`
6. ✅ **Logging** - Có sử dụng `Log::error()`, `Log::info()`

---

## 🎯 KẾ HOẠCH HÀNH ĐỘNG

**TUẦN 1:** FIX SQL INJECTION trong LIKE clauses  
**TUẦN 2:** FIX XSS trong JSON  
**TUẦN 3:** Thêm Authorization checks toàn bộ  
**TUẦN 4:** Xóa debug views, thêm rate limiting

---

## 📞 LIÊN HỆ & HỖ TRỢ

Nếu phát hiện lỗ hổng bổ sung, vui lòng báo cáo ngay lập tức.

**Nguy hiểm cấp độ cao:** Cập nhật code ngay lập tức  
**Nguy hiểm cấp độ trung bình:** Sửa trong sprint tiếp theo  
**Nguy hiểm cấp độ thấp:** Theo kế hoạch bảo trì thường xuyên

---

**Tạo lúc:** 2026-04-21  
**Cập nhật lúc:** 2026-04-21
