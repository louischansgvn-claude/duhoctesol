#!/usr/bin/env python3
"""Đồng bộ số điện thoại trên toàn site: liên kết `tel:` phải khớp số hiển thị.

Bối cảnh (2026-09-20): chân trang hiển thị `0906.510.747` nhưng thẻ <a> lại `href="tel:0909542539"`
— số cũ của Duy Study còn sót từ template WP export. Khách bấm nút gọi sẽ gọi nhầm công ty khác.
Lý do sót: các task nội dung trước bị CLAUDE.md cấm sửa `href`, nên chỉ chữ hiển thị được cập nhật.
User xác nhận 2026-09-20: **0906.510.747 là số chính thức** → sửa 1.497 liên kết `tel:`.

Chỗ xuất hiện: 1 nút `.footer-hotline` trong <footer> + 1 nút gọi nổi `.sc-call` mỗi trang,
riêng `lien-he/` có 3. Sửa liên kết trong <footer> làm **đổi md5 footer** — đó là điều mong đợi,
md5 mới vẫn phải đồng nhất trên cả 748 trang (`tools/verify-site.py` kiểm).

Nút **Zalo** nổi (`.sc-zalo`, 748 link `https://zalo.me/0909542539`) cũng là của Duy Study —
user chốt 2026-09-20: đổi sang `zalo.me/0906510747`.

⚠️ **KHÔNG đụng 4 chỗ trong 2 trang sự kiện** (`0909.542.539` + `duystudy.2107@gmail.com` nằm trong
nội dung bài viết): đó là thông tin liên hệ của Duy Study trong bài tường thuật sự kiện do Duy Study
tổ chức — user đã dặn từ phiên trước "trang này đã đúng rồi không cần sửa". Script chỉ thay trong
thuộc tính `href`, nên nội dung bài viết không bị ảnh hưởng.

Dùng: python tools/fix-contact.py            # dry-run
      python tools/fix-contact.py --apply    # ghi
Idempotent. Không đụng chữ hiển thị, không đụng JSON-LD (phần đó do tools/fix-org-schema.py lo)."""
import glob, os, re, sys

try:
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
except Exception:
    pass

TGT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

PHONE_DISPLAY = "0906.510.747"          # đúng như chữ hiển thị trên trang
PHONE_TEL = "0906510747"                # dùng trong href="tel:" và zalo.me/
PHONE_E164 = "+84906510747"             # dùng trong JSON-LD (xem tools/fix-org-schema.py)
OLD_NUM = "0909542539"                  # số Duy Study còn sót trong href

# chỉ thay trong thuộc tính href -> nội dung bài viết sự kiện không bị đụng
SWAPS = [(f'href="tel:{OLD_NUM}"', f'href="tel:{PHONE_TEL}"'),
         (f'href="https://zalo.me/{OLD_NUM}"', f'href="https://zalo.me/{PHONE_TEL}"')]

# 4 trang sự kiện có khối chữ ký Duy Study trong NỘI DUNG bài (hotline + email + hashtag #DuyStudy)
# — cố ý giữ nguyên, user đã chốt "trang này đã đúng rồi không cần sửa".
ALLOWED_LEFTOVER = {"su-kien/hoi-thao-lien-minh-tieng-anh-binh-phuoc-2025/index.html",
                    "su-kien/le-ky-ket-mou-dai-hoc-gia-dinh/index.html",
                    "su-kien/mo-khoa-tuoi-18-2026-uef/index.html",
                    "su-kien/su-kien-du-hoc-bmi-2025/index.html"}


def read(p):
    with open(p, encoding="utf-8", newline="") as f:
        return f.read()


def main(argv):
    apply = "--apply" in argv
    files = sorted(f.replace("\\", "/") for f in glob.glob(os.path.join(TGT, "**", "index.html"), recursive=True)
                   if not re.search(r"[\\/](\.git|docs|scratchpad|\.superpowers)[\\/]", f))
    pages = 0
    per_kind = {old: 0 for old, _ in SWAPS}
    unexpected = []
    for path in files:
        rel = os.path.relpath(path, TGT).replace("\\", "/")
        page = read(path)
        new = page
        hit = 0
        for old, repl in SWAPS:
            n = page.count(old)
            per_kind[old] += n
            hit += n
            new = new.replace(old, repl)
        if hit:
            pages += 1
            if apply:
                with open(path, "w", encoding="utf-8", newline="") as f:
                    f.write(new)
        # số cũ còn sót ngoài href: chỉ được phép ở 2 trang sự kiện của Duy Study
        rest = (read(path) if apply else new).replace(".", "")
        if OLD_NUM in rest and rel not in ALLOWED_LEFTOVER:
            unexpected.append(rel)
    print(("APPLIED" if apply else "DRY-RUN") + f": {sum(per_kind.values())} liên kết trên {pages} trang")
    for old, n in per_kind.items():
        print(f"    {n:5}  {old}")
    if unexpected:
        print(f"  !! {len(unexpected)} trang NGOÀI danh sách cho phép vẫn còn số cũ:")
        for f in unexpected[:10]:
            print("    ", f)
        return 1
    print(f"  ok — số cũ chỉ còn trong nội dung {len(ALLOWED_LEFTOVER)} trang sự kiện Duy Study (cố ý giữ)")
    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
