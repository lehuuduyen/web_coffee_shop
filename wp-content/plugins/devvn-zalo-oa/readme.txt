/*
Những thay đổi
*/

== Bật debug để ghi lại log của ZALO ==
add_filter('zalo_debug', '__return_true');

add_filter('mess_filter_content_payment_name', function ($thisVal, $content, $orderThis){
    $thisVal = ($orderThis->get_payment_method_title()) ? $orderThis->get_payment_method_title() : 'Không rõ';
    return $thisVal;
}, 10, 3);

= V1.1.5 - 23.01.2024 =

* Thêm chức năng đăng nhập bằng Zalo
* Thêm shortcode để hiển thị nút đăng nhập bằng Zalo nếu muốn hiển thị bất kỳ chỗ nào muốn [zalo_login_btn]

= V1.1.4 - 18.01.2024 =

* Cập nhật để có thể dùng các biến có sẵn có thể dùng trong text1 và text2 của tin giao dịch

= V1.1.3 - 04.01.2024 =

* Loại bỏ tính năng nhắc hẹn 30 ngày phải cấp lại quyền cho ứng dụng

= V1.1.2 - 30.11.2023 =

* Tối ưu code để tương thích với các addon

= V1.1.1 - 28.11.2023 =

* Tối ưu lại thời gian hẹn lịch gửi tin và một số phần khác

= V1.1.0 - 12.10.2023 =

* Thêm text mặc định (Không có) vào phần note nếu đơn hàng đó ko có ghi chú
* [NGHIÊM TRỌNG] Fix lỗi không đúng nội dung gửi tin trong chiến dịch

= V1.0.9 - 09.09.2023 =

* Fix một số cảnh báo
* Thêm chức năng gửi tin nhắn ZNS tới admin OA. Tin này gửi ở chế độ develop nên sẽ miễn phí nhé

= V1.0.8 - 27.07.2023 =

* Thêm chức năng "Lấy toàn bộ danh sách người dùng quan tâm" trong tab Quản lý số điện thoại
* Thêm chức năng trong tab Quản lý tin nhắn: thêm nút Xem chi tiết data gửi đi, thêm nút gửi lại tin nếu tin đó lỗi
* Thêm chức năng lên chiến dịch gửi tin. Tại version hiện tại có thể lên lịch gửi tin truyền thông cá nhân cho người dùng quan tâm OA
* compatible with HPOS

= V1.0.7 - 26.06.2023 =

* Fix lỗi gửi tin tới số điện thoại đầu 084

= V1.0.6 - 20.06.2023 =

* Fix urlencode trong đường dẫn xem chi tiết đơn hàng
* Đếm lại số đánh giá khi có đánh giá mới từ zalo

= V1.0.5 - 20.06.2023 =

* Fix giờ chuẩn gửi tin nhắn ZNS từ 6h - 22h

= V1.0.4 - 18.06.2023 =

* Fix lỗi không có tên khách đánh giá khi đơn không có email
* Thêm biến mới là {order_total_view} để hiển thị tổng tiền đơn hàng có định dạng (ví dụ 150.000đ) trong tin giao dịch
* Thêm xử lý trong nền với tin giao dịch
* Fix lỗi lặp lại nhiều đánh giá trên website khi khách đánh giá từ zalo
* Thêm update tự động trong admin thông qua license

= V1.0.3 - 16.06.2023 =

* Fix tham số product_name trong zns
* Thêm chức năng gửi tin trong nền. Để tăng tốc độ xử lý. Để tắt có thể dùng code
add_filter('send_zns_in_background', '__return_false');

= V1.0.2 - 15.06.2023 =

* Fix và thêm 1 số tham số bên tin nhắn ZNS

= V1.0.1 - 13.06.2023 =
* Tối ưu core
* Thêm <note> vào tham số ZNS
* Thêm <phone_number> vào tham số ZNS
* Thêm <address> vào tham số ZNS
* Thêm hành động gửi tin zns đánh giá cho khách hàng. Có thể đặt được sau khi đơn hoàn thành bao nhiêu ngày sẽ gửi tin nhắc khách đánh giá. Sau khi khách hàng đánh giá sẽ tự động thêm vào website

= V1.0.0 - 12.06.2023 =
* Ra mắt plugin