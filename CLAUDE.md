# CLAUDE.md — Du học TESOL (site du học tổng quát)

> ## ⚠️ ĐỌC TRƯỚC TIÊN (cập nhật 2026-09-20)
> **Site đã chuyển sang WordPress.** https://duhoctesolhcmc.vn chạy WordPress 6.9.4 trên SQLite,
> không còn là 748 trang HTML tĩnh. Theme nằm trong repo ở `wp-content/themes/duy-study/`.
>
> - Deploy: `python tools/wp-deploy.py ~/.duhoctesol-ftp.cfg theme` → `python tools/wp-verify.py` (phải 723/723).
> - **Đừng chạy** `deploy-ftp.py`, `verify-site.py`, `live-compare.py`, `fix-meta.py`, `fix-org-schema.py`,
>   `fix-contact.py`, `add-schema.py`, `build-llms.py`, `add-ga4.py` — công cụ thời site tĩnh, chạy là đè
>   file HTML cũ lên server WordPress.
> - **Đừng chạy phase `db`** của `wp-deploy.py` nếu user đã sửa nội dung trong wp-admin — sẽ xoá sạch.
> - 748 file `index.html` tĩnh còn trong repo và trên server chỉ để làm đường lui, **không phải site nữa**.
> - **Bộ nhận diện thuộc repo này, không phải của Duy Study.** Logo `assets/img/logo.png`
>   (chữ TESOL navy + HCMC đỏ) và bảng màu `--primary:#173C8F` / `--accent:#C4302B` lấy từ logo đó.
>   Theme nguồn dùng cyan `#23a9d8` + hồng `#df1f83` — **màu của Duy Study, không được để lọt lại**.
>   Hôm 20/09 đã lọt (`logo.webp`, `og-default.jpg`, cả bảng màu) và site treo logo Duy Study 1 ngày.
> - **FTP không tự xoá file.** Xoá trong repo là chưa đủ, bản trên server vẫn được phục vụ.
>   Dùng `python tools/wp-deploy.py ~/.duhoctesol-ftp.cfg prune-brand` (danh sách ở hằng `PRUNE`).
> - **Nội dung mẫu nằm trong code theme, không phải trong CSDL.** `inc/demo-data.php` có các hàm
>   `duy_demo_*()` chèn dữ liệu cứng khi CSDL chưa có bài tương ứng. Ngày 21/09/2026 đã gỡ 7 học bổng
>   và 7 câu chuyện học sinh bịa khỏi đó. Gặp nội dung đáng ngờ trên site thì tìm ở đây trước —
>   **xoá trong wp-admin không có tác dụng** với loại này. Đừng chạy `wp-theme-sync.py` nếu không
>   chắc, nó chép đè theme từ project Duy Study và có thể làm dữ liệu mẫu sống lại.
> - Các mục bên dưới viết cho thời site tĩnh; đọc `HANDOFF.md` trước khi làm bất cứ việc gì.

Project: static Vietnamese **study-abroad** site (**748 HTML pages**, LIVE at https://duhoctesolhcmc.vn since 2026-09-18) built on the Duy Study template
(originally a WordPress export). The content is **general du học** — định hướng, chọn quốc gia,
chọn trường, ngành học, học bổng, visa và chuẩn bị trước khi lên đường. Design/CSS/images stay;
only copy changes.

> Lịch sử: site từng được viết lại thành một TESOL consultancy trên nhánh `tesol-content-rewrite`,
> sau đó **hoàn nguyên về bản du học tổng quát** (base `5eeecd0`) trong khi **giữ lại 9 trang video**
> quốc gia. Brand `Du học TESOL` + `logo.png` + màu sắc giữ nguyên như base. Các tài liệu TESOL cũ
> (plan/spec/research) được giữ trong `docs/superpowers/**` làm lưu trữ, không còn phản ánh nội dung site.

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

## Project rules (apply to all content tasks)
- **Never change** the file count (currently **748**), any URL/slug/path, CSS/JS under `wp-content/**`, images, or any `class`/`href`. Replace only text inside tags unless a task explicitly adds new pages/links.
- **Shared header/footer/meta** are byte-identical across all 748 files. Body tasks must NOT touch `<header>`, `<footer>`, `<meta>`, or JSON-LD unless a task explicitly changes shared navigation.
- **`href` exception (2026-09-20):** the rule "never change any `href`" hid a real bug for months — the footer hotline and the floating Zalo button still pointed at Duy Study's number while the visible text showed the current one. When an `href` contradicts what the page displays, raise it; don't silently keep it.
- **Brand stays:** `Du học TESOL` (header/footer/title/logo alt) is the site brand — keep it. It is a name, not a claim about specific TESOL programs.
- **9 country video pages** live at `quoc-gia/{my,uc,canada,new-zealand,tho-nhi-ky,duc,ha-lan,singapore,han-quoc}/video/`. Nav has 5 dropdown-country video links (my/uc/canada/new-zealand/tho-nhi-ky); the other 4 link their video page from the country landing body (`Xem tất cả video`).
- **Numbers are indicative** — hedge ("tham khảo", "cập nhật 2026"); use REAL program/school names.
- **Sub-agent isolation:** when delegating, the implementer must do the work itself — **do NOT spawn nested sub-agents, run in background, or sleep**. (Nested self-parallelization caused file races before.)

## Key files
- Revert plan: `docs/superpowers/plans/2026-07-09-revert-to-pre-tesol.md`
- Live status & next steps: `HANDOFF.md`
- Deploy runbook (FTP): `DEPLOY.md`
- Tools (`tools/`): `verify-site.py` (**chạy trước deploy, phải OK**) · `live-compare.py` (**chạy sau deploy**) · `deploy-ftp.py` · `add-schema.py` · `fix-meta.py` · `fix-org-schema.py` · `fix-contact.py` · `build-llms.py` · `add-ga4.py` — bảng "Công cụ" trong `HANDOFF.md`
- Archived TESOL docs (không còn phản ánh site): `docs/superpowers/plans/2026-07-03-duhoc-tesol-rewrite.md`, `docs/superpowers/specs/2026-07-02-duhoc-tesol-rewrite-design.md`, `docs/superpowers/research/2026-07-02-tesol-facts.md`
- Progress ledger: `.superpowers/sdd/progress.md` (git-ignored)

Work happens on branch **`tesol-content-rewrite`** (base `5eeecd0` on `main`).
