<!-- Hướng dẫn sử dụng bảng lương -->
@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">
            <h4 class="mb-4">Hướng dẫn sử dụng Bảng lương</h4>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="card-title mb-0">Xem Bảng Lương</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-3"><strong>Bước 1:</strong> Truy cập menu <code>Bảng lương</code> trong sidebar</p>
                            <p class="mb-3"><strong>Bước 2:</strong> Chọn tháng và năm để lọc dữ liệu</p>
                            <p class="mb-3"><strong>Bước 3:</strong> Bảng sẽ hiển thị:</p>
                            <ul class="small">
                                <li>Nhân viên</li>
                                <li>Lương cơ bản (từ chấm công)</li>
                                <li>Số lần đi trễ, về sớm, vắng</li>
                                <li>Tiền phạt (nếu có)</li>
                                <li>Tiền thưởng chuyên cần (nếu có)</li>
                                <li>Lương cuối cùng (sau điều chỉnh)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6 class="card-title mb-0">Chi Tiết Từng Nhân Viên</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-3"><strong>Click nút "Xem"</strong> ở cuối dòng của mỗi nhân viên</p>
                            <p class="mb-3">Sẽ mở modal hiển thị:</p>
                            <ul class="small">
                                <li>Tháng/năm lương</li>
                                <li>Tổng giờ làm việc</li>
                                <li>Chi tiết đi trễ, vắng, về sớm</li>
                                <li>Lý do và điều kiện phạt thưởng</li>
                                <li>Công thức tính lương cuối cùng</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="card-title mb-0">Bảng Chi Tiết Lương</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">Truy cập từ sidebar: <code>Bảng lương → Bảng chi tiết lương</code></p>
                            <p class="mb-3">Hiển thị đầy đủ thông tin lương tháng cụ thể:</p>
                            <ul class="small">
                                <li>Tổng lương cơ bản toàn tháng</li>
                                <li>Tổng phạt năm nhân viên</li>
                                <li>Tổng thưởng năm nhân viên</li>
                                <li>Tổng lương cuối cùng</li>
                                <li>Chi tiết từng nhân viên</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="card-title mb-0">Tính Lương</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">Chạy lệnh để tính/cập nhật lương:</p>
                            <div class="bg-dark text-light p-2 rounded small mb-3"
                                style="font-family: monospace;overflow-x: auto;">
                                php artisan salary:calculate --month=3 --year=2026
                            </div>
                            <p class="mb-0 small text-muted">Lệnh sẽ tự động:</p>
                            <ul class="small text-muted">
                                <li>Đếm lần đi trễ, về sớm, vắng</li>
                                <li>Áp dụng phạt & thưởng</li>
                                <li>Lưu kết quả vào database</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mb-4">
                <h6 class="alert-heading mb-2">Quy tắc phạt và thưởng</h6>
                <div class="row">
                    <div class="col-md-4">
                        <strong>🔴 Phạt Đi Trễ</strong>
                        <p class="small mb-0">> 5 lần/tháng: <span class="badge bg-danger">-200.000đ</span></p>
                    </div>
                    <div class="col-md-4">
                        <strong>🟡 Phạt Về Sớm</strong>
                        <p class="small mb-0">> 3 lần/tháng: <span class="badge bg-danger">-200.000đ</span></p>
                    </div>
                    <div class="col-md-4">
                        <strong>🟢 Thưởng Chuyên Cần</strong>
                        <p class="small mb-0">≤3 trễ, 0 vắng, ≤3 sớm: <span class="badge bg-success">+500.000đ</span></p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="card-title mb-0">Support</h6>
                </div>
                <div class="card-body small">
                    <p><strong>Câu hỏi thường gặp:</strong></p>
                    <ul>
                        <li><strong>Lương cuối cùng công thức:</strong> Lương cơ bản - Phạt + Thưởng</li>
                        <li><strong>Đã chạy lệnh tính lương rồi nhưng vẫn không thấy:</strong> Kiểm tra xem tháng/năm có
                            đúng không</li>
                        <li><strong>Muốn cập nhật lương:</strong> Chạy lại lệnh tính lương, nó sẽ ghi đè dữ liệu cũ</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection