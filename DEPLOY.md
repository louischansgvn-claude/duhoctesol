# DEPLOY — https://duhoctesolhcmc.vn

Hosting chia sẻ, **chỉ có FTP**: không SSH, không WP-CLI, không composer trên server.
Mọi thứ đi qua `curl` với file cấu hình kiểu `curl -K`.

Lệnh hằng ngày và danh sách phase: **`tools/README.md`**. File này giữ phần hạ tầng —
credential, tài khoản, những thứ không được đụng, và cách quay lui.

```bash
cd "/c/Users/louis/Dropbox/Tintt/claude code/duhoctesol"
python tools/wp-deploy.py ~/.duhoctesol-ftp.cfg theme
python tools/wp-verify.py          # PHẢI in 723/723
```

Trên Windows dùng `python`, không phải `python3`.

## Credential

```
C:\Users\louis\.duhoctesol-ftp.cfg          (trong Git Bash: ~/.duhoctesol-ftp.cfg)
```

**Quy tắc cho Claude:** file này tồn tại và không chứa chuỗi `DANMATKHAUVAODAY` →
**deploy luôn, không hỏi mật khẩu**. Chỉ hỏi user khi file không tồn tại, còn placeholder,
hoặc FTP trả `530`. Thử đăng nhập **đúng một lần** — cPHulk khoá IP nếu sai nhiều lần.

User đã quyết định 2026-09-19: **giữ nguyên mật khẩu, không đổi, không nhắc lại.**

File nằm **ngoài git repo** — cố ý. Đừng chép mật khẩu vào bất kỳ file nào trong repo,
kể cả file này: repo đẩy lên GitHub, commit vào là lộ vĩnh viễn trong lịch sử.

Nếu phải tạo lại:

```bash
printf 'user "uploadtesolhcm@duhoctesol.duystudy.vn:MATKHAU"\n' > ~/.duhoctesol-ftp.cfg
chmod 600 ~/.duhoctesol-ftp.cfg
```

Hai tài khoản **không dùng được**, đã thử rồi, đừng thử lại:
`deploy@duystudy.vn` trong `~/.duy-ftp.cfg` (đăng nhập được nhưng bị chroot trong docroot
của duystudy.vn) và `~/.netrc` (chỉ có account vnguide.vn).

## Tài khoản và hạ tầng

| | |
|---|---|
| FTP host | `pbf43-22360.azdigihost.com` · **port 21, FTP thường** (server từ chối AUTH TLS — đừng thêm `--ssl`) |
| FTP user | `uploadtesolhcm@duhoctesol.duystudy.vn` |
| Docroot | FTP account chroot thẳng vào docroot → FTP `/` == `/home/infkkcwh/duhoctesol.duystudy.vn` |
| cPanel | `pbf43-22360.azdigihost.com:2083`, user `infkkcwh`, home `/home/infkkcwh` |
| IP | `103.221.223.76` (shared) — DNS `@` và `www` của duhoctesolhcmc.vn trỏ đúng IP này |
| Domain cũ | `duhoctesol.duystudy.vn` → 301 sang domain mới, giữ path |

Docroot, subdomain và FTP account vẫn mang tên cũ `duhoctesol.duystudy.vn`. **Đó là bình
thường, đừng đổi tên cho "gọn"** — đổi là gãy cả đường dẫn lẫn credential.

### Không được làm

cPanel `infkkcwh` là **hosting dùng chung**, còn chạy `amigoagency.vn` (Primary Domain),
`duystudy.vn`, `vnguide.vn`. Đụng vào là hỏng site của người khác.

- Đừng đổi **Primary Domain**.
- Đừng sửa `/home/infkkcwh/.htaccess` hay `/public_html/.htaccess` — chỉ sửa `.htaccess`
  trong docroot của site này.
- Đừng chạy **"Run AutoSSL For All Domains"**.
- Đừng xoá subdomain `duhoctesol.duystudy.vn`, đừng đổi tên docroot.
- `.well-known/` phải luôn được loại khỏi mọi redirect, nếu không AutoSSL không gia hạn được.

### Dung lượng

Chụp ngày 18/09/2026: **22,75 GB / 30 GB (75,83%)**, trước khi đẩy WordPress. Bản cài
WordPress tốn thêm khoảng **760 MB** (lõi 59 MB + uploads 693 MB + theme 11 MB), và site
tĩnh cũ ~478 MB vẫn còn trong docroot. Account khá đầy — **đừng deploy trùng lặp nhiều lần**,
và cân nhắc xoá site tĩnh khi không cần đường lui nữa.

## `.htaccess`

Bản đang chạy: `docs/server/htaccess-wordpress-2026-09-20.txt`, deploy bằng phase
`htaccess-wp`. Nó gồm:

1. loại trừ `.well-known/` (phải đứng đầu)
2. 4 redirect stub `/nganh-hoc/` và 5 redirect trang lưu trữ rỗng
3. `/sitemap.xml` → `/wp-sitemap.xml`
4. **301 mọi URL `/…/index.html` về URL sạch** (thêm 21/09 — chặn bản tĩnh cũ còn đọc được)
5. canonical host: subdomain cũ, www → non-www, http → https
6. khối rewrite WordPress — **đã bỏ điều kiện `!-d`**

Chỗ số 6 là điểm dễ sai nhất: rule mặc định của WordPress có `!-d`, mà docroot còn 748 thư
mục của site tĩnh, nên mod_rewrite bỏ qua và `DirectoryIndex` lại lấy `index.html` —
**WordPress không chạy**. Đã đo thực tế: 746/751 URL vẫn trả về file tĩnh. Khi nào xoá hết
file tĩnh trên server thì mới trả rule về bản mặc định được.

## Quay lui

```bash
python tools/wp-deploy.py ~/.duhoctesol-ftp.cfg htaccess-static
```

Đổi `DirectoryIndex` về `index.html index.php` → 748 file tĩnh phục vụ trở lại trong vài
giây. Bản `.htaccess` cũ: `docs/server/htaccess-live-2026-09-20b.txt`.

Đường lui này còn sống **chừng nào file tĩnh còn trên server**. Xoá chúng là mất.

## Lịch sử

- **18/09/2026** — site tĩnh lên sóng tại domain mới; đổi từ `duhoctesol.duystudy.vn`.
- **19/09/2026** — nộp `sitemap.xml` (723 URL) cho Google Search Console.
- **20/09/2026** — chuyển sang WordPress, giữ nguyên 100% URL.
- **21/09/2026** — gỡ nội dung dựng sẵn, trả lại bộ nhận diện gốc, chặn URL `/index.html`.

Chi tiết từng mốc: `HANDOFF.md`. Runbook thời site tĩnh: `archive/tools-static-era/`.
