# DEPLOY — Du học TESOL (review site)

**Cách deploy: Claude tự chạy qua FTP (curl). Khi user nói "deploy" → chỉ cần user gửi PASSWORD FTP, làm theo runbook dưới.**

## Thông tin tài khoản (KHÔNG lưu password — hỏi user mỗi lần)
- **Site (live/review):** https://duhoctesol.duystudy.vn/  (đang `noindex,nofollow`; footer đã dọn sạch note nội bộ từ 2026-07-09)
- **FTP host:** `pbf43-22360.azdigihost.com`  **port 21, PLAIN FTP** (server từ chối AUTH TLS → dùng FTP thường, không `--ssl`)
- **FTP username:** `uploadtesolhcm@duhoctesol.duystudy.vn`
- **FTP password:** ⚠️ KHÔNG lưu ở đây. Hỏi user gửi mỗi lần deploy. (User đã đồng ý cách này.)
- **Docroot:** FTP account chroot thẳng vào docroot — FTP `/` == `/home/infkkcwh/duhoctesol.duystudy.vn`. Upload path = repo-relative (vd `quoc-gia/my/index.html`).
- cPanel: `pbf43-22360.azdigihost.com:2083`, cPanel user `infkkcwh` (home `/home/infkkcwh`).

## Nguyên tắc deploy
- **Ghi đè, KHÔNG xoá.** Giữ nguyên trên server: `.htaccess`, `.user.ini`, `.well-known`, `cgi-bin`, và toàn bộ `wp-content` (CSS/JS/ảnh — repo không đổi các file này).
- **Chỉ upload các file HTML** (`**/index.html`, 171 file). Bỏ qua `wp-content` (không đổi) → nhanh (~30s, 1 phiên FTP).
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
   for u in "https://duhoctesol.duystudy.vn/" "https://duhoctesol.duystudy.vn/quoc-gia/my/video/" "https://duhoctesol.duystudy.vn/lien-he/"; do
     echo "$(curl -s -o /dev/null -w '%{http_code}' "$u")  $u"; done
   ```
   Kỳ vọng `200` cả 3.
5. **Xoá mọi file tạm chứa password** (config/mktemp). Nhắc user đổi lại pass FTP sau khi deploy vì đã gửi qua chat.

## Nếu wp-content / ảnh / CSS có thay đổi (hiếm)
Thêm các file đó vào vòng upload (đổi glob `**/index.html` → cần thiết) hoặc upload cả cây. Mặc định KHÔNG cần vì các task nội dung không đụng `wp-content`.

## Lịch sử
- 2026-07-07: Deploy lần đầu bản TESOL (171 HTML + 9 trang video) qua FTP, ghi đè bản gốc Jul 1. Verify 200 OK.
- 2026-07-09: Deploy bản revert về nội dung du học tổng quát + đổi tên tổ chức thành "Ban Du học Hội TESOL TP.HCM" + dọn sạch note nội bộ + tagline footer. 171 HTML, verify 200 OK.
