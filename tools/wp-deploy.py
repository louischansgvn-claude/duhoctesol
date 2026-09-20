#!/usr/bin/env python3
"""Tải WordPress lên docroot của https://duhoctesolhcmc.vn qua FTP.

An toàn: docroot đang có 748 file `index.html` tĩnh và `.htaccess` đã đặt
`DirectoryIndex index.html index.php` (khối "static-first"), nên **WordPress nằm sẵn đó mà
site tĩnh vẫn chạy y nguyên**. Chỉ khi đảo thành `index.php index.html` thì WordPress mới lên sóng
— đảo lại là quay về site tĩnh trong vài giây.

Dùng: python tools/wp-deploy.py <cfg> <phase>
  core    2.916 file (59 MB)  — lõi WordPress 6.9.4, KHÔNG gồm wp-content
  theme     ~580 file (12 MB) — theme duy-study, BỎ `inc/data` (347 MB, chỉ seeder dùng)
                                và BỎ 6 file đang phục vụ site tĩnh (logo/CSS/JS) — xem KEEP_STATIC
  plugin     45 file          — sqlite-database-integration + drop-in wp-content/db.php
  db          1 file (7 MB)   — wp-build/.ht.sqlite (đã đổi domain + thương hiệu + mật khẩu)
  config      1 file          — wp-build/wp-config.php
  uploads  3.817 file (693 MB)— ảnh WordPress (`2026/07/...`), khác hệ ảnh của site tĩnh
  theme-assets 2 file        — main.css + main.js bản WordPress; chạy NGAY TRƯỚC khi đổi sang WordPress
  all     tất cả file, đúng thứ tự trên (KHÔNG gồm 2 phase .htaccess bên dưới)

Hai phase đổi công tắc — chỉ đụng đúng file `.htaccess` ở docroot:
  htaccess-wp      ĐỔI SANG WORDPRESS: `DirectoryIndex index.php index.html` + khối rewrite WordPress.
                   748 file index.html tĩnh vẫn nằm nguyên đó, chỉ là không được dùng nữa.
  htaccess-static  ĐƯỜNG LUI: trả về site tĩnh trong vài giây. Chạy nếu WordPress lỗi.
Cả hai đều giữ nguyên khối PHP của cPanel và toàn bộ redirect canonical host + 4 redirect nganh-hoc.
Bản đang chạy và bản cũ đều lưu ở `docs/server/`.

Không bao giờ in mật khẩu ra log."""
import glob, os, subprocess, sys, tempfile, time

try:
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
    sys.stderr.reconfigure(encoding="utf-8", errors="replace")
except Exception:
    pass

TGT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(os.path.dirname(TGT), "duystudy - website")
WP = os.path.join(SRC, "wp")
# theme đọc TỪ REPO NÀY (đã chép + đổi thương hiệu bằng tools/wp-theme-sync.py),
# không đọc từ project Duy Study nữa.
THEME = os.path.join(TGT, "wp-content", "themes", "duy-study")
BUILD = os.path.join(TGT, "wp-build")
HOST = "ftp://pbf43-22360.azdigihost.com"

# Ảnh thương hiệu: bản trong theme WordPress là **logo Duy Study**, bản đang chạy là logo
# "Ban Du học Hội TESOL TP.HCM". KHÔNG BAO GIỜ đè 4 file này, kể cả sau khi chuyển sang WordPress.
KEEP_STATIC = {"assets/img/logo.png", "assets/img/logo-full.png",
               "assets/img/campus-global.svg", "assets/img/hero-students.svg"}

# CSS/JS bản WordPress mới hơn bản đang phục vụ site tĩnh. Template WordPress cần bản mới,
# nhưng đè sớm thì site tĩnh đang chạy đổi giao diện -> tách ra phase `theme-assets`,
# chạy ngay trước khi đảo DirectoryIndex sang WordPress.
DEFER_ASSETS = {"assets/css/main.css", "assets/js/main.js"}


def slash(p):
    return p.replace("\\", "/")


def walk(root, skip_rel=()):
    out = []
    for d, _, fs in os.walk(root):
        for f in fs:
            p = os.path.join(d, f)
            rel = slash(os.path.relpath(p, root))
            if any(rel == s or rel.startswith(s.rstrip("/") + "/") for s in skip_rel):
                continue
            out.append((p, rel))
    return sorted(out)


def files_for(phase):
    """-> [(đường dẫn local, đường dẫn trên server)]"""
    if phase == "core":
        return [(p, rel) for p, rel in walk(WP, skip_rel=("wp-content", "wp-config.php"))]
    if phase == "theme":
        base = "wp-content/themes/duy-study/"
        return [(p, base + rel) for p, rel in walk(THEME, skip_rel=("inc/data",))
                if rel not in KEEP_STATIC and rel not in DEFER_ASSETS]
    if phase == "theme-assets":
        base = "wp-content/themes/duy-study/"
        return [(os.path.join(THEME, rel.replace("/", os.sep)), base + rel)
                for rel in sorted(DEFER_ASSETS)]
    if phase == "plugin":
        out = [(p, "wp-content/plugins/sqlite-database-integration/" + rel)
               for p, rel in walk(os.path.join(WP, "wp-content", "plugins", "sqlite-database-integration"))]
        out.append((os.path.join(WP, "wp-content", "db.php"), "wp-content/db.php"))
        return out
    if phase == "db":
        return [(os.path.join(BUILD, ".ht.sqlite"), "wp-content/database/.ht.sqlite"),
                (os.path.join(WP, "wp-content", "database", ".htaccess"), "wp-content/database/.htaccess"),
                (os.path.join(WP, "wp-content", "database", "index.php"), "wp-content/database/index.php")]
    if phase == "config":
        return [(os.path.join(BUILD, "wp-config.php"), "wp-config.php")]
    if phase == "uploads":
        return [(p, "wp-content/uploads/" + rel)
                for p, rel in walk(os.path.join(WP, "wp-content", "uploads"))]
    if phase == "htaccess-wp":
        return [(os.path.join(TGT, "docs", "server", "htaccess-wordpress-2026-09-20.txt"), ".htaccess")]
    if phase == "htaccess-static":
        return [(os.path.join(TGT, "docs", "server", "htaccess-live-2026-09-20b.txt"), ".htaccess")]
    raise SystemExit("phase? " + __doc__)


def upload(cfg, batch, label):
    fd, path = tempfile.mkstemp(suffix=".cfg"); os.close(fd)
    try:
        with open(cfg) as f:
            cred = f.read().strip()
        lines = [cred, "ftp-create-dirs", "connect-timeout = 25", "retry = 3",
                 "retry-delay = 3", "silent", "show-error"]
        for local, remote in batch:
            lines.append(f'upload-file = "{slash(local)}"')
            lines.append(f'url = "{HOST}/{remote}"')
        open(path, "w", encoding="utf-8").write("\n".join(lines) + "\n")
        os.chmod(path, 0o600)
        t = time.time()
        r = subprocess.run(["curl", "-K", path], capture_output=True, text=True)
        err = "\n".join(l for l in r.stderr.splitlines() if l.strip())[:600]
        print(f"  {label}: {len(batch)} file · exit={r.returncode} · {time.time()-t:.0f}s"
              + (f"\n    stderr: {err}" if err else ""))
        return r.returncode
    finally:
        if os.path.exists(path):
            os.remove(path)


if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(__doc__); raise SystemExit(2)
    cfg, phase = sys.argv[1], sys.argv[2]
    phases = ["core", "theme", "plugin", "uploads", "db", "config"] if phase == "all" else [phase]
    bad_total = 0
    for p in phases:
        fs = files_for(p)
        size = sum(os.path.getsize(a) for a, _ in fs) / 1048576
        print(f"--- {p}: {len(fs)} file · {size:.1f} MB ---")
        CH = 150 if p == "uploads" else 300
        bad = 0
        for i in range(0, len(fs), CH):
            if upload(cfg, fs[i:i + CH], f"{p} {i+1}-{min(i+CH, len(fs))}") != 0:
                bad += 1
        bad_total += bad
        print(f"--- {p} xong, batch lỗi: {bad} ---")
    raise SystemExit(1 if bad_total else 0)
