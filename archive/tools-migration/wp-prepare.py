#!/usr/bin/env python3
"""Chuẩn bị bản WordPress để đưa lên https://duhoctesolhcmc.vn.

Nguồn: `../duystudy - website/wp` — chính là bản WordPress đã sinh ra 748 trang tĩnh hiện tại
(571 school + 9 event, slug trùng khớp, CPT rewrite `truong`/`su-kien`/`hoc-bong`/`hoc-sinh`).
Chạy trên SQLite qua drop-in `wp-content/db.php`, KHÔNG cần MySQL.

Script này KHÔNG đụng vào thư mục nguồn. Nó tạo ra `wp-build/` (không commit vào git):
  wp-build/.ht.sqlite     — bản sao CSDL đã đổi domain + đổi thương hiệu + đặt lại mật khẩu admin
  wp-build/wp-config.php  — config chạy thật (salt mới, debug tắt, khoá sửa file)
  wp-build/ADMIN.txt      — tài khoản đăng nhập (KHÔNG commit, KHÔNG gửi qua chat)

Đổi URL trong CSDL phải **an toàn với dữ liệu serialize** của WordPress: chuỗi serialize mang
sẵn độ dài (`s:21:"http://127.0.0.1:8099"`), thay thô làm hỏng dữ liệu. Script tự sửa lại độ dài.

Dùng: python tools/wp-prepare.py            # xem trước
      python tools/wp-prepare.py --apply    # ghi ra wp-build/
"""
import base64, hashlib, os, re, secrets, shutil, sqlite3, sys

try:
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
except Exception:
    pass

TGT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC_WP = os.path.join(os.path.dirname(TGT), "duystudy - website", "wp")
SRC_DB = os.path.join(SRC_WP, "wp-content", "database", ".ht.sqlite")
BUILD = os.path.join(TGT, "wp-build")

OLD_URL = "http://127.0.0.1:8099"
NEW_URL = "https://duhoctesolhcmc.vn"
ORG = "Ban Du học Hội TESOL TP.HCM"
TAGLINE = "Tư vấn du học và lộ trình học bổng"
ADMIN_USER = "admin"
ADMIN_EMAIL = "louischan.sgvn@gmail.com"

SALT_KEYS = ["AUTH_KEY", "SECURE_AUTH_KEY", "LOGGED_IN_KEY", "NONCE_KEY",
             "AUTH_SALT", "SECURE_AUTH_SALT", "LOGGED_IN_SALT", "NONCE_SALT"]


def fix_serialized(text: str) -> str:
    """Đổi URL rồi sửa lại độ dài trong mọi chuỗi serialize s:<len>:"..."."""
    if OLD_URL not in text:
        return text
    out = text.replace(OLD_URL, NEW_URL)

    def repair(m):
        body = m.group(2)
        return f's:{len(body.encode("utf-8"))}:"{body}"'

    return re.sub(r's:(\d+):"((?:[^"\\]|\\.)*)"', repair, out)


def gen_password(n=20):
    alpha = "abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789"
    return "".join(secrets.choice(alpha) for _ in range(n))


def wp_config(password_note=""):
    salts = "\n".join(
        f"define( '{k}', '{base64.b64encode(secrets.token_bytes(48)).decode().replace(chr(39), chr(46))}' );"
        for k in SALT_KEYS)
    return f"""<?php
/**
 * WordPress config — {ORG}  (https://duhoctesolhcmc.vn)
 * Sinh bởi tools/wp-prepare.py. Chạy trên SQLite qua drop-in wp-content/db.php (không cần MySQL).
 * KHÔNG commit file này vào git.{password_note}
 */

// SQLite: drop-in wp-content/db.php bỏ qua 4 hằng dưới, nhưng WordPress vẫn đòi phải khai báo.
define( 'DB_NAME', 'wordpress' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wp_';

define( 'WP_HOME', '{NEW_URL}' );
define( 'WP_SITEURL', '{NEW_URL}' );

{salts}

// Chạy thật: tắt debug, cấm sửa file trong admin, tắt tự cập nhật lõi.
define( 'WP_ENVIRONMENT_TYPE', 'production' );
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', false );
define( 'DISALLOW_FILE_EDIT', true );
define( 'DISALLOW_FILE_MODS', false );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
define( 'AUTOMATIC_UPDATER_DISABLED', false );
define( 'EMPTY_TRASH_DAYS', 30 );
define( 'WP_POST_REVISIONS', 5 );

if ( ! defined( 'ABSPATH' ) ) {{
	define( 'ABSPATH', __DIR__ . '/' );
}}
require_once ABSPATH . 'wp-settings.php';
"""


def main(argv):
    apply = "--apply" in argv
    if not os.path.isfile(SRC_DB):
        print("Không thấy CSDL nguồn:", SRC_DB); return 2

    if apply:
        os.makedirs(BUILD, exist_ok=True)
    dst = os.path.join(BUILD, ".ht.sqlite")
    work = dst if apply else os.path.join(os.environ.get("TEMP", "."), "_wpprep.sqlite")
    os.makedirs(os.path.dirname(work), exist_ok=True)
    shutil.copy(SRC_DB, work)

    db = sqlite3.connect(work)
    c = db.cursor()
    stats = {}

    # 1. đổi domain trong mọi bảng có chứa URL cũ
    for table, cols, key in (("wp_options", ("option_value",), "option_id"),
                             ("wp_posts", ("post_content", "guid", "post_excerpt"), "ID"),
                             ("wp_postmeta", ("meta_value",), "meta_id"),
                             ("wp_termmeta", ("meta_value",), "meta_id"),
                             ("wp_usermeta", ("meta_value",), "umeta_id")):
        for col in cols:
            rows = c.execute(f"select {key}, {col} from {table} where {col} like ?",
                             (f"%{OLD_URL}%",)).fetchall()
            for rid, val in rows:
                c.execute(f"update {table} set {col}=? where {key}=?", (fix_serialized(val), rid))
            if rows:
                stats[f"{table}.{col}"] = len(rows)

    # 2. thương hiệu
    for k, v in (("blogname", ORG), ("blogdescription", TAGLINE),
                 ("siteurl", NEW_URL), ("home", NEW_URL),
                 ("admin_email", ADMIN_EMAIL), ("timezone_string", "Asia/Ho_Chi_Minh"),
                 ("blog_public", "1"), ("permalink_structure", "/%postname%/")):
        c.execute("update wp_options set option_value=? where option_name=?", (v, k))

    # 3. xoá transient: cache cũ của theme (thẻ trường, kích thước ảnh SEO, lastmod) còn mang
    #    dữ liệu và thương hiệu Duy Study. Xoá đi để WordPress tự dựng lại theo nội dung mới.
    n_tr = c.execute("select count(*) from wp_options where option_name like '\\_transient\\_%' "
                     "escape '\\' or option_name like '\\_site\\_transient\\_%' escape '\\'").fetchone()[0]
    c.execute("delete from wp_options where option_name like '\\_transient\\_%' escape '\\' "
              "or option_name like '\\_site\\_transient\\_%' escape '\\'")
    stats["transient đã xoá"] = n_tr

    # 4. tài khoản admin: giữ mật khẩu đã phát nếu ADMIN.txt còn, để khỏi đổi mỗi lần chạy lại
    pw = None
    admin_txt = os.path.join(BUILD, "ADMIN.txt")
    if os.path.isfile(admin_txt):
        for line in open(admin_txt, encoding="utf-8"):
            if line.startswith("Mật khẩu:"):
                pw = line.split(":", 1)[1].strip()
    pw = pw or gen_password()
    c.execute("update wp_users set user_pass=?, user_email=?, display_name=? where user_login=?",
              (hashlib.md5(pw.encode()).hexdigest(), ADMIN_EMAIL, "Quản trị", ADMIN_USER))
    got = c.execute("select ID, user_login, user_email from wp_users").fetchall()

    db.commit()
    left = c.execute("select count(*) from wp_posts where post_content like ?",
                     (f"%{OLD_URL}%",)).fetchone()[0]
    counts = dict(c.execute("select post_type, count(*) from wp_posts where post_status='publish' group by 1"))
    db.close()

    print(("GHI RA wp-build/" if apply else "XEM TRƯỚC (chưa ghi)") + f" — nguồn: {SRC_DB}")
    print("  đổi URL:", stats or "không có")
    print("  còn sót URL cũ trong post_content:", left)
    print("  nội dung:", counts)
    print("  user:", got)

    if apply:
        open(os.path.join(BUILD, "wp-config.php"), "w", encoding="utf-8", newline="\n").write(wp_config())
        open(os.path.join(BUILD, "ADMIN.txt"), "w", encoding="utf-8", newline="\n").write(
            f"WordPress {NEW_URL}/wp-admin/\nTài khoản: {ADMIN_USER}\nMật khẩu: {pw}\nEmail: {ADMIN_EMAIL}\n"
            "\nĐổi mật khẩu ngay sau lần đăng nhập đầu.\nFile này KHÔNG được commit vào git.\n")
        print(f"\n  đã ghi: {BUILD}\\.ht.sqlite · wp-config.php · ADMIN.txt")
        print("  mật khẩu admin nằm trong ADMIN.txt (không in ra đây)")
    else:
        os.remove(work)
    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
