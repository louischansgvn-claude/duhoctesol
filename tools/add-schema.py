"""Page-level structured data for duhoctesolhcmc.vn.

Usage (from repo root):
    python tools/add-schema.py            # dry run, prints counts + samples
    python tools/add-schema.py --apply    # write
Needs the sibling folder "duystudy.vn - content" (dataset source).
Idempotent: re-running replaces the id="page-schema" block, never stacks.
Run it again whenever school pages are regenerated or new schools are added.

Adds ONE extra <script type="application/ld+json" id="page-schema"> right after
the site-wide org/website/office graph already on every page. The existing
graph is not touched.

  * 571 school pages -> WebPage + school entity + BreadcrumbList
        school @type:  HighSchool | CollegeOrUniversity | EducationalOrganization
        name           official name only (Vietnamese annotations stripped)
        alternateName  acronym when present - (TDSB), (ANU), (TWU - ...)
        url + sameAs   official website from the dataset
        address        addressCountry (ISO) + region / locality when known
  * every other page with a real breadcrumb -> BreadcrumbList

The block carries id="page-schema", so re-running replaces it instead of
stacking duplicates.
"""
import glob, html, json, os, re, sys, collections, hashlib

TGT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))   # repo root
ROOT = os.path.dirname(TGT)                  # parent folder holding "duystudy.vn - content"
DATA = os.path.join(ROOT, "duystudy.vn - content", "outputs",
                    "019f3b94-2b30-7b90-a898-aa362095ed4d")
SOURCES = ["duystudy_canada_australia_content_IMPORTER_READY_APPROVED.json",
           "duystudy_other_countries_content_IMPORTER_READY_APPROVED.json",
           "duystudy_us_college_university_IMPORTER_READY.json",
           "duystudy_us_high_schools_namphong_style_import.json"]
BASE = "https://duhoctesolhcmc.vn"

ISO = {"Mỹ": "US", "Canada": "CA", "Úc": "AU", "Anh": "GB", "New Zealand": "NZ",
       "Hà Lan": "NL", "Malaysia": "MY", "Thụy Sỹ": "CH", "Singapore": "SG",
       "Đức": "DE", "Philippines": "PH", "Thổ Nhĩ Kỳ": "TR", "Hàn Quốc": "KR"}
COUNTRY_WORDS = {"canada", "singapore", "new zealand", "malaysia", "philippines",
                 "switzerland", "netherlands", "germany", "turkey", "korea",
                 "south korea", "australia", "united kingdom", "uk", "usa",
                 "united states", "thụy sỹ", "hà lan", "đức", "hàn quốc",
                 "thổ nhĩ kỳ", "mỹ", "úc", "anh"}

# letters that exist in Vietnamese but not in French/German/Spanish names
VN_CHARS = re.compile("[ăđĐơưạảấầẩẫậắằẳẵặẹẻẽếềểễệỉịọỏốồổỗộớờởỡợụủứừửữựỳỵỷỹĩũ]", re.I)
# annotations that use only accents shared with French/Spanish, e.g. "Bang Tây Úc"
VN_WORDS = re.compile(r"\b(Bang|Tây|Đông|Úc|Khu|vực|các|Hệ|tiếng|Trường|Học|Viện|"
                      r"Mạnh|Rất|Nằm|Dạy|Chuyên)\b")


class _VN:
    @staticmethod
    def search(s):
        return VN_CHARS.search(s) or VN_WORDS.search(s)


VN = _VN()
ACRONYM = re.compile(r"^([A-Z][A-Za-z&]{1,7})\b")
DISTRICT = re.compile(r"\b(District|School Board|Public Schools|School Division|"
                      r"Unified School|Program)\b|\bSchools\b")
REAL_SCHOOL = re.compile(r"High School|Academy|Preparatory|College|Institute")
SCRIPT_ID = 'id="page-schema"'


def read(p):
    with open(p, encoding="utf-8", newline="") as f:
        return f.read()


def is_acronym(s):
    """ANU, TDSB, BUas, UdeM, TU/e, TU Delft - but not Ottawa, North Bay, Terrace."""
    s = s.strip()
    if VN.search(s) or "," in s:
        return False
    if re.fullmatch(r"[A-Za-z&/]{2,8}", s):
        return sum(c.isupper() for c in s) >= 2
    # "TU Delft": an all-caps token followed by one capitalised word
    return bool(re.fullmatch(r"[A-Z]{2,5}\s[A-Z][a-z]+", s))


def top_level_dashes(s):
    """positions of ' - ' / ' – ' that are NOT inside parentheses"""
    out, depth = [], 0
    for i, c in enumerate(s):
        depth += (c == "(") - (c == ")")
        if depth == 0 and s[i:i + 3] in (" - ", " – "):
            out.append(i)
    return out


def clean_name(raw):
    """-> (name, alternateName|None)"""
    name, alt = raw.strip(), None
    # 1. " - <Vietnamese annotation>" at top level only (never split inside parens)
    for pos in reversed(top_level_dashes(name)):
        if VN.search(name[pos + 3:]):
            name = name[:pos].strip()
    # 2. "ACRONYM (Full English Name)" -> swap, e.g. BSBI (Berlin School of ...)
    m = re.fullmatch(r"([A-Z][A-Za-z&]{1,7})\s*\(([^()]+)\)", name)
    if m and not VN.search(m.group(2)) and len(m.group(2).split()) >= 3:
        return m.group(2).strip(), m.group(1)
    # 3. trailing parenthetical groups, peeled from the right
    while True:
        m = re.fullmatch(r"(.*?)\s*\(([^()]*)\)\s*", name)
        if not m:
            break
        head = re.split(r"\s+[-–]\s+", m.group(2).strip())[0].strip()
        if alt is None and is_acronym(head):
            alt = head
        name = m.group(1).strip()
    return name, alt


def school_type(level, name):
    if level == "THPT":
        if DISTRICT.search(name) and not REAL_SCHOOL.search(name):
            return "EducationalOrganization"
        return "HighSchool"
    if level in ("Đại học", "Cao đẳng"):
        return "CollegeOrUniversity"
    return "EducationalOrganization"          # Anh ngữ / language schools


def address(country, state):
    a = {"@type": "PostalAddress", "addressCountry": ISO[country]}
    st = (state or "").strip()
    if st and st.lower() not in COUNTRY_WORDS:
        if "," in st:
            loc, reg = st.rsplit(",", 1)
            a["addressLocality"], a["addressRegion"] = loc.strip(), reg.strip()
        else:
            a["addressRegion"] = st
    return a


def breadcrumb(page, canonical):
    m = re.search(r'<div class="breadcrumb">(.*?)</div>', page, re.S)
    if not m:
        return None
    raw = m.group(1)
    links = re.findall(r'<a href="([^"]+)">(.*?)</a>', raw, re.S)
    if not links:
        return None
    tail = re.sub(r"<[^>]+>", "", raw.split("</a>")[-1]).strip(" /\t\n")
    items = [(html.unescape(re.sub(r"<[^>]+>", "", t)).strip(),
              BASE + (h if h.startswith("/") else "/" + h)) for h, t in links]
    if tail:
        items.append((html.unescape(tail), canonical))
    return {
        "@type": "BreadcrumbList",
        "@id": canonical + "#breadcrumb",
        "itemListElement": [
            {"@type": "ListItem", "position": i + 1, "name": n, "item": u}
            for i, (n, u) in enumerate(items)
        ],
    }


def meta(page, name):
    m = re.search(rf'<meta name="{name}" content="([^"]*)"', page)
    return html.unescape(m.group(1)) if m else ""


def title(page):
    """Prefer the <h1>: 32 pages ship a <title> pre-truncated with '…'
    ("…THPT Mỹ tại Pennsylvania từ…"), the h1 is always complete."""
    m = re.search(r"<h1[^>]*>(.*?)</h1>", page, re.S)
    if m:
        t = html.unescape(re.sub(r"<[^>]+>", "", m.group(1))).strip()
        if t:
            return re.sub(r"\s+", " ", t)
    m = re.search(r"<title>(.*?)</title>", page, re.S)
    return html.unescape(m.group(1).strip()) if m else ""


def build(page, rel, school):
    canonical = re.search(r'<link rel="canonical" href="([^"]+)"', page).group(1)
    graph = []
    bc = breadcrumb(page, canonical)
    if school:
        name, alt = clean_name(school["institution_name"])
        web = re.sub(r"[.,;)\s]+$", "", school["website"].strip())
        if not web.endswith("/") and web.count("/") == 2:
            web += "/"
        ent = {"@type": school_type(school["level"], name),
               "@id": canonical + "#school",
               "name": name}
        if alt:
            ent["alternateName"] = alt
        ent["url"] = web
        ent["sameAs"] = [web]
        logo = re.search(r'src="(/wp-content/uploads/[^"]*logo[^"]*)"', page)
        if logo:
            ent["logo"] = BASE + logo.group(1)
        img = re.search(r'<div class="school-gallery"><figure><img src="([^"]+)"', page)
        if img:
            ent["image"] = BASE + img.group(1)
        ent["address"] = address(school["country"], school.get("state"))
        wp = {"@type": "WebPage", "@id": canonical + "#webpage", "url": canonical,
              "name": title(page), "description": meta(page, "description"),
              "inLanguage": "vi-VN",
              "isPartOf": {"@id": BASE + "/#website"},
              "publisher": {"@id": BASE + "/#organization"},
              "about": {"@id": ent["@id"]},
              "mainEntity": {"@id": ent["@id"]}}
        if bc:
            wp["breadcrumb"] = {"@id": bc["@id"]}
        graph += [wp, ent]
    if bc:
        graph.append(bc)
    if not graph:
        return None
    return {"@context": "https://schema.org", "@graph": graph}


def main(argv):
    apply = "--apply" in argv
    schools = {}
    for f in SOURCES:
        for r in json.load(open(os.path.join(DATA, f), encoding="utf-8")):
            schools[r["post_slug"]] = r
    idx = json.load(open(os.path.join(DATA, "duystudy_country_level_index.json"),
                         encoding="utf-8"))
    # the index is the one file where all 571 records share the same keys
    for r in idx:
        schools[r["post_slug"]].update(
            institution_name=r["institution_name"], level=r["level"],
            country=r["country"], state=r.get("state"))
    missing = [s for s, r in schools.items() if not (r.get("website") or "").strip()]
    assert not missing, f"no website: {missing[:5]}"

    files = sorted(f.replace("\\", "/") for f in
                   glob.glob(os.path.join(TGT, "**", "index.html"), recursive=True)
                   if not re.search(r"[\\/](\.git|docs|scratchpad)[\\/]", f))
    stats = collections.Counter()
    renames, samples = [], {}
    for path in files:
        rel = os.path.relpath(path, TGT).replace("\\", "/")
        page = read(path)
        slug = rel.split("/")[1] if rel.startswith("truong/") and rel.count("/") == 2 else None
        if slug == "north-yarmouth-academy-educatius-exclusive":
            slug = "north-yarmouth-academy"
        school = schools.get(slug) if slug else None
        data = build(page, rel, school)
        if data is None:
            stats["no schema (no breadcrumb)"] += 1
            continue
        blob = json.dumps(data, ensure_ascii=False, separators=(",", ":"))
        json.loads(blob)                                   # must round-trip
        tag = f'<script type="application/ld+json" {SCRIPT_ID}>{blob}</script>'
        # remove a previous run's block, then insert after the site-wide graph
        page2 = re.sub(r'\n\t<script type="application/ld\+json" id="page-schema">.*?</script>',
                       "", page, flags=re.S)
        anchor = page2.index('<script type="application/ld+json">')
        anchor = page2.index("</script>", anchor) + len("</script>")
        new = page2[:anchor] + "\n\t" + tag + page2[anchor:]
        for g in data["@graph"]:
            stats[g["@type"]] += 1
        if school:
            n, a = clean_name(school["institution_name"])
            if n != school["institution_name"]:
                renames.append((school["institution_name"], n, a))
            samples.setdefault(data["@graph"][1]["@type"], (rel, data))
        else:
            samples.setdefault("breadcrumb-only", (rel, data))
        if apply and new != page:
            with open(path, "w", encoding="utf-8", newline="") as f:
                f.write(new)

    print("entities added:")
    for k, v in stats.most_common():
        print(f"   {v:>4}  {k}")
    print(f"\nschool names cleaned: {len(renames)}")
    for raw, n, a in renames[:40]:
        print(f"   {raw[:70]:<70} -> {n}" + (f"   [alt {a}]" if a else ""))
    print("\n=== samples ===")
    for k, (rel, d) in samples.items():
        print(f"\n--- {k}: {rel}")
        print(json.dumps(d, ensure_ascii=False, indent=1)[:1500])
    print("\nmode:", "APPLIED" if apply else "DRY RUN")
    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv))
