# CLAUDE.md — Du học TESOL

Project: static Vietnamese study-abroad site (171 HTML pages after Task V video-page expansion, originally a WordPress export) being rewritten into a **TESOL-focused consultancy** (giúp học viên VN đi học chứng chỉ/bằng TESOL ở nước ngoài để thành giáo viên tiếng Anh). Design/CSS/images stay; only copy changes.

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
- **Never change** the file count (stays **171** after Task V), any URL/slug/path, CSS/JS under `wp-content/**`, images, or any `class`/`href`. Replace only text inside tags unless a task explicitly adds new pages/links.
- **Shared header/footer/meta** are byte-identical across all 171 files after Task V. Body tasks must NOT touch `<header>`, `<footer>`, `<meta>`, or JSON-LD unless a task explicitly changes shared navigation.
- **Numbers are indicative** — hedge ("tham khảo", "cập nhật 2026"); use REAL program/school names from the research reference.
- **Accuracy guardrails:** Đức & Hà Lan = academic linguistics (English-medium, low cost), NOT a practical teaching licence — say so. Do NOT claim US STEM 24-month OPT for TESOL (only 12 months). Note Sydney M.Ed TESOL / USC MAT-TESOL may pause intake. Turkey Bilkent = MA **TEFL** (not TESOL); "NileTESOL" is Egypt, not Turkey.
- **Sub-agent isolation:** when delegating, the implementer must do the work itself — **do NOT spawn nested sub-agents, run in background, or sleep**. (Nested self-parallelization caused file races in Task 3.)

## Key files
- Plan: `docs/superpowers/plans/2026-07-03-duhoc-tesol-rewrite.md`
- Design spec: `docs/superpowers/specs/2026-07-02-duhoc-tesol-rewrite-design.md`
- Research (copy source, source-cited): `docs/superpowers/research/2026-07-02-tesol-facts.md`
- Live status & next steps: `HANDOFF.md`
- Progress ledger: `.superpowers/sdd/progress.md` (git-ignored)

Work happens on branch **`tesol-content-rewrite`** (base `5eeecd0` on `main`).
