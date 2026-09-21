#!/usr/bin/env python3
"""Kiểm tra site WordPress sau khi chuyển đổi: mọi URL trong sitemap phải còn sống và đúng.

Vì sao cần: 723 URL trong `sitemap.xml` đã nộp cho Google ngày 19/09. Chuyển nền tảng mà URL nào
chết là mất thứ hạng trang đó. Script gọi từng URL (không theo redirect) và so với bản tĩnh cũ.

Kiểm mỗi URL: HTTP 200 · có `<title>` không rỗng, không chứa `…` · có `<link rel="canonical">`
trỏ đúng chính nó · có thẻ mô tả · không lộ lỗi PHP/CSDL · vẫn mang tên tổ chức đúng ·
không còn chuỗi "Duy Study" ngoài 4 trang sự kiện (bài viết của Duy Study, cố ý giữ).

Dùng: python tools/wp-verify.py            # toàn bộ sitemap
      python tools/wp-verify.py 60         # chỉ 60 URL đầu (chạy nhanh)"""
import concurrent.futures, html, os, re, sys, time, urllib.error, urllib.request

try:
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
except Exception:
    pass

TGT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
BASE = "https://duhoctesolhcmc.vn"
ORG = "Ban Du học Hội TESOL TP.HCM"
OLD_BRAND = "Duy Study"
OLD_PHONE = "0909542539"
# 4 bài tường thuật sự kiện do Duy Study tổ chức -> được phép nhắc tên Duy Study trong nội dung
EXPECT_301 = {   # 5 trang lưu trữ phân loại rỗng: theme cố ý không index, .htaccess 301 về trang thật
    "/bac-hoc/cao-dang/": "/truong/", "/bac-hoc/dai-hoc/": "/truong/",
    "/gia-tri-hoc-bong/25/": "/hoc-bong/", "/gia-tri-hoc-bong/50/": "/hoc-bong/",
    "/gia-tri-hoc-bong/toan-phan/": "/hoc-bong/",
}
# 14 trang dữ liệu mẫu đã gỡ ngày 21/09/2026: 7 học bổng bịa (tên chương trình,
# giá trị, deadline đều tự đặt) và 7 câu chuyện học sinh bịa. Chúng vẫn nằm trong
# sitemap.xml tĩnh nộp hôm 19/09 nên phải khai báo là ĐÃ XOÁ, nếu không script báo lỗi.
EXPECT_GONE = {
    "/hoc-bong/early-bird-ca/", "/hoc-bong/future-leaders/", "/hoc-bong/global-excellence/",
    "/hoc-bong/international-merit/", "/hoc-bong/principal-50/", "/hoc-bong/stem-30/",
    "/hoc-bong/turkiye-pathway/",
    "/hoc-sinh/an-nhien/", "/hoc-sinh/khuong-duy/", "/hoc-sinh/minh-anh/",
    "/hoc-sinh/quoc-bao/", "/hoc-sinh/thao-vy/", "/hoc-sinh/thao-vy-video/",
    "/hoc-sinh/tuong-van/",
}
ALLOW_BRAND = {"/",                       # trang chủ liệt kê tiêu đề sự kiện do Duy Study tổ chức
               "/quoc-gia/uc/", "/quoc-gia/uc/video/", "/quoc-gia/my/video/",
               "/quoc-gia/canada/video/",  # tiêu đề video từ kênh YouTube Duy Study — nội dung thật
               "/su-kien/hoi-thao-lien-minh-tieng-anh-binh-phuoc-2025/",
               "/su-kien/le-ky-ket-mou-dai-hoc-gia-dinh/",
               "/su-kien/mo-khoa-tuoi-18-2026-uef/",
               "/su-kien/su-kien-du-hoc-bmi-2025/",
               "/su-kien/gdu-tuan-le-cong-dan/",
               "/su-kien/hoi-xuan-thpt-trung-vuong-2024/",
               "/su-kien/uef-miss-uef-2024/",
               "/su-kien/uef-workshop-from-home-to-globe-2024/",
               "/su-kien/workshop-bi-kip-san-hoc-bong-2025-07-19/",
               "/su-kien/"}


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k):
        return None


OPENER = urllib.request.build_opener(NoRedirect)


def check(url):
    path = url.replace(BASE, "") or "/"
    for _ in range(3):
        try:
            req = urllib.request.Request(url, headers={"User-Agent": "wp-verify/1.0",
                                                       "Cache-Control": "no-cache"})
            with OPENER.open(req, timeout=40) as r:
                body = r.read().decode("utf-8", "replace")
                code = r.status
            break
        except urllib.error.HTTPError as e:
            code, body = e.code, ""
            break
        except Exception:
            code, body = 0, ""
            time.sleep(1)
    problems = []
    if path in EXPECT_301:
        return path, code, [] if code == 301 else [f"đáng lẽ 301 nhưng nhận HTTP {code}"]
    if path in EXPECT_GONE:
        return path, code, [] if code in (404, 410) else [f"đáng lẽ đã xoá nhưng nhận HTTP {code}"]
    if code != 200:
        return path, code, [f"HTTP {code}"]
    t = re.search(r"<title>(.*?)</title>", body, re.S)
    title = html.unescape(t.group(1)).strip() if t else ""
    if not title:
        problems.append("thiếu <title>")
    elif "…" in title:
        problems.append(f"title bị cắt: {title[:60]}")
    can = re.search(r'<link rel="canonical" href="([^"]+)"', body)
    if not can:
        problems.append("thiếu canonical")
    elif can.group(1).rstrip("/") != url.rstrip("/"):
        problems.append(f"canonical lệch -> {can.group(1)}")
    if not re.search(r'<meta name="description" content="[^"]{20,}"', body):
        problems.append("thiếu/ngắn meta description")
    for bad in ("Fatal error", "Parse error", "Warning:", "Notice:", "WordPress database error"):
        if bad in body:
            problems.append(f"lỗi PHP: {bad}")
    if ORG not in body:
        problems.append("không thấy tên tổ chức")
    if OLD_BRAND in body and path not in ALLOW_BRAND:
        problems.append("còn chữ Duy Study")
    if OLD_PHONE in body:
        problems.append("còn số điện thoại cũ")
    return path, code, problems


def main(argv):
    limit = int(argv[0]) if argv and argv[0].isdigit() else None
    sm = open(os.path.join(TGT, "sitemap.xml"), encoding="utf-8").read()
    urls = re.findall(r"<loc>(.*?)</loc>", sm)
    if limit:
        urls = urls[:limit]
    print(f"Kiểm {len(urls)} URL trên {BASE} …")
    t = time.time()
    ok = 0
    bad = []
    with concurrent.futures.ThreadPoolExecutor(max_workers=6) as ex:
        for path, code, problems in ex.map(check, urls):
            if problems:
                bad.append((path, code, problems))
            else:
                ok += 1
    print(f"  OK {ok}/{len(urls)} · có vấn đề {len(bad)} · {time.time()-t:.0f}s")
    by = {}
    for path, code, problems in bad:
        for p in problems:
            by.setdefault(re.sub(r"[:→].*", "", p).strip(), []).append(path)
    for kind, paths in sorted(by.items(), key=lambda x: -len(x[1])):
        print(f"\n  [{len(paths)}] {kind}")
        for p in paths[:6]:
            print("      ", p)
    return 1 if bad else 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
