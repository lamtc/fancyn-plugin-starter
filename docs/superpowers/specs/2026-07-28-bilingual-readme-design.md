# Thiết kế README song ngữ

## Mục tiêu

Cung cấp tài liệu bắt đầu sử dụng bằng tiếng Việt làm mặc định, đồng thời tiếp tục duy trì bản tiếng Anh cho người dùng quốc tế.

## Cấu trúc tài liệu

- `README.md` là bản tiếng Việt mặc định trên GitHub và trong gói phát hành.
- `README.en.md` lưu nội dung tiếng Anh hiện tại.
- Đầu mỗi file có liên kết rõ ràng sang phiên bản ngôn ngữ còn lại.

## Quy tắc chuyển ngữ

- Dịch toàn bộ tiêu đề, đoạn mô tả, danh sách và hướng dẫn sang tiếng Việt tự nhiên, nhất quán.
- Giữ nguyên command, code block, namespace, tên class/API, đường dẫn, slug, text domain và yêu cầu phiên bản.
- Không thay đổi hành vi, quy trình build hoặc phạm vi tính năng của Starter Kit.
- Thuật ngữ kỹ thuật phổ biến như WordPress, PHP, npm, REST, AJAX, Tailwind CSS và Vite được giữ nguyên; phần giải thích xung quanh dùng tiếng Việt.

## Đóng gói

Cả `README.md` và `README.en.md` phải có trong ZIP phát hành. Script đóng gói và `.distignore` không được loại trừ hai file này.

## Kiểm chứng

- Kiểm tra liên kết chuyển ngôn ngữ ở cả hai file.
- So sánh cấu trúc để bảo đảm bản tiếng Việt không bỏ sót phần nào của bản tiếng Anh.
- Chạy `npm run lint`, `npm test`, `npm run build`, `npm run validate:build` và `npm run package`.
- Kiểm tra ZIP chứa cả hai README và không làm thay đổi các quy tắc loại trừ hiện có.

## Phạm vi ngoài thiết kế

- Không dịch mã nguồn, tên API hoặc catalog gettext trong thay đổi này.
- Không thay đổi nội dung các spec kỹ thuật khác sang tiếng Việt.
- Không tạo cơ chế tự động đồng bộ hai README ở phiên bản đầu tiên.
