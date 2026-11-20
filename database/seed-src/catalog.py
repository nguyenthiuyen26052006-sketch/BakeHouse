# -*- coding: utf-8 -*-
"""Nguồn dữ liệu danh mục / sản phẩm cho seed demo. Sửa ở đây rồi chạy generate.py."""

CATEGORIES = [
    (1, 'Bột & Ngũ cốc', 'bot-ngu-coc', 'Các loại bột mì, bột bắp, yến mạch và ngũ cốc làm bánh.'),
    (2, 'Chất tạo ngọt', 'chat-tao-ngot', 'Đường, mật ong, siro và các chất tạo ngọt.'),
    (3, 'Chất béo', 'chat-beo', 'Bơ, dầu, shortening và margarine.'),
    (4, 'Dụng cụ làm bánh', 'dung-cu-lam-banh', 'Khuôn, cân, phới, cọ và dụng cụ cơ bản.'),
    (5, 'Hương liệu & Màu thực phẩm', 'huong-lieu-mau-thuc-pham', 'Vani, cacao, bột trà xanh và màu thực phẩm.'),
    (6, 'Sữa, Kem & Trứng', 'sua-kem-trung', 'Kem tươi, sữa, phô mai kem và trứng cho bánh kem, bánh mousse.'),
    (7, 'Socola, Hạt & Trái cây sấy', 'socola-hat-trai-cay-say', 'Socola, các loại hạt và trái cây sấy để trang trí và làm nhân bánh.'),
    (8, 'Phụ gia & Chất tạo nở', 'phu-gia-chat-tao-no', 'Men, bột nở, gelatin và các chất hỗ trợ kết cấu bánh.'),
]

BRANDS = [
    (1, 'Meizan'), (2, 'Anchor'), (3, 'Tường An'), (4, 'Biên Hòa'), (5, 'Wilton'),
    (6, 'Cái Lân'), (7, 'Nielsen-Massey'), (8, 'Vinamilk'), (9, 'Dr. Oetker'),
    (10, 'Callebaut'), (11, 'Mauri'), (12, 'Philadelphia'), (13, 'Président'),
]

TAGS = [
    (1, 'Làm bánh mì'), (2, 'Làm bánh ngọt'), (3, 'Không gluten'), (4, 'Cho người mới'),
    (5, 'Nhập khẩu'), (6, 'Hữu cơ'), (7, 'Cho bánh kem'), (8, 'Không đường'),
    (9, 'Bán chạy'), (10, 'Làm bánh quy'),
]

# slug, category, brand, name, description, unit, price, stock, featured, active, created, tags, image queries
P = []


def add(slug, cat, brand, name, desc, unit, price, stock, featured, active, created, tags, queries):
    P.append(dict(slug=slug, cat=cat, brand=brand, name=name, desc=desc, unit=unit, price=price, stock=stock,
                  featured=featured, active=active, created=created, tags=tags, queries=queries))


# ---- 1. Bột & Ngũ cốc ----
add('bot-mi-da-dung', 1, 1, 'Bột Mì Đa Dụng (All-purpose Flour)',
    'Bột mì đa dụng phù hợp làm bánh quy, bánh bông lan và bánh muffin. Độ đạm vừa phải, dễ sử dụng cho người mới.',
    'gói 1kg', 28000, 180, 1, 1, '2025-11-05', [2, 4, 9, 10], ['all-purpose flour', 'wheat flour bag'])
add('bot-mi-so-11', 1, 1, 'Bột Mì Số 11 (Bread Flour)',
    'Bột mì hàm lượng protein cao, chuyên làm bánh mì, pizza và bánh bao cho độ dai ngon.',
    'gói 1kg', 32000, 140, 1, 1, '2025-11-05', [1, 9], ['bread flour', 'bread dough flour'])
add('bot-mi-so-8', 1, 1, 'Bột Mì Số 8 (Cake Flour)',
    'Bột mì đạm thấp, mịn nhẹ, cho ruột bánh bông lan và chiffon mềm xốp.',
    'gói 1kg', 34000, 95, 0, 1, '2025-11-08', [2, 7], ['cake flour', 'sifting flour'])
add('bot-bap-bien-hoa', 1, 4, 'Bột Bắp Biên Hòa',
    'Bột bắp dùng làm đặc sốt, làm mềm bánh quy và pha bột chiên.',
    'hộp 400g', 18000, 150, 0, 1, '2025-11-08', [3, 4], ['cornstarch', 'corn starch'])
add('yen-mach-can-det', 1, None, 'Yến Mạch Cán Dẹt',
    'Yến mạch nguyên hạt cán dẹt, dùng làm cookie, granola và bánh ăn kiêng.',
    'gói 500g', 45000, 70, 0, 1, '2025-11-10', [10, 6], ['rolled oats', 'oats'])
add('bot-gao-nep', 1, None, 'Bột Gạo Nếp',
    'Bột gạo nếp xay mịn, làm bánh dẻo, bánh trôi, mochi và bánh giầy.',
    'gói 400g', 19000, 85, 0, 1, '2025-11-12', [3, 4], ['glutinous rice flour', 'rice flour'])
add('bot-nang', 1, None, 'Bột Năng (Tapioca Starch)',
    'Bột năng tinh khiết, tạo độ trong và dai cho bánh bột lọc, trân châu, bánh flan.',
    'gói 400g', 16000, 110, 0, 1, '2025-11-12', [3], ['tapioca starch', 'tapioca flour'])
add('bot-hanh-nhan', 1, None, 'Bột Hạnh Nhân',
    'Bột hạnh nhân xay mịn, nguyên liệu chính của macaron, financier và bánh không gluten.',
    'gói 200g', 98000, 38, 1, 1, '2025-11-15', [3, 5, 2], ['almond flour', 'almond meal'])
add('bot-mi-nguyen-cam', 1, 1, 'Bột Mì Nguyên Cám (Whole Wheat)',
    'Bột mì nguyên cám giàu chất xơ, thích hợp làm bánh mì đen và bánh quy ăn kiêng.',
    'gói 1kg', 42000, 6, 0, 1, '2026-01-10', [1, 6], ['whole wheat flour', 'whole grain flour'])
add('granola-mix', 1, None, 'Granola Trộn Hạt Và Mật Ong',
    'Granola giòn nướng với yến mạch, hạt và mật ong, ăn kèm sữa chua hoặc rắc lên bánh.',
    'gói 400g', 85000, 42, 0, 1, '2026-02-03', [6], ['granola'])

# ---- 2. Chất tạo ngọt ----
add('duong-cat-trang', 2, 6, 'Đường Cát Trắng',
    'Đường cát trắng tinh luyện, hạt mịn dễ tan.',
    'gói 1kg', 24000, 220, 0, 1, '2025-11-05', [4, 9], ['white sugar', 'granulated sugar'])
add('duong-nau-muscovado', 2, 6, 'Đường Nâu Muscovado',
    'Đường nâu giàu mật mía, hương caramel đậm, cho cookie và bánh gừng thơm hơn.',
    'gói 500g', 38000, 100, 0, 1, '2025-11-06', [2, 10], ['muscovado sugar', 'brown sugar'])
add('duong-bot', 2, 6, 'Đường Bột (Icing Sugar)',
    'Đường xay mịn có pha bột bắp, dùng làm kem bơ, glaze và phủ mặt bánh.',
    'gói 500g', 22000, 130, 0, 1, '2025-11-06', [7, 4], ['powdered sugar', 'icing sugar'])
add('duong-caster', 2, 6, 'Đường Caster Hạt Mịn',
    'Đường hạt siêu mịn tan nhanh, lý tưởng để đánh bông trứng và làm meringue.',
    'gói 500g', 26000, 90, 0, 1, '2025-11-10', [2], ['caster sugar', 'fine sugar'])
add('mat-ong-hoa-nhan', 2, None, 'Mật Ong Hoa Nhãn',
    'Mật ong nguyên chất, dùng làm bánh, ướp thịt và pha đồ uống.',
    'chai 500ml', 120000, 40, 0, 1, '2025-11-12', [6], ['honey jar', 'honey'])
add('siro-phong', 2, None, 'Siro Phong (Maple Syrup)',
    'Siro cây phong nguyên chất nhập khẩu Canada, rưới pancake, waffle và làm bánh.',
    'chai 250ml', 165000, 0, 0, 1, '2026-02-14', [5, 6], ['maple syrup'])
add('mach-nha', 2, None, 'Mạch Nha (Malt Syrup)',
    'Mạch nha đặc, giúp bánh mì có màu đẹp và vỏ bóng, bánh quy giữ ẩm lâu.',
    'lọ 400g', 48000, 24, 0, 1, '2026-03-01', [1], ['malt syrup', 'barley malt syrup'])
add('golden-syrup', 2, None, 'Siro Đường Vàng (Golden Syrup)',
    'Siro đường vàng sánh mịn, vị caramel nhẹ, dùng cho bánh flapjack, bánh gừng và kẹo.',
    'chai 454g', 79000, 18, 0, 1, '2026-04-02', [5, 10], ['golden syrup'])

# ---- 3. Chất béo ----
add('bo-lat-anchor', 3, 2, 'Bơ Lạt Anchor',
    'Bơ lạt nhập khẩu New Zealand, béo thơm tự nhiên, làm bánh quy và kem bơ.',
    'cục 227g', 65000, 120, 1, 1, '2025-11-05', [2, 5, 9], ['butter block', 'unsalted butter'])
add('bo-man-anchor', 3, 2, 'Bơ Mặn Anchor',
    'Bơ mặn dùng phết bánh mì, nấu ăn và làm bánh quy bơ mặn.',
    'cục 227g', 66000, 70, 0, 1, '2025-11-06', [5], ['salted butter', 'butter dish'])
add('bo-lat-president', 3, 13, 'Bơ Lạt Président',
    'Bơ lạt kiểu Pháp 82% béo, cho croissant và bánh puff nhiều lớp thơm ngậy.',
    'cục 200g', 78000, 55, 1, 1, '2025-12-02', [1, 5], ['french butter', 'butter curl'])
add('dau-an-tuong-an', 3, 3, 'Dầu Ăn Thực Vật Tường An',
    'Dầu ăn tinh luyện, không mùi, dùng cho bánh bông lan, chiffon và cupcake.',
    'chai 1L', 52000, 80, 0, 1, '2025-11-06', [2, 4], ['cooking oil bottle', 'vegetable oil'])
add('margarine', 3, None, 'Bơ Thực Vật Margarine',
    'Margarine mềm dẻo, giá tốt, phù hợp làm bánh mì ngọt, bánh quy và phủ khuôn.',
    'hộp 500g', 58000, 48, 0, 1, '2025-12-05', [1], ['margarine tub', 'margarine'])
add('shortening', 3, None, 'Shortening (Mỡ Thực Vật Làm Bánh)',
    'Shortening trắng không mùi, cho bánh quy xốp, vỏ bánh pie giòn và kem bơ bền dáng.',
    'hộp 1kg', 95000, 22, 0, 1, '2026-01-15', [10, 7], ['vegetable shortening', 'shortening'])
add('dau-dua', 3, None, 'Dầu Dừa Nguyên Chất',
    'Dầu dừa ép lạnh thơm nhẹ, thay thế bơ trong bánh thuần chay và phủ socola.',
    'chai 500ml', 85000, 31, 0, 1, '2026-02-20', [6], ['coconut oil'])

# ---- 4. Dụng cụ làm bánh ----
add('khuon-banh-tron-wilton', 4, 5, 'Khuôn Bánh Tròn Wilton 20cm',
    'Khuôn nhôm chống dính, đường kính 20cm, cao 7cm, dùng cho bánh bông lan và bánh kem.',
    'cái', 145000, 25, 1, 1, '2025-11-07', [4, 7], ['round cake pan', 'cake tin'])
add('can-dien-tu-5kg', 4, None, 'Cân Điện Tử Nhà Bếp 5kg',
    'Cân điện tử độ chính xác 1g, tối đa 5kg, có chức năng trừ bì.',
    'cái', 175000, 30, 1, 1, '2025-11-07', [4, 9], ['digital kitchen scale', 'kitchen scale'])
add('phoi-danh-trung-inox', 4, 5, 'Phới Đánh Trứng Inox',
    'Phới lồng inox 304 cán dài, bền chắc, đánh trứng và kem tươi nhanh.',
    'cái', 55000, 60, 0, 1, '2025-11-08', [4, 9], ['balloon whisk', 'wire whisk'])
add('giay-nen-nuong-banh', 4, None, 'Giấy Nến Nướng Bánh',
    'Giấy nến chống dính, chịu nhiệt đến 220°C, lót khay nướng và khuôn.',
    'cuộn 5m', 32000, 100, 0, 1, '2025-11-08', [4, 9], ['parchment paper', 'baking paper'])
add('can-bot-go', 4, None, 'Cán Bột Gỗ',
    'Cán bột bằng gỗ nguyên khối, trơn láng, cán đều bột bánh quy và bánh mì.',
    'cái', 68000, 40, 0, 1, '2025-11-10', [4, 10], ['rolling pin'])
add('ray-bot-cam-tay', 4, None, 'Rây Bột Cầm Tay',
    'Rây inox mịn, lọc bột và đường tơi xốp, loại bỏ vón cục.',
    'cái', 48000, 55, 0, 1, '2025-11-10', [4], ['flour sieve', 'sifter'])
add('co-phet-bo-silicon', 4, None, 'Cọ Phết Bơ Silicon',
    'Cọ silicon mềm, chịu nhiệt, phết bơ, trứng và siro lên bánh.',
    'cái', 29000, 90, 0, 1, '2025-11-12', [4], ['silicone pastry brush', 'pastry brush'])
add('khuon-cupcake-12-ly', 4, 5, 'Khuôn Cupcake 12 Ly Wilton',
    'Khay 12 ly cupcake/muffin chống dính, dùng được trong lò nướng đến 230°C.',
    'cái', 135000, 28, 0, 1, '2025-12-01', [4, 7], ['muffin tin', 'cupcake pan'])
add('spatula-silicon', 4, None, 'Phới Dẹt Silicon (Spatula)',
    'Spatula silicon chịu nhiệt, vét sạch tô, trộn bột nhẹ tay.',
    'cái', 42000, 75, 0, 1, '2025-12-01', [4], ['silicone spatula', 'spatula'])
add('may-danh-trung-cam-tay', 4, None, 'Máy Đánh Trứng Cầm Tay 5 Tốc Độ',
    'Máy đánh trứng cầm tay công suất 300W, 5 tốc độ, kèm 2 que đánh và 2 que nhồi bột.',
    'cái', 420000, 14, 1, 1, '2025-12-10', [4, 9], ['hand mixer', 'electric hand mixer'])
add('khuon-banh-mi-hop', 4, None, 'Khuôn Bánh Mì Hình Chữ Nhật',
    'Khuôn nướng bánh mì sandwich thép carbon phủ chống dính, kích thước 25x12cm.',
    'cái', 98000, 33, 0, 1, '2026-01-05', [1], ['loaf pan', 'bread loaf tin'])
add('tui-bat-kem-bo-12', 4, 5, 'Bộ Túi Bắt Kem Và Đui Inox 12 Món Wilton',
    'Bộ 8 đui inox, 2 đầu nối, 2 túi bắt kem tái sử dụng để trang trí bánh kem, cupcake.',
    'bộ', 185000, 20, 0, 1, '2026-01-20', [7], ['piping bag nozzle set', 'piping tips'])
add('nhiet-ke-lo-nuong', 4, None, 'Nhiệt Kế Lò Nướng',
    'Nhiệt kế treo lò, thang đo 0-300°C, giúp kiểm soát nhiệt độ nướng chính xác.',
    'cái', 89000, 4, 0, 1, '2026-03-10', [4], ['oven thermometer'])
add('to-tron-inox-24', 4, None, 'Tô Trộn Inox 24cm',
    'Tô inox đáy chống trượt, dùng trộn bột, đánh trứng và ủ bột bánh mì.',
    'cái', 85000, 45, 0, 1, '2026-04-12', [4], ['stainless steel mixing bowl', 'mixing bowl'])

# ---- 5. Hương liệu & Màu thực phẩm ----
add('tinh-chat-vani', 5, 7, 'Tinh Chất Vani Nguyên Chất',
    'Vani nguyên chất từ Madagascar, hương thơm sâu, dùng cho kem, custard và bánh.',
    'chai 60ml', 220000, 20, 1, 1, '2025-11-15', [5, 2], ['vanilla extract', 'vanilla beans'])
add('bot-cacao-nguyen-chat', 5, None, 'Bột Cacao Nguyên Chất',
    'Bột cacao không đường, vị đắng đậm đà, làm bánh brownie, mousse và đồ uống.',
    'gói 250g', 75000, 55, 0, 1, '2025-11-15', [2, 8, 9], ['cocoa powder'])
add('bot-tra-xanh-matcha', 5, None, 'Bột Trà Xanh Matcha',
    'Bột matcha Nhật Bản, màu xanh tươi, dùng làm bánh, kem và đồ uống.',
    'gói 100g', 150000, 45, 1, 1, '2025-11-20', [5, 2], ['matcha powder', 'matcha'])
add('mau-thuc-pham-gel', 5, 5, 'Màu Thực Phẩm Dạng Gel Wilton (Bộ 4 Màu)',
    'Bộ 4 màu gel đậm đặc (đỏ, vàng, xanh dương, xanh lá), chỉ cần vài giọt, không làm loãng kem.',
    'bộ', 120000, 26, 0, 1, '2025-12-08', [7, 5], ['gel food coloring', 'food coloring'])
add('bot-que', 5, None, 'Bột Quế',
    'Bột quế Việt Nam thơm nồng, dùng cho bánh táo, cinnamon roll và đồ uống.',
    'gói 50g', 38000, 65, 0, 1, '2025-12-15', [2], ['ground cinnamon', 'cinnamon sticks'])
add('duong-vani-dr-oetker', 5, 9, 'Đường Vani Dr. Oetker',
    'Đường vani gói nhỏ tiện dụng, tạo hương vani nhẹ cho bánh quy, pancake và custard.',
    'hộp 3 gói', 28000, 90, 0, 1, '2026-02-08', [10, 5], ['vanilla sugar'])
add('ruou-rum-lam-banh', 5, None, 'Rượu Rum Làm Bánh',
    'Rượu rum nâu dùng ngâm trái cây khô và làm bánh trái cây, tiramisu. Sản phẩm đã ngừng nhập.',
    'chai 100ml', 72000, 8, 0, 0, '2025-12-20', [2], ['rum bottle', 'dark rum'])

# ---- 6. Sữa, Kem & Trứng ----
add('kem-tuoi-whipping-cream', 6, 2, 'Kem Tươi Whipping Cream Anchor',
    'Whipping cream 35% béo, đánh bông ổn định, làm kem phủ bánh và mousse.',
    'hộp 1L', 135000, 35, 1, 1, '2025-11-15', [5, 7, 9], ['whipped cream', 'whipping cream'])
add('sua-tuoi-vinamilk', 6, 8, 'Sữa Tươi Tiệt Trùng Không Đường Vinamilk',
    'Sữa tươi 100% không đường, dùng làm bánh, pudding và pha chế.',
    'hộp 1L', 33000, 160, 0, 1, '2025-11-15', [2, 4], ['milk carton', 'fresh milk'])
add('sua-dac-ong-tho', 6, 8, 'Sữa Đặc Có Đường Ông Thọ',
    'Sữa đặc có đường, vị béo ngọt, dùng làm bánh flan, bánh bông lan sữa và cà phê.',
    'lon 380g', 24000, 120, 0, 1, '2025-11-20', [4, 2], ['sweetened condensed milk', 'condensed milk'])
add('phomai-kem-philadelphia', 6, 12, 'Phô Mai Kem Philadelphia',
    'Cream cheese mịn béo, dùng cho cheesecake, frosting và bánh cuộn.',
    'hộp 200g', 79000, 52, 1, 1, '2025-12-02', [5, 7, 9], ['cream cheese'])
add('phomai-mascarpone', 6, None, 'Phô Mai Mascarpone',
    'Mascarpone Ý béo mịn, nguyên liệu chính của tiramisu và các loại kem mousse.',
    'hộp 250g', 95000, 0, 0, 1, '2026-01-12', [5, 7], ['mascarpone'])
add('trung-ga-ta', 6, None, 'Trứng Gà Ta',
    'Trứng gà ta tuyển chọn, lòng đỏ đậm, làm bánh bông lan, custard và bánh flan.',
    'vỉ 10 quả', 38000, 75, 0, 1, '2025-11-15', [2, 4, 9], ['chicken eggs', 'eggs carton'])

# ---- 7. Socola, Hạt & Trái cây sấy ----
add('socola-chip-ban-ngot', 7, None, 'Socola Chip Bán Ngọt',
    'Socola viên nhỏ chịu nhiệt, rắc cookie, muffin và brownie.',
    'gói 250g', 68000, 80, 0, 1, '2025-11-18', [10, 4, 9], ['chocolate chips'])
add('socola-den-callebaut', 7, 10, 'Socola Đen Callebaut 54% (Callets)',
    'Socola đen Bỉ dạng viên dễ tan chảy, dùng làm ganache, mousse và phủ bánh.',
    'gói 400g', 195000, 22, 1, 1, '2025-12-03', [5, 7], ['dark chocolate', 'chocolate pieces'])
add('socola-trang-callebaut', 7, 10, 'Socola Trắng Callebaut (Callets)',
    'Socola trắng Bỉ béo ngọt, dùng làm ganache trắng, mousse và trang trí.',
    'gói 400g', 195000, 0, 0, 1, '2025-12-03', [5, 7], ['white chocolate'])
add('hanh-nhan-lat', 7, None, 'Hạnh Nhân Lát',
    'Hạnh nhân cắt lát mỏng, trang trí và tăng độ giòn cho bánh.',
    'gói 200g', 89000, 50, 0, 1, '2025-11-18', [2, 5], ['sliced almonds', 'almonds'])
add('oc-cho', 7, None, 'Óc Chó (Walnut) Nhập Khẩu',
    'Nhân óc chó giòn bùi, làm brownie, bánh chuối và bánh mì hạt.',
    'gói 200g', 98000, 36, 0, 1, '2025-12-10', [5, 2], ['walnuts', 'walnut kernels'])
add('nho-kho', 7, None, 'Nho Khô Không Hạt',
    'Nho khô mềm ngọt, dùng cho bánh mì nho, cookie yến mạch và bánh trái cây.',
    'gói 250g', 55000, 64, 0, 1, '2025-12-12', [1, 10], ['raisins'])
add('dua-say-soi', 7, None, 'Dừa Sấy Sợi',
    'Dừa nạo sấy khô giòn thơm, dùng làm bánh dừa, cookie và trang trí.',
    'gói 200g', 42000, 58, 0, 1, '2026-01-08', [10, 2], ['desiccated coconut', 'shredded coconut'])
add('nam-viet-quat-say', 7, None, 'Nam Việt Quất Sấy',
    'Cranberry sấy chua ngọt, dùng cho cookie, scone và bánh mì hạt.',
    'gói 250g', 89000, 29, 0, 1, '2026-03-18', [5, 1], ['dried cranberries'])

# ---- 8. Phụ gia & Chất tạo nở ----
add('bot-no-baking-powder', 8, 1, 'Bột Nở Baking Powder',
    'Bột nở không nhôm, giúp bánh nở xốp đều.',
    'hộp 100g', 22000, 140, 0, 1, '2025-11-06', [2, 4, 9], ['baking powder'])
add('baking-soda', 8, None, 'Baking Soda (Muối Nở)',
    'Baking soda dùng cho bánh quy giòn, bánh gừng và pancake nở xốp.',
    'gói 100g', 12000, 170, 0, 1, '2025-11-06', [10, 4], ['baking soda', 'sodium bicarbonate'])
add('men-kho-mauri', 8, 11, 'Men Khô Instant Mauri',
    'Men khô hoạt động nhanh, không cần ủ trước, làm bánh mì, pizza và bánh bao.',
    'gói 100g', 45000, 88, 1, 1, '2025-11-10', [1, 9], ['instant yeast', 'dry yeast'])
add('gelatin-la', 8, None, 'Gelatin Lá',
    'Gelatin dạng lá trong, không mùi, dùng làm mousse, panna cotta và bánh lạnh.',
    'hộp 10 lá', 38000, 47, 0, 1, '2025-12-15', [7, 5], ['gelatin sheets', 'gelatin'])
add('bot-rau-cau-deo', 8, None, 'Bột Rau Câu Dẻo',
    'Bột rau câu dẻo (agar) tạo thạch trong, giòn dai cho rau câu và bánh trung thu.',
    'gói 25g', 14000, 100, 0, 1, '2026-01-25', [4], ['agar powder', 'agar agar'])
add('kem-tartar', 8, None, 'Kem Tartar (Cream of Tartar)',
    'Chất ổn định lòng trắng trứng, giúp meringue và bánh chiffon lên bông bền.',
    'hũ 50g', 52000, 3, 0, 1, '2026-04-20', [2], ['cream of tartar'])
