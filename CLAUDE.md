# CLAUDE.md — Ban Du học Hội TESOL TP.HCM

Site tư vấn du học tổng quát, **LIVE tại https://duhoctesolhcmc.vn**: WordPress 6.9.4 chạy
trên SQLite (không cần MySQL), hosting chia sẻ chỉ có FTP. Nội dung là du học tổng quát —
định hướng, chọn quốc gia, chọn trường, ngành học, học bổng, visa, chuẩn bị lên đường.

Sản phẩm của repo này là **theme `wp-content/themes/duy-study/`**. Mọi thứ hiển thị trên
site đều do theme quyết định: giao diện, SEO, JSON-LD, `robots.txt`, `llms.txt`, `wp-sitemap.xml`.

Nhánh làm việc: **`tesol-content-rewrite`**.

## Bố cục repo

| Thư mục | Là gì |
|---|---|
| `wp-content/themes/duy-study/` | **theme — sản phẩm chính.** `inc/seo.php` lo toàn bộ SEO, `inc/routes.php` lo URL ảo, `inc/demo-data.php` giữ dữ liệu dựng sẵn |
| `tools/` | `wp-deploy.py` (đẩy lên server) và `wp-verify.py` (kiểm tra) — xem `tools/README.md` |
| `docs/server/` | các bản `.htaccess` trên server, kèm bản cũ để quay lui |
| `docs/sitemap-submitted-2026-09-19.xml` | 723 URL đã nộp cho Google — mốc kiểm tra hồi quy của `wp-verify.py` |
| `archive/` | **site tĩnh đã nghỉ hưu + công cụ cũ. Không chạy gì ở đây** — xem `archive/README.md` |
| `wp-build/` | CSDL + `wp-config.php` + mật khẩu admin. Ngoài git, không bao giờ commit |

## Quy trình sửa

```bash
# sửa theme trong wp-content/themes/duy-study/
python tools/wp-deploy.py ~/.duhoctesol-ftp.cfg theme
python tools/wp-verify.py          # PHẢI in 723/723
```

## Bẫy đã sập một lần — đừng sập lại

- **Bộ nhận diện thuộc repo này, không phải của Duy Study.** Logo `assets/img/logo.png`
  (chữ TESOL navy, chữ HCMC đỏ), màu `--primary:#173C8F` và `--accent:#C4302B` lấy từ
  chính logo đó. Theme nguồn dùng cyan `#23a9d8` + hồng `#df1f83`. Ngày 20/09 cả bảng màu
  lẫn `logo.webp`, `og-default.jpg` của Duy Study lọt sang và **site treo logo họ suốt một
  ngày**. Thấy màu cyan/hồng ở đâu là có thứ vừa lọt ngược vào.
- **Nội dung dựng sẵn nằm trong code theme, không nằm trong CSDL.** Các hàm `duy_demo_*()`
  trong `inc/demo-data.php` chèn dữ liệu cứng khi CSDL chưa có bài tương ứng. Gặp nội dung
  đáng ngờ trên site thì tìm ở đây trước — **xoá trong wp-admin không có tác dụng** với
  loại này. Ngày 21/09 đã gỡ 7 học bổng, 7 câu chuyện học sinh và 1 trường bịa khỏi đó.
- **FTP chỉ biết tải lên.** Xoá file trong repo là chưa đủ, bản trên server vẫn được phục
  vụ. Thêm vào hằng `PRUNE` trong `wp-deploy.py` rồi chạy phase `prune`.
- **Đừng chạy phase `db`** nếu user đã sửa nội dung trong wp-admin — ghi đè cả CSDL trên
  server. Kể từ lúc user đăng nhập, **bản trên server là bản gốc**.
- **Cache thẻ trường sống 12 giờ** (`duy_schools_cards_v2`, `duy_school_order_v2`). Đổi dữ
  liệu trường mà không tăng số phiên bản trong tên khoá thì server vẫn trả bản cũ.

## Quy tắc nội dung

- **Chỉ đăng thứ có thật.** Tên trường, tên học bổng, giá trị, deadline, câu chuyện học
  viên — nếu không tra được ở nguồn chính thức thì không đăng. Thà để trống và ghi "đang
  cập nhật" còn hơn dựng nội dung mẫu: user đã yêu cầu gỡ đúng loại nội dung đó ngày 21/09.
- **Số liệu là tham khảo** — luôn có chữ "tham khảo", "cập nhật 2026", và dùng tên
  trường/chương trình thật.
- **Thương hiệu giữ nguyên:** `Ban Du học Hội TESOL TP.HCM`. Đây là **một ban thuộc hội**,
  không phải công ty — đừng viết "Công ty Tư vấn…".
- **Không bao giờ thêm lại `noindex`** cho trang có nội dung thật. Trang danh sách rỗng thì
  theme tự đặt `noindex` và tự bỏ khi có nội dung (`duy_seo_is_noindex_route`).
- **Số điện thoại chính thức: `0906510747`.** Khi một `href` mâu thuẫn với chữ hiển thị
  trên trang thì nêu ra — lỗi này từng khiến mọi nút gọi và Zalo trỏ sang công ty khác
  suốt nhiều tháng.
- 4 bài tường thuật sự kiện do Duy Study tổ chức được phép nhắc tên họ — user đã xác nhận
  "trang này đã đúng rồi không cần sửa".

## Bảo mật và hạ tầng

- **Mật khẩu FTP không bao giờ nằm trong repo.** Chỉ ở `~/.duhoctesol-ftp.cfg`. Không in ra
  log, không in ra chat. User đã chốt 19/09: **giữ mật khẩu, không đổi, không hỏi lại.**
- **Mật khẩu admin ở `wp-build/ADMIN.txt`** (ngoài git). Không gửi qua chat — chỉ dẫn user
  tới file.
- Hosting cPanel `infkkcwh` còn chạy `amigoagency.vn` (domain chính), `duystudy.vn`,
  `vnguide.vn`. **Đừng** đổi Primary Domain, **đừng** sửa `/home/infkkcwh/.htaccess` hay
  `/public_html/.htaccess`, **đừng** chạy "Run AutoSSL For All Domains", **đừng** xoá
  subdomain `duhoctesol.duystudy.vn`, **đừng** đổi tên docroot.
- `.well-known/` phải luôn được loại khỏi mọi redirect, nếu không AutoSSL không gia hạn được.

## MANDATORY WORKFLOW — Claude ↔ Codex (bắt buộc)

Every implementation task in this repo follows this loop. Do NOT skip steps.

1. **Claude = kiến trúc + review.** Claude builds/owns the plan and specs, and **reviews Codex's output** (spec compliance, accuracy, no broken structure). Claude does NOT write the implementation itself — it delegates coding to Codex.
2. **Codex = triển khai.** Codex implements the code, **reviews the plan first and gives feedback** before/while implementing, and **must turn on "goal" mode khi làm** (Codex runs with an explicit tracked goal). In this workflow, **"Codex" means the implementation agent currently doing the task in-session**.
3. **Prompt exchange is mandatory.** Every output handed over MUST be accompanied by the **prompt** that produced it (the prompts pass back and forth in both directions).
   - The prompt **Claude sends to Codex MUST explicitly remind Codex to bật "goal"/prompt mode** before it starts.
   - When Codex returns output, it returns **OUTPUT + PROMPT/GOAL it used**, so Claude can review both.
   - Codex's final response and any task report MUST include:
     - **OUTPUT**: status (DONE/BLOCKED), commit hash if there is a commit, verification summary, and changed files/artifacts.
     - **PROMPT/GOAL**: the task prompt Codex used (or a concise faithful restatement) plus the explicit tracked goal objective.
4. **Handoff discipline.** At every important step/milestone, **update `HANDOFF.md`** immediately so nothing is missed when work resumes in a later session. Treat the handoff as the source of truth on resume.

### Roles at a glance
| Step | Owner | Must do |
|---|---|---|
| Plan / spec | Claude | Write & maintain plan; hand Codex a task prompt (with "bật goal" reminder) |
| Plan feedback | Codex | Review the plan, flag issues before coding |
| Implement | Codex | Code with goal mode ON; return **OUTPUT + PROMPT/GOAL** |
| Review output | Claude | Verify against spec/constraints; approve or send fixes (with prompt) |
| Handoff | Both | Update `HANDOFF.md` at each milestone |

- **Sub-agent isolation:** when delegating, the implementer must do the work itself — **do NOT spawn nested sub-agents, run in background, or sleep**. (Nested self-parallelization caused file races before.)

## Lịch sử

Site từng là **748 trang HTML tĩnh** (18/09 → 20/09/2026), xuất ra từ chính theme này bên
project `../duystudy - website/`. Chuyển sang WordPress ngày 20/09 giữ nguyên 100% URL.
Trước đó, trên nhánh này site từng được viết lại thành một TESOL consultancy rồi **hoàn
nguyên** về bản du học tổng quát (base `5eeecd0`), giữ lại 9 trang video quốc gia. Các tài
liệu TESOL cũ trong `docs/superpowers/**` là lưu trữ, không còn phản ánh nội dung site.

Chi tiết từng mốc và lý do: **`HANDOFF.md`** — đọc trước khi làm bất cứ việc gì.
