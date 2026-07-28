# Fancyn Plugin Starter

[English version](README.en.md)

Bộ khởi tạo độc lập dành cho plugin quản trị WordPress, sử dụng các component PHP, Tailwind CSS được giới hạn phạm vi, Vite, Alpine.js, Tom Select và một số WordPress API. Mỗi dự án sau khi đổi tên có thể build và đóng gói độc lập; không cần “plugin UI lõi” khi chạy.

## Yêu cầu hệ thống

- WordPress 6.5 trở lên
- PHP 8.1 trở lên
- Node.js 20.19 trở lên
- npm 10 trở lên

Composer không được hỗ trợ và cũng không cần thiết. PHP vẫn là runtime hệ thống được gọi thông qua các lệnh npm.

## Cài đặt để phát triển

```bash
git clone <repository-url>
cd fancyn-plugin-starter
npm run init
npm install
npm run lint
npm test
npm run package
```

Chạy `npm run init` trước `npm install` để đặt tên cho plugin mới. Trình khởi tạo chỉ sử dụng các module tích hợp sẵn của Node nên chưa cần tải package JavaScript nào ở bước này.

Để tự động dừng khi khởi tạo thất bại, hãy nối các bước trong Bash, Command Prompt hoặc PowerShell 7:

```powershell
npm run init && npm install && npm run package
```

Nếu dùng Windows PowerShell 5.1, hãy kiểm tra exit code trước khi chạy bước tiếp theo:

```powershell
npm run init
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
npm install
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
npm run package
```

Lần chạy `npm install` đầu tiên sẽ thực thi `postinstall`, sau đó tải PHPUnit, PHPCS, WordPress Coding Standards và các standard phụ thuộc đã được khóa phiên bản và checksum vào cache `tools/` bị Git bỏ qua. Các lần cài đặt sau sẽ tái sử dụng cache hợp lệ, kể cả khi ngoại tuyến. Để buộc làm mới, hãy xóa các thư mục phiên bản được tạo bên dưới `tools/`, nhưng giữ lại manifest `tools/php-tools.json` đang được quản lý bởi Git, rồi chạy lại `npm install`. Lần thiết lập đầu tiên cần truy cập HTTPS đến các URL release đã khóa; nếu proxy hoặc tường lửa chặn tải xuống, hãy cấu hình proxy cho npm/hệ thống rồi thử lại. Nếu checksum không khớp, quá trình thiết lập sẽ dừng mà không thay thế tool hợp lệ trong cache—không được bỏ qua bước kiểm tra hoặc tin tưởng checksum tính từ response lỗi.

Đặt dự án trong `wp-content/plugins`, kích hoạt **Fancyn Plugin Starter**, rồi mở trang Dashboard hoặc Settings của plugin.

Để hiển thị trang showcase component khi phát triển cục bộ, hãy khai báo hằng số sau trước khi WordPress tải plugin:

```php
define( 'FANCYN_PLUGIN_STARTER_DEV_MODE', true );
```

Showcase xuất hiện tại **Tools → Plugin UI Kit**. Mặc định trang này bị tắt.

## Asset phát triển

```bash
npm run dev
```

Vite lắng nghe tại `http://127.0.0.1:5173`. Trên các màn hình thuộc plugin, PHP kiểm tra Vite client và dùng source entry HMR khi có thể. Nếu server không khả dụng, hệ thống chuyển sang `assets/dist/.vite/manifest.json`.

Asset không bao giờ được enqueue trên các trang quản trị WordPress không liên quan. Tailwind sử dụng prefix `fps-`, tắt Preflight, dùng `@tailwindcss/forms` ở class mode và giới hạn toàn bộ output PostCSS cuối cùng bên dưới `.fps-plugin-root`.

Tạo asset production bằng:

```bash
npm run build
npm run validate:build
```

Bản build release được minify, đặt tên theo hash, có Vite manifest và không chứa source map.

## Các lệnh kiểm tra chất lượng

```bash
npm run lint
npm run test
npm run build
```

- PHP tuân thủ WordPress Coding Standards, ngoại trừ các trường hợp tên file dùng namespace đã được ghi rõ.
- PHPUnit PHAR được khóa bằng checksum và WordPress shim thuộc repository kiểm thử các service cùng component PHP.
- Vitest kiểm thử hành vi JavaScript và các script release chạy bằng Node.
- ESLint và Stylelint kiểm tra JavaScript cùng CSS do dự án viết.

## Ranh giới thư mục

- `includes/` chứa mã ứng dụng PHP dùng namespace; giữ phần khởi động trong `Core`, điều phối quản trị trong `Admin`, endpoint HTTP trong `Http` và các control chỉ tạo markup trong `UI`.
- `templates/` chứa template trang thuộc plugin. Bọc UI của plugin trong `.fps-plugin-root`.
- `assets/src/` chứa JavaScript và CSS nguồn; `assets/dist/` được sinh tự động và không được chỉnh sửa thủ công.
- `languages/` chứa catalog bản dịch.
- `scripts/` chứa công cụ đổi tên, xác thực và đóng gói; `tests/` phản ánh các phạm vi trách nhiệm PHP, JavaScript, script và acceptance.
- `tools/`, `node_modules/`, `assets/dist/` và `build/` được tạo cục bộ. Tool tải xuống và dependency phát triển không bao giờ được đưa vào ZIP release.

## Sử dụng component

Cả mười một component đều cung cấp `make(array $args): string` và `render(array $args): void`.

```php
use Fancyn\PluginStarter\UI\Input;

Input::render(
	array(
		'name'        => 'company_name',
		'label'       => __( 'Company name', 'fancyn-plugin-starter' ),
		'value'       => $company_name,
		'description' => __( 'Displayed on reports.', 'fancyn-plugin-starter' ),
		'required'    => true,
		'error'       => $errors['company_name'] ?? '',
	)
);
```

Các component có sẵn:

- Button
- Input
- Textarea
- Select
- Checkbox
- Radio
- SwitchField
- Label
- FormField
- FormSection
- ValidationMessage

Component chỉ render và escape markup. Chúng không đọc request data, lưu option, kiểm tra capability hoặc enqueue dependency.

## Settings và WordPress API

Trang Settings minh họa:

- Đăng ký Settings API và xử lý nonce tiêu chuẩn
- Sanitize riêng theo từng field
- Bảo vệ bằng capability `manage_options`
- WordPress Color Picker
- Media uploader lưu attachment ID
- `wp_editor()`
- Thông báo lưu có hỗ trợ dịch

Luồng xử lý gồm đăng ký tại `admin_init`, gọi `register_setting()` với callback sanitize, xuất nonce bằng `settings_fields()`, render field bằng `do_settings_sections()` và dùng `submit_button()` để gửi form đến `options.php`. WordPress thực hiện kiểm tra capability và nonce; sanitizer xác thực từng field trước khi option được lưu.

Dependency media/editor/color của WordPress chỉ được tải trên các trang thực sự sử dụng chúng.

Các ví dụ REST và AJAX đều yêu cầu xác thực cùng capability `manage_options`. REST route khai báo schema và permission callback. AJAX xác thực nonce trước khi sanitize input. Không đăng ký action `nopriv` công khai.

## Khởi tạo Starter Kit

Chạy trình khởi tạo tương tác trước khi cài dependency:

```bash
npm run init
```

Trình khởi tạo lần lượt hỏi tên plugin, plugin slug, PHP namespace, PHP prefix và CSS prefix. Từ đó, công cụ suy ra text domain, option prefix, REST namespace và tên file PHP chính; hiển thị cấu hình JSON đầy đủ rồi yêu cầu xác nhận rõ ràng bằng `yes`. Câu trả lời khác sẽ hủy thao tác mà không thay đổi file.

Quy tắc xác thực yêu cầu slug viết thường và phân tách bằng dấu gạch ngang; PHP namespace có ít nhất hai segment hợp lệ; PHP prefix dùng snake case chữ thường, có ít nhất bốn ký tự trước dấu `_` cuối để đáp ứng WPCS; CSS prefix viết thường và kết thúc bằng `-`. Ví dụ, dùng `fcbook_` thay vì `fcb_`. Chạy lại với metadata giống hệt là an toàn; metadata khác sẽ bị từ chối sau khi dự án đã khởi tạo.

Để tự động hóa, hãy tạo một file metadata bên ngoài dự án, ví dụ `new-plugin.json`:

```json
{
  "pluginName": "Example Plugin",
  "pluginSlug": "example-plugin",
  "textDomain": "example-plugin",
  "namespace": "Vendor\\ExamplePlugin",
  "phpPrefix": "example_plugin_",
  "cssPrefix": "ep-",
  "optionPrefix": "example_plugin_",
  "restNamespace": "example-plugin/v1"
}
```

Chạy:

```bash
npm run rename -- --config ../new-plugin.json
```

Chế độ đổi tên bằng JSON sử dụng cùng quy tắc xác thực và rename engine theo giai đoạn như trình khởi tạo tương tác.

Trong phiên bản 1.0, text domain phải trùng với plugin slug và option prefix phải trùng với PHP prefix.

## Đóng gói bản phát hành

```bash
npm run package
```

Lệnh này chạy lint, toàn bộ test, production build, xác thực build và tạo ZIP không dùng Composer. Kết quả:

```text
build/fancyn-plugin-starter.zip
```

Sau khi khởi tạo thành công, tên ZIP phải khớp với plugin slug mới. Không upload `fancyn-plugin-starter.zip` vào website đang chứa thư mục source Starter cùng slug, vì WordPress sẽ thay thế thư mục đó bằng nội dung ZIP production.

`.distignore` là nguồn quy tắc loại trừ. ZIP chứa asset đã biên dịch, PHP autoloader thuộc dự án, runtime class, template, ngôn ngữ và README. ZIP loại bỏ source asset, test, script, cấu hình phát triển, `node_modules/`, `tools/`, `vendor/`, Composer manifest, source map và file repository.

Sau khi cài đặt, ZIP chỉ yêu cầu runtime WordPress/PHP được hỗ trợ.

## Kiểm chứng trên website sạch

Sử dụng website WordPress 6.5+ sạch với PHP 8.1+ và làm theo [checklist smoke test gói phát hành](tests/acceptance/package-smoke.md). Cài ZIP được tạo thông qua **Plugins → Add New Plugin → Upload Plugin**, kích hoạt plugin, bật dev mode rõ ràng, truy cập mọi trang plugin và showcase, lưu settings, kiểm tra các tích hợp WordPress cùng đường dẫn HTTP được phép/không được phép, sau đó tắt dev mode và xác nhận showcase biến mất. Test tự động không thay thế cho kiểm tra responsive và thao tác chỉ bằng bàn phím trên trình duyệt.

## Quốc tế hóa

Mọi chuỗi PHP hiển thị đều dùng text domain `fancyn-plugin-starter`. Không nối các đoạn câu đã dịch. Khi bổ sung message động mới, chuỗi dành cho JavaScript nên được truyền qua cơ chế localization hoặc script translation của WordPress.

Cài lệnh i18n của WordPress một lần, sau đó tạo lại translation template khi thay đổi chuỗi hiển thị:

```bash
wp package install wp-cli/i18n-command
wp i18n make-pot . languages/fancyn-plugin-starter.pot --domain=fancyn-plugin-starter --exclude=node_modules,tools,assets/dist,build,.worktrees
```

Commit file POT được tạo khi chuẩn bị release có bản dịch. Chạy lại lệnh sau khi đổi tên để slug, text domain và tên file output khớp với dự án đã đổi tên.

## Quy tắc bảo mật

- Xem dữ liệu `$_POST`, `$_GET`, REST và AJAX là không đáng tin cậy.
- Sanitize theo loại field và xác thực trước khi lưu.
- Escape output theo đúng ngữ cảnh HTML.
- Yêu cầu nonce cho mọi thao tác thay đổi dữ liệu tùy chỉnh.
- Ưu tiên capability thay vì tên role.
- Mọi REST route phải có permission callback rõ ràng.
- Hàm và hằng số global phải dùng prefix của dự án.

## Phạm vi phiên bản 1.0

Không bao gồm: React, shadcn/ui, component frontend công khai, dark mode, adapter select từ xa, data table nâng cao, date-range picker, hạ tầng toast đầy đủ hoặc trình tạo form kéo thả.

Xem `docs/WORDPRESS-PLUGIN-STARTER-SPEC.md` và `docs/DESIGN-RULES.md` để tham khảo các yêu cầu sản phẩm và hình ảnh có tính chuẩn tắc.
