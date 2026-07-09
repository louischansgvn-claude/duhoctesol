# Plan — Hoàn nguyên nội dung về bản trước đợt rewrite TESOL

Ngày: 2026-07-09 · Nhánh: `tesol-content-rewrite` · Base tham chiếu: `5eeecd0`

## Mục tiêu

Đưa nội dung site trở lại bản **du học tổng quát** (cấu trúc giống mẫu Duy Study),
giữ nguyên **màu sắc / logo / CSS** đang dùng theo logo TESOL HCM, và **giữ lại 9 trang video**
(user sẽ thay video mới sau). Kết quả: vẫn **171** file HTML.

## Phát hiện nền tảng (đã xác minh)

- `git diff 5eeecd0 HEAD -- wp-content` → **rỗng**. CSS, JS, ảnh, logo chưa từng bị đợt TESOL đụng vào.
  ⇒ Chỉ cần hoàn nguyên text trong HTML là màu sắc/logo tự động giữ.
- Base `5eeecd0` đã có sẵn đủ 9 thư mục quốc gia và đúng bộ route mẫu Duy Study
  (`/lo-trinh-du-hoc/`, `/quoc-gia/`, `/truong/`, `/hoc-bong/`, `/su-kien/`, `/hoc-sinh/`,
  `/tin-tuc/`, `/ve-chung-toi/`, `/lien-he/`).
- Base đã mang brand `Du học TESOL` + `logo.png` + `<title>` giống HEAD ⇒ **không đổi brand**.
- Commit `e85cc29` (thêm trang video) chỉ sửa **nav**, không sửa body — trừ 4 trang landing.
- Nav có **5 dropdown** quốc gia (my, uc, canada, new-zealand, tho-nhi-ky).
  4 quốc gia còn lại (duc, ha-lan, singapore, han-quoc) nằm trong khối `drop-right` dạng link phẳng
  ⇒ trang video của chúng được link từ **body landing page** bằng nút `Xem tất cả video`.

## Việc phải làm

### R1 — Hoàn nguyên 162 trang HTML + docs cũ
Từ gốc repo: `git checkout 5eeecd0 -- .`

File tạo mới sau base (9 trang video, `CLAUDE.md`, `HANDOFF.md`, `DEPLOY.md`) **không bị đụng** —
đúng như mong muốn. Không xoá file nào.

### R2 — Chèn lại link video vào nav (áp cho cả 171 file)
Với mỗi quốc gia trong bảng dưới, tìm cặp dòng liền kề trong nav và thay dòng thứ hai.
**Neo vào dòng `hoc-bong/?country=` để không đụng nhầm nút `Đăng ký tư vấn` trong body.**

```
<a href="/hoc-bong/?country=SLUG">Học bổng</a>
<a href="/lien-he/">Đăng ký tư vấn</a>          ← thay dòng này
```
thành
```
<a href="/quoc-gia/SLUG/video/">Video du học TÊN</a>
```

| slug | tên |
|---|---|
| `my` | Mỹ |
| `uc` | Úc |
| `canada` | Canada |
| `new-zealand` | New Zealand |
| `tho-nhi-ky` | Thổ Nhĩ Kỳ |

Giữ nguyên indentation (4 tab) của dòng bị thay.

### R3 — Chèn lại nút `Xem tất cả video` cho 4 landing page
Áp cho `quoc-gia/{duc,ha-lan,singapore,han-quoc}/index.html`.
Trong khối `results-top` ngay trước `<div class="event-video-grid">`, sau dòng `<h2>Video về du học X</h2>`,
chèn (đúng markup HEAD, 4 tab indent):

```html
<a class="btn btn-ghost btn-sm" href="/quoc-gia/SLUG/video/">Xem tất cả video <svg class="ic" aria-hidden="true" focusable="false"><use href="/wp-content/themes/duy-study/assets/icons/sprite.svg#i-arrow"></use></svg></a>
```

### R4 — Viết lại copy 9 trang video sang du học tổng quát
File: `quoc-gia/{my,uc,canada,new-zealand,tho-nhi-ky,duc,ha-lan,singapore,han-quoc}/video/index.html`

- `<header>`, `<footer>`, `<meta>`, JSON-LD: đồng bộ **byte-identical** với header/footer sau R1+R2
  (lấy từ một trang bất kỳ đã xử lý, ví dụ `dich-vu/index.html`).
- Body: giữ **nguyên** mọi `class`, `data-youtube-id`, `src` ảnh thumb, cấu trúc thẻ.
  Chỉ thay text: bỏ mọi nội dung riêng TESOL (`Master of TESOL`, `Thạc sĩ TESOL`, `Applied Linguistics`,
  `chứng chỉ TESOL`, `practicum`, `giáo viên tiếng Anh`…) → copy du học tổng quát cho quốc gia đó
  (chọn trường, ngành, học phí, visa, học bổng, hồ sơ/SOP, cuộc sống du học sinh).
- Breadcrumb, `<h1>Video du học X</h1>`, `<title>` giữ nguyên khung.
- Số liệu vẫn hedge (`tham khảo`, `cập nhật 2026`).

### R5 — Tài liệu
- Giữ `DEPLOY.md` nguyên trạng.
- Viết lại `CLAUDE.md`: project = site du học tổng quát (cấu trúc mẫu Duy Study), giữ nguyên
  mục **MANDATORY WORKFLOW — Claude ↔ Codex** và các invariant (171 file, không đổi URL/CSS/class/href).
  Bỏ mục *Accuracy guardrails* riêng TESOL.
- Viết lại `HANDOFF.md` phản ánh trạng thái sau revert.
- `docs/superpowers/research/2026-07-02-tesol-facts.md` + spec/plan TESOL cũ: **giữ làm lưu trữ**.

## Verify (bắt buộc chạy, dán output)

```bash
find . -name '*.html' -not -path './.git/*' | wc -l          # = 171
git diff 5eeecd0 -- wp-content | wc -l                        # = 0
git diff 5eeecd0 --stat -- . ':!quoc-gia/*/video' ':!*.md'    # chỉ nav + 4 nút video
grep -rl 'quoc-gia/.*/video/' --include=index.html . | wc -l  # = 171 (nav) 
```
- Header/footer byte-identical trên cả 171 file (so `md5` đoạn `<header>…</header>`).
- Không còn chuỗi TESOL trong body (cho phép trong brand `Du học TESOL`, `<title>`, `alt`, `aria-label` logo).
- Không có link gãy tới `/quoc-gia/*/video/`.

## Ràng buộc

- Không đổi số file (171), URL/slug, `wp-content/**`, ảnh, `class`, `href` (ngoài R2/R3).
- Không spawn nested sub-agent, không chạy background, không sleep.
