# HANDOFF — Du học TESOL (site du học tổng quát)

_Last updated: 2026-07-09 (REVERT to pre-TESOL content complete). Update this at every milestone (see CLAUDE.md)._

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
