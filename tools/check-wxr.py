#!/usr/bin/env python3
"""生成した WXR を検証する。

壊れたWXRはインポート途中で黙って止まることがあるので、読み込む前にここで見る。
- XMLとして妥当か
- 投稿タイプごとの件数
- スラッグが URL移行マップ と完全一致するか（1本でも違えばSEOを失う）
- 日本語スラッグが生の日本語で入っているか（パーセントエンコードされていないか）
- ACFのフィールドキーが acf-json/ に実在するか
- 添付URLが全て uploads/ を指しているか
"""
import json
import pathlib
import re
import sys
import urllib.parse
import xml.etree.ElementTree as ET

ROOT = pathlib.Path(__file__).resolve().parent.parent
XML = ROOT / 'import/torasake-content.xml'
NS = {'wp': 'http://wordpress.org/export/1.2/',
      'content': 'http://purl.org/rss/1.0/modules/content/',
      'excerpt': 'http://wordpress.org/export/1.2/excerpt/'}

fail = 0


def ok(cond, msg, detail=''):
    global fail
    if not cond:
        fail += 1
    print(f"  {'✓' if cond else '✗'} {msg}{('  ' + detail) if detail and not cond else ''}")


try:
    tree = ET.parse(XML)
except ET.ParseError as e:
    print(f'  ✗ XMLとして壊れている: {e}')
    sys.exit(1)
ok(True, 'XMLとして妥当')

items = tree.getroot().find('channel').findall('item')
by_type = {}
for it in items:
    t = it.find('wp:post_type', NS).text
    by_type.setdefault(t, []).append(it)

print()
for t, lst in sorted(by_type.items()):
    print(f'    {t:12s} {len(lst):3d}件')
print()

ok(len(by_type.get('brewery', [])) == 33, '酒蔵 33件')
ok(len(by_type.get('post', [])) == 5, 'お知らせ 5件')
ok(len(by_type.get('event', [])) == 2, 'イベント 2件')
ok(len(by_type.get('past_event', [])) == 1, '過去開催 1件')

# スラッグが移行マップと完全一致するか
map_slugs = set(x.strip() for x in re.search(
    r'酒蔵スラッグ33件: `([^`]+)`',
    (ROOT / 'docs/design_handoff_wordpress/URL移行マップ.md').read_text(encoding='utf-8')
).group(1).split(','))
got = {i.find('wp:post_name', NS).text for i in by_type.get('brewery', [])}
ok(got == map_slugs, '酒蔵スラッグが URL移行マップ と完全一致',
   f'余分={got - map_slugs} 不足={map_slugs - got}')

NEWS_SLUGS = {'2026-07-03-event-report', '2026-06-27-当日券', '2026-05-28-matching',
              '2026-05-25-event-system', '2026-05-21-開催決定'}
news_got = {i.find('wp:post_name', NS).text for i in by_type.get('post', [])}
ok(news_got == NEWS_SLUGS, 'お知らせスラッグが現行ファイル名と一致',
   f'余分={news_got - NEWS_SLUGS} 不足={NEWS_SLUGS - news_got}')

jp = [s for s in news_got if re.search(r'[^\x00-\x7F]', s)]
ok(len(jp) == 2, '日本語スラッグが2本ある', str(jp))
ok(not any('%' in s for s in news_got),
   '日本語スラッグが生の日本語（パーセントエンコードされていない）')

# ACFキーの実在確認
keys = set()
for p in (ROOT / 'themes/torasake/acf-json').glob('*.json'):
    def walk(fs):
        for f in fs:
            keys.add(f['key'])
            walk(f.get('sub_fields', []))
    walk(json.loads(p.read_text(encoding='utf-8'))['fields'])

used, bad = set(), set()
for it in items:
    for pm in it.findall('wp:postmeta', NS):
        k = pm.find('wp:meta_key', NS).text or ''
        v = pm.find('wp:meta_value', NS).text or ''
        if k.startswith('_') and v.startswith('field_'):
            used.add(v)
            if v not in keys:
                bad.add(v)
ok(not bad, f'ACFフィールドキーが全て実在する（{len(used)}種）', str(sorted(bad)))

# 添付URL
atts = by_type.get('attachment', [])
urls = [a.find('wp:attachment_url', NS).text for a in atts]
ok(all(u.startswith('https://torasake.com/uploads/') for u in urls),
   f'添付URLが全て /uploads/ を指している（{len(urls)}件）')
bad_enc = [u for u in urls if re.search(r'[^\x00-\x7F]', u)]
ok(not bad_enc, '添付URLがURLエンコード済み', str(bad_enc[:3]))

# サムネイルの参照先が実在する添付か
att_ids = {a.find('wp:post_id', NS).text for a in atts}
missing = set()
for it in items:
    for pm in it.findall('wp:postmeta', NS):
        if (pm.find('wp:meta_key', NS).text or '') == '_thumbnail_id':
            v = pm.find('wp:meta_value', NS).text
            if v not in att_ids:
                missing.add(v)
ok(not missing, 'アイキャッチの参照先が全て添付アイテムに存在', str(missing))

# 価格が酒蔵に入っていないこと（CLAUDE.md #1）
price_in_brewery = []
for it in by_type.get('brewery', []):
    for pm in it.findall('wp:postmeta', NS):
        k = (pm.find('wp:meta_key', NS).text or '').lower()
        if any(w in k for w in ('price', 'cost', 'fee')):
            price_in_brewery.append(k)
ok(not price_in_brewery, '酒蔵に価格系メタが無い（CLAUDE.md #1）', str(price_in_brewery))

# Peatix の utm_source
peatix = [pm.find('wp:meta_value', NS).text for it in items
          for pm in it.findall('wp:postmeta', NS)
          if 'peatix' in (pm.find('wp:meta_value', NS).text or '')]
ok(peatix and all('utm_source=hp' in u for u in peatix),
   f'PeatixリンクにutmSourceが残っている（{len(peatix)}箇所）')

print()
print('  ✗ あり' if fail else '  ✓ 全項目パス')
sys.exit(1 if fail else 0)
