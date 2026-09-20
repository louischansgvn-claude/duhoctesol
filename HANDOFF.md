# HANDOFF — Ban Du học Hội TESOL TP.HCM (site du học tổng quát)

_Last updated: 2026-09-20 — GSC: sitemap 723 OK + Bing import xong · 301 cho 4 stub ngành (server) · Organization.description cố định (748 trang, deployed) · GA4 chờ mã G-. Update this at every milestone (see CLAUDE.md)._

## 👉 BẮT ĐẦU PHIÊN SAU TỪ ĐÂY

**Site đang LIVE:** https://duhoctesolhcmc.vn — 748 trang · 571 trường · 9 sự kiện · 72 video thật · sitemap 723 URL · `llms.txt`.
Google đã được phép index (`noindex` đã gỡ). Live == local **751/751 file** (kiểm 2026-09-19).
**Trước khi deploy bất kỳ lần nào:** `python tools/verify-site.py` phải in `OK — all invariants hold`.

**Phiên sau bắt đầu bằng các việc này (theo ngày):**
1. **Yêu cầu lập chỉ mục 5 URL** trong Search Console — user định làm lúc 17:00 ngày 20/09 (hạn mức ~10/ngày). Nếu chưa: ô "Kiểm tra mọi URL" → dán URL đầy đủ → *Yêu cầu lập chỉ mục*:
   `https://duhoctesolhcmc.vn/` · `/truong/` · `/quoc-gia/my/` · `/quoc-gia/canada/` · `/quoc-gia/uc/`.
2. **Rich Results Test** với URL ĐÚNG `https://duhoctesolhcmc.vn/truong/university-of-toronto-uoft/` (lần đầu Claude đưa nhầm `/university-of-toronto/` → 404). Kỳ vọng *Đường dẫn: 1 mục hợp lệ*.
3. **GA4** — chờ user gửi mã đo lường `G-XXXXXXXXXX` → `python tools/add-ga4.py G-XXXX` (dry-run) → `--apply` → `verify-site.py` → deploy `html` → `live-compare.py`. Script đã viết, chưa chạy.
4. **Từ ~2026-09-22** — user gửi ảnh *Lập chỉ mục → Trang* và *Cải tiến → Đường dẫn*; Claude đọc, xử lý lỗi (kỳ vọng: 723 URL hợp lệ tăng dần, Breadcrumbs hợp lệ).
   Google báo "Trùng lặp, Google chọn canonical khác" cho 10 bản tin gốc `/<slug>/` là **đúng thiết kế**, không sửa. 4 URL `/nganh-hoc/{cntt,kinh-doanh,ky-thuat,y-suc-khoe}/` báo "Trang có lệnh chuyển hướng" cũng đúng.

### Việc tiếp theo, theo thứ tự ưu tiên
| # | Việc | Ai làm | Ghi chú |
|---|---|---|---|
| 1 | ~~Đổi mật khẩu FTP~~ — **user quyết định 2026-09-19: GIỮ NGUYÊN, không đổi.** Đừng nhắc lại. Credential ở `C:\Users\louis\.duhoctesol-ftp.cfg` (ngoài repo, KHÔNG có trong DEPLOY.md/HANDOFF.md — cố ý vì repo trên GitHub) → deploy không hỏi | — | xong |
| 2 | **Google Search Console** — ✅ xác minh (Miền, TXT DNS) · ✅ sitemap **Thành công, 723 trang đã khám phá** (user xác nhận 20/09) · ✅ **Bing Webmaster import xong 20/09**. Còn: yêu cầu index 5 URL (user, 17:00 20/09) · đọc báo cáo từ 22/09 | user + Claude | chi tiết ở mục *Google Search Console* dưới bảng |
| 3 | Kiểm **Rich Results Test** — dùng URL `https://duhoctesolhcmc.vn/truong/university-of-toronto-uoft/` | user | lần đầu 20/09 test nhầm URL không tồn tại → "không thể kiểm tra"; test lại với URL đúng, kỳ vọng *Breadcrumbs* 1 mục hợp lệ |
| 4 | ✅ **xong 2026-09-19** — title: sửa **170** (102 bị generator cũ cắt cứng ở 68 ký tự, 61 `thpt`→`THPT`, 3 trang học sinh, 4 stub ngành) | Claude | đã deploy · chi tiết ở *Milestone 2026-09-19* |
| 5 | ✅ **xong 2026-09-19** — description: viết lại **273** (158 trùng + 129 THPT Mỹ bị cắt dở câu + 3 quá dài) · gỡ **35 canonical thừa** (9 trang sự kiện từng trỏ về `/su-kien/canada-fair/` 404) | Claude | đã deploy |
| 6 | ✅ **xong 2026-09-19** — `https://duhoctesolhcmc.vn/llms.txt` (101 link), sinh bằng `tools/build-llms.py` | Claude | đã deploy, nằm trong phase `seo` |
| 7 | 32 trang trường có mô tả tiếng Việt dính vào `<h1>` + breadcrumb | Claude, **chỉ khi user yêu cầu** | vd `Đại học Cape Breton University (CBU – Thu hút rất đông sinh viên…)`. Schema + `<title>` đã sạch, chữ hiển thị vẫn còn. Nội dung có sẵn → user dặn không tự sửa |
| 8 | ✅ **xong 2026-09-20** — 4 stub `/nganh-hoc/{cntt,ky-thuat}→cong-nghe`, `kinh-doanh→kinh-te`, `y-suc-khoe→suc-khoe` **301 trên server** (rule 1b trong block `.htaccess` của mình, bản sao ở `docs/server/`). File local vẫn giữ (748 không đổi), sitemap không chứa, `live-compare.py` kỳ vọng đúng 4 × 301 | Claude | user duyệt 20/09 |
| 9 | ✅ **xong 2026-09-20** — `EducationalOrganization.description` + `WebSite.description` = **1 câu cố định** (câu của trang chủ) trên 748 trang, bằng `tools/fix-org-schema.py` (round-trip JSON byte-exact, 747 đổi). `verify-site.py` giờ kiểm luôn. Đã deploy, live == local | Claude | user duyệt 20/09 |
| 10 | (không cần làm) 61 title trang trường dài > 65 ký tự | — | không bị cắt, chỉ Google rút gọn khi hiển thị; tên trường dài là lý do |
| 11 | **Google Analytics 4** — chờ user tạo property GA4 và gửi mã `G-XXXXXXXXXX` | user → Claude | `tools/add-ga4.py` đã sẵn: chèn gtag.js trước `</head>` 748 trang (không đụng header/footer/meta), idempotent. Sau đó verify → deploy html → live-compare |


### Google Search Console (bắt đầu 2026-09-19)
Trạng thái 2026-09-19: **ĐÃ XÁC MINH** thuộc tính Miền `duhoctesolhcmc.vn` qua TXT DNS tại PA Việt Nam (`google-site-verification=MpSZ4Fa7X4fjarqolEwm9fzdW4rB10725al9zf91nU8`, host `@`, TTL 300 — giữ bản ghi này mãi, xoá là mất quyền). Đã nộp sitemap (thuộc tính Miền phải nhập URL đầy đủ `https://duhoctesolhcmc.vn/sitemap.xml`, không nhập tên file). Yêu cầu lập chỉ mục: **hết hạn mức ngày 2026-09-19**, user làm tiếp ngày 2026-09-20 với các URL còn lại trong 5 URL (`/`, `/truong/`, `/quoc-gia/my/`, `/quoc-gia/canada/`, `/quoc-gia/uc/`). Bing Webmaster import: chưa xác nhận. Sau 3–7 ngày (từ ~2026-09-22): xem *Lập chỉ mục → Trang* và *Cải tiến → Đường dẫn*.
- Cách đã làm: thuộc tính **Miền (Domain)** `duhoctesolhcmc.vn` — gom http/https/www/non-www vào 1 chỗ. Xác minh bằng **TXT DNS** (user điền ở PA Việt Nam: Host `@`, Loại TXT — lần đầu điền nhầm Host=TXT/Loại=A, panel báo "phải là IP").
  thêm ở đúng panel DNS nơi đã tạo A record `103.221.223.76`. Claude kiểm: `nslookup -type=TXT duhoctesolhcmc.vn 8.8.8.8`.
- Phương án B (không cần nữa, giữ để tham khảo): thuộc tính **Tiền tố URL** + xác minh bằng **file HTML** upload qua FTP. KHÔNG dùng cách "thẻ meta" (phải sửa `<head>` trang chủ).
  user tải file `googleXXXX.html` → đưa Claude → Claude upload lên docroot qua FTP (curl -T, cùng credential deploy) → user bấm Xác minh.
  KHÔNG dùng cách "thẻ meta" (phải sửa `<head>` trang chủ → phá invariant header/meta).
- Sitemap: với thuộc tính Miền phải nhập **URL đầy đủ** `https://duhoctesolhcmc.vn/sitemap.xml` (nhập `sitemap.xml` không thì Google báo "Địa chỉ không hợp lệ"). Đã nộp 19/09; "Các trang đã khám phá" lên 723 sau vài giờ–1 ngày.
  · sau 3–7 ngày xem *Lập chỉ mục trang* + *Cải tiến → Breadcrumbs*.
- Tiền kiểm 2026-09-19 (Claude): homepage 200, không có header `X-Robots-Tag`; sitemap/robots/llms trả 200; http + www đều 301 về `https://duhoctesolhcmc.vn/`.
- Bước phụ đáng làm: **Bing Webmaster Tools** → "Import from Google Search Console" (1 click) — Bing index là nguồn của ChatGPT search / Copilot (GEO).

### Deploy — 1 lệnh, không cần hỏi mật khẩu
Credential nằm **ngoài repo** ở `C:\Users\louis\.duhoctesol-ftp.cfg` (Git Bash: `~/.duhoctesol-ftp.cfg`).
Nếu file tồn tại và không còn chuỗi `DANMATKHAUVAODAY` → deploy luôn, **không hỏi user**:
```bash
cd "/c/Users/louis/Dropbox/Tintt/claude code/duhoctesol"
python tools/deploy-ftp.py ~/.duhoctesol-ftp.cfg html     # chỉ sửa chữ  (~2 phút)
python tools/deploy-ftp.py ~/.duhoctesol-ftp.cfg seo      # robots.txt + sitemap.xml + llms.txt
python tools/deploy-ftp.py ~/.duhoctesol-ftp.cfg css      # main.css
python tools/deploy-ftp.py ~/.duhoctesol-ftp.cfg images   # 442 MB (~4 phút) - chỉ khi đổi ảnh
```
Chỉ hỏi user khi: file mất · còn placeholder · `login` trả `530` (mật khẩu đã đổi).
Trên Windows dùng `python`, không phải `python3`. Runbook đầy đủ: `DEPLOY.md`.

### Công cụ trong repo
| File | Dùng khi |
|---|---|
| `tools/deploy-ftp.py` | deploy. Phase: `login` `images` `css` `html` `seo` `all`. Đã sửa chạy được trên Windows (path `\` → `/`, stdout UTF-8) |
| `tools/add-schema.py` | **chạy lại mỗi khi sinh lại trang trường hoặc thêm trường mới.** Idempotent — thay khối `id="page-schema"` cũ, không nhân đôi. Đọc dataset từ thư mục anh em `../duystudy.vn - content/`. `WebPage.description` copy meta description → đổi description xong phải chạy lại |
| `tools/verify-site.py` | **chạy trước mỗi lần deploy.** Kiểm 748/571/9, header/footer md5, `noindex`=0, 1 canonical/trang, title/description không cắt · không trùng · ≤160, sitemap 723 tự-canonical, JSON-LD parse. Phải in `OK — all invariants hold` |
| `tools/fix-meta.py` | sửa title/description/canonical hàng loạt (2026-09-19). Dry-run mặc định, `--apply` ghi, `-v` in diff. Idempotent. Nếu sinh lại trang trường: chạy `fix-meta.py --apply` → `add-schema.py --apply` → `verify-site.py` |
| `tools/build-llms.py` | sinh lại `llms.txt` từ title/description thật. Chạy khi đổi description trang hub hoặc thêm/bớt trang, rồi deploy phase `seo` |
| `tools/fix-org-schema.py` | đặt lại câu mô tả tổ chức cố định trong graph JSON-LD site-wide (748 trang). Chạy lại nếu sinh lại trang từ template WP cũ. Dry-run mặc định |
| `tools/live-compare.py` | **chạy sau mỗi lần deploy** — GET từng URL không theo redirect, so byte với local. Kỳ vọng `identical 747 · expected 301 4 · OK — live == local` |
| `tools/add-ga4.py` | chèn/thay khối gtag.js GA4 trước `</head>`; tham số mã `G-…`; dry-run mặc định, `--apply` ghi |

### Git
- Nhánh `tesol-content-rewrite` — đã push lên `origin` (github.com/louischansgvn-claude/duhoctesol). **Chưa merge vào `main`.**
- ⚠️ **`wp-content/uploads/` KHÔNG nằm trong git** (`.gitignore`) — 1.753 ảnh / 442 MB.
  Ảnh sống ở 3 nơi: thư mục repo trên Dropbox (bản làm việc) · server · nguồn gốc `../duystudy - website/wp/wp-content/uploads/`
  và `../duystudy.vn - content/assets/`. Clone mới từ GitHub **sẽ không có ảnh** → deploy ảnh phải chạy từ máy có Dropbox.

---

## Trạng thái hạ tầng (2026-09-18)

| Hạng mục | Trạng thái |
|---|---|
| Domain | `duhoctesolhcmc.vn` — **addon domain**, docroot `/home/infkkcwh/duhoctesol.duystudy.vn` |
| DNS | `@` + `www` → A `103.221.223.76` (Shared IP của cPanel), TTL 300 |
| SSL | Let's Encrypt (AutoSSL), SAN `duhoctesolhcmc.vn` + `www`, hết hạn **17/12/2026** — AutoSSL tự gia hạn |
| Deploy | 1.753 ảnh · 748 HTML · main.css · robots + sitemap + llms.txt — **0 lỗi**. Lần cuối 2026-09-19 (`html` + `seo`), live == local 751/751 |
| 34 thư mục demo trên server | đã xoá qua FTP, cả 34 URL trả **404** |
| `noindex,nofollow` | **đã gỡ** khỏi 748 trang |
| `robots.txt` | chặn `/wp-json/`, `/feed/`, `/category/`, `/loai-su-kien/`, `/quoc-gia-filter/` |
| `sitemap.xml` | **723 URL** (748 − 11 trang robots chặn − 10 bản tin trùng − 4 stub `/nganh-hoc/`) — mọi URL đều tự-canonical |
| `llms.txt` | https://duhoctesolhcmc.vn/llms.txt — 101 link, `text/plain`, sinh bằng `tools/build-llms.py` |
| canonical | đúng **1 thẻ/trang**, `og:url` == canonical. 2026-09-19 gỡ 35 thẻ thừa (9 trang sự kiện từng trỏ nhầm về `/su-kien/canada-fair/` đã xoá) |
| 301 (`.htaccess`) | subdomain cũ + `duhoctesolhcmc.vn.amigoagency.vn` + `www` + `http` → `https://duhoctesolhcmc.vn`, **giữ nguyên đường dẫn**. **20/09 thêm rule 1b:** 4 stub `/nganh-hoc/` → trang ngành thật (1 hop, đích tuyệt đối). Bản live + bản backup trước đó lưu ở `docs/server/` |
| `.well-known/` | loại trừ khỏi 301 — nếu chặn, AutoSSL không gia hạn được cert, sau 90 ngày site chết HTTPS |
| 10 bài tin trùng `/<slug>/` + `/tin-tuc/<slug>/` | bản root (rác WP export, không ai link tới) đã trỏ canonical về bản `/tin-tuc/` |

### `.htaccess` trên server
Block PHP do cPanel sinh **giữ nguyên 100%**; phần rewrite nằm dưới, trong
`# BEGIN duhoctesolhcmc.vn canonical host` … `# END`. Backup bản gốc (chỉ có block cPanel, 612 byte) —
nếu cần khôi phục, xoá toàn bộ phần nằm giữa 2 marker đó.

### ⚠️ Hosting dùng chung — đừng làm những việc này
cPanel `infkkcwh` còn chạy **`amigoagency.vn` (primary domain)**, `duystudy.vn`, `vnguide.vn`…
- ❌ Đừng đổi Primary Domain
- ❌ Đừng sửa `.htaccess` ở `/home/infkkcwh/` hay `/public_html/`
- ❌ Đừng "Run AutoSSL For All Domains"
- ❌ **Đừng xoá subdomain `duhoctesol.duystudy.vn`** — FTP account deploy gắn với nó, docroot mang tên nó, và nó đang làm nhiệm vụ 301
- ❌ Đừng đổi tên thư mục docroot — nội bộ, khách không thấy, đổi là hỏng mapping addon domain + chroot FTP

FTP account `uploadtesolhcm@duhoctesol.duystudy.vn` bị **chroot** trong docroot → script deploy không thể ghi sang site khác.

### Structured data (đã deploy)
Mỗi trang có thêm **1** khối `<script type="application/ld+json" id="page-schema">` ngay sau graph
site-wide (graph cũ — `EducationalOrganization` + `WebSite` + 3 `LocalBusiness` — không đụng).
- **571 trang trường**: `WebPage` + entity trường + `BreadcrumbList`
  - `@type`: 371 `CollegeOrUniversity` · 161 `HighSchool` · 39 `EducationalOrganization`
    (31 khu học chánh / hội đồng trường + NSISP + 7 trường Anh ngữ Philippines — không phải 1 trường THPT)
  - `name` = tên chính thức: bỏ mô tả tiếng Việt trong ngoặc / sau gạch nối (199 tên)
  - `alternateName` = viết tắt thật (105: UofT, UBC, NUS, LSE, TU Delft, UNSW Sydney, TDSB…)
  - `url` + `sameAs` = website chính thức từ dataset (571/571 hợp lệ)
  - `WebPage.name` lấy từ `<h1>`, **không** lấy `<title>`; `WebPage.description` = meta description (đồng bộ lại 2026-09-19)
- **176 trang còn lại**: chỉ `BreadcrumbList` (trang chủ không có — breadcrumb chỉ 1 cấp)
- Verify: 0 lỗi parse JSON · 2.287 mục breadcrumb đều trỏ trang có thật · `position` liên tục · header/footer vẫn 1 md5

## Milestone 2026-09-20 — Search Console hoàn tất phần Claude · 301 stub · Organization.description · GA4 chuẩn bị

**OUTPUT** — DONE (trừ GA4 chờ mã). `.htaccess` server thêm rule 1b (4 × 301), kiểm: 4 stub 301 đúng đích, trang chủ/trang trường/CSS/`/nganh-hoc/` 200,
www + domain cũ vẫn 301, `.well-known` không bị đụng, md5 server == bản local. `tools/fix-org-schema.py --apply`: 747 trang, round-trip JSON
byte-exact; deploy `html` 0 lỗi; `tools/live-compare.py`: identical 747 · expected 301 4 · OK. Commit: xem `git log` (sau `3fcaf2f`).
File mới: `tools/fix-org-schema.py`, `tools/live-compare.py`, `tools/add-ga4.py`, `docs/server/htaccess-live-2026-09-20.txt`, `docs/server/htaccess-backup-2026-09-18.txt`.

**PROMPT/GOAL** — user: *"các phần liên quan tới google search console chưa xong thì tiến hành xử lý cho xong rồi tiếp tục các công việc còn lại, step by step"*.
Goal theo dõi: GSC xong phần làm được (sitemap 723 ✅, Bing ✅, index request chờ hạn mức 17:00, Rich Results test lại URL đúng) → 301 stub → mô tả tổ chức cố định → GA4.
Ràng buộc giữ: không đổi body/header/footer/file count; `.htaccess` chỉ sửa trong block của mình, có rollback tự động trong cùng lệnh.

Ghi chú: lần upload `.htaccess` đầu bị rollback tự động vì URL health-check `/truong/university-of-toronto/` không tồn tại (slug thật `-uoft`), không phải lỗi rewrite;
lần 2 dùng `/truong/adrian-high-school/` → OK. Cùng lỗi URL đó khiến user test Rich Results bị "không thể kiểm tra" → đã đưa URL đúng.

## Milestone 2026-09-19 — SEO meta cleanup + `llms.txt` (đã deploy)

**OUTPUT** — DONE. Deploy `html` + `seo` 0 lỗi; live == local **751/751** file; `tools/verify-site.py` → OK.
Commit: ngay sau `889d628` trên `tesol-content-rewrite` (xem `git log`). File đổi: 409 `index.html`, `sitemap.xml`,
`llms.txt` (mới), `tools/fix-meta.py` (mới), `tools/verify-site.py` (mới), `tools/build-llms.py` (mới),
`tools/add-schema.py` (stdout UTF-8), `tools/deploy-ftp.py` (phase `seo` += `llms.txt`).

**PROMPT/GOAL** — user: *"tiếp tục phiên làm việc hôm nay"* → làm 3 việc Claude trong bảng "Việc tiếp theo"
(title bị cắt · description trùng · `llms.txt`). Goal theo dõi: mọi trang có title/description sạch, duy nhất, không cắt;
1 canonical/trang; `llms.txt` live; **không** đụng body, header/footer, JSON-LD site-wide, file count/URL/slug.

Phát hiện khi đo — rộng hơn HANDOFF cũ ghi:
- Generator cũ **cắt cứng `<title>` ở 68 ký tự**: 32 có `…` + **70 cắt giữa từ** (`| Du học đạ`, `| Du h`). Sửa 102 title bằng
  `clean_name()` của add-schema (tên chính thức + tên tắt thật), **không bao giờ cắt tên trường**, rút đuôi cho ≤ 65 (giữ tên tắt tới 70).
  61 title `Du học thpt X` → `Du học THPT X`. 3 trang học sinh dùng chung title "Câu chuyện học sinh" → tên học sinh. 4 stub ngành đổi title.
- **129/129 description THPT Mỹ bị cắt dở câu** (`..., yêu cầu đầu vào,…`) → dựng lại từ khối THÔNG TIN NHANH của chính trang
  (loại trường công/tư/nội trú, bang, dải lớp, học phí), ≤ 160.
- 158 trang / 20 nhóm dùng chung description → mỗi trang 1 câu: quốc gia × bậc có **số trường thật**; video/tin tức lấy đoạn dẫn của trang;
  sự kiện/học bổng/học sinh/archive viết tay trong `tools/fix-meta.py`. 3 description trang quốc gia quá dài (Anh 185, Thụy Sỹ 171, Philippines 166) rút gọn.
- **35 trang có 2 thẻ canonical**: 9 trang sự kiện có thẻ thứ hai trỏ về `/su-kien/canada-fair/` (demo đã xoá → 404, Google có thể bỏ index
  cả 9 trang); 10 bản tin gốc còn thẻ tự-canonical cạnh thẻ `/tin-tuc/`; 16 trang có 2 thẻ giống nhau. Giữ thẻ đầu, xoá thẻ sau.
- 4 stub `/nganh-hoc/{cntt,kinh-doanh,ky-thuat,y-suc-khoe}/` (h1 "Không tìm thấy trang", vẫn nằm trong sitemap = soft-404)
  → canonical + `og:url` về trang ngành thật (`cong-nghe` / `kinh-te` / `suc-khoe`), bỏ khỏi sitemap (727 → 723).
- `llms.txt` theo llmstxt.org: 101 link (trang chính, 13 quốc gia kèm số trường, bậc học có danh sách, lộ trình, ngành, cẩm nang, học bổng,
  sự kiện; Optional: video + học sinh), sinh từ title/description thật. Đã thêm vào phase `seo` của `deploy-ftp.py`.
- Sau khi đổi description phải chạy lại `add-schema.py --apply` (WebPage.description copy meta) — đã chạy, idempotent (tree hash không đổi lần 3).
Không đổi: body, header/footer (md5 giữ nguyên), graph JSON-LD site-wide, file count/URL/slug/CSS/ảnh.
Đã cân nhắc nhưng **không** làm (cần user gật): 301 cho 4 stub (đụng `.htaccess` server) · đặt 1 câu cố định cho
`Organization.description` site-wide (607 trang đang lệch) — xem "Việc tiếp theo" #8, #9.

## Milestone 2026-08-18 — Import dữ liệu trường + sự kiện từ duystudy.vn

**Yêu cầu user:** lấy dữ liệu các trường / sự kiện đã import bên `duystudy - website` đưa sang duhoctesol,
**không sửa nội dung/hình ảnh**, rồi deploy.

### Nguồn dữ liệu
Site live **https://duystudy.vn** (đã seed đủ 5 dataset) là source of truth — mirror phần `<main>` của từng
trang. Dataset gốc nằm ở `duystudy - website/wp-content/themes/duy-study/inc/data/`:
`seo/seo-import.json` (129 THPT Mỹ) · `postsecondary/` (103 CĐ/ĐH Mỹ) · `canada-australia/` (195) ·
`other-countries/` (144) · `events/` (9).

### Đã làm
- **572 trang trường** `truong/<slug>/` + **9 trang sự kiện** `su-kien/<slug>/` (mới).
- **38 trang danh sách cập nhật lại**: `truong/`, `su-kien/`, và 36 trang `quoc-gia/{9 nước}/{4 bậc}/`.
- **37 trang mới cho nước/bậc chưa có**: `quoc-gia/{anh,malaysia,thuy-sy,philippines}/` (landing + 5 bậc + video)
  và bậc `anh-ngu/` cho 9 nước cũ — dữ liệu import có 4 quốc gia + 1 bậc học mà site chưa có route.
- **1.712 ảnh** → `wp-content/uploads/{thpt-seo,postsecondary,canada-australia,other-countries,events}/` (414 MB).
  4 thư mục copy từ dataset local; `thpt-seo` (347 file) tải từ duystudy.vn.
- **CSS**: append 50 rule block còn thiếu (`.seo-article/.sa-*/.school-logo-thumb/.ev-*` …) vào
  `wp-content/themes/duy-study/assets/css/main.css`. **Chỉ append**, không sửa rule cũ → 171 trang cũ không đổi.
- **Tổng file HTML: 171 → 782.**

### Quy tắc brand khi mirror (quan trọng)
Nội dung bài import (`<div class="seo-body">`) và **tiêu đề sự kiện** giữ **nguyên văn**, kể cả các chỗ ghi
"Duy Study" (9 sự kiện là sự kiện thật của Duy Study — user dặn không sửa nội dung).
Chỉ đổi **chữ khung của theme** `Duy Study` → `Ban Du học Hội TESOL TP.HCM` (CTA tư vấn, eyebrow, lead của
trang danh sách…) cho khớp 171 trang cũ. URL `duystudy.vn` → root-relative.

### Verify (local)
- 782 file · header 1 md5 `482537d6b5e00b6df298935c27b4eefb` · footer 1 md5 `3e1a808c1520d98d61f7616b4c193be1`
  (đúng cả 782 file) · đúng 1 `<h1>`/trang · 0 `{{TODO}}` · 0 link `https://duystudy.vn` sót lại.
- 44.709 link root-relative: **0 link nội bộ gãy**, **1 ảnh thiếu** (xem dưới).
- Serve local + browser: trang trường / sự kiện / `truong/` / `su-kien/` / `quoc-gia/anh/dai-hoc/` render đúng,
  finder lọc được (chọn "Anh" → 30 kết quả), nút "Tải thêm" chạy client-side (không cần AJAX).

### Trạng thái deploy
**CHƯA DEPLOY.** Kiểm chứng lúc 2026-08-18: `/truong/winthrop-high-school/`,
`/su-kien/hoi-xuan-thpt-trung-vuong-2024/`, `/quoc-gia/anh/dai-hoc/` → **404** trên live;
`/truong/` live vẫn 32 card. Script deploy đã viết sẵn: `tools/deploy-ftp.py`.

### Tồn đọng
- `wp-content/uploads/thpt-seo/jordan-school-district-campus.jpg` **404 ngay trên duystudy.vn** → trang
  `truong/jordan-school-district/` thiếu 1 ảnh trong bài. Giữ nguyên như nguồn; cần fix ở phía duystudy.
- 25 trang trường demo cũ (`truong/asu`, `toronto`, `sydney`…) vẫn còn trên đĩa nhưng **không còn nằm trong
  finder** vì bản duystudy đã bỏ. Không xoá (deploy theo nguyên tắc ghi đè, không xoá).
- Deploy lần này **phải upload cả `wp-content/uploads/` (414 MB) + `main.css`**, khác runbook mặc định
  (mặc định chỉ upload `**/index.html`). Kiểm tra dung lượng host còn đủ trước khi đẩy.
- Chưa commit. Nếu muốn commit: 588 file mới (`truong/*`, `su-kien/*`, `quoc-gia/*`, `wp-content/uploads/*`,
  `tools/deploy-ftp.py`) + 47 file sửa. Cân nhắc `.gitignore` cho `wp-content/uploads/` vì 414 MB ảnh.
- Password FTP `deploy@duystudy.vn` trong `~/.duy-ftp.cfg` đã **lộ ra log phiên chat 2026-08-18**
  (lệnh che password sai định dạng) → **nhắc user đổi password tài khoản đó**.

## Milestone 2026-07-09 — Deploy lên review site
- Deploy 171 `index.html` qua FTP theo `DEPLOY.md` (1 phiên curl, `wp-content` không upload vì không đổi).
- Verify live: `/`, `/quoc-gia/my/video/`, `/lien-he/`, `/truong/duke-university/`, `/hoc-sinh/minh-anh/` → **200** cả 5.
- Live spot-check: footer hiện `© 2026 Ban Du học Hội TESOL TP.HCM.` + tagline; `Học phí: liên hệ` trên trang trường;
  `Nhập học tại University of Sydney · Úc.` trên trang học sinh; `noindex,nofollow` vẫn còn (user muốn giữ).
- Grep live homepage cho `bản dev|Liquid Glass|Du học TESOL` → **0 hit**.
- ⚠️ Password FTP đã gửi qua chat → **nhắc user đổi lại**.

## Việc gợi ý cho lần sau (chưa làm, không gấp)
- Merge `tesol-content-rewrite` vào `main` (nhánh đã sạch, verify đầy đủ).
- ~~Gỡ `noindex` khi lên domain chính thức~~ → **ĐÃ LÀM 2026-09-18** trên `duhoctesolhcmc.vn`.
- Còn **159 chỗ** dùng cụm "cần xác nhận" dạng câu hedge hợp lệ trong nội dung học bổng/trường
  (vd `Phạm vi áp dụng cần xác nhận theo thư học bổng.`). Cố ý giữ — user dặn không đụng thông tin
  chương trình/trường. Chỉ sửa nếu user yêu cầu rõ.
- Bản export tham chiếu `duystudy - website/exports/duy-study-full-html/` **cũng còn nguyên** các note nội bộ
  của dev theme (76 hit `Bản dev theme`, 107 hit `Học phí cần xác nhận`, footer `… Bản dev theme — chưa phải bản live.`).
  ⇒ **Không dùng nó làm nguồn copy sạch.** Site duystudy.vn có thể đang lộ chúng — đáng báo user.

## Milestone 2026-07-09 — Clean remaining tuition placeholders on school pages
- Prior commit had replaced `<span class="price">Học phí cần xác nhận</span>` → `Học phí: liên hệ` (70 chỗ).
  This milestone finishes the job: the **3 remaining markup forms** of `Học phí cần xác nhận` across **19
  `truong/*` pages** (57 hits, 3 per page) → `Học phí: liên hệ`:
  1. subtitle `<p>… · Học phí cần xác nhận</p>` (phần trước dấu `·` giữ nguyên),
  2. `<b>Học phí cần xác nhận</b>`,
  3. bare `<span>Học phí cần xác nhận</span>` (icon award svg/use kept intact).
- No invented tuition figures; school/country/level/program names all untouched.
- Verify: 171 html · `git diff -- wp-content`=0 · `Học phí cần xác nhận` hits=0 · noindex in 171 · header 1 md5
  (`482537d6b5e00b6df298935c27b4eefb`) & footer 1 md5 (`3e1a808c1520d98d61f7616b4c193be1`) each across 171 ·
  diff symmetric (57 added / 57 removed, 19 files). Combined with prior revert + internal-note cleanup + org
  rename + footer tagline → **ready to deploy** (see DEPLOY.md).

## Milestone 2026-07-09 — Rename org to full name + fix duplicated titles
- **Org name** across all 171: every use of `Du học TESOL` / `Công ty Tư vấn Du học TESOL` as the organisation
  name → **`Ban Du học Hội TESOL TP.HCM`** (a Ban thuộc Hội, not a company). ~2.6k body/meta hits + 171 copyright
  spans + 513 JSON-LD office-name fields. `TESOL` alone (chứng chỉ) and common-noun `du học` left untouched.
- **Header/footer** (still 1 md5 each, NEW hashes): logo `alt`, `aria-label="… trang chủ"`, footer
  `aria-label="Văn phòng …"`, nav `Về …`, and copyright `<span>&copy; 2026 Ban Du học Hội TESOL TP.HCM.</span>`
  (dropped "Công ty Tư vấn"). Footer tagline was already correct — untouched. Same transform applied to all 171
  so both blocks stay byte-identical.
- **JSON-LD**: only `name` fields changed; `@id`/`url` (and every `duhoctesol.duystudy.vn` URL) preserved. Offices
  now `Ban Du học Hội TESOL TP.HCM – VP TP.HCM` / `– VP Đà Nẵng` / `– VP Buôn Ma Thuột`. All 171 blocks re-parse.
- **Titles**: normal suffix `— Du học TESOL` → `— Ban Du học Hội TESOL TP.HCM`. Fixed **17 duplicated titles**
  (`Du học TESOL — Du học TESOL` in 16 taxonomy pages + `Về Du học TESOL — Du học TESOL` in `ve-chung-toi`) by
  giving each a real page-specific first part (e.g. `Hội thảo — …`, `Văn phòng — …`, `Về chúng tôi — …`);
  title/og:title/twitter:title kept in sync. 0 `X — X` titles remain, 1 `<title>` per file.
- **Internal notes cleaned** (VIỆC 2): "…cần Du học TESOL xác nhận trước khi public" (9), "…công bố logo" +
  "…cần user…trước khi public" (ve-chung-toi), "…cần trường xác nhận trước khi công bố" (32), "…claim nào cần
  xác nhận trước khi nộp" (ve-chung-toi), "Lưu lại claim…" (17), and 7 `hoc-sinh/*` cards' "…cần đối chiếu hồ sơ
  trước khi công bố rộng rãi" → neutral copy. `du học TESOL` in 2 cẩm-nang alts → `du học chứng chỉ TESOL`.
- Verify: 171 html · `git diff -- wp-content` empty · `Công ty Tư vấn`=0 · bare `Du học TESOL` (ci)=0 · full name
  in 171 · dup titles=0 · noindex in 171 · url hits `duhoctesol.duystudy.vn`=3007 (unchanged) · header & footer
  1 md5 each · 171 ld+json blocks valid · 1 `<title>` per file · residual claim/internal/TODO/publish=0.

## Milestone 2026-07-09 — Remove internal dev/demo notes + footer tagline
- **Footer** (all 171): removed the `Bản dev theme — chưa phải bản live` copyright suffix and deleted the
  `Liquid Glass design · … chính sách sẽ được rà soát trước khi live.` line from `.foot-bottom`; added
  `<p class="muted">Ban Du học Hội TESOL TP.HCM …</p>` tagline right under the footer logo `</a>` (uses existing
  `muted` class, no new CSS). Footer is now **1 new md5** (`c300b61c9d8438994fbe8dd53f8a7a0a`) across all 171.
- **Header** untouched → still the original single md5 (`2270e0e04a56e71a92987c1e18b724af`).
- **Body cleanup** — rewrote every remaining internal/dev/demo/repo/"xác minh"/placeholder-"mẫu" note into
  natural marketing copy: 4 × 404 pages; 5 video pages ("Repo…"/"trong repo" → "Danh sách trường tại <nước>
  đang được cập nhật. Liên hệ Du học TESOL…"); 32-page "Ranking và ghi chú xác minh" heading + "claim chưa xác
  minh"/"cần xác minh" paragraphs/rows; empty-states ("… mẫu" → "…"); `demo` in meta/OG/Twitter/JSON-LD/body of
  `tai-cam-nang`, `su-kien`, `truong`, and 8 `hoc-bong/*`; removed the `<!-- Demo stats … -->` HTML comment on
  the homepage; and placeholder "học bổng mẫu"/"ngân sách mẫu" phrases.
- **Kept `noindex,nofollow`** on every page (user wants it), brand `Du học TESOL`, all URLs/slugs/classes/hrefs,
  and `wp-content/**`. Legit "mẫu" copy (lộ trình mẫu / chi phí mẫu / hồ sơ mẫu / khu học tập mẫu) left intact —
  these are real consultancy deliverables, and the spec's own verify grep scopes only the placeholder set.
- Verify: 171 html · `git diff -- wp-content` empty · internal-note grep no hits · tagline in 171 · noindex in
  171 · header/footer 1 md5 each · every file exactly one `<footer>`/`</footer>`.

## What this project is
A static Vietnamese study-abroad site on the Duy Study template — **LIVE at https://duhoctesolhcmc.vn**,
**748 pages** (grew from 171 after the 2026-08/09 import of 571 schools + 9 events from duystudy.vn).
The "171" figures in the historical sections below describe the site *before* that import. Content is **general du học**
(định hướng, chọn quốc gia/trường/ngành, học bổng, visa, chuẩn bị lên đường). Keep design/CSS/images/URLs;
rewrite only copy except explicit page/link tasks.

The site was briefly rewritten into a **TESOL consultancy**, then **reverted to the general study-abroad copy**
(base `5eeecd0`) while **keeping the 9 country video pages**. `logo.png` and colors are unchanged (they already
existed in base). The organisation is named **`Ban Du học Hội TESOL TP.HCM`** — always written in full, never
abbreviated. `TESOL` on its own is only a certificate type and must **not** be used as the org name (the old
brand string `Du học TESOL` was removed site-wide on 2026-07-09). Old TESOL plan/spec/research under
`docs/superpowers/**` are kept as **archive only** — they no longer describe the live content.

## Branch & commits
- Branch: **`tesol-content-rewrite`** (base `5eeecd0` on `main`). Not merged yet.
- History: the TESOL rewrite commits (`c8634c5` … `05158fb`) are still in the branch log, followed by the
  **revert commit** `Revert content to pre-TESOL general study-abroad copy (keep video pages + brand colors)`.
- The revert did **not** delete anything: it restored the 162 base HTML pages via `git checkout 5eeecd0 -- .`,
  re-added the video nav links (R2) + landing "Xem tất cả video" buttons (R3), and rewrote the 9 video pages to
  general copy (R4). File count stays **171**.

## Current state (after revert)
- **171** `index.html` files. `wp-content/**`, images, CSS, JS = byte-identical to base (never touched).
- **Header** and **footer** are 1 md5 hash each across all 171 files.
- The 162 non-video pages == base `5eeecd0` **except** the nav "Đăng ký tư vấn" line swapped for a country
  `Video du học …` link (5 dropdown countries), and 4 landings (`duc/ha-lan/singapore/han-quoc`) gained one
  `Xem tất cả video` button in the country-video block.
- The **9 video pages** carry general study-abroad copy — no standalone "TESOL"/"Applied Linguistics"/
  "practicum"/"giáo viên tiếng Anh" business content.
- **No internal/dev notes anywhere**: `bản dev`, `Liquid Glass design`, `demo`, `repo`, `trước khi công bố`,
  `Học phí cần xác nhận`, `Kết quả cần xác nhận` all = 0 hits.

## FIRST THING ON RESUME — sanity check (cập nhật 2026-09-18, máy Windows)
```bash
cd "/c/Users/louis/Dropbox/Tintt/claude code/duhoctesol"
git log --oneline | head -5
git status --short | wc -l                                              # 0 = sạch
find . -name index.html -not -path './.git/*' | wc -l                   # == 748
ls -d truong/*/ | wc -l                                                 # == 571
ls -d su-kien/*/ | wc -l                                                # == 9
grep -rl 'noindex' --include=index.html . | wc -l                       # == 0  ← PHẢI = 0, site đang live
grep -rl 'id="page-schema"' --include=index.html . | wc -l              # == 747
grep -rl 'M7lc1UVf-VE\|ScMzIvxBSi4' --include=index.html . | wc -l      # == 0  (video demo Google)
grep -rl 'duhoctesol.duystudy.vn' --include=index.html . | wc -l        # == 0  (domain cũ)
grep -c '<loc>' sitemap.xml                                             # == 723
python tools/verify-site.py                                             # "OK — all invariants hold"
python tools/live-compare.py                                            # "OK — live == local" (747 identical + 4 expected 301)
curl -s -o /dev/null -w '%{http_code}\n' https://duhoctesolhcmc.vn/     # 200
curl -s https://duhoctesolhcmc.vn/truong/ | grep -o data-finder-card | wc -l   # 571
```
Header/footer đồng nhất: kiểm bằng Python (xem invariants), **không** dùng awk one-liner.

## Global invariants (cập nhật 2026-09-18 — site đã LIVE)
- `index.html` == **748** · `truong/*` == **571** · `su-kien/*` == **9**
- header == 1 md5 `482537d6b5e00b6df298935c27b4eefb` · footer == 1 md5 `3e1a808c1520d98d61f7616b4c193be1`
  (dùng Python, **không** dùng awk one-liner — `<` `>` làm vỡ)
- 🔴 **`noindex` == 0 trên mọi trang.** Site đã go-live. Invariant cũ "noindex có trên cả 171 trang" đã
  **HẾT HIỆU LỰC** — **tuyệt đối không thêm lại**, thêm lại là Google gỡ cả site khỏi kết quả tìm kiếm.
- mọi URL tuyệt đối trỏ `https://duhoctesolhcmc.vn`; 0 lần xuất hiện `duhoctesol.duystudy.vn` trong HTML
- mọi trang (trừ trang chủ) có đúng **1** khối `id="page-schema"`; mọi khối JSON-LD parse được
- đúng **1** `<link rel="canonical">` mỗi trang, `og:url` == canonical, đích canonical tồn tại · 0 `<title>` chứa `…` ·
  description 50–160 ký tự, không kết thúc bằng `…` · title/description không trùng giữa 2 trang trừ cặp canonical
  (10 bản tin gốc ↔ `/tin-tuc/`) · sitemap == **723** URL, tất cả tự-canonical — `tools/verify-site.py` kiểm hết
- graph JSON-LD site-wide: `EducationalOrganization.description` == `WebSite.description` == 1 câu cố định (hằng `ORG_DESC` trong `tools/fix-org-schema.py` và `tools/verify-site.py`) trên cả 748 trang
- server: 4 URL `/nganh-hoc/{cntt,kinh-doanh,ky-thuat,y-suc-khoe}/` trả **301** (rule 1b `.htaccess`); mọi URL khác trong sitemap trả 200 và == local (`tools/live-compare.py`)
- 0 video demo Google (`M7lc1UVf-VE`, `ScMzIvxBSi4`); 0 link nội bộ gãy; 0 ảnh thiếu
- org name luôn viết đầy đủ `Ban Du học Hội TESOL TP.HCM`; 0 lần `Du học TESOL` / `Công ty Tư vấn`
- 0 ghi chú nội bộ/dev/demo trong `index.html`

## 🚀 Deploy — runbook đầy đủ trong `DEPLOY.md`
Khi user nói **"deploy"**: dùng credential file `~/.duhoctesol-ftp.cfg` — **không hỏi mật khẩu** nếu file có sẵn
(xem mục *Deploy* ở đầu file). Host `pbf43-22360.azdigihost.com` (port 21, plain FTP), user
`uploadtesolhcm@duhoctesol.duystudy.vn`, docroot = FTP `/`; **ghi đè, không xoá** (giữ `.htaccess`/`.well-known`/`cgi-bin`).
Xoá file trên server thì làm tay qua FTP (`DELE` + `RMD`) sau khi **liệt kê nội dung thư mục trước**.

## Execution method going forward (mandatory workflow)
Follow the **Claude ↔ Codex loop in `CLAUDE.md`**: Claude hands Codex a task prompt (each prompt MUST remind
Codex to **bật "goal" mode**); Codex reviews the plan, implements with goal on, and returns
**OUTPUT + PROMPT/GOAL it used**; Claude reviews against the constraints; both update this HANDOFF at each
milestone. Hard rule for the implementer: **do the work yourself — no sub-agents, no background, no sleep**.

## Hard-won learnings
- **General-purpose implementer agents self-parallelize** (spawn nested sub-agents) and cause file races.
  ALWAYS include: "do the work yourself; no sub-agents, no background, no sleep."
- **Header/footer are byte-identical site-wide** — verify uniformity with an md5-of-block check across files.
- `wp-content/**` (design/logo/colors) was never touched by the TESOL rewrite, so reverting content alone keeps
  the current look intact (`git diff 5eeecd0 HEAD -- wp-content` was empty).
- **The dev theme ships placeholder copy that looks like real content** — `Học phí cần xác nhận`,
  `Kết quả cần xác nhận`, `Danh sách trường cần xác nhận`, the `Bản dev theme` footer. Both this site *and* the
  reference `duy-study-full-html` export carry them. Grep for the **phrase**, not one markup form: the tuition
  placeholder existed in 4 different wrappers (`<span class="price">`, bare `<span>`, `<b>`, `<p>… · …`).
- When mass-inserting into a shared block, **anchor on the enclosing container** (`footer-brand`), not on a class
  that appears in both header and footer (`logo`/`logo-img`) — otherwise the edit lands in the header and breaks
  the byte-identical invariant.
- Verifying header/footer md5 with `awk '/<header/,/<\/header>/'` inside a `for f in $(...)` loop is fragile
  (word-splitting + `<`/`>` redirection). Use Python.

## Milestone 2026-09-18 — Bù 4 gap còn sót của lần import duystudy.vn

**Yêu cầu user:** clone tiếp nội dung/hình ảnh từ duystudy sang cho site đủ nội dung hơn;
**không sửa gì đang có** ("tôi đã sửa lại đúng thông tin rồi"), **chỉ bơm phần đang thiếu**.
Với 3 nước không có video: "nếu không có video thì xóa demo đi để trống".

### Gap analysis — kết quả đối chiếu toàn bộ
Đã đủ, không cần làm gì: 571/571 record trường có trang · 20/20 route `quoc-gia/<nước>/<bậc>` và link đủ
571 trường · 9/9 sự kiện + 45 ảnh · `tin-tuc` (11) / `hoc-sinh` (8) / `hoc-bong` (7) trùng khớp nguồn 100%.
103 trang CĐ/ĐH Mỹ **giàu hơn** `content_html` nguồn (7.520 vs 2.700 ký tự, 12 heading vs 5) — không phải gap.

| # | Gap | Đã xử lý |
|---|---|---|
| 1 | 65/72 video thật của kênh Duy Study chưa có | bơm đủ **72/72** vào 13 trang `/video/` |
| 2 | 9 trang `/video/` + 9 trang landing hiện **video demo của Google** (`M7lc1UVf-VE`, `ScMzIvxBSi4`) | **0 placeholder** còn lại trên trang nước |
| 3 | 42 trang trường thiếu ảnh (4 trang 1 ảnh, 38 trang 2 ảnh) | 37 trang bù xong → còn 6 trang 2 ảnh |
| 4 | 1 ảnh hỏng `jordan-school-district-campus.jpg` | restore từ kho → **0 broken ref** |

### Nguồn video (gap #1)
`duystudy - website/wp-content/themes/duy-study/inc/components.php` →
`duy_official_youtube_videos()`: **72 entry** (đồng bộ 2026-07-27), mỗi entry có
`id / countries / level / title / school / school_slug / desc / published`.
Phân bổ: canada 32 · my 23 · uc 7 · thuy-sy 5 · malaysia 2 · tho-nhi-ky 1 · singapore 1 · new-zealand 1.
`duc / ha-lan / han-quoc / anh / philippines` **không có video** trong dataset → dùng empty-state.

### Đã làm
- **13 trang `quoc-gia/<nước>/video/`**: 6 nước được bơm video thật (canada 32, my 23, uc 7,
  new-zealand/singapore/tho-nhi-ky 1); `duc/ha-lan/han-quoc` chuyển sang empty-state "đang cập nhật"
  giống `anh`/`philippines`; `thuy-sy`/`malaysia` đã đúng từ trước → không đụng.
- **9 trang landing `quoc-gia/<nước>/`**: teaser "Video du học X" → 3 video thật đầu tiên + link
  "Xem tất cả N video"; 3 nước không có video thì **xoá hẳn teaser** (đúng như `anh`/`philippines`).
- **37 trang trường** thêm 1–2 `<figure>` vào gallery `HÌNH ẢNH TRƯỜNG`; **41 ảnh** copy vào
  `wp-content/uploads/{thpt-seo,other-countries}/`. Ảnh **1.712 → 1.753**.
- 4 trang trước đây không có gallery (`bishop-rosecrans`, `evangel-christian-academy`,
  `kuemper-catholic`, `sparta-high-school-kent-isd`) giờ có gallery.

### Còn lại — cố ý không làm
- **6 trang trường vẫn 2 ảnh**: `bishop-carroll-catholic-high-school`, `comstock-park-high-school-kent-isd`,
  `kent-city-high-school-kent-isd`, `kuemper-catholic-high-school`, `greenbay-highschool`,
  `swinburne-university-of-technology-sarawak-kuching`. Kho **không có** ảnh thứ hai khác thật —
  các file `-article-1/2` của chúng khi hash ra **trùng byte** với logo hoặc với ảnh đã có trên trang.
- **8 trang sự kiện mẫu mồ côi** (`su-kien/{australia-webinar,canada-fair,essay-workshop,parent-night,`
  `pre-departure-aug,top-aus-unis,turkey-open-day,visa-check-day}/`) vẫn còn video demo Google.
  Không có link trỏ tới từ `su-kien/index.html`; là rác template, không thuộc dữ liệu duystudy → chờ user quyết.
- `the-gilbert-school`: `-hinh-1.webp` và `-logo.webp` trùng byte (lỗi có từ trước, không do milestone này).

### Verify (local, sau khi apply)
- 782 file (không đổi) · header 1 md5 · footer 1 md5 (đúng cả 782 file)
- **72/72** video thật · **0** video demo Google trên mọi trang nước
- **0** broken upload ref · 1.753 ảnh trên đĩa = 1.753 ref duy nhất
- ảnh/trang trường: `{2: 6, 3: 565}` (trước: `{1: 4, 2: 38, 3: 528}`)
- mọi link nội bộ trong khối video mới đều resolve; mọi youtube id đều thuộc dataset Duy Study

### Learnings
- **Generator phải tự chứng minh**: cả 3 script chạy `--selftest` — tái tạo lại các trang **đã đúng**
  (`thuy-sy`, `malaysia`, `anh`, `philippines`) từ dataset rồi diff **byte-for-byte** trước khi cho ghi.
  Cách này bắt được toàn bộ sai lệch tab (theme xuất thụt lề rất lệch: `t=17`, `t=22`, `t=25`).
- File repo dùng **LF thuần**, không phải CRLF. Ghi file tạm bằng Python trên Windows sẽ tự đổi thành CRLF
  rồi `cat -A` báo `^M` → **kết luận sai**. Luôn mở bằng `newline=""`.
- **2 template video song song**: trang placeholder thụt sâu hơn 1 tab (`<section class="band">` ở t=1)
  so với trang đã đúng (t=0). Dùng base-indent shift, đừng hardcode.
- **Tên nước có 2 dạng**, đừng dùng lẫn: tên ngắn (`Philippines`, lấy từ `<h2>Cẩm nang X</h2>` /
  `<h2>Trường liên quan tại X</h2>`) và tên link (`Anh ngữ Philippines`, lấy từ h1/breadcrumb).
- **Luôn hash + đo kích thước ảnh trước khi thêm vào gallery.** Hash chặn được 3 trang sắp bị chèn
  chính file logo làm "ảnh khuôn viên" (`-article-1.png` trùng byte với `-logo-favicon.png`); đo kích thước
  loại thêm 6 ảnh crest/banner 256–500px mà tên file nhìn như ảnh thật.
- Suy ra tên trường từ `<h1>` dễ sinh lặp tiền tố (`Trường Trung học Trung học Evangel…`) — h1 đã chứa
  "Trung học". Ưu tiên lấy tên từ alt của ảnh gallery/logo có sẵn.
- `truong/index.html` chỉ link 573 slug; 25 trang slug ngắn cũ (`asu`, `boston`, `sydney`, `toronto`,
  `washington`, `melbourne`, `monash`…) là trang legacy trước import, không được list. Đã có từ trước.

## Milestone 2026-09-18 (b) — Dọn sạch nội dung demo của template

**Yêu cầu user:** "các trường đang có ở demo thì xóa đi không cần giữ lại"; 8 trang sự kiện mẫu → "xoá luôn";
9 landing quốc gia còn lại → liệt kê **toàn bộ** trường thật giống 4 trang đã chuẩn.

### Đã xoá
- **26 trang trường demo** `truong/*` — không nằm trong dataset duystudy, mang số liệu bịa
  (`#19 QS`, `38.000 AUD/năm`, `Học phí 31.000 USD/năm`) và ảnh minh hoạ của theme:
  `asu` `auckland` `australian-national-university` `boston` `broward` `dalhousie-university`
  `massey-university` `melbourne` `metu` `monash` `olympic` `oregon-state-university` `purdue-nw`
  `sheridan-college` `st-peters` `sydney` `the-university-of-melbourne`
  `the-university-of-new-south-wales` `the-university-of-queensland` `toronto` `university-of-auckland`
  `university-of-canterbury` `university-of-connecticut` `university-of-otago` `university-of-waikato` `washington`
- **8 trang sự kiện mẫu** `su-kien/*`: `australia-webinar` `canada-fair` `essay-workshop` `parent-night`
  `pre-departure-aug` `top-aus-unis` `turkey-open-day` `visa-check-day`
- ⚠️ **GIỮ LẠI** `truong/north-yarmouth-academy-educatius-exclusive/` — đây là trường thật, slug bị đổi tên
  (dataset ghi `north-yarmouth-academy`). Đừng xoá nhầm.

### Xử lý 190 link trỏ tới trường demo (25 trang)
| Dạng markup | Số | Xử lý | Lý do |
|---|---|---|---|
| `<article class="card" data-finder-card>` | 29 | xoá card | chứa số liệu bịa + ảnh theme |
| `<a class="promo-banner">` (quoc-gia/index) | 5 | xoá banner | chứa học phí bịa |
| `<a class="school-mini">` chip | 40 | **repoint** sang trường thật | chip chỉ có tên + khu vực, không số liệu |
| `<a class="school-mini">` chip (không có trường thật) | 11 | xoá chip | 6 trường demo không có bản thật |
| `<a class="event-school-card">` | 21 | repoint/xoá | nằm trên 8 trang bị xoá |

Mapping 19 legacy → trường thật **tra bằng tên chính xác trong dataset, không dùng fuzzy match**
(`sydney`→`the-university-of-sydney-usyd-bang-nsw`, `toronto`→`university-of-toronto-uoft`,
`asu`→`arizona-state-university`, `melbourne`+`the-university-of-melbourne`→`the-university-of-melbourne-unimelb-bang-victoria`…).
**Cố ý KHÔNG map `purdue-nw` → `purdue-university`**: Purdue University Northwest là campus khác.
7 trường demo không có bản thật: `boston` `broward` `metu` `oregon-state-university` `purdue-nw`
`st-peters` `university-of-connecticut`.

### Bơm trường thật vào chỗ card demo bị xoá
- **9 landing quốc gia** dựng lại khối "Các trường tại X" theo đúng chuẩn 4 trang `anh`/`malaysia`/`thuy-sy`/`philippines`
  (toàn bộ trường, nhóm theo bậc THPT→Cao đẳng→Đại học→Sau đại học→Anh ngữ, A→Z trong từng bậc, kèm link
  "Xem tất cả" sang finder). Card **harvest nguyên văn** từ trang bậc học của chính nước đó → không tự viết markup:
  `my` 232 · `canada` 136 · `uc` 59 · `new-zealand` 27 · `ha-lan` 21 · `singapore` 12 · `duc` 8 ·
  `tho-nhi-ky` 6 · `han-quoc` 4. (4 trang trước đó có **0 card**: duc, ha-lan, han-quoc, singapore.)
- **Trang chủ** khối "Trường và học bổng đang được quan tâm": 3 card demo → 3 card thật
  (`harvard-university`, `university-of-toronto-uoft`, `the-university-of-sydney-usyd-bang-nsw`).
- **Trang chủ** khối sự kiện: 8 sự kiện bịa → **9 sự kiện thật** (3 featured card mới nhất + 6 dòng agenda).
  Heading `Lịch sắp tới` → `Sự kiện đã diễn ra` (mọi sự kiện thật đều đã qua; `su-kien/index.html` cũng ghi "Đã diễn ra").
  4 sự kiện chỉ có năm trong manifest → `date-badge` hiện đúng `2024`, **không bịa ngày/tháng**.
- **`nganh-hoc/{cong-nghe,kinh-te,suc-khoe}`** khối "Trường tiêu biểu ngành X": 4 card demo →
  **empty-state y như `nganh-hoc/giao-duc` và `nganh-hoc/tieng-anh` đang dùng**. Dataset **không có** mapping
  ngành→trường, nên tự chọn "trường tiêu biểu ngành X" là bịa nhận định → không làm.

### Verify (local, sau khi apply)
- **748 trang** (782 − 26 trường − 8 sự kiện) · `truong/` **571** thư mục = đúng 571 record dataset
- `su-kien/` **9** thư mục = đúng 9 sự kiện thật · `truong/index.html` **571** `data-finder-card`
- header 1 md5 · footer 1 md5 (đúng cả 748 file)
- **0** link nội bộ gãy · **0** broken upload ref · **0** ảnh mồ côi trong uploads (1.753 file = 1.753 ref)
- **72** video thật · **0** placeholder Google
- grep = 0 với `#19 QS` / `38.000 AUD/năm` / `31.000 USD/năm` / `Top innovation` / `Trọng tâm:` / `Lịch sắp tới`

### Learnings
- **Xoá trang không bao giờ chỉ là xoá trang.** 26 trang trường demo có **190 link** từ 25 trang khác
  (trang chủ, 13 landing, ngành học, trang video, trang sự kiện) ở **5 dạng markup khác nhau**. Phải phân loại
  markup trước, rồi mới quyết từng dạng: dạng chứa số liệu bịa thì xoá, dạng chỉ có tên thì repoint.
- **Kiểm "container thành rỗng" trước khi xoá.** Xoá card xong thì 4 khối "Các trường tại X" + 1 khối trang chủ
  còn lại grid trắng. Phát hiện bằng cách đo content-weight của từng container trước/sau.
- **Đừng tin fuzzy match cho tên trường.** `difflib` khớp `melbourne`→`melbourne-central-catholic-high-school`
  và `sydney`→`sydney-grammar-school` (đều sai, trung học vs đại học). Phải tra bằng tên chính xác.
- Trang bậc học `quoc-gia/*/dai-hoc/` **cũng chứa card demo** (`metu` trong `tho-nhi-ky/dai-hoc`) → khi harvest
  card thật phải lọc legacy, nếu không sẽ nhân bản card demo sang landing.
- `su-kien/index.html` dùng layout magazine: 1 `lead-story` + 2 `secondary-story` + 6 `card` = 9. Đếm
  `data-finder-card` sẽ ra 6 và tưởng thiếu 3 sự kiện.
- Icon phải có thật trong `assets/icons/sprite.svg` (26 id: i-alert i-arrow i-award i-book i-calendar i-cap
  i-chat i-check i-clock i-globe i-heart i-layers i-mail i-map i-news i-phone i-pin i-play i-route i-search
  i-send i-shield i-sparkles i-star i-target i-user). **Không có `i-image`.**
- `Minh họa campus` / `photo-campus-library.webp` / `photo-event-workshop.webp` còn lại là **asset thiết kế
  của theme** (og:image mọi trang, hero landing, slideshow `ve-chung-toi`, rail "Trường đối tác nổi bật" trên
  `truong/index.html`) — **không phải nội dung demo**, đừng dọn.
