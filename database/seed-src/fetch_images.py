# -*- coding: utf-8 -*-
"""Tìm ảnh ứng viên trên Wikimedia Commons (chỉ giấy phép tự do) cho từng sản phẩm.

Usage: python3 fetch_images.py <cache_dir> [slug ...]
Ghi <cache_dir>/candidates.json và tải bản thumb 800px vào <cache_dir>/<slug>/<n>.jpg
"""
import json
import os
import re
import sys
import time

import requests

sys.path.insert(0, os.path.dirname(__file__))
from catalog import P  # noqa: E402

API = 'https://commons.wikimedia.org/w/api.php'
HEADERS = {'User-Agent': 'bakery-demo-seed-image-fetch/1.0 (educational demo project)'}
OK_LICENSE = re.compile(r'^(CC0|CC[ -]BY(?!.*-(NC|ND))|CC[ -]BY-SA|Public domain|PD|No restrictions)', re.I)
BAD_LICENSE = re.compile(r'(NC|ND|fair use|GFDL only)', re.I)
BAD_TITLE = re.compile(r'(logo|icon|map|diagram|screenshot|poster|flag|stamp|coat of arms|\.svg|\.png)', re.I)
MAX_PER_PRODUCT = 3


def get(url, **kw):
    """GET with backoff: Wikimedia answers 429 when called too fast."""
    for attempt in range(6):
        r = requests.get(url, headers=HEADERS, timeout=60, **kw)
        if r.status_code != 429:
            r.raise_for_status()
            return r
        time.sleep(15 * (attempt + 1))
    r.raise_for_status()
    return r


def strip_html(value):
    return re.sub(r'<[^>]+>', '', value or '').strip()


def search(query):
    params = {
        'action': 'query', 'format': 'json', 'generator': 'search',
        'gsrsearch': 'filetype:bitmap ' + query, 'gsrnamespace': 6, 'gsrlimit': 20,
        'prop': 'imageinfo', 'iiprop': 'url|extmetadata|size|mime', 'iiurlwidth': 800,
    }
    r = get(API, params=params)
    pages = (r.json().get('query') or {}).get('pages') or {}
    out = []
    for page in sorted(pages.values(), key=lambda p: p.get('index', 0)):
        info = (page.get('imageinfo') or [{}])[0]
        meta = info.get('extmetadata') or {}
        lic = (meta.get('LicenseShortName') or {}).get('value', '')
        title = page.get('title', '')
        if info.get('mime') != 'image/jpeg' or info.get('width', 0) < 700 or info.get('height', 0) < 500:
            continue
        if BAD_TITLE.search(title) or BAD_LICENSE.search(lic) or not OK_LICENSE.search(lic):
            continue
        out.append({
            'title': title,
            'thumb': info.get('thumburl'),
            'page': info.get('descriptionurl'),
            'license': lic,
            'artist': strip_html((meta.get('Artist') or {}).get('value', '')),
            'license_url': (meta.get('LicenseUrl') or {}).get('value', ''),
        })
    return out


def main():
    cache = sys.argv[1]
    only = set(sys.argv[2:])
    os.makedirs(cache, exist_ok=True)
    path = os.path.join(cache, 'candidates.json')
    result = json.load(open(path, encoding='utf-8')) if os.path.exists(path) else {}

    for p in P:
        slug = p['slug']
        if only and slug not in only:
            continue
        done = result.get(slug)
        if done and not only and all(os.path.exists(os.path.join(cache, slug, f'{i}.jpg')) for i in range(1, len(done) + 1)):
            continue
        found, seen = [], set()
        for q in p['queries']:
            try:
                items = search(q)
            except Exception as exc:  # noqa: BLE001
                print('ERR', slug, q, exc)
                items = []
            time.sleep(4)
            for it in items:
                if it['title'] not in seen:
                    seen.add(it['title'])
                    found.append(it)
            if len(found) >= MAX_PER_PRODUCT:
                break
        found = found[:MAX_PER_PRODUCT]
        os.makedirs(os.path.join(cache, slug), exist_ok=True)
        for i, it in enumerate(found, 1):
            dest = os.path.join(cache, slug, f'{i}.jpg')
            if not os.path.exists(dest):
                try:
                    data = get(it['thumb'])
                    open(dest, 'wb').write(data.content)
                except Exception as exc:  # noqa: BLE001
                    print('DL-ERR', slug, i, exc)
                time.sleep(4)
        result[slug] = found
        json.dump(result, open(path, 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
        print(slug, len(found))


if __name__ == '__main__':
    main()
