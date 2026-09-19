#!/usr/bin/env python3
"""Sinh llms.txt (https://llmstxt.org) ở gốc repo từ title/description thật của các trang.
Chạy lại mỗi khi đổi cấu trúc site hoặc description các trang hub. Dùng: python tools/build-llms.py"""
import glob, html, os, re, sys

try:
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
except Exception:
    pass

TGT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
BASE = "https://duhoctesolhcmc.vn"
ORG = "Ban Du học Hội TESOL TP.HCM"
COUNTRIES = [("my", "Mỹ"), ("uc", "Úc"), ("canada", "Canada"), ("anh", "Anh"), ("new-zealand", "New Zealand"),
             ("singapore", "Singapore"), ("malaysia", "Malaysia"), ("han-quoc", "Hàn Quốc"), ("duc", "Đức"),
             ("ha-lan", "Hà Lan"), ("thuy-sy", "Thụy Sỹ"), ("tho-nhi-ky", "Thổ Nhĩ Kỳ"), ("philippines", "Philippines")]
LEVELS = [("dai-hoc", "Đại học"), ("cao-dang", "Cao đẳng"), ("thpt", "THPT"), ("anh-ngu", "Anh ngữ"), ("sau-dai-hoc", "Sau đại học")]
STUBS = {"cntt", "kinh-doanh", "ky-thuat", "y-suc-khoe"}


def read(p):
    with open(p, encoding="utf-8", newline="") as f:
        return f.read()


def meta(rel):
    p = os.path.join(TGT, rel, "index.html") if rel else os.path.join(TGT, "index.html")
    s = read(p)
    t = html.unescape(re.search(r"<title>(.*?)</title>", s, re.S).group(1)).split(" — ")[0].strip()
    d = html.unescape(re.search(r'<meta name="description" content="([^"]*)"', s).group(1))
    h = re.search(r"<h1[^>]*>(.*?)</h1>", s, re.S)
    h = html.unescape(re.sub(r"<[^>]+>", "", h.group(1))).strip() if h else t
    n = len(set(re.findall(r'href="/truong/([^"/]+)/"', s)))
    return t, d, h, n


def line(rel, label=None, note=None):
    t, d, h, n = meta(rel)
    url = f"{BASE}/{rel}/" if rel else f"{BASE}/"
    return f"- [{label or t}]({url}): {note or d}"


def main():
    n_schools = len(glob.glob(os.path.join(TGT, "truong", "*", "index.html")))
    out = [f"# {ORG} — Du học",
           "",
           f"> Website tư vấn du học bằng tiếng Việt của {ORG}: {n_schools} trang trường tại 13 quốc gia "
           "(THPT, cao đẳng, đại học, sau đại học, Anh ngữ), lộ trình du học, học bổng, sự kiện và cẩm nang. "
           "Hotline 0906.510.747 · văn phòng TP.HCM, Đà Nẵng, Buôn Ma Thuột.",
           "",
           "Lưu ý khi trích dẫn:",
           "- Học phí, giá trị học bổng và deadline trên site là số liệu tham khảo (cập nhật 2026); luôn xác nhận lại với trường.",
           "- Mỗi trang trường có JSON-LD (CollegeOrUniversity / HighSchool / EducationalOrganization + BreadcrumbList) kèm website chính thức của trường trong `url` / `sameAs`.",
           f"- Ngôn ngữ: tiếng Việt (vi-VN). Toàn bộ URL: {BASE}/sitemap.xml",
           "",
           "## Trang chính",
           line("", "Trang chủ"),
           line("truong", f"Danh sách {n_schools} trường"),
           line("quoc-gia", "Quốc gia du học"),
           line("lo-trinh-du-hoc"),
           line("hoc-bong"),
           line("nganh-hoc"),
           line("su-kien"),
           line("tin-tuc"),
           line("dich-vu"),
           line("ve-chung-toi"),
           line("lien-he"),
           line("tai-cam-nang"),
           "",
           "## Quốc gia"]
    for slug, name in COUNTRIES:
        t, d, h, n = meta(f"quoc-gia/{slug}")
        out.append(f"- [Du học {name}]({BASE}/quoc-gia/{slug}/): {n} trường. {d}")
    out += ["", "## Bậc học theo quốc gia (trang có danh sách trường)"]
    for slug, name in COUNTRIES:
        for lv, lvname in LEVELS:
            rel = f"quoc-gia/{slug}/{lv}"
            if os.path.isfile(os.path.join(TGT, rel, "index.html")):
                t, d, h, n = meta(rel)
                if n:                       # description already states the count
                    out.append(f"- [{lvname} {name}]({BASE}/{rel}/): {d}")
    out += ["", "## Lộ trình du học"]
    for rel in sorted(glob.glob(os.path.join(TGT, "lo-trinh-du-hoc", "*", "index.html"))):
        out.append(line(os.path.relpath(os.path.dirname(rel), TGT).replace("\\", "/")))
    out += ["", "## Ngành học"]
    for rel in sorted(glob.glob(os.path.join(TGT, "nganh-hoc", "*", "index.html"))):
        slug = os.path.basename(os.path.dirname(rel))
        if slug not in STUBS:
            out.append(line(f"nganh-hoc/{slug}"))
    out += ["", "## Cẩm nang / tin tức"]
    for rel in sorted(glob.glob(os.path.join(TGT, "tin-tuc", "*", "index.html"))):
        out.append(line(f"tin-tuc/{os.path.basename(os.path.dirname(rel))}"))
    out += ["", "## Học bổng (tham khảo, cần xác nhận với trường)"]
    for rel in sorted(glob.glob(os.path.join(TGT, "hoc-bong", "*", "index.html"))):
        out.append(line(f"hoc-bong/{os.path.basename(os.path.dirname(rel))}"))
    out += ["", "## Sự kiện đã tổ chức"]
    for rel in sorted(glob.glob(os.path.join(TGT, "su-kien", "*", "index.html"))):
        out.append(line(f"su-kien/{os.path.basename(os.path.dirname(rel))}"))
    out += ["", "## Optional", "", "### Video du học theo quốc gia"]
    for slug, name in COUNTRIES:
        rel = f"quoc-gia/{slug}/video"
        if os.path.isfile(os.path.join(TGT, rel, "index.html")):
            out.append(line(rel, f"Video du học {name}"))
    out += ["", "### Câu chuyện học sinh"]
    for rel in sorted(glob.glob(os.path.join(TGT, "hoc-sinh", "*", "index.html"))):
        r = f"hoc-sinh/{os.path.basename(os.path.dirname(rel))}"
        t, d, h, n = meta(r)
        out.append(f"- [{h}]({BASE}/{r}/): {d}")
    txt = "\n".join(out) + "\n"
    with open(os.path.join(TGT, "llms.txt"), "w", encoding="utf-8", newline="") as f:
        f.write(txt)
    print(f"llms.txt: {len(txt.encode('utf-8'))} bytes · {sum(1 for l in out if l.startswith('- ['))} links")


if __name__ == "__main__":
    main()
