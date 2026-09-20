#!/usr/bin/env python3
"""So byte toàn bộ site live với bản local sau mỗi lần deploy.
Dùng: python tools/live-compare.py
- Mỗi index.html -> GET URL tương ứng (không theo redirect). 200 thì so byte với file local.
- URL nào server trả 301 thì in đích redirect (4 stub /nganh-hoc/ được 301 trong .htaccess từ 2026-09-20 -> đó là ĐÚNG).
- robots.txt / sitemap.xml / llms.txt so byte luôn.
Thoát mã 1 nếu có trang 200 mà khác local, hoặc lỗi mạng/404."""
import concurrent.futures, glob, os, sys, time, urllib.error, urllib.request

try:
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
except Exception:
    pass

TGT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
BASE = "https://duhoctesolhcmc.vn"
EXPECT_301 = {  # local file -> đích 301 mong đợi (xem .htaccess block "1b")
    "nganh-hoc/cntt/index.html": BASE + "/nganh-hoc/cong-nghe/",
    "nganh-hoc/ky-thuat/index.html": BASE + "/nganh-hoc/cong-nghe/",
    "nganh-hoc/kinh-doanh/index.html": BASE + "/nganh-hoc/kinh-te/",
    "nganh-hoc/y-suc-khoe/index.html": BASE + "/nganh-hoc/suc-khoe/",
}


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


OPENER = urllib.request.build_opener(NoRedirect)


def fetch(item):
    f, url = item
    local = open(os.path.join(TGT, f), "rb").read()
    for _ in range(3):
        try:
            req = urllib.request.Request(url, headers={"User-Agent": "live-compare/1.0", "Cache-Control": "no-cache"})
            with OPENER.open(req, timeout=30) as r:
                return f, r.status, r.read() == local, None
        except urllib.error.HTTPError as e:
            if 300 <= e.code < 400:
                return f, e.code, None, e.headers.get("Location")
            last = e.code
        except Exception:
            last = 0
        time.sleep(1)
    return f, last, False, None


def main():
    os.chdir(TGT)
    files = sorted(f.replace("\\", "/") for f in glob.glob("**/index.html", recursive=True)
                   if not f.startswith((".git", "docs", ".superpowers", "scratchpad")))
    targets = [(f, BASE + "/" + f[:-len("index.html")]) for f in files]
    targets += [(f, BASE + "/" + f) for f in ("robots.txt", "sitemap.xml", "llms.txt") if os.path.isfile(f)]
    t = time.time()
    same = diff = redir_ok = 0
    bad = []
    with concurrent.futures.ThreadPoolExecutor(max_workers=8) as ex:
        for f, code, ok, loc in ex.map(fetch, targets):
            if code == 200 and ok:
                same += 1
            elif 300 <= (code or 0) < 400:
                if EXPECT_301.get(f) == loc:
                    redir_ok += 1
                else:
                    bad.append((f, code, "unexpected redirect ->", loc))
            elif code == 200:
                diff += 1; bad.append((f, code, "DIFFERENT from local"))
            else:
                bad.append((f, code, "error"))
    print(f"{len(targets)} URL · identical {same} · expected 301 {redir_ok} · different {diff} · errors {len(bad) - diff} · {time.time() - t:.0f}s")
    for b in bad[:30]:
        print("  ", b)
    if bad:
        print("FAIL")
        return 1
    print("OK — live == local")
    return 0


if __name__ == "__main__":
    sys.exit(main())
