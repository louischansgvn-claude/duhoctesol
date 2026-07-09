# HANDOFF — Du học TESOL (site du học tổng quát)

_Last updated: 2026-07-09 (cleaned remaining tuition placeholders on school pages). Update this at every milestone (see CLAUDE.md)._

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
A **171-page static Vietnamese study-abroad site** on the Duy Study template. Content is **general du học**
(định hướng, chọn quốc gia/trường/ngành, học bổng, visa, chuẩn bị lên đường). Keep design/CSS/images/URLs;
rewrite only copy except explicit page/link tasks.

The site was briefly rewritten into a **TESOL consultancy**, then **reverted to the general study-abroad copy**
(base `5eeecd0`) while **keeping the 9 country video pages**. Brand `Du học TESOL`, `logo.png` and colors are
unchanged (they already existed in base). Old TESOL plan/spec/research under `docs/superpowers/**` are kept as
**archive only** — they no longer describe the live content.

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
  "practicum"/"giáo viên tiếng Anh" business content; only the brand `Du học TESOL` remains (title/header/footer).

## FIRST THING ON RESUME — sanity check
```bash
cd "/Users/louis/Library/CloudStorage/Dropbox/Tintt/claude code/duhoctesol"
git log --oneline | head -5
git status --short                                   # should be clean
find . -name index.html -not -path './.git/*' | wc -l   # == 171
git diff 5eeecd0 -- wp-content | wc -l                # == 0
# every non-brand TESOL hit should be empty:
grep -rniE 'TESOL' --include=index.html . | grep -viE 'Du học TESOL|duhoctesol' | grep -v 'IELTS'
```

## Global invariants to re-check before merge
- `find . -name index.html -not -path './.git/*' | wc -l` == 171
- `git diff 5eeecd0 -- wp-content | wc -l` == 0 (design untouched)
- header/footer still 1 distinct md5 hash each site-wide
- no standalone `TESOL` (non-brand) in any `index.html` body
- every `/quoc-gia/<slug>/video/` link resolves to an existing file

## 🚀 Deploy — runbook đầy đủ trong `DEPLOY.md`
Khi user nói **"deploy"**: mở `DEPLOY.md`, **xin user gửi password FTP** (không lưu sẵn), rồi chạy runbook
(curl, 1 phiên, ~30s). Tóm tắt: host `pbf43-22360.azdigihost.com` (port 21 plain FTP),
user `uploadtesolhcm@duhoctesol.duystudy.vn`, docroot = FTP `/`; **ghi đè, không xoá**
(giữ `.htaccess`/`.well-known`/`cgi-bin`/`wp-content`); chỉ upload `**/index.html`.
Nhắc user đổi pass FTP sau mỗi lần gửi qua chat.

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
