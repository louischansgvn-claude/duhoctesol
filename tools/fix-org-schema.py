#!/usr/bin/env python3
"""Đặt 1 câu mô tả cố định cho tổ chức trong graph JSON-LD site-wide (khối <script type="application/ld+json">
đầu tiên, KHÔNG phải khối id="page-schema") trên toàn bộ trang.

Vì sao: WP export copy meta description của từng trang vào `EducationalOrganization.description` và
`WebSite.description`, nên 607/748 trang mô tả tổ chức lệch (trang Anh mang câu của Đức, trang trường mang
câu của trường...). Organization/WebSite là 1 thực thể duy nhất → phải cùng 1 câu.

Dry-run mặc định, `--apply` để ghi. Idempotent. Không đụng gì khác trong graph (round-trip JSON được kiểm
byte-for-byte trước khi ghi: nếu chỉ serialize lại mà đã khác bản gốc thì trang đó bị BỎ QUA và báo)."""
import glob, json, os, re, sys

try:
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
except Exception:
    pass

TGT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
ORG_DESC = ("Ban Du học Hội TESOL TP.HCM đồng hành cùng học sinh và phụ huynh trong lộ trình du học: "
            "chọn quốc gia, chọn trường, học bổng, visa và chuẩn bị lên đường.")
PAT = re.compile(r'(<script type="application/ld\+json">)(.*?)(</script>)', re.S)


def read(p):
    with open(p, encoding="utf-8", newline="") as f:
        return f.read()


def dumps(obj):
    return json.dumps(obj, ensure_ascii=False, separators=(",", ":"))


def main(argv):
    apply = "--apply" in argv
    files = sorted(f.replace("\\", "/") for f in glob.glob(os.path.join(TGT, "**", "index.html"), recursive=True)
                   if not re.search(r"[\\/](\.git|docs|scratchpad|\.superpowers)[\\/]", f))
    changed = same = skipped = 0
    for path in files:
        page = read(path)
        m = PAT.search(page)
        if not m:
            print("  !! no site-wide ld+json:", path); skipped += 1; continue
        raw = m.group(2)
        data = json.loads(raw)
        if dumps(data) != raw:                      # must round-trip exactly before we touch it
            print("  !! JSON does not round-trip byte-exact, skipped:", os.path.relpath(path, TGT)); skipped += 1; continue
        touched = False
        for node in data.get("@graph", []):
            if node.get("@type") in ("EducationalOrganization", "WebSite") and node.get("description") != ORG_DESC:
                node["description"] = ORG_DESC
                touched = True
        if not touched:
            same += 1; continue
        new = page[:m.start(2)] + dumps(data) + page[m.end(2):]
        json.loads(PAT.search(new).group(2))        # sanity
        changed += 1
        if apply:
            with open(path, "w", encoding="utf-8", newline="") as f:
                f.write(new)
    print(("APPLIED" if apply else "DRY-RUN") + f": changed {changed} · already ok {same} · skipped {skipped} · total {len(files)}")
    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
