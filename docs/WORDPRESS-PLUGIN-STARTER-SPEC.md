# WordPress Plugin Starter Kit with Tailwind CSS
## Product & Technical Specification

## 1. Mục tiêu

Xây dựng một bộ khung tiêu chuẩn để khởi tạo nhanh các plugin WordPress có giao diện quản trị riêng, sử dụng PHP templates, Tailwind CSS, Vite và một bộ component form tái sử dụng.

Starter Kit phải đáp ứng các mục tiêu:

- Dùng làm nền cho nhiều plugin WordPress khác nhau.
- Mỗi plugin được build và đóng gói độc lập.
- Không phụ thuộc vào một plugin lõi dùng chung.
- Không gây xung đột CSS với WordPress hoặc plugin khác.
- Có sẵn các component form cơ bản.
- Có quy trình phát triển, build và đóng gói ZIP rõ ràng.
- Phù hợp với plugin PHP truyền thống.
- Có thể mở rộng sang Alpine.js, Tom Select và WordPress APIs.
- Chỉ dùng React + shadcn/ui khi plugin phát triển theo dạng SPA phức tạp.

---

## 2. Nguyên tắc kiến trúc

### 2.1. Starter template, không phải plugin dependency

Starter Kit được sử dụng để tạo plugin mới bằng cách copy hoặc generate project.

Không thiết kế theo mô hình:

```text
Core UI Plugin
├── Plugin A
├── Plugin B
└── Plugin C
```

Thiết kế mục tiêu:

```text
WordPress Plugin Starter Kit
        ↓ copy / generate
Plugin A    Plugin B    Plugin C
```

Mỗi plugin sau khi tạo phải:

- Tự chứa CSS và JavaScript đã build.
- Hoạt động độc lập.
- Có namespace và prefix riêng.
- Không cần cài thêm plugin nền.
- Có thể đóng gói thành một file ZIP riêng.

---

## 3. Công nghệ sử dụng

### Bắt buộc

- WordPress Plugin API
- PHP 8.1+
- Tailwind CSS
- Vite
- `@tailwindcss/forms`
- ES Modules
- Project-owned PHP namespace autoloader; Composer is unsupported
- npm-provisioned, checksum-pinned PHPUnit, PHPCS, WPCS, and standards dependencies

### Tích hợp sẵn

- Alpine.js
- Tom Select

### Tích hợp qua WordPress API

- WordPress Media Uploader
- WordPress Editor
- WordPress Color Picker
- Settings API
- REST API hoặc AJAX mẫu
- Nonce và capability checks

### Không tích hợp mặc định

- React
- shadcn/ui

React và shadcn/ui chỉ được thêm khi plugin là một SPA phức tạp, có state lớn, routing nội bộ hoặc nhiều màn hình động.

---

## 4. Cấu trúc thư mục

```text
wordpress-plugin-starter/
├── assets/
│   ├── src/
│   │   ├── css/
│   │   │   ├── admin.css
│   │   │   ├── components.css
│   │   │   └── utilities.css
│   │   ├── js/
│   │   │   ├── admin.js
│   │   │   ├── alpine.js
│   │   │   ├── tom-select.js
│   │   │   └── wordpress-api.js
│   │   └── icons/
│   └── dist/
│       ├── css/
│       │   └── admin.css
│       ├── js/
│       │   └── admin.js
│       └── manifest.json
├── includes/
│   ├── Admin/
│   │   ├── Admin.php
│   │   ├── Assets.php
│   │   ├── Menu.php
│   │   └── Settings.php
│   ├── Core/
│   │   ├── Activator.php
│   │   ├── Deactivator.php
│   │   ├── Plugin.php
│   │   └── ServiceProvider.php
│   ├── Http/
│   │   ├── Ajax.php
│   │   └── Rest.php
│   ├── Support/
│   │   ├── Arr.php
│   │   ├── Html.php
│   │   ├── View.php
│   │   └── Validation.php
│   └── UI/
│       ├── Button.php
│       ├── Checkbox.php
│       ├── FormField.php
│       ├── FormSection.php
│       ├── Input.php
│       ├── Label.php
│       ├── Radio.php
│       ├── Select.php
│       ├── SwitchField.php
│       ├── Textarea.php
│       └── ValidationMessage.php
├── templates/
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── settings.php
│   │   └── components-demo.php
│   └── partials/
│       ├── page-header.php
│       ├── notice.php
│       └── empty-state.php
├── languages/
├── scripts/
│   ├── build-zip.mjs
│   ├── init-plugin.mjs
│   ├── rename-plugin.mjs
│   ├── run-php-tool.mjs
│   ├── setup-php-tools.mjs
│   └── validate-build.mjs
├── tools/
│   └── php-tools.json
├── tests/
├── package.json
├── vite.config.js
├── tailwind.config.js
├── postcss.config.js
├── phpunit.xml
├── plugin-name.php
├── uninstall.php
├── README.md
├── DESIGN-RULES.md
└── .distignore
```

---

## 5. Quy ước định danh

Mỗi plugin phải định nghĩa một bộ thông tin duy nhất:

```text
Plugin Name: Example Plugin
Plugin Slug: example-plugin
Text Domain: example-plugin
PHP Namespace: Vendor\ExamplePlugin
PHP Prefix: example_plugin_
CSS Prefix: ep-
Option Prefix: example_plugin_
Database Prefix: {$wpdb->prefix}example_plugin_
REST Namespace: example-plugin/v1
```

Không hard-code prefix của starter trong component.

Nên lưu metadata project tại một file cấu hình:

```json
{
  "pluginName": "Example Plugin",
  "pluginSlug": "example-plugin",
  "namespace": "Vendor\\ExamplePlugin",
  "phpPrefix": "example_plugin_",
  "cssPrefix": "ep-",
  "textDomain": "example-plugin"
}
```

Script `rename-plugin.mjs` phải thay toàn bộ placeholder trong project khi tạo plugin mới.

---

## 6. Tailwind CSS

### 6.1. Prefix class riêng

Tailwind phải sử dụng prefix riêng cho từng plugin:

```js
export default {
  prefix: 'ep-',
}
```

Ví dụ:

```html
<div class="ep-rounded-2xl ep-bg-white ep-p-6 ep-shadow-sm">
  Nội dung
</div>
```

### 6.2. Tắt Preflight

```js
export default {
  corePlugins: {
    preflight: false,
  },
}
```

Mục tiêu:

- Không reset button, input, heading và table của WordPress.
- Không làm thay đổi giao diện trang admin ngoài phạm vi plugin.
- Không gây xung đột với plugin khác.

### 6.3. Content scanning

```js
content: [
  './plugin-name.php',
  './includes/**/*.php',
  './templates/**/*.php',
  './assets/src/**/*.{js,ts,jsx,tsx}',
]
```

### 6.4. Root scope

Toàn bộ giao diện plugin phải nằm trong:

```html
<div id="example-plugin-app" class="ep-plugin-root">
  ...
</div>
```

CSS tự viết phải được scope trong `.ep-plugin-root` khi có khả năng ảnh hưởng đến phần tử WordPress.

### 6.5. Forms plugin

Dùng `@tailwindcss/forms` theo strategy `class`:

```js
plugins: [
  require('@tailwindcss/forms')({
    strategy: 'class',
  }),
]
```

Không áp dụng global form reset.

---

## 7. Vite

### 7.1. Entry points

```js
build: {
  manifest: true,
  rollupOptions: {
    input: {
      admin: 'assets/src/js/admin.js',
      styles: 'assets/src/css/admin.css',
    },
  },
}
```

### 7.2. Chế độ development

```bash
npm run dev
```

Yêu cầu:

- HMR cho CSS và JavaScript.
- Có helper PHP để phát hiện Vite dev server.
- Khi dev server không chạy, tự động dùng file trong `assets/dist`.

### 7.3. Chế độ production

```bash
npm run build
```

Yêu cầu:

- Minify CSS và JavaScript.
- Sinh `manifest.json`.
- Không tạo source map trong bản release.
- Hash file hoặc dùng version plugin để cache busting.

---

## 8. Enqueue assets

### 8.1. Chỉ enqueue trong trang plugin

Không enqueue trên toàn bộ `wp-admin`.

```php
public function enqueue(string $hookSuffix): void
{
    if (!in_array($hookSuffix, $this->allowedHooks(), true)) {
        return;
    }

    $this->enqueueAdminAssets();
}
```

### 8.2. Kiểm tra màn hình

Có thể dùng thêm:

```php
$currentScreen = get_current_screen();

if (!$currentScreen || !str_starts_with($currentScreen->id, 'example-plugin')) {
    return;
}
```

### 8.3. WordPress dependencies

Chỉ enqueue những dependency thực sự cần:

- `wp-color-picker`
- `wp-editor`
- `media-editor`
- `wp-api-fetch`

Không tải mặc định trên mọi trang plugin.

---

## 9. Component system

Component dùng PHP để render HTML. Không xây một DSL phức tạp.

Mỗi component phải:

- Escape output đúng ngữ cảnh.
- Hỗ trợ attributes bổ sung.
- Hỗ trợ disabled, required và readonly khi phù hợp.
- Có state mặc định, hover, focus, active, disabled và error.
- Có ARIA attributes hợp lý.
- Dùng native HTML element khi có thể.
- Không chứa logic nghiệp vụ.
- Không tự lưu dữ liệu.
- Không tự đọc `$_POST`.

API component nên nhất quán:

```php
UI\Input::render([
    'name' => 'company_name',
    'label' => 'Tên công ty',
    'value' => $companyName,
    'description' => 'Tên hiển thị trên báo cáo.',
    'required' => true,
    'error' => $errors['company_name'] ?? null,
]);
```

Có thể hỗ trợ trả chuỗi:

```php
$html = UI\Input::make([...]);
```

---

## 10. Danh sách component cơ bản

### 10.1. Button

Biến thể:

- primary
- secondary
- outline
- ghost
- danger
- link

Kích thước:

- sm
- md
- lg
- icon

Thuộc tính:

```php
[
  'type' => 'button',
  'variant' => 'primary',
  'size' => 'md',
  'loading' => false,
  'disabled' => false,
  'icon' => null,
  'text' => 'Lưu thay đổi',
]
```

### 10.2. Input

Hỗ trợ:

- text
- email
- number
- password
- url
- search
- hidden

States:

- default
- focus
- error
- disabled
- readonly

### 10.3. Textarea

Hỗ trợ:

- rows
- maxlength
- character counter
- auto-resize tùy chọn

### 10.4. Select

Hai chế độ:

- Native select
- Enhanced select bằng Tom Select

Hỗ trợ:

- placeholder
- options
- option groups
- searchable
- clearable
- multiple
- remote data adapter ở tầng mở rộng

### 10.5. Checkbox

Dùng native checkbox.

Hỗ trợ:

- label
- description
- checked
- disabled
- indeterminate bằng JavaScript khi cần

### 10.6. Radio

Hỗ trợ:

- radio đơn
- radio group
- card radio ở tầng mở rộng

### 10.7. Switch

Switch phải dùng input checkbox thật.

Yêu cầu:

- Submit được với form PHP.
- Có trạng thái checked và disabled.
- Hỗ trợ label và description.
- Không bắt buộc Alpine.js với switch đơn giản.
- Alpine.js chỉ dùng khi switch điều khiển vùng nội dung khác.

### 10.8. Label

Hỗ trợ:

- `for`
- required marker
- optional text
- tooltip slot ở tầng mở rộng

### 10.9. FormField

Component wrapper chuẩn:

```text
Label
Control
Description
ValidationMessage
```

FormField chịu trách nhiệm:

- Khoảng cách dọc.
- Liên kết label và input.
- Hiển thị description.
- Hiển thị error.
- Áp dụng `aria-describedby`.
- Áp dụng `aria-invalid`.

### 10.10. FormSection

Cấu trúc:

```text
Section title
Section description
Content area
Optional actions
```

Biến thể:

- card
- flat
- divided
- compact

### 10.11. ValidationMessage

Biến thể:

- error
- warning
- success
- info

Yêu cầu:

- Có icon tùy chọn.
- Dùng `role="alert"` cho lỗi submit.
- Không chỉ dựa vào màu sắc.

---

## 11. Alpine.js

Alpine.js được tích hợp sẵn nhưng chỉ khởi tạo ở trang plugin.

Use cases:

- Modal
- Tabs
- Dropdown
- Disclosure
- Conditional form
- Toast
- Confirm dialog
- Show/hide advanced settings

Không dùng Alpine.js để thay thế toàn bộ data layer của plugin.

Quy ước:

```html
<div x-data="settingsPanel()">
```

Không viết object Alpine dài trực tiếp trong template. Logic dài phải đặt trong file JS.

---

## 12. Tom Select

Tom Select chỉ khởi tạo trên element có data attribute:

```html
<select data-enhanced-select>
```

Không thay thế tất cả native select.

Dùng Tom Select khi cần:

- Search.
- Multi-select.
- Clear value.
- Tạo option mới.
- Danh sách lớn.
- Async data.

Các select nhỏ dưới khoảng 10–15 option nên giữ native.

---

## 13. WordPress APIs

### Media Uploader

Tạo helper cho:

- Chọn ảnh.
- Chọn file.
- Hiển thị preview.
- Xóa lựa chọn.
- Lưu attachment ID, không chỉ URL.

### Editor

Dùng `wp_editor()` cho nội dung rich text.

Không tự nhúng editor bên thứ ba trong starter.

### Color Picker

Dùng `wp-color-picker`.

Component color input chỉ chịu trách nhiệm render field và data attribute.

### Settings API

Starter phải có ví dụ:

- Register setting.
- Sanitize callback.
- Capability check.
- Nonce.
- Settings section.
- Save notice.

---

## 14. Validation và bảo mật

### Input

- Không tin dữ liệu từ `$_POST`, `$_GET`, REST request.
- Sanitize theo kiểu dữ liệu.
- Validate trước khi lưu.
- Không sử dụng một hàm sanitize chung cho mọi field.

### Output

- `esc_html()`
- `esc_attr()`
- `esc_url()`
- `wp_kses_post()`

### Quyền

- Dùng `current_user_can()`.
- Capability mặc định cho admin page: `manage_options`.
- Không kiểm tra role trực tiếp nếu capability phù hợp hơn.

### Nonce

Mọi thao tác thay đổi dữ liệu phải có nonce.

### REST API

- Permission callback bắt buộc.
- Validate schema.
- Không để public endpoint mặc định.

---

## 15. Design demo page

Starter Kit phải có một trang:

```text
Tools > Plugin UI Kit
```

Hoặc menu riêng trong development mode.

Trang này hiển thị:

- Tất cả button variants.
- Input states.
- Textarea.
- Native select.
- Tom Select.
- Checkbox.
- Radio group.
- Switch.
- Validation states.
- Form sections.
- Color picker.
- Media uploader.
- Editor.
- Alpine modal.
- Tabs và dropdown.

Trang demo không được đăng ký trong production nếu cấu hình tắt.

---

## 16. Build ZIP

Lệnh:

```bash
npm run package
```

Quy trình:

1. Chạy lint.
2. Chạy test.
3. Chạy production build.
4. Kiểm tra `assets/dist`.
5. Copy file sang thư mục tạm.
6. Loại bỏ file development.
7. Tạo ZIP theo plugin slug.

Không đưa các mục sau vào ZIP:

```text
node_modules/
tools/
vendor/
.git/
.github/
tests/
scripts/
assets/src/
*.map
vite.config.js
tailwind.config.js
postcss.config.js
package.json
package-lock.json
composer.json
composer.lock
phpunit.xml
.env*
.DS_Store
```

Composer không được hỗ trợ. ZIP phải loại trừ `composer.json`, `composer.lock`, `vendor/`, `tools/` và mọi development dependency; runtime phải dùng `includes/Autoloader.php`.

Dùng `.distignore` làm nguồn quy tắc loại trừ.

---

## 17. Scripts

### `npm run dev`

Khởi chạy Vite dev server.

### `npm run build`

Build production assets.

### `npm run lint`

Lint JavaScript, CSS và PHP.

### `npm run test`

Chạy unit tests.

### `npm run package`

Build và tạo ZIP.

### `npm run init`

Hỏi plugin name, slug, PHP namespace, PHP prefix và CSS prefix; hiển thị toàn bộ metadata suy ra để xác nhận rồi đổi project bằng staged rename engine có tính atomic và idempotent. Script chỉ dùng Node built-ins và phải chạy được trước `npm install`.

### `npm run rename`

Đổi toàn bộ metadata starter sang plugin mới.

---

## 18. Coding standards

### PHP

- Bắt buộc WordPress Coding Standards; không dùng PSR-12 làm standard thay thế.
- Namespace cho class.
- Function global phải có prefix.
- Type declarations khi phù hợp.
- Không để business logic trong template.

### JavaScript

- ES Modules.
- Không gắn biến tùy ý vào `window`.
- Mỗi tính năng là một module nhỏ.
- Không dùng jQuery trừ khi tích hợp WordPress API yêu cầu.

### CSS

- Ưu tiên Tailwind utility.
- Component CSS chỉ dùng cho mẫu lặp lại khó đọc.
- Không dùng `!important` trừ integration bắt buộc.
- Không style trực tiếp selector chung như `button`, `input`, `table`, `h1`.

---

## 19. Accessibility

Mức tối thiểu:

- Label liên kết đúng input.
- Focus ring rõ ràng.
- Keyboard navigation.
- Contrast đủ đọc.
- Error có text, không chỉ màu.
- Modal có focus management.
- Dropdown và tabs dùng ARIA phù hợp.
- Switch có accessible name.
- Icon-only button phải có `aria-label`.

---

## 20. Internationalization

Mọi chuỗi hiển thị phải dịch được:

```php
__('Save changes', 'example-plugin');
esc_html_e('Settings', 'example-plugin');
```

Không ghép câu dịch bằng nhiều fragment.

JavaScript strings truyền qua `wp_localize_script()` hoặc `wp_set_script_translations()`.

---

## 21. Acceptance criteria

Starter Kit được coi là hoàn thành khi:

- Cài đặt và activate không có warning/error.
- `npm run dev` hoạt động.
- `npm run build` tạo file production.
- CSS có prefix riêng.
- Tailwind Preflight bị tắt.
- Assets chỉ enqueue trên trang plugin.
- Trang demo render đầy đủ 11 component.
- Tom Select hoạt động.
- Alpine modal, tabs và dropdown hoạt động.
- Media uploader, editor và color picker hoạt động.
- Component có validation state.
- Form demo lưu bằng Settings API.
- ZIP production không chứa `node_modules`.
- ZIP production không chứa source development.
- Plugin hoạt động khi cài ZIP trên một WordPress sạch.
- Không làm thay đổi style ở trang admin khác.

---

## 22. Phạm vi phiên bản đầu tiên

### Version 1.0

Bao gồm:

- Core plugin bootstrap.
- Vite.
- Tailwind.
- PHP component renderer.
- 11 form components cơ bản.
- Alpine.js.
- Tom Select.
- WordPress APIs demo.
- Settings page demo.
- Components demo page.
- Packaging script.
- Documentation.

### Chưa bao gồm

- Data table nâng cao.
- Date range picker.
- Toast system hoàn chỉnh.
- React SPA.
- shadcn/ui.
- Remote select adapter.
- Form builder kéo thả.
- Theme switcher.
- Frontend public UI system.

---

## 23. Định hướng mở rộng

Sau phiên bản 1.0 có thể bổ sung:

- Modal.
- Tabs.
- Dropdown.
- Alert.
- Badge.
- Card.
- Tooltip.
- Toast.
- Pagination.
- Empty state.
- Data table.
- Date picker.
- File uploader.
- Confirm dialog.
- CLI generator.
- Plugin update checker.
- Licensing adapter.
