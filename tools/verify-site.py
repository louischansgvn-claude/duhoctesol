#!/usr/bin/env python3
"""Kiểm tra invariant toàn site sau mỗi lần sửa hàng loạt (chạy trước khi deploy).
Thoát mã 1 nếu có vi phạm. Dùng: python tools/verify-site.py"""
import collections, glob, hashlib, html, json, os, re, sys

try:
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
except Exception:
    pass

TGT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
BASE = "https://duhoctesolhcmc.vn"
HEADER_MD5 = "482537d6b5e00b6df298935c27b4eefb"
FOOTER_MD5 = "3e1a808c1520d98d61f7616b4c193be1"
EXPECT = {"index.html": 748, "truong": 571, "su-kien": 9, "page-schema": 747, "sitemap": 723}


def read(p):
    with open(p, encoding="utf-8", newline="") as f:
        return f.read()


def main():
    os.chdir(TGT)
    files = sorted(f.replace("\\", "/") for f in glob.glob("**/index.html", recursive=True)
                   if not f.startswith((".git", "docs", ".superpowers", "scratchpad")))
    pages = {f: read(f) for f in files}
    bad = []

    def check(cond, msg):
        if not cond:
            bad.append(msg)

    check(len(files) == EXPECT["index.html"], f"index.html = {len(files)} (≠ {EXPECT['index.html']})")
    check(len(glob.glob("truong/*/index.html")) == EXPECT["truong"], "truong count")
    check(len(glob.glob("su-kien/*/index.html")) == EXPECT["su-kien"], "su-kien count")

    hm, fm = set(), set()
    titles, descs = collections.defaultdict(list), collections.defaultdict(list)
    n_schema = n_noindex = n_old = n_demo = 0
    for f, s in pages.items():
        h = re.search(r"<header\b.*?</header>", s, re.S)
        ft = re.search(r"<footer\b.*?</footer>", s, re.S)
        hm.add(hashlib.md5(h.group(0).encode()).hexdigest() if h else "MISSING")
        fm.add(hashlib.md5(ft.group(0).encode()).hexdigest() if ft else "MISSING")
        n_noindex += "noindex" in s
        n_schema += 'id="page-schema"' in s
        n_old += "duhoctesol.duystudy.vn" in s
        n_demo += bool(re.search(r"M7lc1UVf-VE|ScMzIvxBSi4", s))
        t = html.unescape(re.search(r"<title>(.*?)</title>", s, re.S).group(1))
        d = html.unescape(re.search(r'<meta name="description" content="([^"]*)"', s).group(1))
        titles[t].append(f); descs[d].append(f)
        check("…" not in t, f"title has …: {f}")
        check(not d.endswith("…"), f"description ends with …: {f}")
        check(len(d) <= 160, f"description > 160 ({len(d)}): {f}")
        check(len(d) >= 50, f"description < 50 ({len(d)}): {f}")
        for tag, pat in (("og:title", r'<meta property="og:title" content="([^"]*)"'),
                         ("twitter:title", r'<meta name="twitter:title" content="([^"]*)"')):
            check(html.unescape(re.search(pat, s).group(1)) == t, f"{tag} ≠ title: {f}")
        for tag, pat in (("og:description", r'<meta property="og:description" content="([^"]*)"'),
                         ("twitter:description", r'<meta name="twitter:description" content="([^"]*)"')):
            check(html.unescape(re.search(pat, s).group(1)) == d, f"{tag} ≠ description: {f}")
        cans = re.findall(r'<link rel="canonical" href="([^"]+)"', s)
        check(len(cans) == 1, f"{len(cans)} canonical tags: {f}")
        ogu = re.search(r'<meta property="og:url" content="([^"]+)"', s)
        check(ogu and cans and ogu.group(1) == cans[0], f"og:url ≠ canonical: {f}")
        if cans:
            rel = cans[0].replace(BASE, "").strip("/")
            check(os.path.isfile(os.path.join(rel, "index.html") if rel else "index.html"), f"canonical target missing: {f} -> {cans[0]}")
        # JSON-LD
        for blob in re.findall(r'<script type="application/ld\+json"[^>]*>(.*?)</script>', s, re.S):
            try:
                j = json.loads(blob)
            except Exception as e:
                check(False, f"JSON-LD parse error: {f}: {e}")
                continue
            for node in j.get("@graph", []):
                if node.get("@type") == "WebPage":
                    check(node.get("description") == d, f"WebPage.description ≠ meta description: {f}")
    check(hm == {HEADER_MD5}, f"header md5 set = {hm}")
    check(fm == {FOOTER_MD5}, f"footer md5 set = {fm}")
    check(n_noindex == 0, f"noindex on {n_noindex} pages (must be 0 — site is live)")
    check(n_schema == EXPECT["page-schema"], f"page-schema on {n_schema} pages")
    check(n_old == 0, f"old domain in {n_old} pages")
    check(n_demo == 0, f"demo video ids in {n_demo} pages")

    # duplicates are allowed only between a page and its canonical twin
    def twins(v):
        cans = {re.search(r'<link rel="canonical" href="([^"]+)"', pages[x]).group(1) for x in v}
        return len(cans) == 1
    dup_t = {t: v for t, v in titles.items() if len(v) > 1 and not twins(v)}
    dup_d = {d: v for d, v in descs.items() if len(v) > 1 and not twins(v)}
    check(not dup_t, "duplicate titles: " + "; ".join(f"{v}" for v in list(dup_t.values())[:5]))
    check(not dup_d, "duplicate descriptions: " + "; ".join(f"{v}" for v in list(dup_d.values())[:5]))

    # sitemap / robots
    sm = read("sitemap.xml")
    locs = re.findall(r"<loc>(.*?)</loc>", sm)
    check(len(locs) == EXPECT["sitemap"], f"sitemap has {len(locs)} URLs (≠ {EXPECT['sitemap']})")
    blocked = [l.split("Disallow:")[1].strip() for l in read("robots.txt").splitlines() if l.startswith("Disallow:")]
    for u in locs:
        rel = u.replace(BASE, "").strip("/")
        check(os.path.isfile(os.path.join(rel, "index.html") if rel else "index.html"), f"sitemap URL missing locally: {u}")
        check(not any(("/" + rel + "/").startswith(b.replace("*", "")) for b in blocked if not b.startswith("/*")), f"sitemap URL blocked by robots: {u}")
        page = pages.get((rel + "/index.html") if rel else "index.html")
        if page:
            can = re.search(r'<link rel="canonical" href="([^"]+)"', page).group(1)
            check(can == u, f"sitemap URL is not self-canonical: {u} -> {can}")
    check(os.path.isfile("llms.txt"), "llms.txt missing")

    long_titles = sorted((len(html.unescape(re.search(r"<title>(.*?)</title>", s).group(1))), f)
                         for f, s in pages.items() if f.startswith("truong/"))
    print(f"pages {len(files)} · header md5 {len(hm)} · footer md5 {len(fm)} · noindex {n_noindex} · page-schema {n_schema}"
          f" · sitemap {len(locs)} · truong titles > 65: {sum(1 for n, _ in long_titles if n > 65)} (max {long_titles[-1][0]})")
    if bad:
        print(f"FAIL — {len(bad)} problem(s):")
        for b in bad[:60]:
            print("  -", b)
        return 1
    print("OK — all invariants hold")
    return 0


if __name__ == "__main__":
    sys.exit(main())
