# archive/ — những thứ đã nghỉ hưu

Không có gì trong thư mục này còn chạy. Giữ lại để tra cứu và để làm đường lui,
không phải để dùng tiếp. **Đừng chạy bất cứ script nào ở đây.**

## `static-site/` — site HTML tĩnh (18/09 → 20/09/2026)

748 file `index.html` cùng `robots.txt`, `llms.txt`, `favicon.png` của bản site tĩnh
đã phục vụ duhoctesolhcmc.vn trong hai ngày, trước khi chuyển sang WordPress ngày
20/09/2026.

Vì sao còn giữ:

- **Bản trên server là đường lui thật.** 748 file này vẫn nằm trong docroot. Chạy
  `python tools/wp-deploy.py ~/.duhoctesol-ftp.cfg htaccess-static` là chúng phục vụ
  trở lại trong vài giây. Thư mục `archive/static-site/` ở đây chỉ là bản sao để đối chiếu.
- Muốn xem một trang cũ trông thế nào thì mở thẳng file, không cần lục lịch sử git.

Từ 21/09/2026, `.htaccess` chuyển hướng 301 mọi URL `/…/index.html` về URL sạch, nên
bản tĩnh trên server **không còn truy cập được qua trình duyệt** nữa (trước đó vẫn đọc
được, kể cả những trang dữ liệu mẫu đã gỡ). Đường lui vẫn nguyên vẹn vì nó đổi
`DirectoryIndex` chứ không phụ thuộc vào rule 301 đó.

Khi nào chắc chắn không cần đường lui nữa thì xoá cả thư mục này **và** xoá file trên
server, rồi có thể trả rule rewrite về bản mặc định của WordPress (bỏ ghi chú `!-d`
trong `docs/server/htaccess-wordpress-2026-09-20.txt`).

## `tools-static-era/` — công cụ của thời site tĩnh

Chín script đọc và sửa thẳng 748 file `index.html`. Site không còn chạy như vậy nữa,
nên **chạy lại là đè file HTML cũ lên bản WordPress đang sống**.

| Script | Việc nó từng làm |
|---|---|
| `deploy-ftp.py` | tải 748 file HTML + ảnh + robots/sitemap/llms lên server |
| `verify-site.py` | kiểm bất biến của site tĩnh trước khi deploy |
| `live-compare.py` | so bản trên server với bản trong repo sau khi deploy |
| `fix-meta.py` | sửa hàng loạt title/description/canonical |
| `fix-org-schema.py` | ghi JSON-LD Organization/WebSite cố định vào mọi trang |
| `fix-contact.py` | sửa 1.497 link `tel:` và 748 link Zalo về số chính thức |
| `add-schema.py` | thêm JSON-LD cho từng loại trang |
| `build-llms.py` | sinh `llms.txt` |
| `add-ga4.py` | chèn thẻ GA4 vào từng file HTML |

Bản WordPress đã có sẵn cơ chế tương đương trong theme (`inc/seo.php` lo
title/description/canonical/OG/JSON-LD, `robots.txt`, `llms.txt` và `wp-sitemap.xml`
đều sinh động). Cần GA4 thì chèn vào theme rồi deploy, không dùng `add-ga4.py`.

## `tools-migration/` — công cụ chuyển đổi, chạy đúng một lần

| Script | Việc nó đã làm | Vì sao đừng chạy lại |
|---|---|---|
| `wp-prepare.py` | dựng `wp-build/` — đổi domain trong CSDL, sinh `wp-config.php`, đặt mật khẩu admin | Dựng lại CSDL từ nguồn cũ là **xoá sạch** mọi nội dung đã sửa trong wp-admin |
| `wp-theme-sync.py` | chép theme `duy-study` từ `../duystudy - website/` vào repo rồi đổi thương hiệu | Theme giờ thuộc repo này. Chạy lại có thể kéo ngược nhận diện của Duy Study — **đã xảy ra một lần ngày 20/09** (xem `HANDOFF.md`) |
