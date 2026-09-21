#!/usr/bin/env python3
"""Chép theme `duy-study` từ project Duy Study sang repo này rồi đổi thương hiệu.

Vì sao chép vào repo: theme là bộ mặt + toàn bộ logic SEO của site này. Để nguyên bên project
Duy Study thì (a) sửa là đụng vào project của họ, (b) thay đổi của ta không được git theo dõi.
Sau khi chép, `tools/wp-deploy.py` đọc theme TỪ REPO NÀY.

Bỏ qua `inc/data/` (1.521 file, 347 MB — dữ liệu nguồn cho seeder, runtime không đọc).
**Giữ nguyên 4 file ảnh thương hiệu** đang có trong repo (logo TESOL); bản trong theme Duy Study
là logo Duy Study, đè vào là mất nhận diện.

Đổi thương hiệu: 166 chỗ "Duy Study" trong theme đều là **chữ hiển thị** — đã kiểm, không có tên
hàm/hằng/class nào chứa chuỗi này nên thay an toàn. Số điện thoại + email mặc định cũng đổi
(dù runtime lấy từ tuỳ chọn trong wp-admin, vẫn nên sửa mặc định để phòng khi tuỳ chọn trống).

Dùng: python tools/wp-theme-sync.py            # xem trước
      python tools/wp-theme-sync.py --apply    # chép + đổi
"""
import os, shutil, sys

try:
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
except Exception:
    pass

TGT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(os.path.dirname(TGT), "duystudy - website", "wp-content", "themes", "duy-study")
DST = os.path.join(TGT, "wp-content", "themes", "duy-study")

SKIP_DIRS = ("inc/data",)
# KHONG BAO GIO chep sang: day la nhan dien cua Duy Study. Ban dong bo 20/09 da chep
# `logo.webp` va `og-default.jpg` sang, va vi duy_logo_uri() uu tien .webp hon .png nen
# site treo logo "DUY Study" suot tu do; moi link chia se cung hien anh bia cua ho.
SKIP_FILES = {"assets/img/logo.webp", "assets/img/og-default.jpg"}
# Bản của repo này phải thắng bản nguồn. Ngoài 4 ảnh thương hiệu, 6 file dưới đã bị
# sửa ngày 21/09/2026 để xoá 7 học bổng và 7 câu chuyện học sinh bịa (xem
# duy_demo_scholarships_defaults / duy_demo_pairs / duy_demo_stories). Chép đè là
# dữ liệu giả sống lại ngay trên production.
KEEP_OURS = {"assets/img/logo.png", "assets/img/logo-full.png",
             "assets/img/campus-global.svg", "assets/img/hero-students.svg",
             "inc/demo-data.php", "inc/seo.php", "front-page.php",
             "archive-scholarship.php", "archive-student_story.php",
             "page-templates/ve-chung-toi.php",
             # 21/09: go truong mockup METU + sua cach khop nganh-truong de trang
             # /nganh-hoc/ hien duoc truong that (ban nguon khop bang truong `major`
             # rong nen 4/5 trang khong co truong nao).
             "inc/mockup-v2-data.php", "inc/mockup-v2-data.json",
             "inc/components.php", "page-templates/major.php",
             # Bang mau: 21/09 da tra ve navy #173C8F + do #C4302B lay tu logo TESOL HCMC.
             # Ban nguon dung cyan #23a9d8 + hong #df1f83 cua Duy Study.
             "assets/css/main.css", "theme.json",
             "assets/img/article-cover.svg", "assets/img/country-au.svg",
             "assets/img/country-ca.svg", "assets/img/country-nz.svg",
             "assets/img/country-tr.svg", "assets/img/country-us.svg",
             "assets/img/event-workshop.svg", "assets/img/team-office.svg"}
TEXT_EXT = (".php", ".css", ".js", ".json", ".txt", ".md", ".html")

ORG = "Ban Du học Hội TESOL TP.HCM"
# thứ tự quan trọng: chuỗi dài thay trước
REPLACE = [
    ("Du học Duy Study", ORG),
    ("DU HỌC DUY STUDY", "BAN DU HỌC HỘI TESOL TP.HCM"),
    ("Về Duy Study", "Về chúng tôi"),
    ("DUY STUDY", "BAN DU HỌC HỘI TESOL TP.HCM"),
    ("Duy Study", ORG),
    ("duystudy.2107@gmail.com", "louischan.sgvn@gmail.com"),
    ("tvv.duystudy@gmail.com", "louischan.sgvn@gmail.com"),
    ("0909.542.539", "0906.510.747"),
    ("0909542539", "0906510747"),
    # Sau khi thay thương hiệu, hai chuỗi dưới thành sai nghĩa nên phải sửa tiếp:
    # tổ chức là một ban thuộc hội, KHÔNG phải công ty. Bản tĩnh cũ cũng ghi
    # "© 2026 Ban Du học Hội TESOL TP.HCM." (không có "Công ty Tư vấn").
    ("Công ty Tư vấn Ban Du học Hội TESOL TP.HCM", "Ban Du học Hội TESOL TP.HCM"),
    ("là công ty tư vấn du học tại Việt Nam", "là đơn vị tư vấn du học tại Việt Nam"),
]


def rel_of(root, p):
    return os.path.relpath(p, root).replace("\\", "/")


def main(argv):
    apply = "--apply" in argv
    if not os.path.isdir(SRC):
        print("Không thấy theme nguồn:", SRC); return 2

    copied = kept = edited = 0
    hits = {}
    for d, dirs, fs in os.walk(SRC):
        rel_dir = rel_of(SRC, d)
        if rel_dir != "." and any(rel_dir == s or rel_dir.startswith(s + "/") for s in SKIP_DIRS):
            dirs[:] = []
            continue
        for f in fs:
            sp = os.path.join(d, f)
            rel = rel_of(SRC, sp)
            dp = os.path.join(DST, rel.replace("/", os.sep))
            if rel in SKIP_FILES:
                continue
            if rel in KEEP_OURS and os.path.exists(dp):
                kept += 1
                continue
            copied += 1
            if not apply:
                if rel.endswith(TEXT_EXT):
                    try:
                        txt = open(sp, encoding="utf-8", newline="").read()
                    except (UnicodeDecodeError, OSError):
                        continue
                    n = sum(txt.count(a) for a, _ in REPLACE)
                    if n:
                        hits[rel] = n
                continue
            os.makedirs(os.path.dirname(dp), exist_ok=True)
            if rel.endswith(TEXT_EXT):
                try:
                    txt = open(sp, encoding="utf-8", newline="").read()
                except (UnicodeDecodeError, OSError):
                    shutil.copy2(sp, dp); continue
                n = sum(txt.count(a) for a, _ in REPLACE)
                for a, b in REPLACE:
                    txt = txt.replace(a, b)
                open(dp, "w", encoding="utf-8", newline="").write(txt)
                if n:
                    edited += 1; hits[rel] = n
            else:
                shutil.copy2(sp, dp)

    print(("ĐÃ CHÉP" if apply else "XEM TRƯỚC") + f" {SRC}\n  -> {DST}")
    print(f"  file chép: {copied} · giữ bản của ta: {kept} · file có đổi chữ: {edited if apply else len(hits)}")
    print(f"  tổng số chỗ thay: {sum(hits.values())}")
    for rel, n in sorted(hits.items(), key=lambda x: -x[1])[:12]:
        print(f"    {n:4}  {rel}")
    if apply:
        left = []
        for d, dirs, fs in os.walk(DST):
            for f in fs:
                p = os.path.join(d, f)
                if p.endswith(TEXT_EXT):
                    try:
                        t = open(p, encoding="utf-8", newline="").read()
                    except (UnicodeDecodeError, OSError):
                        continue
                    if "Duy Study" in t or "duystudy" in t:
                        left.append(rel_of(DST, p))
        print(f"  còn sót 'Duy Study' sau khi đổi: {len(left)} file", left[:5])
    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
