# tools/ — hai công cụ đang dùng

Site chạy WordPress trên hosting chỉ có FTP: không SSH, không WP-CLI, không composer
trên server. Vì vậy mọi thứ đi qua `curl` với file cấu hình FTP.

Mật khẩu FTP nằm ở `~/.duhoctesol-ftp.cfg`, **ngoài repo** và không bao giờ được in ra log.

## `wp-deploy.py` — tải lên server

```bash
python tools/wp-deploy.py ~/.duhoctesol-ftp.cfg <phase>
```

| Phase | Làm gì |
|---|---|
| `theme` | đẩy theme `duy-study` (~560 file). **Đây là phase dùng hằng ngày.** |
| `theme-assets` | riêng `main.css` + `main.js` |
| `prune` | xoá trên server những file đã bỏ khỏi repo — xem hằng `PRUNE` |
| `core` · `plugin` · `uploads` · `db` · `config` | phần còn lại của bản cài WordPress |
| `htaccess-wp` | `.htaccess` bản WordPress (đang chạy) |
| `htaccess-static` | **đường lui** — trả về site tĩnh trong vài giây |

Hai điều dễ quên:

- **FTP chỉ biết tải lên.** Xoá file trong repo là chưa đủ, bản trên server vẫn được
  phục vụ. Thêm đường dẫn vào `PRUNE` rồi chạy phase `prune`.
- **Đừng chạy phase `db`** khi user đã sửa nội dung trong wp-admin. Nó ghi đè cả cơ sở
  dữ liệu trên server bằng bản trong `wp-build/`.

## `wp-verify.py` — kiểm tra sau khi deploy

```bash
python tools/wp-verify.py        # toàn bộ, ~3 phút, PHẢI in 723/723
python tools/wp-verify.py 60     # 60 URL đầu, chạy nhanh khi đang sửa
```

Gọi từng URL trong `docs/sitemap-submitted-2026-09-19.xml` — đúng danh sách đã nộp cho
Google ngày 19/09 — và kiểm: HTTP 200 · có `<title>` không bị cắt · canonical trỏ đúng
chính nó · có meta description · không lộ lỗi PHP · còn tên tổ chức · không còn chuỗi
"Duy Study" ngoài các trang được phép · không còn số điện thoại cũ.

Ba nhóm ngoại lệ khai báo sẵn trong script: `EXPECT_301` (5 trang lưu trữ rỗng cố ý
301), `EXPECT_GONE` (14 trang dữ liệu mẫu đã gỡ, phải 404) và `ALLOW_BRAND` (các bài
sự kiện do Duy Study tổ chức, được phép nhắc tên họ).

## Công cụ cũ

Thời site tĩnh và thời chuyển đổi nằm ở `archive/tools-static-era/` và
`archive/tools-migration/`. Đừng chạy — xem `archive/README.md`.
