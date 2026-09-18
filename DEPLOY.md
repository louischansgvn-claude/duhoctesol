# DEPLOY — Du học TESOL (review site)

**Cách deploy: Claude tự chạy qua FTP (curl). Khi user nói "deploy" → cần credential FTP (xem §Credential), rồi chạy `tools/deploy-ftp.py`.**

## ⚠️ ĐANG CHỜ DEPLOY + ĐỔI DOMAIN (cập nhật 2026-09-18)

Bản **571 trường + 9 sự kiện + 72 video thật** đã dựng xong và verify trên máy, **chưa lên server**.
Live hiện tại vẫn là bản cũ 171 trang trên `https://duhoctesol.duystudy.vn`.

**Metadata trong repo đã trỏ sang domain mới `https://duhoctesolhcmc.vn`** (canonical / og:url /
og:image / JSON-LD — 12.799 URL). Vì vậy **phải trỏ domain mới vào docroot TRƯỚC khi deploy**,
xem **§Đổi domain** ngay dưới. `noindex,nofollow` vẫn còn trên cả 748 trang — chỉ gỡ ở bước cuối.

Khi deploy: dùng **§Deploy lớn**, KHÔNG dùng runbook `**/index.html` mặc định — lần này phải đẩy cả
`wp-content/uploads/` (**1.753 file**) + `main.css`, rồi **xoá tay 34 thư mục demo** (xem §Xoá thư mục demo).

## Đổi domain — `duhoctesol.duystudy.vn` → `duhoctesolhcmc.vn`

> ⚠️ **Hosting này chạy nhiều website khác** (`duystudy.vn`, `vnguide.vn`, …) trên cùng cPanel `infkkcwh`.
> Mọi bước dưới đây **chỉ tác động đúng 1 site**. Đọc kỹ phần "KHÔNG được làm".

**KHÔNG được làm:**
- ❌ **Đừng đổi Primary Domain của cPanel.** Đổi primary ảnh hưởng toàn bộ account và mọi site khác.
- ❌ Đừng sửa `.htaccess` ở `/home/infkkcwh/` hay `/home/infkkcwh/public_html/` — đó là vùng của site khác.
- ❌ Đừng bật "Force HTTPS" ở mức account nếu các site khác chưa có SSL.
- ❌ Đừng xoá subdomain `duhoctesol.duystudy.vn` — còn dùng để 301 về domain mới.

**Điểm an toàn có sẵn:** FTP account `uploadtesolhcm@duhoctesol.duystudy.vn` bị **chroot** vào đúng
`/home/infkkcwh/duhoctesol.duystudy.vn`, nên script deploy **không thể** ghi sang site khác. Cứ dùng account này.

### Thứ tự (đúng thứ tự này, đừng đảo)

1. **Trỏ domain mới vào docroot hiện có** — cPanel → *Domains* → **Create A Domain**
   - Domain: `duhoctesolhcmc.vn`
   - **Bỏ tick** "Share document root"
   - **Document Root: sửa thành `/home/infkkcwh/duhoctesol.duystudy.vn`**
     (cPanel mặc định gợi ý `/home/infkkcwh/duhoctesolhcmc.vn` → **phải đổi**, nếu không domain mới
     trỏ vào thư mục rỗng và bạn sẽ thấy trang trắng / Index of /)
   - DNS: trỏ A record của `duhoctesolhcmc.vn` (và `www`) về IP của host này.
2. **SSL cho domain mới** — cPanel → *SSL/TLS Status* → tick **chỉ** `duhoctesolhcmc.vn` + `www` → **Run AutoSSL**.
   Đừng chạy AutoSSL cho cả account nếu không cần. Chờ cert xong mới sang bước sau.
3. **Kiểm tra domain mới đã serve đúng thư mục** (lúc này vẫn là bản cũ 171 trang — đúng như kỳ vọng):
   ```bash
   curl -sI https://duhoctesolhcmc.vn/ | head -1          # kỳ vọng 200
   curl -s https://duhoctesolhcmc.vn/ | grep -c 'duy-study'   # >0 = đúng thư mục site này
   ```
4. **Deploy** — chạy §Deploy lớn (images → css → html).
5. **Xoá 34 thư mục demo** — xem §Xoá thư mục demo.
6. **Verify** — §Deploy lớn có sẵn danh sách URL (đã trỏ domain mới).
7. **Gỡ `noindex`** — chỉ làm khi bước 6 xanh hết:
   Thẻ thật trong file là `<meta name="robots" content="noindex,nofollow">` (KHÔNG có ` />`).
   ```bash
   python3 - <<'PYEOF'
   import glob
   TAG = '<meta name="robots" content="noindex,nofollow">
'
   n = 0
   for f in glob.glob('**/index.html', recursive=True):
       if f.startswith(('.git', 'docs/', '.superpowers', 'scratchpad')): continue
       s = open(f, encoding='utf-8', newline='').read()
       if TAG not in s: continue
       open(f, 'w', encoding='utf-8', newline='').write(s.replace(TAG, '', 1))
       n += 1
   print('go noindex:', n, 'file')     # ky vong 748
   PYEOF
   grep -rl 'noindex' --include=index.html . | wc -l        # ky vong 0
   python3 tools/deploy-ftp.py ~/.duhoctesol-ftp.cfg html
   ```
8. **301 subdomain test → domain mới.** Sửa `.htaccess` **trong docroot này** (`FTP /` == docroot,
   KHÔNG phải `public_html`). Thêm vào đầu file, và **phải có điều kiện `HTTP_HOST`** để không
   ảnh hưởng domain mới hay site khác:
   ```apache
   RewriteEngine On
   RewriteCond %{HTTP_HOST} ^duhoctesol\.duystudy\.vn$ [NC]
   RewriteRule ^(.*)$ https://duhoctesolhcmc.vn/$1 [R=301,L]
   ```
   ⚠️ Tải `.htaccess` hiện tại về **trước khi sửa** (nguyên tắc deploy: giữ nguyên `.htaccess`).
9. **`robots.txt` + `sitemap.xml`** — repo hiện **chưa có**. Tạo trong docroot này, `Sitemap:` trỏ
   `https://duhoctesolhcmc.vn/sitemap.xml`.

### Nếu buộc phải dùng docroot khác
Nếu không set được Document Root về thư mục cũ, mọi đường dẫn FTP ở dưới đổi theo, và
`uploadtesolhcm@duhoctesol.duystudy.vn` **sẽ không vào được** thư mục mới (nó bị chroot) → phải tạo
FTP account mới trỏ vào docroot mới. Cập nhật lại §Credential khi đó.

## Credential

### Máy Windows của user (mặc định từ 2026-09-18)

File credential **đã có sẵn** tại:

```
C:\Users\louis\.duhoctesol-ftp.cfg          (trong Git Bash: ~/.duhoctesol-ftp.cfg)
```

**Quy tắc cho Claude:** nếu file này tồn tại và KHÔNG chứa chuỗi `DANMATKHAUVAODAY`
-> **deploy luôn, KHÔNG hỏi user mật khẩu**:

```bash
cd "/c/Users/louis/Dropbox/Tintt/claude code/duhoctesol"
python tools/deploy-ftp.py ~/.duhoctesol-ftp.cfg login
python tools/deploy-ftp.py ~/.duhoctesol-ftp.cfg images   # 442 MB, chay nen
python tools/deploy-ftp.py ~/.duhoctesol-ftp.cfg css
python tools/deploy-ftp.py ~/.duhoctesol-ftp.cfg html
```

Chỉ hỏi user khi: file không tồn tại - còn placeholder - hoặc `login` trả `530` (mật khẩu đã đổi).
Trên Windows dùng `python`, không phải `python3`.

File nằm **ngoài git repo** - cố ý. Đừng chép mật khẩu vào bất kỳ file nào trong repo
(kể cả `DEPLOY.md`, `HANDOFF.md`): repo là git, commit vào là lộ vĩnh viễn trong lịch sử.



Ưu tiên đọc từ file cấu hình kiểu `curl -K` (password không đi qua chat, tái dùng được nhiều lần):

```bash
# user tự chạy 1 lần trên máy mình
printf 'user "uploadtesolhcm@duhoctesol.duystudy.vn:PASSWORD"\n' > ~/.duhoctesol-ftp.cfg && chmod 600 ~/.duhoctesol-ftp.cfg
```

Cần một tài khoản **có quyền vào docroot `/home/infkkcwh/duhoctesol.duystudy.vn`**. Chấp nhận được:
`uploadtesolhcm@duhoctesol.duystudy.vn` · tài khoản cPanel chính `infkkcwh` · hoặc FTP account mới trỏ vào thư mục đó.

❌ **`~/.duy-ftp.cfg` (tài khoản `deploy@duystudy.vn`) KHÔNG dùng được** — đã test 2026-08-18: login 230 OK
nhưng `CWD /home/infkkcwh/duhoctesol.duystudy.vn` trả `Server denied you to change to the given directory`
(chroot trong docroot duystudy.vn). Đừng thử lại.
❌ `~/.netrc` chỉ có account của vnguide.vn trên cùng host — cũng không dùng được.

Nếu user gửi password qua chat thay vì tạo file: tự tạo `~/.duhoctesol-ftp.cfg` (chmod 600), deploy xong
thì **nhắc user đổi password**.

## Deploy lớn — dùng `tools/deploy-ftp.py`

```bash
cd "/Users/louis/Library/CloudStorage/Dropbox/Tintt/claude code/duhoctesol"
CFG=~/.duhoctesol-ftp.cfg
python3 tools/deploy-ftp.py $CFG login     # kỳ vọng "< 230"; nếu 530 → sai pass, ĐỪNG thử lại nhiều (cPHulk)
python3 tools/deploy-ftp.py $CFG images    # 1.712 file / 414 MB — lâu nhất, chia batch 150
python3 tools/deploy-ftp.py $CFG css       # assets/css/main.css
python3 tools/deploy-ftp.py $CFG html      # 782 index.html, batch 400
```

**Thứ tự ảnh → CSS → HTML là cố ý**: nếu đứt giữa chừng thì site cũ vẫn nguyên vẹn, chưa trang nào trỏ vào ảnh thiếu.
Script không in password, tự xoá file config tạm sau mỗi batch, `--ftp-create-dirs` để tạo thư mục mới.

### Verify sau deploy
```bash
for u in "https://duhoctesolhcmc.vn/" \
         "https://duhoctesolhcmc.vn/truong/" \
         "https://duhoctesolhcmc.vn/truong/winthrop-high-school/" \
         "https://duhoctesolhcmc.vn/truong/toronto-district-school-board-tdsb/" \
         "https://duhoctesolhcmc.vn/su-kien/hoi-xuan-thpt-trung-vuong-2024/" \
         "https://duhoctesolhcmc.vn/quoc-gia/anh/dai-hoc/" \
         "https://duhoctesolhcmc.vn/quoc-gia/my/anh-ngu/" \
         "https://duhoctesolhcmc.vn/wp-content/uploads/thpt-seo/winthrop-high-school-logo-scaled.png" \
         "https://duhoctesolhcmc.vn/wp-content/uploads/events/hoi-xuan-thpt-trung-vuong-2024-img-01.jpg"; do
  echo "$(curl -s -o /dev/null -w '%{http_code}' "$u")  $u"; done
# kỳ vọng 200 cả 9. Thêm: /truong/ phải có 572 'data-finder-card'
curl -s "https://duhoctesolhcmc.vn/truong/" | grep -o "data-finder-card" | wc -l
```

---

## Thông tin tài khoản (KHÔNG lưu password trong file này)
- **Site đang live:** https://duhoctesol.duystudy.vn/ (bản cũ 171 trang, `noindex,nofollow`)
- **Domain đích:** https://duhoctesolhcmc.vn/ — metadata trong repo đã trỏ về đây; cần làm §Đổi domain trước khi deploy
- **Hosting dùng chung:** cPanel `infkkcwh` còn chạy `duystudy.vn`, `vnguide.vn`… → xem phần "KHÔNG được làm" ở §Đổi domain
- **FTP host:** `pbf43-22360.azdigihost.com`  **port 21, PLAIN FTP** (server từ chối AUTH TLS → dùng FTP thường, không `--ssl`)
- **FTP username:** `uploadtesolhcm@duhoctesol.duystudy.vn`
- **FTP password:** ⚠️ KHÔNG lưu ở đây. Hỏi user gửi mỗi lần deploy. (User đã đồng ý cách này.)
- **Docroot:** FTP account chroot thẳng vào docroot — FTP `/` == `/home/infkkcwh/duhoctesol.duystudy.vn`. Upload path = repo-relative (vd `quoc-gia/my/index.html`).
- cPanel: `pbf43-22360.azdigihost.com:2083`, cPanel user `infkkcwh` (home `/home/infkkcwh`).

## Nguyên tắc deploy
- **Ghi đè, KHÔNG xoá.** Giữ nguyên trên server: `.htaccess`, `.user.ini`, `.well-known`, `cgi-bin`, và toàn bộ `wp-content` (CSS/JS/ảnh — repo không đổi các file này).
- **Chỉ upload các file HTML** (`**/index.html`, **748 file**). Bỏ qua `wp-content` (không đổi) → nhanh, 1 phiên FTP.
  (Lần deploy đầu sau import thì KHÔNG dùng runbook này — phải đẩy cả ảnh + css, xem §Deploy lớn.)
- `--ftp-create-dirs` để tự tạo thư mục mới (vd các trang `/video/`).
- Bỏ qua file nội bộ: `.git`, `docs/`, `.superpowers/`, `CLAUDE.md`, `HANDOFF.md`, `DEPLOY.md`, `.gitignore`, `duhoctesol-deploy.zip`.

## Runbook (chạy trên máy Mac của user — chỉ có `curl`, không có lftp/brew)
1. Xin user password FTP → đặt biến (không echo ra log):
   ```bash
   FTPUSER='uploadtesolhcm@duhoctesol.duystudy.vn'
   FTPPASS='<<PASSWORD USER GỬI>>'
   HOST='ftp://pbf43-22360.azdigihost.com'
   ```
2. Login test 1 lần (tránh cPHulk — đừng thử sai nhiều):
   ```bash
   curl -sv --connect-timeout 15 -u "$FTPUSER:$FTPPASS" "$HOST/" 2>&1 | grep -iE "^< 230|^< 530|Access denied" | head
   ```
   Kỳ vọng `230 OK`. Nếu `530` → sai pass, xin lại (đừng lặp nhiều lần).
3. Sinh curl config (upload toàn bộ `**/index.html`) rồi upload 1 phiên:
   ```bash
   cd "/Users/louis/Library/CloudStorage/Dropbox/Tintt/claude code/duhoctesol"
   CFG="$(mktemp)"
   python3 - "$CFG" "$FTPUSER" "$FTPPASS" <<'PY'
   import os,sys,glob
   cfg,user,pw=sys.argv[1],sys.argv[2],sys.argv[3]
   host="ftp://pbf43-22360.azdigihost.com"
   L=['ftp-create-dirs','connect-timeout = 20','retry = 3',f'user = "{user}:{pw}"']
   n=0
   for f in glob.glob('**/index.html',recursive=True):
       if f.startswith(('.git','docs/','.superpowers')): continue
       L.append(f'upload-file = "{os.path.abspath(f)}"'); L.append(f'url = "{host}/{f}"'); n+=1
   open(cfg,'w').write("\n".join(L)+"\n"); print("files:",n)
   PY
   curl -sS -K "$CFG"; echo "curl exit: $?"
   rm -f "$CFG"     # xoá ngay: config chứa password
   ```
4. Verify (server + HTTPS):
   ```bash
   for u in "https://duhoctesolhcmc.vn/" "https://duhoctesolhcmc.vn/quoc-gia/my/video/" "https://duhoctesolhcmc.vn/lien-he/"; do
     echo "$(curl -s -o /dev/null -w '%{http_code}' "$u")  $u"; done
   ```
   Kỳ vọng `200` cả 3.
5. **Xoá mọi file tạm chứa password** (config/mktemp). Nhắc user đổi lại pass FTP sau khi deploy vì đã gửi qua chat.

## Runbook nhỏ (chỉ đổi text, KHÔNG đổi ảnh/CSS)
Dùng khi task chỉ sửa nội dung HTML: `python3 tools/deploy-ftp.py ~/.duhoctesol-ftp.cfg html` (bỏ qua images/css).

## Nếu wp-content / ảnh / CSS có thay đổi (hiếm)
Thêm các file đó vào vòng upload (đổi glob `**/index.html` → cần thiết) hoặc upload cả cây. Mặc định KHÔNG cần vì các task nội dung không đụng `wp-content`.

## Lịch sử
- 2026-07-07: Deploy lần đầu bản TESOL (171 HTML + 9 trang video) qua FTP, ghi đè bản gốc Jul 1. Verify 200 OK.
- 2026-07-09: Deploy bản revert về nội dung du học tổng quát + đổi tên tổ chức thành "Ban Du học Hội TESOL TP.HCM" + dọn sạch note nội bộ + tagline footer. 171 HTML, verify 200 OK.
- 2026-08-18: **CHƯA DEPLOY** — bản import 572 trường + 9 sự kiện dựng xong, verify local đủ, chờ credential FTP.
  Khi deploy: 782 HTML + 1.712 ảnh (414 MB) + `main.css`. Kiểm tra dung lượng host còn đủ trước khi đẩy.

## Xoá thư mục demo (bắt buộc sau lần deploy lớn đầu tiên)

Deploy theo nguyên tắc **ghi đè, không xoá** → 34 thư mục demo đã xoá khỏi repo **vẫn còn trên server**
và vẫn truy cập được. Phải xoá tay **sau khi** deploy xong.

> ⚠️ Hosting dùng chung (`amigoagency.vn` là primary, còn `duystudy.vn`, `vnguide.vn`…).
> Chỉ thao tác **bên trong** `/home/infkkcwh/duhoctesol.duystudy.vn`. Kiểm tra đường dẫn trước mỗi lần xoá.

**Cách an toàn nhất — cPanel File Manager** (thấy rõ đang đứng ở đâu):
cPanel → *File Manager* → vào `/home/infkkcwh/duhoctesol.duystudy.vn` → xoá 34 thư mục dưới đây.

`truong/` — 26 thư mục:
```
asu  auckland  australian-national-university  boston  broward  dalhousie-university
massey-university  melbourne  metu  monash  olympic  oregon-state-university  purdue-nw
sheridan-college  st-peters  sydney  the-university-of-melbourne
the-university-of-new-south-wales  the-university-of-queensland  toronto
university-of-auckland  university-of-canterbury  university-of-connecticut
university-of-otago  university-of-waikato  washington
```

`su-kien/` — 8 thư mục:
```
australia-webinar  canada-fair  essay-workshop  parent-night
pre-departure-aug  top-aus-unis  turkey-open-day  visa-check-day
```

⚠️ **ĐỪNG xoá `truong/north-yarmouth-academy-educatius-exclusive/`** — đây là trường **thật**, chỉ bị đổi slug.

**Cách qua FTP** (account đã chroot đúng docroot nên không chạm được site khác):
```bash
CFG=~/.duhoctesol-ftp.cfg
HOST='ftp://pbf43-22360.azdigihost.com'
for d in asu auckland australian-national-university boston broward dalhousie-university \
         massey-university melbourne metu monash olympic oregon-state-university purdue-nw \
         sheridan-college st-peters sydney the-university-of-melbourne \
         the-university-of-new-south-wales the-university-of-queensland toronto \
         university-of-auckland university-of-canterbury university-of-connecticut \
         university-of-otago university-of-waikato washington; do
  curl -s -K "$CFG" "$HOST/truong/$d/" -Q "DELE /truong/$d/index.html" -Q "RMD /truong/$d" -o /dev/null
done
for d in australia-webinar canada-fair essay-workshop parent-night \
         pre-departure-aug top-aus-unis turkey-open-day visa-check-day; do
  curl -s -K "$CFG" "$HOST/su-kien/$d/" -Q "DELE /su-kien/$d/index.html" -Q "RMD /su-kien/$d" -o /dev/null
done
```

**Verify — cả 34 URL phải trả 404:**
```bash
for u in https://duhoctesolhcmc.vn/truong/sydney/ \
         https://duhoctesolhcmc.vn/truong/metu/ \
         https://duhoctesolhcmc.vn/truong/toronto/ \
         https://duhoctesolhcmc.vn/su-kien/canada-fair/ \
         https://duhoctesolhcmc.vn/su-kien/visa-check-day/; do
  echo "$(curl -s -o /dev/null -w '%{http_code}' "$u")  $u"; done
# và trường thật vẫn phải 200:
curl -s -o /dev/null -w '%{http_code}\n' https://duhoctesolhcmc.vn/truong/north-yarmouth-academy-educatius-exclusive/
```

## Thông số hosting (chụp 2026-09-18)
- cPanel user `infkkcwh` · home `/home/infkkcwh` · **Primary Domain: `amigoagency.vn`** (KHÔNG phải site này)
- **Shared IP: `103.221.223.76`** — DNS của `duhoctesolhcmc.vn` (`@` + `www`, A record, TTL 300) đã trỏ đúng IP này
- Addon Domains: **4/20** → còn chỗ thêm `duhoctesolhcmc.vn`
- **Disk: 22.75 GB / 30 GB (75.83%)** — còn ~7.25 GB. Lần deploy này tốn **~478 MB**
  (442 MB ảnh + 35.6 MB HTML + 76 KB CSS) → đủ, nhưng account đã khá đầy, đừng deploy trùng lặp nhiều lần.
- SSL của account đang là **Self-signed** → sau khi thêm domain phải chạy AutoSSL cho `duhoctesolhcmc.vn`
