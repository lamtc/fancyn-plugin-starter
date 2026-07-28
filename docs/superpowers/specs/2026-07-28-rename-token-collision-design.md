# Thiết kế sửa lỗi va chạm token khi đổi tên plugin

## Mục tiêu

Cho phép `npm run init` đổi Starter Kit thành plugin mới ngay cả khi metadata mới tiếp tục sử dụng thương hiệu hoặc namespace root `Fancyn`, đồng thời giữ nguyên cơ chế staging và rollback an toàn hiện tại.

Trường hợp bắt buộc phải hoạt động:

```text
Plugin name: Fancyn Car Booking
Plugin slug: fancyn-car-booking
PHP namespace: Fancyn\CarBooking
PHP prefix: fcbook_
CSS prefix: fcb-
```

## Nguyên nhân gốc

Rename engine hiện thêm replacement toàn cục từ namespace root cũ `Fancyn` sang namespace root mới. Khi root mới vẫn là `Fancyn`, replacement trở thành no-op nhưng validator vẫn xem mọi chuỗi `Fancyn` còn lại là placeholder. Khi root mới khác, replacement toàn cục cũng có thể sửa nhầm plugin name hoặc nội dung hợp lệ chứa thương hiệu `Fancyn`.

Sau khi `init` thất bại, transaction rollback đúng về identity Starter. Nếu người dùng bỏ qua exit code và tiếp tục `npm run package`, hệ thống hợp lệ tạo ZIP của Starter gốc; upload ZIP này vào WordPress sẽ thay thế thư mục source cùng slug.

## Thiết kế replacement

- Loại bỏ replacement namespace root `Fancyn` trên toàn bộ nội dung.
- Tiếp tục thay namespace đầy đủ ở cả dạng PHP và dạng escaped JSON.
- Thay namespace root trong `phpcs.xml.dist` bằng token XML chính xác `<element value="Fancyn"/>`, không tác động đến plugin name hoặc văn bản khác.
- Chỉ kiểm tra những placeholder nhận dạng Starter đầy đủ như namespace, constant prefix, PHP prefix, CSS prefix, slug và plugin name cũ.
- Không xem một token còn lại là lỗi nếu replacement tương ứng là no-op.
- Giữ nguyên danh sách file/directory loại trừ để rename engine và test của chính nó không tự sửa.

## Hành vi lỗi và rollback

- Validation metadata vẫn chạy trước khi tạo stage.
- PHP prefix phải có ít nhất bốn ký tự trước dấu `_`, đồng nhất với `WordPress.NamingConventions.PrefixAllGlobals` của WPCS 3.4.1.
- Prefix ngắn như `fcb_` phải bị từ chối ngay trong `npm run init` với thông báo nêu rõ độ dài tối thiểu.
- Mọi lỗi trong stage hoặc apply tiếp tục trả exit code khác 0.
- Source phải được rollback nguyên trạng nếu validation sau replacement thất bại.
- Không tạo marker trạng thái mới hoặc thay đổi identity khi rename thất bại.

## Workflow người dùng

README hướng dẫn dùng chuỗi lệnh fail-fast:

```powershell
npm run init && npm install && npm run package
```

Nếu `init` trả exit code khác 0, các bước sau không chạy. README cũng yêu cầu kiểm tra ZIP mang slug mới trước khi upload và không upload ZIP `fancyn-plugin-starter.zip` vào website đang chứa source Starter cùng slug.

Không thêm khóa cứng cấm đóng gói identity Starter vì maintainer vẫn cần phát hành Starter Kit gốc.

## Kiểm thử

- Regression test gọi rename engine với metadata `Fancyn Car Booking`, namespace `Fancyn\CarBooking`, PHP prefix `fcbook_` và xác nhận thành công.
- Validation test xác nhận `fcb_` bị từ chối trước khi rename.
- Kiểm tra main PHP file đổi thành `fancyn-car-booking.php` và file cũ biến mất.
- Kiểm tra namespace `Fancyn\CarBooking`, plugin name, slug, PHP prefix và CSS prefix xuất hiện đúng.
- Kiểm tra `phpcs.xml.dist` vẫn dùng namespace root `Fancyn` hợp lệ.
- Chạy lại test idempotency, validation và rollback hiện có.
- Trên clone sạch, chạy `npm run init`, `npm install`, `npm run lint`, `npm test` và `npm run package`.
- Xác nhận output là `build/fancyn-car-booking.zip`, ZIP có một root `fancyn-car-booking/` và không chứa identity Starter trong runtime files.

## Phạm vi

Thay đổi chỉ sửa token collision, bổ sung regression/acceptance test và làm rõ README. Không chuyển toàn bộ dự án sang hệ placeholder template và không thay đổi API runtime của plugin.
