# Du học TESOL — Chuyển toàn bộ nội dung site sang chủ đề TESOL

**Ngày:** 2026-07-02
**Trạng thái:** Chờ user duyệt
**Nguồn dữ liệu:** [research/2026-07-02-tesol-facts.md](../research/2026-07-02-tesol-facts.md)

## 1. Bối cảnh & mục tiêu
Site hiện là bản export WordPress tĩnh, **162 trang HTML**, đã đổi tên thương hiệu thành "Du học TESOL" nhưng **nội dung vẫn là công ty du học đa quốc gia tổng quát** (du học Mỹ/Úc/Canada, học bổng, ngành Kinh tế/Sức khoẻ/Công nghệ…). Nhiệm vụ: **viết lại 100% nội dung** để site thực sự là **đơn vị tư vấn du học chuyên ngành TESOL** — giúp học viên Việt Nam đi học chứng chỉ/bằng TESOL ở nước ngoài để trở thành giáo viên tiếng Anh.

Quyết định đã chốt với user:
- **Định hướng:** Chuyên về TESOL (không giữ du học tổng quát).
- **Phạm vi:** Toàn bộ 162 trang.
- **Bộ quốc gia:** Giữ nguyên 9 nước hiện có + reframe (không thêm UK/Ireland thành trang riêng, không đổi slug).

## 2. Nguyên tắc kiến trúc (approach đã chọn)
**Giữ nguyên toàn bộ cấu trúc URL / điều hướng / template trang; chỉ viết lại copy + đổi nhãn taxonomy.** Lý do: header/footer (chứa nav) **giống hệt nhau byte-for-byte** ở cả 162 file; toàn site là ma trận liên kết chéo Quốc gia × Bậc học × Ngành × Trường × Học bổng. Giữ topology → **không gãy link nội bộ, rủi ro thấp**, mỗi trang vẫn trở nên đúng chủ đề TESOL.

Không chọn phương án tái cấu trúc (đổi bộ nước, đổi slug/thư mục, dựng lại nav) vì rủi ro gãy link cao và nặng hơn nhiều mà lợi ích thấp.

## 3. Re-map taxonomy

### 3.1 "Bậc học" (nav + bac-hoc/ + quoc-gia/*/[level]) → Nấc thang bằng cấp TESOL
Giữ 4 slug con `thpt / cao-dang / dai-hoc / sau-dai-hoc`, đổi nhãn hiển thị + nội dung:

| slug | Nhãn cũ | Nhãn mới | Nội dung |
|---|---|---|---|
| thpt | THPT | **Chứng chỉ TESOL nền tảng** | TEFL/TESOL certificate (~120h), điểm khởi đầu |
| cao-dang | Cao đẳng | **CELTA / CertTESOL & Diploma** | CELTA, Trinity CertTESOL, DELTA/DipTESOL |
| dai-hoc | Đại học | **Cử nhân TESOL / Ngôn ngữ Anh** | BA TESOL / Applied Linguistics |
| sau-dai-hoc | Sau đại học | **Thạc sĩ MA TESOL** | MA TESOL / MA Applied Linguistics |

> Lưu ý: đây là nhóm "hình thức/bậc" bằng cấp TESOL để marketing, không phải bậc học phổ thông. Copy nói rõ nấc thang & đối tượng phù hợp (pre-service vs in-service).

### 3.2 "Ngành học" (nav + nganh-hoc/ 9 trang) → Chuyên ngành TESOL
Giữ 9 slug, đổi nhãn + nội dung:

| slug | Nhãn mới |
|---|---|
| tieng-anh | TESOL tổng quát / Giảng dạy tiếng Anh |
| giao-duc | Đào tạo giáo viên (Teacher Training) |
| kinh-doanh | Business English (ESP) |
| cntt | Dạy tiếng Anh trực tuyến (Online ELT) |
| cong-nghe | EdTech trong giảng dạy (Technology-Enhanced ELT) |
| kinh-te | English for Academic Purposes (EAP) |
| ky-thuat | Luyện thi IELTS/TOEFL (Exam Prep) |
| suc-khoe | Teaching Young Learners (dạy trẻ em) |
| y-suc-khoe | Curriculum Design & Language Assessment |

Nav hiện highlight 5 "Ngành HOT" (Kinh tế/Sức khoẻ/Công nghệ/Giáo dục/Tiếng Anh) → đổi thành 5 chuyên ngành TESOL nổi bật: TESOL tổng quát, TEYL, Business English, EAP, Online ELT.

### 3.3 Quốc gia (nav + quoc-gia/ 9 nước) → "Du học TESOL tại [nước]"
Giữ 9 slug. Mỗi nước reframe theo dữ liệu research (trường có MA TESOL/Applied Linguistics thật, học phí, visa post-study, góc nhìn cho SV VN). Xử lý trung thực điểm đặc thù:
- **Mỹ, Úc, Canada, New Zealand**: điểm đến TESOL "chính thống" (nói tiếng Anh) — nhấn immersion, trường thật, post-study work.
- **Singapore, Hàn Quốc**: English-medium mạnh, gần VN, nhiều MA TESOL/Applied Linguistics thật.
- **Thổ Nhĩ Kỳ**: chi phí thấp, học phí thường miễn; có MA ELT/TEFL English-medium (Bilkent/METU/Boğaziçi).
- **Đức, Hà Lan**: **nói rõ** đây là MA Linguistics/Applied Linguistics **học thuật** (English-medium, học phí thấp), khác với TESOL thực hành — định vị cho SV muốn nền tảng nghiên cứu ngôn ngữ. Không hứa hẹn "bằng dạy học".

### 3.4 Trường (truong/ 33 trang)
Giữ danh sách trường (đa số — Melbourne, Monash, Sydney, Auckland, UCLA, Toronto… — thật sự có MA TESOL/Applied Linguistics). Mỗi trang reframe quanh chương trình TESOL/Applied Linguistics của trường: tên chương trình, bậc, học phí tham khảo, IELTS đầu vào, career service cho nghề giảng dạy. Trường nào không có chương trình phù hợp → mô tả ở góc "ngôn ngữ Anh/linguistics" hoặc chỉnh nhãn cho hợp lý.

### 3.5 Các nhóm còn lại
- **hoc-bong/ (8)** → học bổng TESOL/giáo dục thật: Fulbright (nêu đích danh TESOL), Chevening, Australia Awards, GREAT, Ireland Fellows, học bổng trường. Đổi tên các học bổng hư cấu cũ (Future Leaders, STEM Scholarship…) sang khung học bổng TESOL.
- **gia-tri-hoc-bong/ (25/50/toan-phan)** → giữ khung "giá trị học bổng", nội dung ví dụ theo học bổng TESOL.
- **tin-tuc/ (11) + các trang topical trùng ở root** (us-living-cost, us-culture, us-it-major, us-health-insurance, parent-budget, visa-500-2026, canada-pgwp, australia-scholarship, deadline-sep, scholarship-tips) → viết lại thành bài chủ đề TESOL/dạy tiếng Anh (VD: "IELTS cần bao nhiêu cho MA TESOL", "OPT sau MA TESOL ở Mỹ", "Nhu cầu giáo viên tiếng Anh tại VN đến 2030", "PGWP Canada cho SV TESOL"). Mỗi trang root + bản trong tin-tuc/ giữ đồng bộ.
- **su-kien/ (9) + loai-su-kien/ (3)** → sự kiện chủ đề TESOL: workshop CELTA/IELTS, hội thảo MA TESOL, ngày hội trường TESOL, buổi định hướng nghề giáo viên tiếng Anh.
- **hoc-sinh/ (8)** → chân dung học viên: cựu học viên nay là giáo viên tiếng Anh / trainer / học MA TESOL ở nước ngoài. Đổi ngành/câu chuyện cho khớp.
- **lo-trinh-du-hoc/ (6)** → lộ trình đi học TESOL (mục 9 research): xác định mục tiêu → chọn bậc → chọn nước/trường → IELTS → hồ sơ (SOP, thư GT, pre/in-service) → visa → lên đường.
- **category/ (kinh-nghiem, visa, hoc-bong)** → trang danh mục, cập nhật mô tả TESOL.
- **quoc-gia-filter/ (5)** → trang lọc theo nước, cập nhật copy.
- **ve-chung-toi, dich-vu, lien-he, tai-cam-nang** → giới thiệu đơn vị tư vấn du học TESOL, dịch vụ (tư vấn chọn bậc/nước/trường, luyện IELTS, hồ sơ, học bổng, visa), form liên hệ.

### 3.6 Thành phần dùng chung (đổi 1 lần → thay ở cả 162 file)
- **Header/nav** (5843 byte, giống hệt): cập nhật nhãn quốc gia (giữ tên nước), đổi mục "Bậc học" → nhãn bằng cấp TESOL, "Ngành học HOT" → chuyên ngành TESOL, các CTA/wording "du học" chung → giữ (vẫn là "du học").
- **Footer** (giống hệt): cập nhật mô tả, liên kết, tagline.
- **Schema JSON-LD / meta / title**: đã mang tên "Du học TESOL"; cập nhật `description` cho đúng định hướng TESOL. Giữ SĐT/địa chỉ 3 văn phòng (0906.510.747; HCM/ĐN/BMT) như hiện có.
- **Tagline hero**: "Avenue to New World / Trở thành công dân toàn cầu" → đề xuất "Học TESOL — Dạy tiếng Anh khắp thế giới" / "Trở thành giáo viên tiếng Anh chuẩn quốc tế cùng Du học TESOL". Các số liệu hero (5 quốc gia, 3 văn phòng, 1.200+ trường) → điều chỉnh sang thông điệp TESOL (VD "9 điểm đến TESOL", "trường có MA TESOL/Applied Linguistics", "24h phản hồi").

## 4. Kỹ thuật thực thi
- **Không đổi CSS** (`wp-content/themes/duy-study/assets/css/main.css`) — giữ hệ màu/thiết kế. Không đổi ảnh (giữ ảnh sinh viên/campus hiện có; phù hợp bối cảnh giáo dục).
- **Header/footer chung**: sinh 1 lần bản mới, thay đồng loạt ở 162 file bằng script (khối byte-identical → replace an toàn).
- **Nội dung từng trang**: viết lại theo template sẵn có của từng loại trang (giữ class/section, chỉ thay text trong tag). Bám sát dữ liệu research; mọi số liệu để dạng "tham khảo", tránh cam kết con số cứng.
- **Tính chính xác**: dùng đúng tên chương trình/trường thật từ research; tuân các cảnh báo (Đức/Hà Lan là linguistics học thuật; không quảng cáo STEM OPT cho TESOL Mỹ; Sydney/USC có thể tạm dừng tuyển).
- **Không tạo/xoá trang, không đổi đường dẫn.** Số file trước/sau = 162.

## 5. Đơn vị công việc (isolation)
Mỗi *loại trang* là một đơn vị độc lập, có thể viết & kiểm tra riêng:
1. Shared header/footer/meta (ảnh hưởng toàn site) — làm trước.
2. Trang chủ (index.html).
3. Cụm Quốc gia (quoc-gia/ 46) + quoc-gia-filter (5).
4. Cụm Bậc học (bac-hoc 2) + Ngành học (nganh-hoc 10).
5. Cụm Trường (truong 33).
6. Cụm Học bổng (hoc-bong 8 + gia-tri-hoc-bong 3).
7. Cụm Tin tức (tin-tuc 11 + trang topical root ~10).
8. Cụm Sự kiện (su-kien 9 + loai-su-kien 3) + Học sinh (8).
9. Lộ trình (6) + category (3) + trang tĩnh (ve-chung-toi, dich-vu, lien-he, tai-cam-nang).

Do khối lượng lớn (162 trang), thực thi phù hợp với chạy **song song nhiều agent** theo từng cụm; sẽ chốt ở bước viết implementation plan.

## 6. Tiêu chí hoàn thành
- 162 trang đều nói về TESOL; không còn nội dung du học tổng quát lạc chủ đề (ngành Kinh tế/Y/Kỹ thuật… theo nghĩa cũ).
- Nav/footer đồng bộ trên mọi trang; không gãy link nội bộ (số file & đường dẫn không đổi).
- Số liệu bám research, có hedge; tuân mọi cảnh báo chính xác.
- Giữ nguyên thiết kế (CSS/ảnh), SĐT & 3 văn phòng, cấu trúc SEO/schema hợp lệ.

## 7. Ngoài phạm vi (YAGNI)
- Không thêm quốc gia/slug mới (UK/Ireland trang riêng).
- Không tái thiết kế giao diện, không đổi CSS/ảnh.
- Không dựng backend/form thật (giữ form tĩnh như hiện tại).
- Không dịch site sang ngôn ngữ khác (giữ tiếng Việt).
