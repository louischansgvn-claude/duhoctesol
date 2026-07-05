# HANDOFF — Du học TESOL rewrite

_Last updated: 2026-07-05 (end of session). Update this at every milestone (see CLAUDE.md)._

## What this project is
Rewriting a 162-page static Vietnamese study-abroad site into a **TESOL-focused consultancy**. Keep design/CSS/images/URLs; rewrite only copy. Full context: `CLAUDE.md`, plan `docs/superpowers/plans/2026-07-03-duhoc-tesol-rewrite.md`, spec `docs/superpowers/specs/2026-07-02-duhoc-tesol-rewrite-design.md`, facts `docs/superpowers/research/2026-07-02-tesol-facts.md`.

## Branch & commits
- Branch: **`tesol-content-rewrite`** (base `5eeecd0` on `main`). Not merged yet.
- Commits so far:
  - `c8634c5` Task 1 — shared header/footer/meta across all 162 files
  - `93cc8f5` Task 2 — homepage
  - `db31568` Task 3a — 9 country landings + quoc-gia landing + 5 filter pages (15 files)
  - `98c8f18` Task 3b — 20 level subpages (my/uc/canada/new-zealand/tho-nhi-ky)
  - `9cd41c3` Task 3c — 16 level subpages (singapore/han-quoc/duc/ha-lan). **Task 3 COMPLETE + verified** (51-file countries cluster clean, header/footer 1 hash each, 162 files, guardrails present).

## ⚠️ FIRST THING ON RESUME — start Task 4
Task 3 is done and verified. Begin at **Task 4** (nganh-hoc + bac-hoc). Quick sanity check first:
```bash
cd "/Users/louis/Library/CloudStorage/Dropbox/Tintt/claude code/duhoctesol"
git log --oneline | head -6          # top should be 9cd41c3 (Task 3c)
git status --short                    # should be clean
find . -name index.html -not -path './.git/*' | wc -l   # == 162
```

## Task status
- [x] Task 1 — shared header/footer/meta (`c8634c5`)
- [x] Task 2 — homepage (`93cc8f5`)
- [x] Task 3a — country landings + filters (`db31568`)
- [x] Task 3b — level subpages batch 1 (`98c8f18`)
- [x] Task 3c — level subpages batch 2 (`9cd41c3`) — **Task 3 COMPLETE**
- [ ] **Task 4 (START HERE)** — Levels + Majors: `bac-hoc/` (2) + `nganh-hoc/` (10)
- [ ] Task 5 — Schools: `truong/` (33)
- [ ] Task 6 — Scholarships: `hoc-bong/` (8) + `gia-tri-hoc-bong/` (3)
- [ ] Task 7 — News + guides: `tin-tuc/` (11) + topical root pages (~10)
- [ ] Task 8 — Events + Students: `su-kien/` (9) + `loai-su-kien/` (3) + `hoc-sinh/` (8)
- [ ] Task 9 — Roadmap + categories + static: `lo-trinh-du-hoc/` (6) + `category/` (3) + `ve-chung-toi/ dich-vu/ lien-he/ tai-cam-nang/`
- [ ] Task 10 — final verification sweep (see plan)

Each remaining task's full brief + verification commands are in the plan file, sections "Task 4"…"Task 10".

## Execution method going forward (per new mandatory workflow)
Follow the **Claude ↔ Codex loop in `CLAUDE.md`**: Claude hands Codex a task prompt (each prompt MUST remind Codex to **bật "goal" mode**); Codex reviews the plan, implements with goal on, and returns **output + the prompt it used**; Claude reviews against the constraints; both update this HANDOFF at each milestone.

### Task prompt pattern (Claude → Codex)
For each task, give Codex: (1) "**Bật goal mode trước khi làm**"; (2) the exact file list; (3) "read `docs/superpowers/research/2026-07-02-tesol-facts.md` + the plan's Task-N section + `CLAUDE.md` Project rules"; (4) hard rules — body only, don't touch header/footer/meta/CSS/href/slug, file count stays 162, hedge numbers, honor accuracy guardrails; (5) "**do the work yourself — do NOT spawn sub-agents, background, or sleep**" (this prevented the Task-3 file races); (6) verification greps from the plan; (7) commit message; (8) "return your output **and** the prompt/goal you used."

## Hard-won learnings
- **General-purpose implementer agents self-parallelize** (spawn nested sub-agents) and cause file races / reverted edits. ALWAYS include: "do the work yourself; no sub-agents, no background, no sleep." Task 3a had to be recovered from this exact failure.
- **Big clusters → split** into ~15–20 file sub-batches per implementer to stay within context and keep quality.
- **Verify from committed state**, not `git diff --quiet` on a clean tree (that always reports "no diff" and misled me once). Use tag-stripping H1 extraction (H1s contain a nested `<span>` eyebrow, so naive `grep` returns empty).
- Header/footer are byte-identical site-wide — verify uniformity with an md5-of-block check across files.

## Known cleanup for Task 10
- **~28 pages still have old-style `<head>` `<title>`** (e.g. "Du học Mỹ bậc Đại học — Du học TESOL") on the level subpages. Bodies are correct; only the `<title>` tag lags. Normalize these to TESOL tier naming during the final sweep: `grep -rlE "<title>Du học (Mỹ|Úc|Canada|New Zealand|Thổ|Singapore|Hàn|Đức|Hà Lan) bậc" --include=index.html .`

## Global invariants to re-check before merge (Task 10)
- `find . -name index.html -not -path './.git/*' | wc -l` == 162
- No stale terms anywhere: `grep -rlE "công dân toàn cầu|1\.200\+ trường|ngành Kinh tế|>THPT<|Bậc học</span>" --include=index.html .`
- header/footer still 1 distinct hash each site-wide
- No STEM-OPT-for-TESOL claim; Đức/Hà Lan academic-linguistics caveat present.
