# Design Rules
## Flat Minimalist Admin UI for WordPress Plugins

## 1. Định hướng hình ảnh

Bộ giao diện sử dụng phong cách:

- Flat design.
- Minimalist.
- Sạch, rõ ràng và có nhiều khoảng thở.
- Tập trung vào typography mạnh.
- Card trắng trên nền xám nhạt.
- Shadow nhẹ khi card nằm trên nền trắng hoặc cần phân lớp.
- Bo góc vừa đến lớn.
- Màu nhấn ấm, ưu tiên cam đỏ.
- Không dùng gradient mặc định.
- Không dùng glassmorphism.
- Không lạm dụng border.
- Không dùng hiệu ứng 3D nặng.

Cảm giác tổng thể cần đạt:

```text
Hiện đại
Thân thiện
Gọn gàng
Có cá tính
Dễ quét nội dung
Không giống giao diện WordPress mặc định
```

---

## 2. Design tokens

### 2.1. Màu nền

```text
Page background:       #F4F4F3
Surface primary:       #FFFFFF
Surface muted:         #F8F8F7
Surface selected:      #FFF3EE
```

### 2.2. Màu chữ

```text
Text primary:          #171717
Text secondary:        #5F5F5B
Text muted:            #8A8A84
Text inverse:          #FFFFFF
```

### 2.3. Màu nhấn

```text
Accent primary:        #F4511E
Accent hover:          #DC4317
Accent soft:           #FFF0EA
Accent border:         #FF6A3D
```

### 2.4. Semantic colors

```text
Success:               #16A36A
Success soft:          #EAF8F1

Warning:               #D98E04
Warning soft:          #FFF7E3

Danger:                #D9363E
Danger soft:           #FDECEE

Info:                  #2563EB
Info soft:             #EDF4FF
```

### 2.5. Border

```text
Border default:        #E2E2DF
Border strong:         #CFCFCA
Border focus:          Accent primary
```

---

## 3. Typography

### 3.1. Font body

Ưu tiên font hệ thống để plugin nhẹ và đồng nhất với admin:

```css
font-family:
  Inter,
  ui-sans-serif,
  system-ui,
  -apple-system,
  BlinkMacSystemFont,
  "Segoe UI",
  sans-serif;
```

Không đóng gói font thương mại hoặc font tùy chỉnh trong starter.

### 3.2. Font heading

Ảnh tham chiếu sử dụng heading condensed, đậm và mang tính display.

Trong starter mặc định:

- Dùng system font với `font-extrabold`.
- Có thể cho phép project cụ thể thêm display font riêng.
- Không bắt buộc starter tải Google Fonts.

### 3.3. Thang chữ

```text
Page title:            32–40px / 800
Section title:         20–24px / 700
Card title:            16–18px / 700
Body:                  14–16px / 400
Label:                 13–14px / 600
Helper text:           12–13px / 400
Button:                14px / 600
```

### 3.4. Line-height

```text
Heading:               1.15–1.25
Body:                  1.55–1.7
Label:                 1.4
```

Không dùng body text quá chật.

---

## 4. Layout

### 4.1. Page shell

```text
Max width:             1280px
Horizontal padding:    24px desktop
Horizontal padding:    16px mobile
Vertical padding:      24–32px
```

### 4.2. Nền trang

Mặc định:

```text
Nền trang xám nhạt
Card nội dung trắng
```

Ví dụ:

```html
<div class="ep-min-h-screen ep-bg-[#F4F4F3] ep-p-6">
```

### 4.3. Content card

```text
Background:            White
Border radius:         16–20px
Padding:               20–28px
Border:                Optional 1px
Shadow:                Rất nhẹ
```

Ví dụ:

```text
rounded-2xl
bg-white
p-6
shadow-sm
ring-1 ring-black/5
```

### 4.4. Khi page background là trắng

Card phải có ít nhất một trong hai:

- Border xám rất nhạt.
- Shadow nhẹ.

Không để box trắng hòa hoàn toàn vào nền trắng.

### 4.5. Grid

```text
Dashboard cards:       12-column grid
Settings layout:       8/4 hoặc 9/3
Form sections:         1 column mặc định
Two-column form:       Chỉ dùng khi field ngắn và liên quan
```

Mobile luôn chuyển về một cột.

---

## 5. Spacing

Dùng hệ 4px:

```text
4, 8, 12, 16, 20, 24, 32, 40, 48, 64
```

Quy tắc:

- Label → control: 6–8px.
- Control → helper/error: 6–8px.
- Field → field: 20–24px.
- Section title → content: 16–20px.
- Card → card: 20–24px.
- Page title → first section: 24–32px.

Không dùng spacing ngẫu nhiên như 13px, 19px hoặc 27px nếu không có lý do.

---

## 6. Border radius

```text
Input:                 8–10px
Button:                8–10px
Badge:                 Full hoặc 9999px
Card:                  16–20px
Modal:                 18–24px
Avatar/Icon surface:   Full hoặc 14px
```

Không bo góc quá lớn cho input nhỏ.

---

## 7. Shadow

Shadow phải tinh tế:

### Card trên nền xám

```css
box-shadow:
  0 1px 2px rgba(0, 0, 0, 0.03),
  0 8px 24px rgba(0, 0, 0, 0.04);
```

### Popover hoặc dropdown

```css
box-shadow:
  0 12px 30px rgba(0, 0, 0, 0.10);
```

### Quy tắc

- Không dùng shadow đậm cho mọi card.
- Không kết hợp border đậm và shadow đậm.
- Hover card chỉ nâng nhẹ.
- Form control không cần shadow thường trực.

---

## 8. Header và nhãn section

Có thể dùng nhãn capsule nhỏ tương tự ảnh tham chiếu:

```text
[ W. ]  SETTINGS
```

Đặc điểm:

- Border màu accent.
- Hình pill.
- Icon hoặc ký hiệu nằm trong vòng tròn accent.
- Text uppercase.
- Font 11–12px, đậm.
- Không dùng cho mọi section; chỉ dùng ở page header hoặc nhóm nội dung lớn.

Page title:

- Đậm.
- Tương phản cao.
- Có thể uppercase với màn hình marketing hoặc onboarding.
- Với settings/admin thuần, ưu tiên title case để dễ đọc.

---

## 9. Form controls

### 9.1. Input và textarea

```text
Height input:          40–44px
Border:                1px solid
Background:            White
Text:                  14px
Padding horizontal:    12–14px
Focus ring:            2–3px accent soft
Focus border:          Accent
```

Không dùng input quá cao trong settings page.

### 9.2. Select

Native select giữ style giống input.

Tom Select phải được theme lại để:

- Cùng chiều cao.
- Cùng border radius.
- Cùng typography.
- Dropdown shadow nhẹ.
- Selected item dễ nhận biết.
- Multi-value tag không quá sặc sỡ.

### 9.3. Checkbox

- Kích thước 16–18px.
- Bo nhẹ.
- Checked background dùng accent.
- Focus ring rõ.
- Label đặt bên phải.
- Description nằm dưới label.

### 9.4. Radio

- Kích thước 16–18px.
- Selected dùng accent.
- Radio card chỉ dùng khi lựa chọn có mô tả dài.

### 9.5. Switch

```text
Track:                 40–44px × 22–24px
Thumb:                 16–18px
Off:                   Gray
On:                    Accent
Transition:            150–200ms
```

Switch chỉ dùng cho trạng thái bật/tắt ngay lập tức.

Không dùng switch cho lựa chọn cần bấm nút Save nếu người dùng có thể hiểu nhầm rằng thay đổi đã được lưu. Trong trường hợp đó cần hiển thị rõ trạng thái chưa lưu.

---

## 10. Button

### Primary

```text
Background:            Accent
Text:                  White
Hover:                 Accent hover
```

### Secondary

```text
Background:            Text primary
Text:                  White
```

### Outline

```text
Background:            White
Border:                Border strong
Text:                  Text primary
```

### Ghost

```text
Background:            Transparent
Hover:                 Surface muted
```

### Danger

```text
Background:            Danger
Text:                  White
```

Quy tắc:

- Một khu vực chỉ nên có một primary action.
- Không dùng màu accent cho mọi button.
- Button icon-only phải có tooltip hoặc aria-label.
- Loading state không làm button đổi chiều rộng.
- Không dùng all-caps cho button dài.

---

## 11. Form sections

Form section mặc định:

```text
Card trắng
Title đậm
Description màu secondary
Fields chia khoảng cách rõ
Action nằm cuối card
```

Cấu trúc gợi ý:

```text
┌──────────────────────────────────────┐
│ Thông tin chung                      │
│ Cấu hình cơ bản của plugin.          │
│                                      │
│ [Field]                              │
│ [Field]                              │
│ [Field]                              │
│                                      │
│                         [Lưu thay đổi]│
└──────────────────────────────────────┘
```

Với form dài, chia thành nhiều card thay vì một card khổng lồ.

---

## 12. Lists và service-style rows

Ảnh tham chiếu dùng các hàng nhỏ có border trong card lớn.

Pattern này có thể dùng cho:

- Feature list.
- Permission list.
- Integration list.
- Checklist.
- Summary rows.

Style:

```text
Background:            White hoặc surface muted
Border:                1px solid border default
Radius:                8–10px
Padding:               10–12px
Gap:                   8–10px
```

Không dùng emoji mặc định cho sản phẩm hoàn chỉnh. Starter có thể dùng Dashicons, SVG icon riêng hoặc icon library được chọn thống nhất.

---

## 13. Cards thống kê

Có thể dùng giống ảnh giới thiệu:

```text
Icon surface nhỏ
Số lớn, đậm
Label nhỏ, 2 dòng tối đa
Card trắng
Shadow nhẹ
```

Quy tắc:

- Tối đa 4 card một hàng.
- Số là nội dung nổi bật nhất.
- Không dùng quá nhiều màu.
- Icon cùng một hệ thống.
- Không dùng chart giả chỉ để trang trí.

---

## 14. Icons

Ưu tiên theo thứ tự:

1. WordPress Dashicons khi phù hợp.
2. SVG icon nội bộ.
3. Một icon library duy nhất cho project.

Không trộn nhiều icon style.

Icon mặc định:

```text
Size inline:           16–18px
Size button:           16px
Size card:             20–24px
Stroke width:          Đồng nhất
```

---

## 15. Interaction

### Hover

- Thay đổi nền hoặc border nhẹ.
- Không scale mạnh.
- Không rung, bounce hoặc glow.

### Focus

- Luôn nhìn thấy.
- Dùng accent soft + accent border.
- Không xóa outline mà không có phương án thay thế.

### Transition

```text
Duration:              150–200ms
Properties:            color, background, border, opacity, transform
```

Không dùng transition-all khi không cần.

---

## 16. Modal, dropdown và tabs

### Modal

- Nền overlay đen 30–40%.
- Card modal trắng.
- Radius 18–24px.
- Max width theo nội dung.
- Header, body và footer phân tách rõ.
- Nút đóng dễ tìm.
- Escape để đóng.
- Focus trap.

### Dropdown

- Nền trắng.
- Border nhẹ.
- Shadow rõ hơn card.
- Item cao tối thiểu 36px.
- Selected/hover dùng surface muted.
- Destructive item dùng màu danger.

### Tabs

- Dùng underline hoặc segmented control.
- Không kết hợp cả hai.
- Active state rõ ràng.
- Mobile có thể scroll ngang.

---

## 17. Validation states

### Error

- Border danger.
- Focus ring danger soft.
- Message danger.
- Có icon tùy chọn.
- Message đặt sát field.

### Success

Chỉ hiển thị khi thực sự hữu ích, không đánh dấu xanh tất cả field hợp lệ.

### Warning

Dùng cho dữ liệu có thể lưu nhưng cần chú ý.

Không chỉ dùng màu; luôn có text hoặc icon.

---

## 18. Empty state

Cấu trúc:

```text
Icon hoặc illustration nhỏ
Title
Mô tả 1–2 dòng
Primary action
Optional secondary action
```

Không để trang trống hoàn toàn.

---

## 19. Responsive

### Desktop

- Sidebar WordPress vẫn được tôn trọng.
- Không đặt content rộng vượt viewport.
- Form text dài giới hạn khoảng 720–800px.

### Tablet

- Grid giảm cột.
- Card padding giảm nhẹ.
- Action group có thể wrap.

### Mobile

- Một cột.
- Button quan trọng có thể full width.
- Table chuyển thành scroll hoặc card list.
- Không giữ layout hai cột bằng mọi giá.

---

## 20. Dark mode

Không nằm trong phạm vi version 1.0.

Không sử dụng màu token khiến việc thêm dark mode sau này trở nên bất khả thi.

Không hard-code màu trong hàng trăm template; gom vào Tailwind theme và component classes.

---

## 21. Tailwind theme mapping

Gợi ý:

```js
theme: {
  extend: {
    colors: {
      surface: {
        page: '#F4F4F3',
        DEFAULT: '#FFFFFF',
        muted: '#F8F8F7',
        selected: '#FFF3EE',
      },
      ink: {
        DEFAULT: '#171717',
        secondary: '#5F5F5B',
        muted: '#8A8A84',
      },
      brand: {
        DEFAULT: '#F4511E',
        hover: '#DC4317',
        soft: '#FFF0EA',
      },
      line: {
        DEFAULT: '#E2E2DF',
        strong: '#CFCFCA',
      },
    },
    borderRadius: {
      control: '10px',
      card: '18px',
    },
    boxShadow: {
      card: '0 1px 2px rgba(0,0,0,.03), 0 8px 24px rgba(0,0,0,.04)',
      popover: '0 12px 30px rgba(0,0,0,.10)',
    },
  },
}
```

Các class sử dụng vẫn phải có prefix plugin.

---

## 22. Do / Don’t

### Do

- Dùng nền xám nhạt và card trắng.
- Dùng spacing rộng rãi.
- Dùng typography rõ hierarchy.
- Dùng một màu accent chính.
- Dùng border và shadow nhẹ.
- Tách form dài thành section.
- Giữ component nhất quán.
- Dùng native HTML khi có thể.

### Don’t

- Không dùng gradient mặc định.
- Không dùng glassmorphism.
- Không dùng neon.
- Không dùng shadow nặng.
- Không bo góc mọi thứ thành pill.
- Không trộn quá nhiều màu.
- Không dùng emoji như icon sản phẩm chính thức.
- Không làm input giống giao diện marketing quá khổ.
- Không override CSS toàn bộ WordPress admin.
- Không phụ thuộc vào font bên ngoài nếu không cần thiết.

---

## 23. Mẫu page layout

```html
<div id="example-plugin-app"
     class="ep-min-h-screen ep-bg-surface-page ep-p-4 md:ep-p-6">

  <div class="ep-mx-auto ep-max-w-7xl ep-space-y-6">

    <header class="ep-space-y-3">
      <div class="ep-inline-flex ep-items-center ep-rounded-full ep-border ep-border-brand ep-px-3 ep-py-1">
        <span class="ep-text-xs ep-font-bold ep-uppercase">Settings</span>
      </div>

      <div>
        <h1 class="ep-text-3xl ep-font-extrabold ep-tracking-tight ep-text-ink">
          Cấu hình plugin
        </h1>

        <p class="ep-mt-2 ep-max-w-2xl ep-text-sm ep-leading-6 ep-text-ink-secondary">
          Quản lý các thiết lập chung và tích hợp của plugin.
        </p>
      </div>
    </header>

    <section class="ep-rounded-card ep-bg-white ep-p-6 ep-shadow-card">
      <!-- FormSection -->
    </section>

  </div>
</div>
```

---

## 24. Tiêu chí nghiệm thu UI

- Nền trang là xám nhạt.
- Card nội dung màu trắng.
- Card trên nền trắng có shadow hoặc border nhẹ.
- Không có CSS leak ra ngoài root plugin.
- Typography có ít nhất 3 cấp rõ ràng.
- Form control thống nhất chiều cao và radius.
- Focus state nhìn thấy rõ.
- Error state không chỉ dựa vào màu.
- Mobile không tràn ngang.
- Modal dùng keyboard được.
- Tom Select nhìn giống native field trong cùng hệ thống.
- WordPress color picker và editor không bị phá layout.
- Không xuất hiện style mặc định không kiểm soát từ Tailwind Preflight.
