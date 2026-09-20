#!/usr/bin/env python3
"""Chuẩn hoá graph JSON-LD site-wide (khối <script type="application/ld+json"> ĐẦU TIÊN,
KHÔNG phải khối id="page-schema") trên toàn bộ trang.

1. **description** (làm 2026-09-19): WP export copy meta description của từng trang vào
   `EducationalOrganization.description` và `WebSite.description`, nên 607/748 trang mô tả tổ chức lệch
   (trang Anh mang câu của Đức...). Organization/WebSite là 1 thực thể duy nhất → phải cùng 1 câu.
2. **logo / image / address / areaServed** (thêm 2026-09-20): Rich Results Test báo "vấn đề không nghiêm
   trọng" ở *Tổ chức* và *Doanh nghiệp địa phương* = thiếu trường Google khuyến nghị. Bổ sung từ dữ liệu
   CÓ THẬT trên site (logo.png 760×760, địa chỉ VP TP.HCM y như office-1). KHÔNG thêm `priceRange`,
   `openingHours`, `sameAs`, `email` vì site không có dữ liệu đó — bịa ra là sai.
3. **telephone** đổi sang E.164 `+84906510747` (Google khuyến nghị có mã quốc gia). Số gốc do user xác nhận
   2026-09-20; liên kết `tel:` do `tools/fix-contact.py` lo.

Dry-run mặc định, `--apply` để ghi. Idempotent. Không đụng gì khác trong graph (round-trip JSON được kiểm
byte-for-byte trước khi ghi: nếu chỉ serialize lại mà đã khác bản gốc thì trang đó bị BỎ QUA và báo)."""
import glob, json, os, re, sys

try:
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
except Exception:
    pass

TGT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
BASE = "https://duhoctesolhcmc.vn"
ORG_DESC = ("Ban Du học Hội TESOL TP.HCM đồng hành cùng học sinh và phụ huynh trong lộ trình du học: "
            "chọn quốc gia, chọn trường, học bổng, visa và chuẩn bị lên đường.")
ORG_LOGO = BASE + "/wp-content/themes/duy-study/assets/img/logo.png"          # 760×760, có thật trong repo
ORG_PHONE = "+84906510747"                                                    # = 0906.510.747 (E.164)
ORG_ADDRESS = {"@type": "PostalAddress",
               "streetAddress": "BV Bank – 412 Nguyễn Thị Minh Khai, P. Bàn Cờ, TP.HCM",
               "addressCountry": "VN"}                                        # y hệt office-1, tránh lệch NAP
PAT = re.compile(r'(<script type="application/ld\+json">)(.*?)(</script>)', re.S)


def fix_node(node):
    """-> True nếu có sửa. Chỉ ghi khi giá trị khác, để giữ tính idempotent."""
    t, changed = node.get("@type"), False

    def put(key, val):
        nonlocal changed
        if node.get(key) != val:
            node[key] = val
            changed = True

    if t in ("EducationalOrganization", "WebSite"):
        put("description", ORG_DESC)
    if t == "EducationalOrganization":
        put("logo", ORG_LOGO)
        put("image", ORG_LOGO)
        put("address", ORG_ADDRESS)
        put("areaServed", "VN")
        put("telephone", ORG_PHONE)
    if t == "LocalBusiness":
        put("image", ORG_LOGO)
        put("telephone", ORG_PHONE)
    return changed


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
            touched |= fix_node(node)
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
