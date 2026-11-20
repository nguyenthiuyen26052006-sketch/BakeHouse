# -*- coding: utf-8 -*-
"""Sinh database/init/002_seed.sql (deterministic). Usage: python3 generate.py

Ảnh sản phẩm: nếu có database/seed-images/<slug>.jpg (và <slug>-2.jpg) thì ghi image_path
'uploads/products/seed/<slug>.jpg'; thiếu ảnh thì để NULL (app hiển thị placeholder).
"""
import datetime as dt
import os
import random
import re
import unicodedata

from catalog import BRANDS, CATEGORIES, P, TAGS

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
IMG_DIR = os.path.join(ROOT, 'seed-images')
OUT = os.path.join(ROOT, 'init', '002_seed.sql')

NOW = dt.datetime(2026, 11, 20, 9, 0, 0)
SHIPPING_FEE = 30000
ADMIN_HASH = '$2y$10$cZ6PIYrutJLVS3qZMXx2SuH3izGxt71.fHYa0UZbJHbZRzBDC1zky'  # Admin@123
USER_HASH = '$2y$10$ffNvtCJk5E9am/8wVcEYkegU33Bl0EweSesmfTMTYVhNlCK2zI1Ke'  # User@123

rnd = random.Random(20261120)


def q(v):
    if v is None:
        return 'NULL'
    if isinstance(v, bool):
        return '1' if v else '0'
    if isinstance(v, (int, float)):
        return str(v)
    if isinstance(v, dt.datetime):
        return "'" + v.strftime('%Y-%m-%d %H:%M:%S') + "'"
    return "'" + str(v).replace('\\', '\\\\').replace("'", "''") + "'"


def insert(table, cols, rows):
    if not rows:
        return ''
    out = [f'INSERT INTO {table} ({", ".join(cols)}) VALUES']
    out.append(',\n'.join('(' + ', '.join(q(c) for c in r) + ')' for r in rows) + ';\n')
    return '\n'.join(out)


def slugify(text):
    text = unicodedata.normalize('NFKD', text.replace('đ', 'd').replace('Đ', 'D'))
    text = ''.join(c for c in text if not unicodedata.combining(c))
    return re.sub(r'[^a-z0-9]+', '-', text.lower()).strip('-')


def pdate(s):
    return dt.datetime.strptime(s, '%Y-%m-%d').replace(hour=9)


# ---------------------------------------------------------------- users
USERS = [  # id, email, phone, name, address, active, joined
    (1, 'admin@bakehouse.vn', None, 'Quản trị viên', '1 Nguyễn Huệ, Quận 1, TP.HCM', 1, '2025-11-01'),
    (2, 'lan.pham@gmail.com', '0912345678', 'Phạm Thị Lan', '45 Lê Văn Sỹ, Quận 3, TP.HCM', 1, '2025-11-20'),
    (3, 'user@bakehouse.vn', '0901234567', 'Nguyễn Văn A', '12 Trần Hưng Đạo, Quận 5, TP.HCM', 1, '2025-11-25'),
    (4, 'minh.tran@gmail.com', '0933456789', 'Trần Quang Minh', '88 Nguyễn Trãi, Thanh Xuân, Hà Nội', 1, '2025-12-05'),
    (5, 'huong.le@outlook.com', '0987654321', 'Lê Thu Hương', '23 Hoàng Diệu, Hải Châu, Đà Nẵng', 1, '2025-12-18'),
    (6, 'tuan.vo@gmail.com', '0905112233', 'Võ Anh Tuấn', '156 Điện Biên Phủ, Bình Thạnh, TP.HCM', 1, '2026-01-09'),
    (7, 'ngoc.bui@yahoo.com', '0978223344', 'Bùi Bích Ngọc', '9 Trần Phú, Ninh Kiều, Cần Thơ', 1, '2026-01-22'),
    (8, 'khoa.dang@gmail.com', '0919334455', 'Đặng Đăng Khoa', '71 Phan Chu Trinh, Hoàn Kiếm, Hà Nội', 1, '2026-02-14'),
    (9, 'thao.nguyen@gmail.com', '0961445566', 'Nguyễn Phương Thảo', '202 Nguyễn Thị Minh Khai, Quận 3, TP.HCM', 1, '2026-03-03'),
    (10, 'hieu.hoang@gmail.com', '0932556677', 'Hoàng Trung Hiếu', '34 Lê Lợi, TP. Huế', 1, '2026-04-11'),
    (11, 'yen.truong@gmail.com', '0945667788', 'Trương Hải Yến', '17 Pasteur, Quận 1, TP.HCM', 1, '2026-06-02'),
    (12, 'nam.do@gmail.com', '0908778899', 'Đỗ Hoài Nam', '5 Hùng Vương, Biên Hòa, Đồng Nai', 0, '2026-07-19'),
]
CUSTOMERS = [u for u in USERS if u[0] != 1 and u[5] == 1]
# khách hàng quen mua nhiều hơn: user 3 (demo), 2, 4, 6, 9
WEIGHTS = {2: 5, 3: 6, 4: 4, 5: 3, 6: 5, 7: 2, 8: 2, 9: 4, 10: 2, 11: 2}

# ---------------------------------------------------------------- products
products = []
for i, p in enumerate(P, 1):
    main = os.path.join(IMG_DIR, p['slug'] + '.jpg')
    extra = os.path.join(IMG_DIR, p['slug'] + '-2.jpg')
    p['id'] = i
    p['image'] = f"uploads/products/seed/{p['slug']}.jpg" if os.path.exists(main) else None
    p['image2'] = f"uploads/products/seed/{p['slug']}-2.jpg" if os.path.exists(extra) else None
    p['created_dt'] = pdate(p['created'])
    products.append(p)
by_id = {p['id']: p for p in products}

# ---------------------------------------------------------------- orders
# số đơn theo tháng (mùa lễ/Tết nhiều hơn)
MONTHS = [(2025, 12, 4), (2026, 1, 4), (2026, 2, 5), (2026, 3, 3), (2026, 4, 3), (2026, 5, 3),
          (2026, 6, 3), (2026, 7, 3), (2026, 8, 3), (2026, 9, 4), (2026, 10, 5), (2026, 11, 7)]
orders = []
for y, m, n in MONTHS:
    last_day = 18 if (y, m) == (2026, 11) else 28
    days = sorted(rnd.sample(range(2 if (y, m) == (2025, 12) else 1, last_day + 1), n))
    for d in days:
        created = dt.datetime(y, m, d, rnd.randint(8, 21), rnd.choice([0, 7, 15, 22, 31, 40, 48, 55]))
        orders.append({'created': created})
orders.sort(key=lambda o: o['created'])

user_ids = list(WEIGHTS)
user_w = [WEIGHTS[u] for u in user_ids]
HIST_ORDER = ['pending', 'processing', 'shipped', 'delivered']
order_items, histories = [], []
for oid, o in enumerate(orders, 1):
    o['id'] = oid
    o['code'] = f'BH{oid:02d}'
    # chỉ khách đã đăng ký trước ngày đặt
    pool = [(u, w) for u, w in zip(user_ids, user_w) if pdate(USERS[u - 1][6]) <= o['created']]
    o['user'] = rnd.choices([u for u, _ in pool], [w for _, w in pool])[0]
    age = (NOW - o['created']).days
    if age >= 14:
        status = 'delivered'
    elif age >= 8:
        status = rnd.choice(['shipped', 'delivered', 'delivered'])
    elif age >= 5:
        status = rnd.choice(['processing', 'shipped'])
    elif age >= 3:
        status = rnd.choice(['pending', 'processing'])
    else:
        status = 'pending'
    o['status'] = status
    o['payment_method'] = rnd.choices(['cod', 'vietqr'], [5, 5])[0]
    if status == 'delivered':
        o['payment_status'] = 'paid'
    elif o['payment_method'] == 'vietqr':
        o['payment_status'] = 'paid' if status in ('processing', 'shipped') else 'unpaid'
    else:
        o['payment_status'] = 'unpaid'

    avail = [p for p in products if p['created_dt'] <= o['created'] and (p['active'] or p['created_dt'] < o['created'] - dt.timedelta(days=30))]
    # đơn hàng gần đây không chứa sản phẩm hết hàng / ngừng bán
    if age < 30:
        avail = [p for p in avail if p['active'] and p['stock'] > 0]
    n_items = rnd.choices([1, 2, 3, 4, 5], [2, 4, 4, 2, 1])[0]
    picked = rnd.sample(avail, min(n_items, len(avail)))
    items = []
    for p in picked:
        qty = 1 if p['cat'] == 4 and p['price'] > 100000 else rnd.choices([1, 2, 3, 4], [5, 4, 2, 1])[0]
        items.append((p, qty))
    o['items'] = items
    o['subtotal'] = sum(p['price'] * qty for p, qty in items)
    o['total'] = o['subtotal'] + SHIPPING_FEE
    o['note'] = rnd.choice([None, None, None, None, 'Giao giờ hành chính giúp mình nhé.', 'Gọi trước khi giao, cảm ơn shop!',
                            'Đóng gói kỹ giúp mình, hàng làm quà tặng.', 'Giao buổi tối sau 18h.'])

# Tài khoản demo (user 3) phải có đủ đơn ở các trạng thái chưa hoàn tất để demo theo dõi đơn / chatbot
for st in ('pending', 'processing', 'shipped'):
    same = [o for o in orders if o['status'] == st]
    if same and not any(o['user'] == 3 for o in same):
        same[-1]['user'] = 3

for o in orders:
    oid, status = o['id'], o['status']
    u = USERS[o['user'] - 1]
    o['recipient'], o['phone'], o['address'] = u[3], u[2], u[4]
    # lịch sử trạng thái tăng dần
    t = o['created']
    histories.append((oid, None, 'pending', o['user'], t))
    last = t
    prev = 'pending'
    for st in HIST_ORDER[1:HIST_ORDER.index(status) + 1]:
        t = t + dt.timedelta(hours=rnd.randint(3, 20)) if st == 'processing' else t + dt.timedelta(hours=rnd.randint(16, 30)) if st == 'shipped' else t + dt.timedelta(hours=rnd.randint(30, 60))
        histories.append((oid, prev, st, 1, t))
        prev, last = st, t
    o['updated'] = last

# ---------------------------------------------------------------- reviews
GOOD = {
    1: ['Bột mịn, làm bánh rất xốp và lên đều.', 'Chất lượng ổn định, mình mua lại nhiều lần.', 'Đóng gói chắc chắn, bột trắng mịn không vón cục.'],
    2: ['Đường tơi, dễ tan, vị ngọt thanh.', 'Giá hợp lý, chất lượng tốt cho làm bánh.', 'Dùng làm bánh rất ổn, giao hàng nhanh.'],
    3: ['Béo thơm, làm bánh quy bơ rất đã.', 'Chất lượng tốt, đúng hàng nhập khẩu.', 'Dùng làm kem bơ rất mịn, rất đáng mua.'],
    4: ['Dụng cụ chắc chắn, dùng rất tiện.', 'Chất liệu tốt, dễ vệ sinh, đúng như mô tả.', 'Mua về dùng thấy rất hài lòng, giá hợp lý.'],
    5: ['Hương thơm tự nhiên, không bị gắt.', 'Màu lên đẹp, chất lượng tốt.', 'Thơm, dùng ít mà đậm hương, rất thích.'],
    6: ['Hàng tươi, hạn dùng dài, đóng gói cẩn thận.', 'Đánh bông rất bền, làm bánh kem ổn định.', 'Vị béo mịn, rất hợp làm tráng miệng.'],
    7: ['Socola thơm, tan mịn, làm ganache đẹp.', 'Hạt giòn, tươi, không bị ẩm.', 'Chất lượng tốt, đóng gói kín.'],
    8: ['Bánh nở xốp hơn hẳn khi dùng loại này.', 'Hoạt động tốt, mình làm bánh mì lên rất đẹp.', 'Đúng loại cần dùng, giao nhanh.'],
}
OK_ = ['Tạm ổn, đúng mô tả nhưng giá hơi cao.', 'Sản phẩm bình thường, dùng được.', 'Giao hơi chậm nhưng hàng ổn.']
BAD = ['Đóng gói chưa kỹ, hộp bị móp khi nhận.', 'Chất lượng không như mong đợi, hơi thất vọng.', 'Hàng có mùi lạ, mình chưa dùng được.']
REPLY_BAD = 'BakeHouse xin lỗi vì trải nghiệm chưa tốt. Bạn vui lòng liên hệ hotline để được hỗ trợ đổi/trả nhé.'
REPLY_OK = 'Cảm ơn bạn đã ủng hộ BakeHouse! Chúc bạn làm bánh thật thành công.'

reviews = []
seen_pair = set()
for o in orders:
    if o['status'] != 'delivered':
        continue
    for p, qty in o['items']:
        key = (o['user'], p['id'])
        if key in seen_pair or rnd.random() > 0.62:
            continue
        seen_pair.add(key)
        rating = rnd.choices([5, 4, 3, 2, 1], [50, 30, 12, 5, 3])[0]
        text = rnd.choice(GOOD[p['cat']] if rating >= 4 else OK_ if rating == 3 else BAD)
        created = o['updated'] + dt.timedelta(days=rnd.randint(1, 9), hours=rnd.randint(0, 10))
        if created > NOW:
            continue
        reply, replied_at = None, None
        if rating <= 2 or rnd.random() < 0.12:
            reply = REPLY_BAD if rating <= 2 else REPLY_OK
            replied_at = created + dt.timedelta(hours=rnd.randint(4, 40))
        reviews.append({'user': o['user'], 'product': p['id'], 'rating': rating, 'comment': text,
                        'hidden': 0, 'reply': reply, 'replied_at': replied_at, 'created': created})

# ---------------------------------------------------------------- carts / contacts / banners
name_to_id = {p['slug']: p['id'] for p in products}
CART_BY_SLUG = {3: [('bot-mi-da-dung', 2), ('bo-lat-anchor', 1), ('phoi-danh-trung-inox', 1)],
                2: [('kem-tuoi-whipping-cream', 1), ('phomai-kem-philadelphia', 2)],
                6: [('men-kho-mauri', 1), ('bot-mi-so-11', 2), ('khuon-banh-mi-hop', 1)],
                9: [('tinh-chat-vani', 1), ('socola-den-callebaut', 1)]}

CONTACTS = [
    (2, 'Phạm Thị Lan', 'lan.pham@gmail.com', '0912345678', 'Shop có bán sỉ bột mì và bơ cho tiệm bánh nhỏ không ạ? Mình cần khoảng 20kg mỗi tuần.', 'replied',
     'Chào bạn, BakeHouse có chính sách giá sỉ từ 10kg/tuần. Bạn vui lòng để lại số điện thoại, nhân viên sẽ gọi báo giá trong ngày nhé.', '2026-03-12 10:20:00', '2026-03-11 21:05:00'),
    (None, 'Ngô Thị Mai', 'mai.ngo@gmail.com', '0936778899', 'Mình muốn hỏi phô mai mascarpone khi nào có hàng lại vậy shop?', 'replied',
     'Cảm ơn bạn, mascarpone dự kiến về hàng trong tuần sau. Shop sẽ báo ngay khi có hàng ạ.', '2026-08-04 09:00:00', '2026-08-03 18:40:00'),
    (4, 'Trần Quang Minh', 'minh.tran@gmail.com', '0933456789', 'Đơn hàng của mình giao đến bị móp hộp bột nở, shop hỗ trợ đổi giúp mình được không?', 'replied',
     'BakeHouse xin lỗi bạn về sự cố. Bạn gửi ảnh sản phẩm qua email để shop đổi hàng mới miễn phí nhé.', '2026-05-22 14:10:00', '2026-05-21 20:15:00'),
    (None, 'Lý Thanh Tùng', 'tung.ly@gmail.com', None, 'Mình ở Đà Lạt, shop có giao hàng đến đây không và phí ship thế nào?', 'read', None, None, '2026-09-15 08:30:00'),
    (9, 'Nguyễn Phương Thảo', 'thao.nguyen@gmail.com', '0961445566', 'Shop tư vấn giúp mình bộ dụng cụ làm cupcake cho người mới bắt đầu với ngân sách khoảng 500k.', 'read', None, None, '2026-10-02 19:45:00'),
    (None, 'Đinh Văn Long', None, '0977112233', 'Cho mình hỏi hóa đơn VAT cho đơn hàng công ty thì liên hệ thế nào?', 'new', None, None, '2026-11-12 11:20:00'),
    (6, 'Võ Anh Tuấn', 'tuan.vo@gmail.com', '0905112233', 'Cân điện tử mình mua bị lệch khoảng 3g so với quả cân chuẩn, shop kiểm tra giúp mình với.', 'new', None, None, '2026-11-16 22:05:00'),
    (None, 'Phan Kim Chi', 'chi.phan@gmail.com', '0944223311', 'Shop có chương trình ưu đãi cho khách mua nhiều trong dịp Noel không ạ?', 'new', None, None, '2026-11-18 15:40:00'),
]

BANNERS = [
    ('Nguyên liệu làm bánh chất lượng', 'assets/img/banner-1.svg', '/products', 1),
    ('Ưu đãi dụng cụ làm bánh', 'assets/img/banner-2.svg', '/products?category=dung-cu-lam-banh', 1),
    ('Bơ & kem tươi nhập khẩu', 'assets/img/banner-3.svg', '/products?category=chat-beo', 1),
]

# ---------------------------------------------------------------- write
out = ['-- File được sinh tự động bởi database/seed-src/generate.py — đừng sửa tay, hãy sửa catalog.py/generate.py rồi chạy lại.',
       'SET NAMES utf8mb4;', '']
out.append(insert('users', ['id', 'email', 'phone', 'password_hash', 'role', 'is_active', 'created_at'],
                  [(u[0], u[1], u[2], ADMIN_HASH if u[0] == 1 else USER_HASH, 'admin' if u[0] == 1 else 'user', u[5], pdate(u[6])) for u in USERS]))
out.append(insert('user_profiles', ['user_id', 'full_name', 'shipping_phone', 'shipping_address'],
                  [(u[0], u[3], u[2] or '0900000001', u[4]) for u in USERS]))
out.append(insert('carts', ['user_id'], [(u[0],) for u in USERS]))
out.append(insert('categories', ['id', 'name', 'slug', 'description'], CATEGORIES))
out.append(insert('brands', ['id', 'name', 'slug'], [(i, n, slugify(n)) for i, n in BRANDS]))
out.append(insert('tags', ['id', 'name', 'slug'], [(i, n, slugify(n)) for i, n in TAGS]))
out.append(insert(
    'products',
    ['id', 'category_id', 'brand_id', 'name', 'slug', 'description', 'unit', 'price', 'stock_quantity', 'image_path',
     'is_featured', 'is_active', 'created_at', 'updated_at'],
    [(p['id'], p['cat'], p['brand'], p['name'], p['slug'], p['desc'], p['unit'], p['price'], p['stock'], p['image'],
      p['featured'], p['active'], p['created_dt'], p['created_dt']) for p in products]))
out.append(insert('product_images', ['product_id', 'image_path', 'is_primary'],
                  [(p['id'], p['image'], 1) for p in products if p['image']]
                  + [(p['id'], p['image2'], 0) for p in products if p['image2']]))
out.append(insert('product_tags', ['product_id', 'tag_id'], [(p['id'], t) for p in products for t in sorted(set(p['tags']))]))

cart_rows = [('(SELECT id FROM carts WHERE user_id = %d)' % uid, name_to_id[slug], qty)
             for uid, lines in CART_BY_SLUG.items() for slug, qty in lines]
out.append('INSERT INTO cart_items (cart_id, product_id, quantity) VALUES\n'
           + ',\n'.join('(%s, %d, %d)' % r for r in cart_rows) + ';\n')
out.append(insert('banners', ['title', 'image_path', 'link_url', 'is_active'], BANNERS))

out.append(insert(
    'orders',
    ['id', 'order_code', 'user_id', 'recipient_name', 'shipping_phone', 'shipping_address', 'note', 'payment_method',
     'payment_status', 'status', 'shipping_fee', 'total_amount', 'created_at', 'updated_at'],
    [(o['id'], o['code'], o['user'], o['recipient'], o['phone'], o['address'], o['note'], o['payment_method'],
      o['payment_status'], o['status'], SHIPPING_FEE, o['total'], o['created'], o['updated']) for o in orders]))
out.append(insert(
    'order_items',
    ['order_id', 'product_id', 'product_name', 'unit_price', 'quantity', 'status', 'created_at', 'updated_at'],
    [(o['id'], p['id'], p['name'], p['price'], qty, o['status'], o['created'], o['updated']) for o in orders for p, qty in o['items']]))
out.append(insert('order_status_histories', ['order_id', 'from_status', 'to_status', 'changed_by', 'created_at'], histories))
out.append(insert(
    'reviews',
    ['user_id', 'product_id', 'rating', 'comment', 'is_hidden', 'admin_reply', 'admin_replied_at', 'created_at', 'updated_at'],
    [(r['user'], r['product'], r['rating'], r['comment'], r['hidden'], r['reply'], r['replied_at'], r['created'], r['created'])
     for r in sorted(reviews, key=lambda r: r['created'])]))
out.append(insert(
    'contacts',
    ['user_id', 'full_name', 'email', 'phone', 'message', 'status', 'admin_reply', 'admin_replied_at', 'created_at', 'updated_at'],
    [(c[0], c[1], c[2], c[3], c[4], c[5], c[6], c[7], c[8], c[7] or c[8]) for c in CONTACTS]))

out.append("""-- Điểm đánh giá lấy từ các review đang hiển thị (cùng công thức ReviewRepository)
UPDATE products SET
    rating_avg = COALESCE((SELECT ROUND(AVG(r.rating), 2) FROM reviews r WHERE r.product_id = products.id AND r.is_hidden = 0), 0),
    rating_count = (SELECT COUNT(*) FROM reviews r WHERE r.product_id = products.id AND r.is_hidden = 0),
    updated_at = updated_at;
""")

with open(OUT, 'w', encoding='utf-8', newline='\n') as fh:
    fh.write('\n'.join(out))

print('products', len(products), 'orders', len(orders), 'items', sum(len(o['items']) for o in orders),
      'reviews', len(reviews), 'with image', sum(1 for p in products if p['image']))
