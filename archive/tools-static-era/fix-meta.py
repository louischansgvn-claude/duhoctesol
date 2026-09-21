#!/usr/bin/env python3
"""Sửa SEO meta cho toàn site (2026-09-19). Dry-run mặc định, `--apply` để ghi.
Idempotent: chạy lại lần 2 không đổi file nào.

Việc làm:
  1. <title> trang trường bị generator cũ cắt cứng ở 68 ký tự (102 trang: 32 có '…', 70 cắt giữa từ)
     -> dựng lại theo công thức "<tên chính thức> | <bậc> <quốc gia>", KHÔNG BAO GIỜ cắt tên trường,
        ưu tiên giữ tên tắt (LSE, UNSW Sydney...), rút gọn phần đuôi cho đến khi <= 65 ký tự.
     61 title đúng nhưng viết "Du học thpt Úc" -> "Du học THPT Úc".
  2. meta description 129 trang THPT Mỹ bị cắt dở câu ('..., yêu cầu đầu vào,…')
     -> dựng lại từ khối THÔNG TIN NHANH của chính trang (loại trường, bang, dải lớp, học phí).
  3. 158 trang dùng chung description (20 nhóm) -> mỗi trang 1 câu riêng
     (trang quốc gia × bậc học có số trường thật, video/tin tức lấy đoạn dẫn của trang,
      sự kiện/học bổng/học sinh/archive viết tay trong MAP bên dưới).
  4. 35 trang có 2 thẻ <link rel="canonical"> (9 trang sự kiện trỏ nhầm về /su-kien/canada-fair/ đã xoá)
     -> giữ thẻ đầu, xoá thẻ sau.
  5. 4 trang /nganh-hoc/ là stub "Không tìm thấy trang" (cntt, kinh-doanh, ky-thuat, y-suc-khoe)
     -> canonical + og:url trỏ về trang ngành thật, bỏ khỏi sitemap.xml.
  6. 3 trang học sinh dùng title chung "Câu chuyện học sinh" -> "<tên> — Ban Du học Hội TESOL TP.HCM".
og:title / twitter:title luôn = <title>; og:description / twitter:description luôn = description.
Khối JSON-LD site-wide KHÔNG đụng. Sau khi --apply phải chạy lại `tools/add-schema.py --apply`
để WebPage.description trong khối id="page-schema" đồng bộ.
"""
import collections, glob, html, importlib.util, json, os, re, sys

try:
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
except Exception:
    pass

TGT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
BASE = "https://duhoctesolhcmc.vn"
ORG = "Ban Du học Hội TESOL TP.HCM"
TITLE_MAX, DESC_MAX = 65, 160

_spec = importlib.util.spec_from_file_location("addschema", os.path.join(TGT, "tools", "add-schema.py"))
A = importlib.util.module_from_spec(_spec)
_spec.loader.exec_module(A)

COUNTRY = {"my": "Mỹ", "uc": "Úc", "canada": "Canada", "anh": "Anh", "new-zealand": "New Zealand",
           "duc": "Đức", "ha-lan": "Hà Lan", "singapore": "Singapore", "han-quoc": "Hàn Quốc",
           "tho-nhi-ky": "Thổ Nhĩ Kỳ", "malaysia": "Malaysia", "philippines": "Philippines",
           "thuy-sy": "Thụy Sỹ"}
LVN = {"THPT": "THPT", "Đại học": "đại học", "Cao đẳng": "cao đẳng", "Anh ngữ": "Anh ngữ"}
LV_OLD = {"THPT": "thpt", "Đại học": "đại học", "Cao đẳng": "cao đẳng", "Anh ngữ": "anh ngữ"}

# ---------------------------------------------------------------- hand-written copy
EVENTS = {
    "gdu-tuan-le-cong-dan": "Duy Study và Cella English đồng hành cùng Tuần lễ Công dân GDU 2024: tư vấn du học, học bổng và lộ trình hồ sơ cho sinh viên Đại học Gia Định. Xem lại hình ảnh.",
    "hoi-thao-lien-minh-tieng-anh-binh-phuoc-2025": "Hội thảo Giấc Mơ Du Học 26/10/2025 tại Đồng Xoài, Bình Phước: thông tin du học Mỹ, Úc, Canada, Singapore, Malaysia, Hàn Quốc cùng đại diện tuyển sinh.",
    "hoi-xuan-thpt-trung-vuong-2024": "Gian hàng tư vấn du học của Duy Study tại Hội Xuân THPT Trưng Vương 2024: tư vấn lộ trình, học bổng và giải đáp thắc mắc cho học sinh. Xem lại hình ảnh.",
    "le-ky-ket-mou-dai-hoc-gia-dinh": "Ngày 19/09/2024, Duy Study và Đại học Gia Định ký kết MOU hợp tác chiến lược về giáo dục quốc tế, mở thêm cơ hội du học cho sinh viên. Hình ảnh buổi lễ.",
    "mo-khoa-tuoi-18-2026-uef": "Ngày 03/01/2026, Duy Study đồng hành cùng UEF tại Ngày hội Mở Khoá Tuổi 18 cho học sinh THPT TP.HCM: tư vấn lộ trình du học Úc, Mỹ, Canada, châu Âu, Hàn Quốc.",
    "su-kien-du-hoc-bmi-2025": "Ngày 18-19/10/2025, Duy Study cùng Ontario Eschool tham gia sự kiện du học BMI TP.HCM: chương trình học online lấy bằng THPT OSSD của Canada.",
    "uef-miss-uef-2024": "Duy Study đồng hành cùng chung kết Miss UEF 2024: chúc mừng Hoa khôi Nguyễn Phương Hồng Thắm và các Á khôi, giới thiệu cơ hội du học tới sinh viên UEF.",
    "uef-workshop-from-home-to-globe-2024": "Workshop “From Home To Globe” 2024 do UEF phối hợp Duy Study tổ chức: định hướng du học và học bổng sau tốt nghiệp cho sinh viên UEF. Xem lại hình ảnh.",
    "workshop-bi-kip-san-hoc-bong-2025-07-19": "Workshop Săn học bổng triệu USD ngày 19/07/2025 do Duy Study tổ chức: bí kíp xây hồ sơ, chọn trường và săn học bổng cho học sinh, phụ huynh. Xem lại hình ảnh.",
}
STUDENTS = {  # slug: (school, country)
    "an-nhien": ("Arizona State University", "Mỹ"), "khuong-duy": ("UTS Sydney", "Úc"),
    "minh-anh": ("University of Sydney", "Úc"), "quoc-bao": ("University of Toronto", "Canada"),
    "thao-vy": ("University of Auckland", "New Zealand"), "thao-vy-video": ("RMIT University", "Úc"),
    "tuong-van": ("University of Sydney", "Úc"),
}
ARCHIVES = {
    "bac-hoc/cao-dang": "Bài viết, kinh nghiệm và học bổng cho du học bậc cao đẳng: chọn trường, chi phí, liên thông lên đại học và cơ hội việc làm sau tốt nghiệp.",
    "bac-hoc/dai-hoc": "Bài viết, kinh nghiệm và học bổng cho du học bậc đại học: chọn ngành, chọn trường, chi phí, hồ sơ và visa theo từng quốc gia.",
    "category/hoc-bong": "Chuyên mục Học bổng: phân loại học bổng, deadline, cách xây hồ sơ và mẹo săn học bổng du học theo từng quốc gia.",
    "category/kinh-nghiem": "Chuyên mục Kinh nghiệm: chi phí sinh hoạt, văn hoá, bảo hiểm, ngân sách và những điều du học sinh cần biết trước khi lên đường.",
    "category/visa": "Chuyên mục Visa: cập nhật chính sách visa du học Úc, Mỹ, Canada và các bước chuẩn bị hồ sơ visa cho từng kỳ nhập học.",
    "gia-tri-hoc-bong/25": "Bài viết về học bổng 25% học phí: trường cấp, điều kiện, deadline và cách chuẩn bị hồ sơ để tăng cơ hội nhận học bổng.",
    "gia-tri-hoc-bong/50": "Bài viết về học bổng 50% học phí: trường cấp, điều kiện, deadline và cách chuẩn bị hồ sơ để tăng cơ hội nhận học bổng.",
    "gia-tri-hoc-bong/toan-phan": "Bài viết về học bổng toàn phần: trường cấp, điều kiện cạnh tranh, deadline và cách xây hồ sơ có câu chuyện rõ, số liệu cụ thể.",
    "loai-su-kien/hoi-thao": f"Các bài viết và sự kiện dạng hội thảo du học: lịch tổ chức, nội dung chính và cách đăng ký tham dự cùng {ORG}.",
    "loai-su-kien/online": f"Các bài viết và sự kiện du học tổ chức online (webinar): lịch, nội dung và cách đăng ký tham dự cùng {ORG}.",
    "loai-su-kien/van-phong": f"Các bài viết và buổi tư vấn du học trực tiếp tại văn phòng TP.HCM, Đà Nẵng, Buôn Ma Thuột của {ORG}.",
    "quoc-gia-filter/canada": "Tin tức và kinh nghiệm du học Canada: lộ trình sau tốt nghiệp, PGWP, học bổng, chi phí và hồ sơ visa.",
    "quoc-gia-filter/my": "Tin tức và kinh nghiệm du học Mỹ: chi phí sinh hoạt, bảo hiểm, văn hoá campus, ngành công nghệ thông tin và học bổng.",
    "quoc-gia-filter/new-zealand": "Tin tức và kinh nghiệm du học New Zealand: học bổng, chi phí, môi trường học an toàn và hồ sơ visa.",
    "quoc-gia-filter/tho-nhi-ky": "Tin tức và kinh nghiệm du học Thổ Nhĩ Kỳ: chương trình học bằng tiếng Anh, học phí cạnh tranh và học bổng pathway.",
    "quoc-gia-filter/uc": "Tin tức và kinh nghiệm du học Úc: chính sách visa 500, phân loại học bổng, deadline và cách chuẩn bị hồ sơ.",
}
LANDINGS = {  # 3 trang quốc gia có description sẵn dài > 160 ký tự -> rút gọn, giữ ý
    "quoc-gia/anh": "Du học Anh: đại học lâu đời, bằng cấp được công nhận rộng; cử nhân 3 năm, thạc sĩ 1 năm giúp rút ngắn thời gian và chi phí. Chuẩn bị hồ sơ sớm theo lịch UCAS.",
    "quoc-gia/philippines": "Philippines phù hợp với học sinh cần nâng tiếng Anh nhanh trước khóa chính: lớp một kèm một, độ dài khóa linh hoạt và chi phí thấp đã gồm ăn ở tại ký túc.",
    "quoc-gia/thuy-sy": "Thụy Sỹ mạnh về quản trị khách sạn, du lịch và tài chính, mô hình học gắn kỳ thực tập. Học phí và sinh hoạt phí thuộc nhóm cao, cần dự trù ngân sách kỹ từ đầu.",
}
STUBS = {  # slug: (canonical target slug, title, description)
    "cntt": ("cong-nghe", "Ngành Công nghệ thông tin",
             "Nội dung ngành Công nghệ thông tin đã được gộp vào trang Ngành Công nghệ: CNTT, khoa học dữ liệu, AI, kỹ thuật phần mềm, trường tiêu biểu và cơ hội nghề nghiệp."),
    "kinh-doanh": ("kinh-te", "Ngành Kinh doanh",
                   "Nội dung ngành Kinh doanh đã được gộp vào trang Ngành Kinh tế: kinh doanh, tài chính, quản trị, marketing, trường tiêu biểu và cơ hội nghề nghiệp."),
    "ky-thuat": ("cong-nghe", "Ngành Kỹ thuật",
                 "Nội dung ngành Kỹ thuật đã được gộp vào trang Ngành Công nghệ: kỹ thuật, công nghệ, khoa học dữ liệu, trường tiêu biểu và cơ hội nghề nghiệp."),
    "y-suc-khoe": ("suc-khoe", "Ngành Y – Sức khoẻ",
                   "Nội dung ngành Y – Sức khoẻ đã được gộp vào trang Ngành Sức khoẻ: y, điều dưỡng, dược, y tế công cộng, trường tiêu biểu và cơ hội định cư."),
}


# ---------------------------------------------------------------- helpers
def read(p):
    with open(p, encoding="utf-8", newline="") as f:
        return f.read()


def write(p, s):
    with open(p, "w", encoding="utf-8", newline="") as f:
        f.write(s)


def text(s):
    return html.unescape(re.sub(r"\s+", " ", re.sub(r"<[^>]+>", "", s))).strip()


def esc_text(s):            # inside <title>…</title>
    return html.escape(s, quote=False)


def esc_attr(s):            # inside content="…"
    return html.escape(s, quote=False).replace('"', "&quot;")


def get(page, pat):
    m = re.search(pat, page, re.S)
    return html.unescape(m.group(1)) if m else ""


def cur_title(page):
    return get(page, r"<title>(.*?)</title>")


def cur_desc(page):
    return get(page, r'<meta name="description" content="([^"]*)"')


def h1(page):
    m = re.search(r"<h1[^>]*>(.*?)</h1>", page, re.S)
    return text(m.group(1)) if m else ""


def lead(page, min_len=40):
    """first <p> after the <h1> with at least min_len chars"""
    pos = page.find("</h1>")
    for p in re.findall(r"<p[^>]*>(.*?)</p>", page[pos:], re.S):
        t = text(p)
        if len(t) >= min_len:
            return t
    return ""


def set_title(page, t):
    page = re.sub(r"<title>.*?</title>", lambda m: f"<title>{esc_text(t)}</title>", page, count=1, flags=re.S)
    page = re.sub(r'(<meta property="og:title" content=")[^"]*(")', lambda m: m.group(1) + esc_attr(t) + m.group(2), page, count=1)
    page = re.sub(r'(<meta name="twitter:title" content=")[^"]*(")', lambda m: m.group(1) + esc_attr(t) + m.group(2), page, count=1)
    return page


def set_desc(page, d):
    for pat in (r'(<meta name="description" content=")[^"]*(")',
                r'(<meta property="og:description" content=")[^"]*(")',
                r'(<meta name="twitter:description" content=")[^"]*(")'):
        page = re.sub(pat, lambda m: m.group(1) + esc_attr(d) + m.group(2), page, count=1)
    return page


def dedupe_canonical(page):
    """keep the first <link rel="canonical">, drop every later one (with its line break)"""
    pat = re.compile(r'\n?[ \t]*<link rel="canonical" href="[^"]+"[^>]*>')
    hits = list(pat.finditer(page))
    for m in reversed(hits[1:]):
        page = page[:m.start()] + page[m.end():]
    return page, len(hits) - 1


def set_canonical(page, url):
    page = re.sub(r'(<link rel="canonical" href=")[^"]+(")', lambda m: m.group(1) + url + m.group(2), page, count=1)
    page = re.sub(r'(<meta property="og:url" content=")[^"]+(")', lambda m: m.group(1) + url + m.group(2), page, count=1)
    return page


# ---------------------------------------------------------------- school pages
def load_schools():
    schools = {}
    for f in A.SOURCES:
        for r in json.load(open(os.path.join(A.DATA, f), encoding="utf-8")):
            schools[r["post_slug"]] = r
    for r in json.load(open(os.path.join(A.DATA, "duystudy_country_level_index.json"), encoding="utf-8")):
        schools[r["post_slug"]].update(institution_name=r["institution_name"], level=r["level"],
                                       country=r["country"], state=r.get("state"))
    return schools


def facts(page):
    f = {}
    for k, pat in (("type", r"<li>Loại trường: ([^<]*)</li>"), ("state", r"<li>Bang: ([^<]*)</li>"),
                   ("grades", r"<li>Bậc lớp nhận học sinh: ([^<]*)</li>"),
                   ("price", r"<li>Học phí chương trình tham khảo từ: ([\d.,]+) USD</li>")):
        m = re.search(pat, page)
        f[k] = html.unescape(m.group(1)).strip() if m else None
    return f


def expected_old_title(r, price):
    """the formula the old generator used BEFORE it hard-cut at 68 chars"""
    name = r["institution_name"].strip()
    if r["country"] == "Mỹ" and r["level"] == "THPT":
        return f"{name} | THPT Mỹ tại {r['state']} từ {price or '?'} USD"
    if r["country"] == "Mỹ":
        return f"{name} | Du học {LV_OLD[r['level']]} Mỹ tại {r['state']}"
    return f"{name} | Du học {LV_OLD[r['level']]} {r['country']}"


def fit_title(r, price):
    """Never cut the school name. Prefer keeping the real acronym (LSE, UNSW Sydney, BCIT...):
    pass 1 = with acronym <= 70 · pass 2 = plain name <= 65 · pass 3 = acronym + shortest suffix <= 75
    · else plain name + shortest suffix (Google truncates the display, nothing is cut in the source)."""
    name, alt = A.clean_name(r["institution_name"])
    withalt = f"{name} ({alt})" if alt and alt not in name else None
    C, lvl, st = r["country"], r["level"], r.get("state") or ""
    if C == "Mỹ" and lvl == "THPT":
        sfx = [f"THPT Mỹ tại {st} từ {price} USD" if price else f"THPT Mỹ tại {st}", f"THPT Mỹ tại {st}", "THPT Mỹ"]
    elif C == "Mỹ":
        sfx = [f"Du học {LVN[lvl]} Mỹ tại {st}", f"Du học {LVN[lvl]} Mỹ", "Du học Mỹ"]
    else:
        sfx = [f"Du học {LVN[lvl]} {C}", f"Du học {C}"]
    if withalt:
        for s in sfx:
            if len(f"{withalt} | {s}") <= TITLE_MAX + 5:
                return f"{withalt} | {s}"
    for s in sfx:
        if len(f"{name} | {s}") <= TITLE_MAX:
            return f"{name} | {s}"
    if withalt and len(f"{withalt} | {sfx[-1]}") <= TITLE_MAX + 10:
        return f"{withalt} | {sfx[-1]}"
    return f"{name} | {sfx[-1]}"


def grades_vi(g):
    nums = re.findall(r"\d+", g or "")
    pg = "PG" in (g or "")
    if len(nums) >= 2:
        s = f"lớp {nums[0]}-{nums[1]}"
    elif nums:
        s = f"lớp {nums[0]}-12" if pg else f"lớp {nums[0]}"
    else:
        return ""
    return s + (" và năm PG" if pg else "")


def us_hs_desc(r, page):
    f = facts(page)
    name, _ = A.clean_name(r["institution_name"])
    ty = (f["type"] or "").lower()
    district = A.school_type("THPT", name) == "EducationalOrganization"
    if "public" in ty:
        kind = "khu học chánh công lập" if district else "trường THPT công lập"
    elif "boarding" in ty:
        kind = "trường THPT tư thục nội trú"
    elif "private" in ty:
        kind = "trường THPT tư thục"
    else:
        kind = "chương trình THPT" if district else "trường THPT"
    st, g, price = f["state"] or r.get("state") or "", grades_vi(f["grades"]), f["price"]
    fee = f", học phí tham khảo từ {price} USD" if price else ""
    gr = f", nhận du học sinh {g}" if g else ""
    cands = [
        f"{name} là {kind} tại {st}, Mỹ{gr}{fee}. Xem chương trình học, yêu cầu đầu vào, hình ảnh trường và cách nộp hồ sơ.",
        f"{name}: {kind} tại {st}, Mỹ{gr}{fee}. Chương trình học, yêu cầu đầu vào và hình ảnh trường.",
        f"{name}: {kind} tại {st}, Mỹ{', ' + g if g else ''}{fee}. Chương trình học và yêu cầu đầu vào.",
        f"{name}: {kind} tại {st}, Mỹ{fee}.",
    ]
    for c in cands:
        if len(c) <= DESC_MAX:
            return c
    return cands[-1]


# ---------------------------------------------------------------- hub pages
def pick(cands):
    for c in cands:
        if len(c) <= DESC_MAX:
            return c
    return cands[-1]


def level_desc(level, C, n):
    if level == "dai-hoc":
        return pick([f"Du học đại học {C}: {n} trường đại học tiêu biểu kèm học phí tham khảo, yêu cầu đầu vào và học bổng. Ai nên chọn bậc đại học và checklist hồ sơ.",
                     f"Du học đại học {C}: {n} trường đại học tiêu biểu, học phí tham khảo, yêu cầu đầu vào và học bổng. Checklist hồ sơ cần chuẩn bị."] if n else
                    [f"Du học đại học {C}: cách chọn ngành và trường, học phí tham khảo, yêu cầu đầu vào, học bổng và checklist hồ sơ. Tư vấn lộ trình cùng {ORG}.",
                     f"Du học đại học {C}: cách chọn ngành và trường, học phí tham khảo, yêu cầu đầu vào, học bổng và checklist hồ sơ cần chuẩn bị."])
    if level == "cao-dang":
        return pick([f"Du học cao đẳng {C}: {n} trường cao đẳng, bách khoa tiêu biểu với chương trình thực hành, chi phí nhẹ hơn đại học và cơ hội liên thông. Ai nên chọn và checklist hồ sơ.",
                     f"Du học cao đẳng {C}: {n} trường cao đẳng, bách khoa tiêu biểu, chương trình thực hành, chi phí nhẹ hơn đại học và cơ hội liên thông. Checklist hồ sơ."] if n else
                    [f"Du học cao đẳng {C}: chương trình thực hành, chi phí nhẹ hơn đại học, cơ hội liên thông và việc làm. Ai nên chọn bậc cao đẳng và checklist hồ sơ cần chuẩn bị.",
                     f"Du học cao đẳng {C}: chương trình thực hành, chi phí nhẹ hơn đại học, cơ hội liên thông và việc làm. Ai nên chọn và checklist hồ sơ."])
    if level == "thpt":
        return pick([f"Du học THPT {C}: {n} trường trung học nhận du học sinh Việt Nam, học phí tham khảo, dải lớp và yêu cầu đầu vào. Lưu ý cho phụ huynh khi cho con du học sớm.",
                     f"Du học THPT {C}: {n} trường trung học nhận du học sinh Việt Nam, học phí tham khảo, dải lớp, yêu cầu đầu vào và lưu ý cho phụ huynh."] if n else
                    [f"Du học THPT {C}: điều kiện nhập học, chi phí tham khảo, nội trú hay homestay và lưu ý cho phụ huynh khi cho con du học sớm. Checklist hồ sơ cần chuẩn bị.",
                     f"Du học THPT {C}: điều kiện nhập học, chi phí tham khảo, nội trú hay homestay và lưu ý cho phụ huynh khi cho con du học sớm."])
    if level == "anh-ngu":
        return pick([f"Khoá Anh ngữ tại {C}: {n} trường Anh ngữ tiêu biểu, nâng tiếng Anh tập trung trước khoá chính, độ dài khoá linh hoạt, chi phí tham khảo. Checklist hồ sơ.",
                     f"Khoá Anh ngữ tại {C}: {n} trường Anh ngữ tiêu biểu, độ dài khoá linh hoạt, chi phí tham khảo và checklist hồ sơ."] if n else
                    [f"Khoá Anh ngữ tại {C}: nâng tiếng Anh tập trung trước khoá chính, độ dài khoá linh hoạt, chi phí tham khảo và cách chọn trường phù hợp. Checklist hồ sơ.",
                     f"Khoá Anh ngữ tại {C}: nâng tiếng Anh tập trung trước khoá chính, độ dài khoá linh hoạt, chi phí tham khảo và cách chọn trường."])
    if level == "sau-dai-hoc":
        return pick([f"Du học sau đại học {C}: chương trình thạc sĩ, tiến sĩ, yêu cầu đầu vào, học bổng nghiên cứu và định hướng nghề nghiệp sau tốt nghiệp. Ai nên chọn và checklist hồ sơ.",
                     f"Du học sau đại học {C}: thạc sĩ, tiến sĩ, yêu cầu đầu vào, học bổng nghiên cứu và định hướng nghề nghiệp. Ai nên chọn và checklist hồ sơ."])
    return None


def scholarship_desc(page):
    name = h1(page)
    first = lead(page, 20)                       # "5.000 CAD · University of Toronto · Deadline 05/08/2026"
    parts = [p.strip() for p in first.split("·")]
    if len(parts) != 3 or not parts[2].startswith("Deadline"):
        return None
    value, school, date = parts[0], parts[1], parts[2].replace("Deadline", "").strip()
    if value.split()[0] in ("Toàn", "Một", "Tới"):
        value = value[0].lower() + value[1:]
    label = name if name.lower().startswith("học bổng") else f"Học bổng {name}"
    return (f"{label}: {value} tại {school}, deadline tham khảo {date}. "
            f"Điều kiện, hồ sơ, cách nộp; cần xác nhận lại với trường.")


def student_desc(slug, page):
    school, country = STUDENTS[slug]
    return (f"Câu chuyện du học của {h1(page)} tại {school}, {country}: từ câu hỏi đầu tiên về trường, ngành, "
            f"học bổng đến hồ sơ hoàn chỉnh và checklist visa.")


def plan(rel, page, schools):
    """-> dict(title=?, desc=?, canonical=?) for this page, only keys that must change"""
    out = {}
    parts = rel.split("/")
    # ---- school pages
    if rel.startswith("truong/") and len(parts) == 3:
        slug = parts[1]
        slug = "north-yarmouth-academy" if slug == "north-yarmouth-academy-educatius-exclusive" else slug
        r = schools.get(slug)
        if r:
            price = facts(page)["price"]
            t, exp = cur_title(page), expected_old_title(r, price)
            if t == exp:
                if " | Du học thpt " in t:
                    out["title"] = t.replace(" | Du học thpt ", " | Du học THPT ")
            elif "…" in t or (exp.startswith(t) and len(t) < len(exp)):
                out["title"] = fit_title(r, price)
            if r["country"] == "Mỹ" and r["level"] == "THPT":
                out["desc"] = us_hs_desc(r, page)
        return out
    # ---- country × level / video
    m = re.fullmatch(r"quoc-gia/([a-z-]+)/([a-z-]+)/index\.html", rel)
    if m and m.group(1) in COUNTRY:
        C, level = COUNTRY[m.group(1)], m.group(2)
        if level == "video":
            out["desc"] = lead(page)
        else:
            n = len(set(re.findall(r'href="/truong/([^"/]+)/"', page)))
            out["desc"] = level_desc(level, C, n)
        return out
    # ---- news articles (+ the 10 root duplicates that canonical to them)
    m = re.fullmatch(r"(?:tin-tuc/)?([a-z0-9-]+)/index\.html", rel)
    if m and os.path.isfile(os.path.join(TGT, "tin-tuc", m.group(1), "index.html")) and m.group(1) != "index.html":
        out["desc"] = f"{lead(page)} Cẩm nang du học từ {ORG}."
        return out
    m = re.fullmatch(r"su-kien/([a-z0-9-]+)/index\.html", rel)
    if m and m.group(1) in EVENTS:
        out["desc"] = EVENTS[m.group(1)]
        return out
    m = re.fullmatch(r"hoc-bong/([a-z0-9-]+)/index\.html", rel)
    if m:
        d = scholarship_desc(page)
        if d:
            out["desc"] = d
        return out
    m = re.fullmatch(r"hoc-sinh/([a-z0-9-]+)/index\.html", rel)
    if m and m.group(1) in STUDENTS:
        out["desc"] = student_desc(m.group(1), page)
        if cur_title(page) == f"Câu chuyện học sinh — {ORG}":
            out["title"] = f"{h1(page)} — {ORG}"
        return out
    key = rel[:-len("/index.html")]
    if key in ARCHIVES:
        out["desc"] = ARCHIVES[key]
        return out
    if key in LANDINGS:
        out["desc"] = LANDINGS[key]
        return out
    m = re.fullmatch(r"nganh-hoc/([a-z-]+)/index\.html", rel)
    if m and m.group(1) in STUBS:
        target, t, d = STUBS[m.group(1)]
        out.update(title=f"{t} — {ORG}", desc=d, canonical=f"{BASE}/nganh-hoc/{target}/")
        return out
    return out


def fix_sitemap(apply):
    p = os.path.join(TGT, "sitemap.xml")
    s = read(p)
    n = 0
    for slug in STUBS:
        url = f"{BASE}/nganh-hoc/{slug}/"
        s2 = re.sub(r"\s*<url>\s*<loc>" + re.escape(url) + r"</loc>.*?</url>", "", s, count=1, flags=re.S)
        n += s2 != s
        s = s2
    if n and apply:
        write(p, s)
    return n, len(re.findall(r"<loc>", s))


def main(argv):
    apply = "--apply" in argv
    schools = load_schools()
    files = sorted(f.replace("\\", "/") for f in glob.glob(os.path.join(TGT, "**", "index.html"), recursive=True)
                   if not re.search(r"[\\/](\.git|docs|scratchpad|\.superpowers)[\\/]", f))
    st = collections.Counter()
    samples = collections.defaultdict(list)
    changed = 0
    bad = []
    for path in files:
        rel = os.path.relpath(path, TGT).replace("\\", "/")
        page = read(path)
        new = page
        p = plan(rel, page, schools)
        if p.get("title") and p["title"] != cur_title(page):
            assert "…" not in p["title"]
            new = set_title(new, p["title"])
            st["title changed"] += 1
            samples["title"].append((rel, cur_title(page), p["title"]))
        if p.get("desc") and p["desc"] != cur_desc(page):
            if len(p["desc"]) > DESC_MAX or p["desc"].endswith("…"):
                bad.append((rel, len(p["desc"]), p["desc"]))      # skip only this field
            else:
                new = set_desc(new, p["desc"])
                st["desc changed"] += 1
                samples["desc"].append((rel, cur_desc(page), p["desc"]))
        if p.get("canonical") and p["canonical"] != get(page, r'<link rel="canonical" href="([^"]+)"'):
            new = set_canonical(new, p["canonical"])
            st["canonical repointed"] += 1
        new, removed = dedupe_canonical(new)
        if removed:
            st["canonical duplicates removed"] += removed
            st["pages with dup canonical"] += 1
        if new != page:
            changed += 1
            if apply:
                write(path, new)
    n_sm, n_loc = fix_sitemap(apply)
    st["sitemap urls removed"] = n_sm
    if bad:
        print(f"!! {len(bad)} description(s) over {DESC_MAX} chars - NOT applied:")
        for rel, n, d in bad:
            print(f"   {n:4} {rel}: {d}")
    print(("APPLIED" if apply else "DRY-RUN") + f": {changed} page(s) would change · sitemap now {n_loc} URL")
    for k, v in sorted(st.items()):
        print(f"  {k}: {v}")
    if "-v" in argv:
        for kind in ("title", "desc"):
            print(f"\n--- {kind} samples ({len(samples[kind])})")
            for rel, old, newv in samples[kind]:
                print(f"  {rel}\n    - {old}\n    + {newv}  [{len(newv)}]")
    return changed


if __name__ == "__main__":
    main(sys.argv[1:])
