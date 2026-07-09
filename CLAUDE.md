# CLAUDE.md — Du học TESOL (site du học tổng quát)

Project: static Vietnamese **study-abroad** site (171 HTML pages) built on the Duy Study template
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
- **Never change** the file count (stays **171**), any URL/slug/path, CSS/JS under `wp-content/**`, images, or any `class`/`href`. Replace only text inside tags unless a task explicitly adds new pages/links.
- **Shared header/footer/meta** are byte-identical across all 171 files. Body tasks must NOT touch `<header>`, `<footer>`, `<meta>`, or JSON-LD unless a task explicitly changes shared navigation.
- **Brand stays:** `Du học TESOL` (header/footer/title/logo alt) is the site brand — keep it. It is a name, not a claim about specific TESOL programs.
- **9 country video pages** live at `quoc-gia/{my,uc,canada,new-zealand,tho-nhi-ky,duc,ha-lan,singapore,han-quoc}/video/`. Nav has 5 dropdown-country video links (my/uc/canada/new-zealand/tho-nhi-ky); the other 4 link their video page from the country landing body (`Xem tất cả video`).
- **Numbers are indicative** — hedge ("tham khảo", "cập nhật 2026"); use REAL program/school names.
- **Sub-agent isolation:** when delegating, the implementer must do the work itself — **do NOT spawn nested sub-agents, run in background, or sleep**. (Nested self-parallelization caused file races before.)

## Key files
- Revert plan: `docs/superpowers/plans/2026-07-09-revert-to-pre-tesol.md`
- Live status & next steps: `HANDOFF.md`
- Deploy runbook (FTP): `DEPLOY.md`
- Archived TESOL docs (không còn phản ánh site): `docs/superpowers/plans/2026-07-03-duhoc-tesol-rewrite.md`, `docs/superpowers/specs/2026-07-02-duhoc-tesol-rewrite-design.md`, `docs/superpowers/research/2026-07-02-tesol-facts.md`
- Progress ledger: `.superpowers/sdd/progress.md` (git-ignored)

Work happens on branch **`tesol-content-rewrite`** (base `5eeecd0` on `main`).
