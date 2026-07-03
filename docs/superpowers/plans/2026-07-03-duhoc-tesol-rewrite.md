# Du học TESOL — Site Rewrite Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rewrite 100% of the copy across all 162 static HTML pages so the site is a genuine TESOL-focused study-abroad consultancy, without changing any URL, template, CSS, or image.

**Architecture:** Keep the entire URL/nav topology and page templates. Task 1 regenerates the byte-identical shared header/footer once and applies it to all 162 files by script. Tasks 2–9 rewrite page-body copy cluster by cluster (each cluster = one dispatchable unit), reading each existing file and replacing only the text inside tags. Task 10 is a final verification sweep.

**Tech Stack:** Static HTML (WordPress export), plain CSS (`wp-content/themes/duy-study/assets/css/main.css` — DO NOT edit), Python3 for bulk find/replace, git.

## Global Constraints

Copied verbatim from the design spec — every task must honor these:

- **File count stays exactly 162.** Never create, delete, move, or rename a page or change any path/slug. Verify with `find . -name index.html -not -path './.git/*' | wc -l` → `162`.
- **Do NOT edit** `wp-content/**` CSS/JS/images. Design and assets stay as-is.
- **Language: Vietnamese.** Keep the existing tone and design classes; replace only text inside tags.
- **Keep** phone `0906.510.747` and the 3 offices (VP TP.HCM — BV Bank 412 Nguyễn Thị Minh Khai; VP Đà Nẵng — Hilton 50 Bạch Đằng; VP Buôn Ma Thuột — 198 Nguyễn Thị Minh Khai) exactly as they appear.
- **All numbers are indicative** — hedge ("tham khảo", "cập nhật theo năm/[2026]"); never state a figure as a fixed quote. Use real program/school names from the research reference.
- **Accuracy guardrails:** Germany & Netherlands = academic linguistics (English-medium, low cost), NOT a practical teaching licence — say so. Do NOT advertise US STEM 24-month OPT for TESOL (only 12 months). Note Sydney M.Ed TESOL / USC MAT-TESOL may pause intake.
- **Source of facts:** [research/2026-07-02-tesol-facts.md](../research/2026-07-02-tesol-facts.md). **Design:** [specs/2026-07-02-duhoc-tesol-rewrite-design.md](../specs/2026-07-02-duhoc-tesol-rewrite-design.md). Every task's implementer MUST read both before editing.
- **Taxonomy label map** (apply consistently everywhere the labels appear — nav, body, footer, breadcrumbs):
  - Bậc học levels: `thpt`→"Chứng chỉ nền tảng" · `cao-dang`→"CELTA / Diploma" · `dai-hoc`→"Cử nhân TESOL" · `sau-dai-hoc`→"Thạc sĩ TESOL". Drop-label "Bậc học"→"Bằng cấp TESOL".
  - Ngành học specializations: `tieng-anh`→"TESOL tổng quát" · `giao-duc`→"Đào tạo giáo viên" · `kinh-doanh`→"Business English" · `cntt`→"Dạy tiếng Anh trực tuyến" · `cong-nghe`→"EdTech / Online ELT" · `kinh-te`→"English for Academic Purposes" · `ky-thuat`→"Luyện thi IELTS/TOEFL" · `suc-khoe`→"Teaching Young Learners" · `y-suc-khoe`→"Curriculum & Assessment". Nav top label "Ngành học HOT"→"Chuyên ngành TESOL".
  - Country nav labels stay unchanged ("Du học Mỹ", … "Du học các nước").

---

### Task 1: Shared header, footer & meta (touches all 162 files)

**Files:**
- Modify: every `**/index.html` (162 files) — only the `<header>…</header>` block, the `<footer>…</footer>` block, and the `<meta name="description">` / JSON-LD `description` fields.
- Create (temp, delete after): `/private/tmp/claude-501/.../scratchpad/apply_shared.py`

**Interfaces:**
- Produces: new canonical header block (byte-identical across all files) and footer block that Tasks 2–9 must treat as fixed — body tasks edit only content BETWEEN header and footer, never these blocks.

- [ ] **Step 1: Capture current shared blocks & confirm they are identical everywhere**

Run:
```bash
cd "/Users/louis/Library/CloudStorage/Dropbox/Tintt/claude code/duhoctesol"
python3 - <<'PY'
import re,hashlib,glob
def block(h,tag):
    m=re.search(rf'<{tag}.*?</{tag}>',h,flags=re.S); return m.group(0) if m else None
hs=set();fs=set()
for f in glob.glob('**/index.html',recursive=True):
    if '.git' in f: continue
    h=open(f,encoding='utf-8').read()
    hs.add(hashlib.md5(block(h,'header').encode()).hexdigest())
    fs.add(hashlib.md5(block(h,'footer').encode()).hexdigest())
print('distinct headers',len(hs),'distinct footers',len(fs))
PY
```
Expected: `distinct headers 1 distinct footers 1` (both blocks are uniform, so a single search/replace is safe).

- [ ] **Step 2: Build the new header block**

Take the current header (see design spec §3.6 / plan appendix), and apply ONLY these edits, preserving every class, href, and whitespace pattern:
- In each country dropdown, `<span class="drop-label">Bậc học</span>` → `<span class="drop-label">Bằng cấp TESOL</span>`.
- Level links text: `>THPT<`→`>Chứng chỉ nền tảng<`, `>Cao đẳng<`→`>CELTA / Diploma<`, `>Đại học<`→`>Cử nhân TESOL<`, `>Sau đại học<`→`>Thạc sĩ TESOL<` (all occurrences, all 5 country dropdowns).
- Nav top label `>Ngành học HOT <`→`>Chuyên ngành TESOL <` (keep the `<span class="caret">`).
- Majors dropdown link text: `>Kinh tế<`→`>English for Academic Purposes<`, `>Sức khoẻ<`→`>Teaching Young Learners<`, `>Công nghệ<`→`>EdTech / Online ELT<`, `>Giáo dục<`→`>Đào tạo giáo viên<`, `>Tiếng Anh<`→`>TESOL tổng quát<`.
- Leave logo, country labels, "Về Du học TESOL", "Du học các nước", "Tin tức", CTAs unchanged.

Save the resulting exact string as `NEW_HEADER` in the script (Step 4).

- [ ] **Step 3: Build the new footer block**

Read the current footer from `index.html`. Rewrite its text to TESOL framing while preserving all structural HTML, classes, and internal links:
- Replace any tagline/description copy (e.g. "công dân toàn cầu", "chọn quốc gia… visa… lên đường") with TESOL framing: e.g. "Du học TESOL đồng hành cùng học viên Việt Nam trên hành trình học chứng chỉ và bằng TESOL ở nước ngoài để trở thành giáo viên tiếng Anh chuẩn quốc tế."
- Apply the Global taxonomy label map to any level/major link labels in the footer.
- Keep the 3 offices, phone, and all `href`s exactly.
Save as `NEW_FOOTER`.

- [ ] **Step 4: Write and run the bulk-apply script**

Create `scratchpad/apply_shared.py` that, for every non-.git `**/index.html`: regex-replaces the existing `<header…</header>` with `NEW_HEADER`, the existing `<footer…</footer>` with `NEW_FOOTER`, and updates the `<meta name="description" content="…">` plus JSON-LD `"description":"…"` to the TESOL sentence above. Then run it.

Run: `python3 scratchpad/apply_shared.py && echo done`
Expected: `done`, script prints `162 files updated`.

- [ ] **Step 5: Verify uniformity, labels, and file count**

Run:
```bash
cd "/Users/louis/Library/CloudStorage/Dropbox/Tintt/claude code/duhoctesol"
echo "files: $(find . -name index.html -not -path './.git/*' | wc -l)"
python3 - <<'PY'
import re,hashlib,glob
hs=set();fs=set();bad=[]
for f in glob.glob('**/index.html',recursive=True):
    if '.git' in f: continue
    h=open(f,encoding='utf-8').read()
    hs.add(hashlib.md5(re.search(r'<header.*?</header>',h,re.S).group().encode()).hexdigest())
    fs.add(hashlib.md5(re.search(r'<footer.*?</footer>',h,re.S).group().encode()).hexdigest())
    if 'Bậc học</span>' in h or '>THPT<' in h: bad.append(f)
print('distinct headers',len(hs),'distinct footers',len(fs),'stale-label files',len(bad))
PY
grep -rl "Ngành học HOT" --include=index.html . | wc -l
```
Expected: `files: 162`, `distinct headers 1 distinct footers 1 stale-label files 0`, and `0` files still containing "Ngành học HOT".

- [ ] **Step 6: Visual spot-check**

Open `index.html` and `quoc-gia/my/index.html` in a browser (or read the header block). Confirm nav shows "Bằng cấp TESOL", the 4 new level labels, "Chuyên ngành TESOL", and the 5 new specialization labels; layout unchanged.

- [ ] **Step 7: Commit**

```bash
rm -f scratchpad/apply_shared.py
git add -A && git commit -m "TESOL rewrite: shared header, footer, meta across all pages"
```

---

### Task 2: Homepage (`index.html`)

**Files:** Modify: `index.html` (body content only — everything between the new `<header>` and `<footer>`).

**Interfaces:** Consumes Task 1's header/footer (do not touch them). Produces the homepage narrative other pages echo.

- [ ] **Step 1: Read** `index.html`, the design spec, and the research reference.
- [ ] **Step 2: Rewrite each homepage section to TESOL** (headings enumerated in design spec; keep all classes/section structure):
  - Hero H1 "Trở thành công dân toàn cầu…" → e.g. "Học TESOL — Trở thành giáo viên tiếng Anh chuẩn quốc tế"; sub-copy about the TESOL journey (chọn bậc bằng cấp → chọn nước/trường → IELTS → hồ sơ → visa). Hero stat chips → TESOL framing (e.g. "9 điểm đến TESOL", "MA TESOL & Applied Linguistics", "24h phản hồi", "1:1 theo hồ sơ").
  - "Quốc gia du học" section: keep 9 country cards, rewrite each blurb to its TESOL angle (research §4) — incl. honest Germany/Netherlands academic-linguistics note.
  - "Lộ trình" section → the TESOL roadmap.
  - "Trường và học bổng đang được quan tâm" → real TESOL programs/scholarships (e.g. Melbourne Master of TESOL, Auckland MTESOL; Fulbright/Chevening).
  - News/events/testimonials teasers → TESOL topics (match Tasks 7–8 titles).
- [ ] **Step 3: Verify** no stale generic terms remain in the body:
  Run: `grep -oE "công dân toàn cầu|ngành Kinh tế|Sức khoẻ|1\.200\+ trường" index.html | sort -u` → Expected: empty (or only inside allowed TESOL context). Also `find . -name index.html | wc -l` unchanged at 162.
- [ ] **Step 4: Commit** `git add index.html && git commit -m "TESOL rewrite: homepage"`.

---

### Task 3: Countries cluster (`quoc-gia/` 46 + `quoc-gia-filter/` 5 = 51 files)

**Files:** Modify all `quoc-gia/**/index.html` and `quoc-gia-filter/**/index.html` (body only).

**Interfaces:** Consumes Task 1 header/footer + Global label map. Uses research §4 per-country facts.

- [ ] **Step 1: Read** design spec §3.3, research §4, and one sample each: `quoc-gia/my/index.html`, `quoc-gia/my/dai-hoc/index.html`, `quoc-gia-filter/my/index.html`.
- [ ] **Step 2: For each of the 9 country landing pages** (`my, uc, canada, new-zealand, tho-nhi-ky, singapore, han-quoc, duc, ha-lan`): rewrite title/H1/H2 to "Du học TESOL tại [nước]" and every section (schools list, "lý do chọn", bậc học, ngành, học bổng, cẩm nang, video) to that country's real TESOL programs, tuition, and post-study visa from research §4. Apply the level/major label map. Honor Germany/Netherlands & US-OPT guardrails.
- [ ] **Step 3: For each country's 4 level subpages** (`.../thpt|cao-dang|dai-hoc|sau-dai-hoc/`): rewrite around the mapped TESOL qualification (Chứng chỉ nền tảng / CELTA-Diploma / Cử nhân / Thạc sĩ) as offered/relevant in that country.
- [ ] **Step 4: For the 5 `quoc-gia-filter/` pages:** update filter/landing copy to TESOL.
- [ ] **Step 5: Verify** stale terms cleared across the cluster:
  Run: `grep -rlE "THPT|Cao đẳng|Đại học|Sau đại học|công dân toàn cầu" quoc-gia quoc-gia-filter --include=index.html | head` — inspect any hits; only allowed if part of legitimate TESOL copy (e.g. "hoàn tất bậc đại học"), otherwise fix. Confirm each of the 9 landing H1s now reads "Du học TESOL tại …".
- [ ] **Step 6: Commit** `git add quoc-gia quoc-gia-filter && git commit -m "TESOL rewrite: countries + filters"`.

---

### Task 4: Levels + Majors (`bac-hoc/` 2 + `nganh-hoc/` 10 = 12 files)

**Files:** Modify `bac-hoc/**/index.html`, `nganh-hoc/**/index.html` (body only).

**Interfaces:** Uses research §2 (qualification ladder), §3 (entry reqs), §5 (specializations), §6 (careers).

- [ ] **Step 1: Read** design spec §3.1–3.2, research §2/§5, and samples `bac-hoc/dai-hoc/index.html`, `nganh-hoc/tieng-anh/index.html`, `nganh-hoc/index.html`.
- [ ] **Step 2: Rewrite `bac-hoc/cao-dang` and `bac-hoc/dai-hoc`** into TESOL qualification-tier pages per the level map (note: `bac-hoc/dai-hoc` currently renders a news index — rewrite its actual content to the "Cử nhân TESOL / bằng cấp" theme, keeping its template).
- [ ] **Step 3: Rewrite the `nganh-hoc/` landing + 9 specialization pages** (`tieng-anh, giao-duc, kinh-doanh, cntt, cong-nghe, kinh-te, ky-thuat, suc-khoe, y-suc-khoe`) each to its mapped TESOL specialization (label map): what it is, why study it, career outcomes (research §6), featured programs/schools.
- [ ] **Step 4: Verify:** each `nganh-hoc/*` H1 matches its new specialization label; no "ngành Kinh tế/Sức khoẻ/Kỹ thuật" in the old generic sense remains. Run `grep -roE "<h1[^>]*>[^<]+" nganh-hoc --include=index.html`.
- [ ] **Step 5: Commit** `git add bac-hoc nganh-hoc && git commit -m "TESOL rewrite: qualification levels + specializations"`.

---

### Task 5: Schools (`truong/` 33 files)

**Files:** Modify `truong/**/index.html` (body only).

**Interfaces:** Uses research §4 (which real universities offer MA TESOL/Applied Linguistics).

- [ ] **Step 1: Read** design spec §3.4, research §4, and sample `truong/melbourne/index.html`, `truong/the-university-of-new-south-wales/index.html`.
- [ ] **Step 2: For each of the 33 school pages**, reframe around that university's TESOL/Applied Linguistics offering: program name, level, IELTS entry, tuition (tham khảo), career service for English teaching. For schools with a verified TESOL program in research §4 (Melbourne, Monash, Sydney, UQ, Macquarie, Auckland, Waikato, Massey, Otago, Toronto, UBC, McGill, Columbia-adjacent US schools, etc.) use those specifics. For schools with no clear TESOL program, frame around "Ngôn ngữ Anh / Applied Linguistics" honestly, or note "chương trình đang cập nhật" rather than inventing.
- [ ] **Step 3: Verify:** no page advertises US STEM 24-month OPT for TESOL; Sydney page notes possible intake pause. Run `grep -rl "STEM" truong --include=index.html` and inspect each hit.
- [ ] **Step 4: Commit** `git add truong && git commit -m "TESOL rewrite: partner schools"`.

---

### Task 6: Scholarships (`hoc-bong/` 8 + `gia-tri-hoc-bong/` 3 = 11 files)

**Files:** Modify `hoc-bong/**/index.html`, `gia-tri-hoc-bong/**/index.html` (body only).

**Interfaces:** Uses research §8 (scholarships table).

- [ ] **Step 1: Read** design spec §3.5, research §8, sample `hoc-bong/index.html`, `hoc-bong/principal-50/index.html`, `gia-tri-hoc-bong/toan-phan/index.html`.
- [ ] **Step 2: Rewrite the `hoc-bong/` landing + 7 scholarship detail pages** to real TESOL/education scholarships (Fulbright — names TESOL; Chevening; Australia Awards; GREAT; Ireland Fellows; university merit awards). Reuse existing slugs; relabel the old fictional names (Future Leaders, STEM Scholarship, Turkiye Pathway, Early Bird, Global Excellence, International Merit, Principal 50) to fit TESOL scholarship profiles. Keep the Chevening priority-area & Fulbright funding-cycle caveats.
- [ ] **Step 3: Rewrite `gia-tri-hoc-bong/25|50|toan-phan`** value-tier pages with TESOL scholarship examples.
- [ ] **Step 4: Verify:** `grep -roE "<h1[^>]*>[^<]+" hoc-bong gia-tri-hoc-bong --include=index.html` — titles reflect real/TESOL scholarships.
- [ ] **Step 5: Commit** `git add hoc-bong gia-tri-hoc-bong && git commit -m "TESOL rewrite: scholarships"`.

---

### Task 7: News (`tin-tuc/` 11 + topical root pages ~10 = ~21 files)

**Files:** Modify `tin-tuc/**/index.html` and the duplicated root topical pages: `us-living-cost, us-culture, us-it-major, us-health-insurance, parent-budget, visa-500-2026, canada-pgwp, australia-scholarship, deadline-sep, scholarship-tips, tai-cam-nang` (each has a root copy and a `tin-tuc/` copy — keep the pair consistent).

**Interfaces:** Uses research §3/§4/§6/§7/§8.

- [ ] **Step 1: Read** design spec §3.5, sample `tin-tuc/index.html`, `tin-tuc/us-living-cost/index.html`, and its root twin `us-living-cost/index.html`.
- [ ] **Step 2: Rewrite the `tin-tuc/` landing** (article list + featured) to TESOL article titles.
- [ ] **Step 3: Rewrite each topical article** to a TESOL subject, keeping the slug's rough theme where sensible:
  - `us-it-major`→"MA TESOL ở Mỹ: chương trình & OPT 12 tháng"; `us-living-cost`→"Chi phí học TESOL tại Mỹ"; `us-health-insurance`→"Bảo hiểm cho du học sinh TESOL tại Mỹ"; `us-culture`→"Trải nghiệm immersion khi học TESOL tại Mỹ"; `canada-pgwp`→"PGWP Canada cho tốt nghiệp MA TESOL (tới 3 năm)"; `australia-scholarship`→"Học bổng & visa 485 cho MA TESOL tại Úc"; `visa-500-2026`→"Visa du học & lộ trình cho SV TESOL"; `deadline-sep`→"Deadline nộp hồ sơ MA TESOL kỳ tháng 9"; `scholarship-tips`→"Săn học bổng TESOL: Fulbright, Chevening, Australia Awards"; `parent-budget`→"Phụ huynh dự trù ngân sách du học TESOL"; `tai-cam-nang`→cẩm nang TESOL landing.
  - Keep each root page and its `tin-tuc/` twin in sync.
- [ ] **Step 4: Verify:** `grep -rlE "ngành công nghệ thông tin|IT tại Mỹ" . --include=index.html` returns none in the generic sense; article H1s are TESOL.
- [ ] **Step 5: Commit** `git add tin-tuc us-* parent-budget visa-500-2026 canada-pgwp australia-scholarship deadline-sep scholarship-tips tai-cam-nang && git commit -m "TESOL rewrite: news & guides"`.

---

### Task 8: Events + Students (`su-kien/` 9 + `loai-su-kien/` 3 + `hoc-sinh/` 8 = 20 files)

**Files:** Modify `su-kien/**`, `loai-su-kien/**`, `hoc-sinh/**` index.html (body only).

**Interfaces:** Uses research §2/§6/§7.

- [ ] **Step 1: Read** design spec §3.5, samples `su-kien/index.html`, `su-kien/essay-workshop/index.html`, `hoc-sinh/index.html`, `hoc-sinh/thao-vy/index.html`.
- [ ] **Step 2: Rewrite the 8 event detail pages + `su-kien/` landing + 3 `loai-su-kien/` category pages** to TESOL events: CELTA/IELTS workshops, "Hội thảo MA TESOL tại Anh/Úc", "Ngày hội trường TESOL New Zealand", "Định hướng nghề giáo viên tiếng Anh", SOP/essay workshop for MA TESOL applications, pre-departure for TESOL students.
- [ ] **Step 3: Rewrite the 7 student profiles + `hoc-sinh/` landing** as TESOL learner stories: cựu học viên nay dạy tiếng Anh / là trainer / đang học MA TESOL abroad — adjust their program, country, and quotes to TESOL. Keep names/photos.
- [ ] **Step 4: Verify:** `grep -roE "<h1[^>]*>[^<]+" su-kien hoc-sinh loai-su-kien --include=index.html` — all TESOL-themed.
- [ ] **Step 5: Commit** `git add su-kien loai-su-kien hoc-sinh && git commit -m "TESOL rewrite: events & student stories"`.

---

### Task 9: Roadmap, categories & static pages (`lo-trinh-du-hoc/` 6 + `category/` 3 + `ve-chung-toi, dich-vu, lien-he, tai-cam-nang` = ~13 files)

**Files:** Modify `lo-trinh-du-hoc/**`, `category/**`, `ve-chung-toi/`, `dich-vu/`, `lien-he/` index.html. (`tai-cam-nang` already handled in Task 7 — skip if done.)

**Interfaces:** Uses research §9 (roadmap), §1/§6 (about/services framing).

- [ ] **Step 1: Read** design spec §3.5, samples `lo-trinh-du-hoc/index.html`, `lo-trinh-du-hoc/chuan-bi-ho-so-du-hoc/index.html`, `ve-chung-toi/index.html`, `dich-vu/index.html`, `lien-he/index.html`.
- [ ] **Step 2: Rewrite `lo-trinh-du-hoc/` landing + 5 step pages** to the TESOL roadmap (research §9): vì sao chọn TESOL → lên kế hoạch & chọn bậc bằng cấp → chuẩn bị hồ sơ (IELTS 6.5–7.0, SOP, thư GT, pre/in-service) → sau khi nhận thư mời → trước khi lên đường.
- [ ] **Step 3: Rewrite `category/kinh-nghiem|visa|hoc-bong`** descriptions to TESOL.
- [ ] **Step 4: Rewrite `ve-chung-toi`** (đơn vị tư vấn du học TESOL — sứ mệnh, đội ngũ, vì sao TESOL cho VN using research §7 demand), **`dich-vu`** (tư vấn chọn bậc/nước/trường, luyện IELTS, hồ sơ SOP, học bổng, visa), **`lien-he`** (keep form/offices; update intro & the country/interest dropdowns copy to TESOL — keep field structure).
- [ ] **Step 5: Verify:** `grep -rl "công dân toàn cầu\|du học tổng quát" lo-trinh-du-hoc category ve-chung-toi dich-vu lien-he --include=index.html` returns none.
- [ ] **Step 6: Commit** `git add lo-trinh-du-hoc category ve-chung-toi dich-vu lien-he && git commit -m "TESOL rewrite: roadmap, categories, static pages"`.

---

### Task 10: Final verification sweep

**Files:** none modified unless fixes needed.

- [ ] **Step 1: File count & integrity**
  Run: `find . -name index.html -not -path './.git/*' | wc -l` → `162`. `git status` clean after Task 9 commit.
- [ ] **Step 2: Stale-content sweep across the whole site**
  Run:
```bash
cd "/Users/louis/Library/CloudStorage/Dropbox/Tintt/claude code/duhoctesol"
grep -rlE "công dân toàn cầu|ngành Kinh tế|ngành Sức khoẻ|ngành Kỹ thuật|Bậc học</span>|>THPT<|1\.200\+ trường" --include=index.html . | sort
```
  Expected: empty. Investigate & fix any file listed (dispatch back to the owning task).
- [ ] **Step 3: Link integrity** — confirm no href/slug changed:
  Run: `git diff d09d561 --stat | grep -E "rename|=> " || echo "no renames"` → `no renames`; and spot-check that internal links still resolve to existing directories.
- [ ] **Step 4: Accuracy guardrail check**
  Run: `grep -rn "STEM" --include=index.html . ; grep -rln "Đức\|Hà Lan" quoc-gia/duc quoc-gia/ha-lan --include=index.html` — confirm US-OPT and Germany/Netherlands guardrail wording present; no false STEM-OPT-for-TESOL claim.
- [ ] **Step 5: Visual pass** — open `index.html`, one country page, one school page, one article, and `lien-he/` in a browser; confirm design intact and copy is TESOL throughout.
- [ ] **Step 6: Final commit (if any fixes)** `git add -A && git commit -m "TESOL rewrite: final verification fixes"`.

---

## Self-Review notes
- **Spec coverage:** every design-spec section (§3.1 levels → T4; §3.2 majors → T4; §3.3 countries → T3; §3.4 schools → T5; §3.5 scholarships/news/events/students/roadmap/static → T6–T9; §3.6 shared header/footer/meta → T1; homepage → T2) maps to a task. Final sweep = T10.
- **File count invariant** enforced in Global Constraints + T1S5 + T10S1.
- **Accuracy guardrails** (Germany/NL, US-OPT, Sydney/USC) repeated in Global Constraints and checked in T5/T10.
- No placeholders: label maps and per-slug rewrites are given explicitly; subagents read each file for exact structure.
```
