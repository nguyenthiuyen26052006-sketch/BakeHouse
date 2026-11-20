# -*- coding: utf-8 -*-
"""Chép ảnh đã duyệt từ cache của fetch_images.py vào database/seed-images và ghi CREDITS.md.

Usage: python3 build_images.py <cache_dir>
SELECTION: slug -> (ảnh chính, [ảnh phụ...]) theo số thứ tự ứng viên (1..3) đã xem bằng mắt.
Sản phẩm không có trong SELECTION sẽ không có ảnh (image_path = NULL, app hiện placeholder).
"""
import json
import os
import shutil
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(ROOT, 'seed-images')

SELECTION = {
    'bot-mi-da-dung': (3, []),
    'bot-mi-so-11': (1, []),
    'bot-bap-bien-hoa': (1, []),
    'yen-mach-can-det': (1, []),
    'bot-gao-nep': (1, []),
    'bot-mi-nguyen-cam': (1, []),
    'granola-mix': (2, []),
    'duong-cat-trang': (3, []),
    'duong-nau-muscovado': (3, []),
    'mat-ong-hoa-nhan': (1, []),
    'siro-phong': (1, []),
    'mach-nha': (3, []),
    'golden-syrup': (1, []),
    'bo-lat-anchor': (2, [3]),
    'margarine': (2, []),
    'shortening': (2, []),
    'dau-dua': (1, []),
    'can-dien-tu-5kg': (1, []),
    'phoi-danh-trung-inox': (1, []),
    'giay-nen-nuong-banh': (3, [2]),
    'can-bot-go': (2, []),
    'ray-bot-cam-tay': (2, []),
    'khuon-cupcake-12-ly': (1, []),
    'may-danh-trung-cam-tay': (1, []),
    'khuon-banh-mi-hop': (1, []),
    'nhiet-ke-lo-nuong': (2, []),
    'to-tron-inox-24': (1, []),
    'tinh-chat-vani': (1, []),
    'phomai-kem-philadelphia': (2, []),
    'phomai-mascarpone': (1, []),
    'trung-ga-ta': (3, []),
    'socola-chip-ban-ngot': (1, [2]),
    'socola-den-callebaut': (1, []),
    'oc-cho': (2, []),
    'nho-kho': (3, []),
    'kem-tartar': (1, []),
    'bo-man-anchor': (3, []),
    'co-phet-bo-silicon': (1, []),
    'bot-cacao-nguyen-chat': (3, []),
    'bot-tra-xanh-matcha': (2, []),
    'mau-thuc-pham-gel': (1, []),
    'bot-que': (1, []),
    'kem-tuoi-whipping-cream': (2, []),
    'dua-say-soi': (1, []),
    'nam-viet-quat-say': (2, []),
    'bot-no-baking-powder': (1, []),
    'baking-soda': (1, []),
}


def main():
    cache = sys.argv[1]
    cand = json.load(open(os.path.join(cache, 'candidates.json'), encoding='utf-8'))
    shutil.rmtree(OUT, ignore_errors=True)
    os.makedirs(OUT)
    credits = []
    for slug, (main_idx, extras) in SELECTION.items():
        for n, idx in enumerate([main_idx] + extras):
            src = os.path.join(cache, slug, f'{idx}.jpg')
            if not os.path.exists(src):
                print('THIẾU', slug, idx)
                continue
            name = f'{slug}.jpg' if n == 0 else f'{slug}-{n + 1}.jpg'
            shutil.copyfile(src, os.path.join(OUT, name))
            c = cand[slug][idx - 1]
            credits.append((name, c['title'].removeprefix('File:'), c['artist'] or 'Wikimedia Commons contributor', c['license'], c['page']))
    with open(os.path.join(OUT, 'CREDITS.md'), 'w', encoding='utf-8') as fh:
        fh.write('# Nguồn ảnh seed\n\nẢnh minh hoạ lấy từ Wikimedia Commons (giấy phép tự do), đã thu nhỏ về 800px. '
                 'Ảnh chỉ mang tính minh hoạ loại nguyên liệu, không phải ảnh bao bì thương hiệu.\n\n'
                 '| Tệp | Ảnh gốc | Tác giả | Giấy phép | Nguồn |\n|---|---|---|---|---|\n')
        for name, title, artist, lic, page in credits:
            fh.write(f'| {name} | {title} | {artist.replace("|", "/")[:80]} | {lic} | {page} |\n')
    shutil.copyfile(os.path.join(cache, 'candidates.json'), os.path.join(ROOT, 'seed-src', 'candidates.json'))
    print(len(credits), 'ảnh')


if __name__ == '__main__':
    main()
