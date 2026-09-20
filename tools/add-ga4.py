#!/usr/bin/env python3
"""Chèn Google Analytics 4 (gtag.js) vào <head> của toàn bộ trang.
Dùng: python tools/add-ga4.py G-XXXXXXXXXX            # dry-run
      python tools/add-ga4.py G-XXXXXXXXXX --apply    # ghi
Idempotent: đã có đúng mã thì không đổi; có mã GA4 khác thì thay khối cũ (không nhân đôi).
Chèn ngay trước </head> để không đụng <title>/<meta>/canonical/JSON-LD và không đụng <header>/<footer> (md5 giữ nguyên).
Đúng đoạn mã Google phát cho "Cài đặt thủ công" trong GA4 → Luồng dữ liệu → Hướng dẫn gắn thẻ."""
import glob, os, re, sys

try:
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
except Exception:
    pass

TGT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MARK_START, MARK_END = "<!-- Google tag (gtag.js) -->", "<!-- End Google tag -->"
BLOCK_RE = re.compile(re.escape(MARK_START) + r".*?" + re.escape(MARK_END) + r"\n?", re.S)


def snippet(mid):
    return (f"{MARK_START}\n"
            f'<script async src="https://www.googletagmanager.com/gtag/js?id={mid}"></script>\n'
            "<script>\n"
            "  window.dataLayer = window.dataLayer || [];\n"
            "  function gtag(){dataLayer.push(arguments);}\n"
            "  gtag('js', new Date());\n"
            f"  gtag('config', '{mid}');\n"
            "</script>\n"
            f"{MARK_END}\n")


def main(argv):
    ids = [a for a in argv if a.startswith("G-")]
    if len(ids) != 1 or not re.fullmatch(r"G-[A-Z0-9]{6,12}", ids[0]):
        print(__doc__); return 2
    mid, apply = ids[0], "--apply" in argv
    snip = snippet(mid)
    files = sorted(f.replace("\\", "/") for f in glob.glob(os.path.join(TGT, "**", "index.html"), recursive=True)
                   if not re.search(r"[\\/](\.git|docs|scratchpad|\.superpowers)[\\/]", f))
    added = replaced = same = 0
    for path in files:
        with open(path, encoding="utf-8", newline="") as f:
            page = f.read()
        if snip in page:
            same += 1; continue
        if BLOCK_RE.search(page):
            new = BLOCK_RE.sub(lambda m: snip, page, count=1); replaced += 1
        else:
            assert page.count("</head>") == 1, path
            new = page.replace("</head>", snip + "</head>", 1); added += 1
        if apply:
            with open(path, "w", encoding="utf-8", newline="") as f:
                f.write(new)
    print(("APPLIED" if apply else "DRY-RUN") + f" {mid}: added {added} · replaced {replaced} · already ok {same} · total {len(files)}")
    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
