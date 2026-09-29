#!/usr/bin/env python3
"""WordPress インポート用の WXR を生成する。

参照HTML（docs/design_handoff_wordpress/reference/）に入っている実データから、
酒蔵33件・お知らせ5件・イベント2件・過去開催1件と、それらが使う画像の
添付アイテムを組み立てる。ACFのフィールドキーは acf-json/ から読むので、
フィールド定義を変えたらここも自動で追従する。

    python3 tools/make-wxr.py

出力: import/torasake-content.xml

■ 前提（順番を守ること）
  1. テーマを有効化してから読み込む。
     未登録の投稿タイプはインポーターに無視されるため、テーマが有効でないと
     酒蔵もイベントも1件も入らない。
  2. 画像は本番 https://torasake.com/uploads/ から取得する。
     インポート時に「添付ファイルをダウンロードしてインポートする」にチェックを入れる。

■ 入らないもの（インポート後に手で入れる）
  - イベントの「参加酒蔵」（relationship）
    WordPressのインポーターは投稿IDを振り直すが、relationshipのメタは
    振り直してくれないため、壊れたIDを入れるくらいなら空にしてある。
    インポート後にイベント編集画面で10蔵を選ぶ（数クリック）。
  - お知らせ4件の本文（参照HTMLに抜粋しか無いため下書きで入る）
  - 蔵の物語・タグライン・出展銘柄の詳細（参照HTMLに無い）
"""
import html
import json
import pathlib
import re
import urllib.parse
from datetime import datetime, timezone, timedelta

ROOT = pathlib.Path(__file__).resolve().parent.parent
REF = ROOT / 'docs/design_handoff_wordpress/reference'
ACF_DIR = ROOT / 'themes/torasake/acf-json'
OUT = ROOT / 'import/torasake-content.xml'

SITE = 'https://torasake.com'
UPLOADS = f'{SITE}/uploads'
AUTHOR = 'torasake'
JST = timezone(timedelta(hours=9))
NOW = datetime.now(JST).strftime('%Y-%m-%d %H:%M:%S')

# ── ACF フィールドキー ────────────────────────────────────────
KEYS: dict[str, dict[str, str]] = {}


def _walk(fields, group, prefix=''):
    for f in fields:
        KEYS[group][prefix + f['name']] = f['key']
        _walk(f.get('sub_fields', []), group, prefix + f['name'] + '.')


for p in sorted(ACF_DIR.glob('*.json')):
    d = json.loads(p.read_text(encoding='utf-8'))
    g = d['key'].replace('group_', '')
    KEYS[g] = {}
    _walk(d['fields'], g)


def key(group: str, name: str) -> str:
    k = KEYS.get(group, {}).get(name)
    if not k:
        raise SystemExit(f'ACFキーが見つからない: {group}.{name}')
    return k


# ── 都道府県のスラッグ ────────────────────────────────────────
PREF_SLUG = {
    '北海道': 'hokkaido', '青森県': 'aomori', '岩手県': 'iwate', '宮城県': 'miyagi',
    '秋田県': 'akita', '山形県': 'yamagata', '福島県': 'fukushima', '茨城県': 'ibaraki',
    '栃木県': 'tochigi', '群馬県': 'gunma', '埼玉県': 'saitama', '千葉県': 'chiba',
    '東京都': 'tokyo', '神奈川県': 'kanagawa', '新潟県': 'niigata', '富山県': 'toyama',
    '石川県': 'ishikawa', '福井県': 'fukui', '山梨県': 'yamanashi', '長野県': 'nagano',
    '岐阜県': 'gifu', '静岡県': 'shizuoka', '愛知県': 'aichi', '三重県': 'mie',
    '滋賀県': 'shiga', '京都府': 'kyoto', '大阪府': 'osaka', '兵庫県': 'hyogo',
    '奈良県': 'nara', '和歌山県': 'wakayama', '鳥取県': 'tottori', '島根県': 'shimane',
    '岡山県': 'okayama', '広島県': 'hiroshima', '山口県': 'yamaguchi', '徳島県': 'tokushima',
    '香川県': 'kagawa', '愛媛県': 'ehime', '高知県': 'kochi', '福岡県': 'fukuoka',
    '佐賀県': 'saga', '長崎県': 'nagasaki', '熊本県': 'kumamoto', '大分県': 'oita',
    '宮崎県': 'miyazaki', '鹿児島県': 'kagoshima', '沖縄県': 'okinawa',
}

EDITIONS = {
    'torasake-2026': 'TORASAKE -虎ノ酒- 2026',
    'torasake-mini-2026': 'TORASAKE mini',
}
CATEGORIES = {'report': '開催報告', 'info': 'お知らせ', 'guide': 'イベントガイド'}

# ── XML 組み立ての小道具 ──────────────────────────────────────
_id = 1000


def next_id() -> int:
    global _id
    _id += 1
    return _id


def cdata(s) -> str:
    return '<![CDATA[' + str(s).replace(']]>', ']]]]><![CDATA[>') + ']]>'


def esc(s) -> str:
    return html.escape(str(s), quote=False)


def enc_url(path: str) -> str:
    """非ASCIIのファイル名をURLエンコードする。"""
    return f'{UPLOADS}/' + urllib.parse.quote(path)


def meta(k: str, v) -> str:
    return (f'\t\t<wp:postmeta>\n\t\t\t<wp:meta_key>{cdata(k)}</wp:meta_key>\n'
            f'\t\t\t<wp:meta_value>{cdata(v)}</wp:meta_value>\n\t\t</wp:postmeta>\n')


def acf(group: str, name: str, value) -> str:
    """ACFの値と、それが指すフィールドキーの2行を書き出す。"""
    return meta(name, value) + meta('_' + name, key(group, name))


def acf_repeater(group: str, name: str, rows: list[dict]) -> str:
    """repeater は 行数 + row_N_subfield を並べる。"""
    out = meta(name, len(rows)) + meta('_' + name, key(group, name))
    for i, row in enumerate(rows):
        for sub, val in row.items():
            out += meta(f'{name}_{i}_{sub}', val)
            out += meta(f'_{name}_{i}_{sub}', key(group, f'{name}.{sub}'))
    return out


def item(*, title, name, post_type, post_id, status='publish', date=None,
         content='', excerpt='', parent=0, terms=(), metas='', attachment_url=None,
         menu_order=0) -> str:
    date = date or NOW
    link = f'{SITE}/?p={post_id}'
    x = ['\t<item>']
    x.append(f'\t\t<title>{cdata(title)}</title>')
    x.append(f'\t\t<link>{esc(link)}</link>')
    x.append(f'\t\t<pubDate>{esc(datetime.strptime(date, "%Y-%m-%d %H:%M:%S").strftime("%a, %d %b %Y %H:%M:%S +0900"))}</pubDate>')
    x.append(f'\t\t<dc:creator>{cdata(AUTHOR)}</dc:creator>')
    x.append(f'\t\t<guid isPermaLink="false">{esc(link)}</guid>')
    x.append('\t\t<description></description>')
    x.append(f'\t\t<content:encoded>{cdata(content)}</content:encoded>')
    x.append(f'\t\t<excerpt:encoded>{cdata(excerpt)}</excerpt:encoded>')
    x.append(f'\t\t<wp:post_id>{post_id}</wp:post_id>')
    x.append(f'\t\t<wp:post_date>{cdata(date)}</wp:post_date>')
    x.append(f'\t\t<wp:post_date_gmt>{cdata(date)}</wp:post_date_gmt>')
    x.append('\t\t<wp:comment_status>closed</wp:comment_status>')
    x.append('\t\t<wp:ping_status>closed</wp:ping_status>')
    x.append(f'\t\t<wp:post_name>{cdata(name)}</wp:post_name>')
    x.append(f'\t\t<wp:status>{cdata(status)}</wp:status>')
    x.append(f'\t\t<wp:post_parent>{parent}</wp:post_parent>')
    x.append(f'\t\t<wp:menu_order>{menu_order}</wp:menu_order>')
    x.append(f'\t\t<wp:post_type>{cdata(post_type)}</wp:post_type>')
    x.append('\t\t<wp:post_password></wp:post_password>')
    x.append('\t\t<wp:is_sticky>0</wp:is_sticky>')
    if attachment_url:
        x.append(f'\t\t<wp:attachment_url>{cdata(attachment_url)}</wp:attachment_url>')
    for domain, slug, label in terms:
        x.append(f'\t\t<category domain="{esc(domain)}" nicename="{esc(slug)}">{cdata(label)}</category>')
    body = '\n'.join(x) + '\n'
    if metas:
        body += metas
    return body + '\t</item>\n'


# ── 参照HTMLから酒蔵33件を抜く ────────────────────────────────
block = re.search(r'const breweries = \[(.*?)\n\];',
                  (REF / 'brewery-archive.html').read_text(encoding='utf-8'), re.S).group(1)
BREWERIES = []
for m in re.finditer(
        r'\{\s*name:\s*"([^"]*)",\s*sake:\s*"([^"]*)",\s*pref:\s*"([^"]*)",'
        r'\s*img:\s*"([^"]*)",\s*slug:\s*"([^"]*)",\s*events:\s*\[([^\]]*)\]', block):
    n, sake, pref, img, slug, ev = m.groups()
    BREWERIES.append(dict(
        name=n, sake=sake, pref=pref, slug=slug,
        img=img.replace('../uploads/', ''),
        events=[e.strip().strip('"') for e in ev.split(',') if e.strip()],
    ))
assert len(BREWERIES) == 33, f'酒蔵が33件ではない: {len(BREWERIES)}'

# 移行マップの33スラッグと突き合わせる（1本でも違ったら止める）
map_slugs = set(x.strip() for x in re.search(
    r'酒蔵スラッグ33件: `([^`]+)`',
    (ROOT / 'docs/design_handoff_wordpress/URL移行マップ.md').read_text(encoding='utf-8')
).group(1).split(','))
got = {b['slug'] for b in BREWERIES}
if got != map_slugs:
    raise SystemExit(f'スラッグ不一致\n  余分: {got - map_slugs}\n  不足: {map_slugs - got}')

# ── 画像を添付アイテムにする ──────────────────────────────────
IMAGES = {}   # ファイル名 → 添付の仮ID


def attachment(filename: str) -> int:
    if filename in IMAGES:
        return IMAGES[filename]
    IMAGES[filename] = next_id()
    return IMAGES[filename]


for b in BREWERIES:
    if b['img']:
        attachment(b['img'])
KV = attachment('OOSHIMA-KV-0903_16-9.jpg')
VENUE = attachment('toranomonhirls.webp')
CROWD = attachment('torasake2026daiseikyou.jpg')
FLYER = attachment('flyer-keyvisual.jpg')

attachment_items = ''
for fname, aid in IMAGES.items():
    stem = pathlib.Path(fname).stem
    attachment_items += item(
        title=stem, name=re.sub(r'[^a-z0-9\-]+', '-', stem.lower()).strip('-') or f'img-{aid}',
        post_type='attachment', post_id=aid, status='inherit',
        attachment_url=enc_url(fname),
    )

# ── 酒蔵 ──────────────────────────────────────────────────────
brewery_items = ''
for i, b in enumerate(BREWERIES):
    terms = []
    if b['pref'] in PREF_SLUG:
        terms.append(('prefecture', PREF_SLUG[b['pref']], b['pref']))
    for ev in b['events']:
        terms.append(('event_edition', ev, EDITIONS.get(ev, ev)))

    m = ''
    if b['img']:
        m += meta('_thumbnail_id', IMAGES[b['img']])
    m += acf('brewery', 'brand_name', b['sake'].split()[0])
    m += acf('brewery', 'lineup_summary', b['sake'])

    brewery_items += item(
        title=b['name'], name=b['slug'], post_type='brewery',
        post_id=next_id(), terms=terms, metas=m, menu_order=i,
    )

# ── お知らせ ──────────────────────────────────────────────────
# 開催報告だけ参照HTMLに全文がある。残り4件は抜粋のみなので下書きで入れる。
report_html = re.search(r'<article class="article-body">(.*?)</article>',
                        (REF / 'news-single-example.html').read_text(encoding='utf-8'), re.S).group(1)
report_html = report_html.replace('../uploads/', f'{UPLOADS}/')
# 画像パスの空白・括弧をエンコードする
report_html = re.sub(
    r'(https://torasake\.com/uploads/)([^"\']+)',
    lambda m: m.group(1) + urllib.parse.quote(m.group(2)), report_html)

NEWS = [
    dict(slug='2026-07-03-event-report', date='2026-07-03 10:00:00', cat='report',
         title='盛況のうちに終了いたしました ─ 開催報告とお礼',
         excerpt='6月27日開催の「TORASAKE -虎ノ酒- 2026」は、多くのお客様にご来場いただき大盛況のうちに終了いたしました。ご来場・ご協力いただいたすべての皆様に、心より御礼申し上げます。',
         thumb=CROWD, content=report_html.strip(), status='publish'),
    dict(slug='2026-06-27-当日券', date='2026-06-27 09:00:00', cat='info',
         title='本日いよいよ開幕！当日券販売のお知らせ',
         excerpt='2026年6月27日（土）、TORASAKE -虎ノ酒- 2026がいよいよ開幕。会場受付にて当日券を販売いたしました。みなさまのご来場を心よりお待ちしております。',
         thumb=FLYER, content='', status='draft'),
    dict(slug='2026-05-28-matching', date='2026-05-28 10:00:00', cat='info',
         title='酒蔵 × 飲食店 マッチング31組 発表！',
         excerpt='お待たせいたしました。TORASAKE 2026 にて、全国31蔵と虎ノ門ヒルズ23店舗のペアリングコラボが決定。蔵元と料理人が、この日だけの特別な日本酒×料理の出会いをお届けしました。',
         thumb=FLYER, content='', status='draft'),
    dict(slug='2026-05-25-event-system', date='2026-05-25 10:00:00', cat='guide',
         title='TORASAKEの楽しみ方 ─ 回遊型・ペアリング体験イベント',
         excerpt='全国31蔵 × 虎ノ門ヒルズ23飲食店が交わる、一日限定の回遊型日本酒イベント。「システムがよく分からない」というお声にお応えして、改めて楽しみ方をご案内します。',
         thumb=None, content='', status='draft'),
    dict(slug='2026-05-21-開催決定', date='2026-05-21 10:00:00', cat='info',
         title='TORASAKE 2026 開催決定！参加酒蔵31蔵が出揃いました',
         excerpt='2026年6月27日（土）、虎ノ門ヒルズ ステーションタワーにて「TORASAKE -虎ノ酒- 2026」の開催が決定。全国から選び抜かれた31の酒蔵が集結しました。',
         thumb=None, content='', status='draft'),
]

news_items = ''
for n in NEWS:
    m = meta('_thumbnail_id', n['thumb']) if n['thumb'] else ''
    news_items += item(
        title=n['title'], name=n['slug'], post_type='post', post_id=next_id(),
        status=n['status'], date=n['date'], content=n['content'], excerpt=n['excerpt'],
        terms=[('category', n['cat'], CATEGORIES[n['cat']])], metas=m,
    )

# ── イベント ──────────────────────────────────────────────────
mini_meta = ''.join([
    meta('_thumbnail_id', KV),
    acf('event', 'event_status', '開催予定'),
    acf('event', 'event_date', '20261016'),
    acf('event', 'event_start_time', '17:00'),
    acf('event', 'event_end_time', '23:00'),
    acf('event', 'event_time_range', '17:00-23:00'),
    acf('event', 'venue_name', '虎ノ門ヒルズ ステーションタワー B2F T-MARKET'),
    acf('event', 'venue_access', '東京メトロ日比谷線 虎ノ門ヒルズ駅 直結'),
    acf('event', 'venue_floor', 'B2F'),
    acf('event', 'key_visual', KV),
    acf('event', 'ticker_text', '🎟 チケット販売中 ─ 早割[限定100枚]・30分先行入場[限定50枚]は予定枚数に達し次第終了'),
    acf('event', 'ticket_url', 'https://peatix.com/event/5186533?utm_source=hp'),
    acf('event', 'lead_heading', '造り手と話して、<br>気になる一杯に出会う。'),
    acf('event', 'lead_copy', '全国から選りすぐりの<span class="em">10蔵</span>が虎ノ門ヒルズに集結。<br>おいしい料理と一緒に、日本酒を楽しむ<span class="em">秋の夜</span>を。'),
    acf('event', 'lead_body', '今回は300〜500名規模のミニイベント。前回よりも酒蔵との距離が近く、一つひとつのお酒をじっくり楽しめる一夜を目指します。'),
    acf('event', 'brewery_section_sub', '造り手が会場に立ちます。気になる一杯について、直接聞いてみてください。'),
    acf('event', 'food_image', VENUE),
    acf('event', 'food_image_position', '50% 26%'),
    acf('event', 'food_heading', '日本酒に合う料理を、<br>T-MARKETの各飲食店で。'),
    acf('event', 'food_body', '会場となるT-MARKETの各飲食店では、この日のために日本酒に合う料理をご用意します。気になるお酒と料理を、その都度購入して自由にお楽しみください。'),
    acf('event', 'ticket_section_title', 'チケット販売中'),
    acf('event', 'ticket_section_sub', 'すべてのチケットに専用コイン1,500円分とオリジナルグラスが付いています。'),
    acf('event', 'ticket_notes', '※追加のお酒・料理は別途ご購入いただけます。／※混雑状況により入場制限を行う場合があります。\n※早割・先行入場チケットは、予定枚数に達し次第販売終了。ぜひお早めにお求めください。'),
    acf('event', 'sticky_cta_text', 'TORASAKE mini <b>10/16(金)</b> 虎ノ門ヒルズ T-MARKET ─ チケット販売中'),
    acf_repeater('event', 'lead_points', [
        {'label_en': 'SCALE', 'value': '300-500名', 'body': '前回より小さな規模だから、<br>蔵人とゆっくり話せる。'},
        {'label_en': 'BREWERIES', 'value': '10蔵', 'body': '数を絞ったからこそ、<br>一蔵ずつじっくり味わえる。'},
        {'label_en': 'PAIRING', 'value': 'T-MARKET', 'body': '各飲食店が日本酒に合う<br>料理をご用意します。'},
    ]),
    acf_repeater('event', 'outline', [
        {'key_en': 'DATE & TIME', 'value': '2026年10月16日(金)<br>17:00 - 23:00',
         'note': 'L.O. 22:30 ／ 先行入場チケットをお持ちの方は16:30から入場可能'},
        {'key_en': 'VENUE', 'value': '虎ノ門ヒルズ ステーションタワー B2F<br>T-MARKET',
         'note': '東京メトロ日比谷線 虎ノ門ヒルズ駅 直結'},
        {'key_en': 'STYLE', 'value': '日本酒飲み歩きイベント',
         'note': '300〜500名規模／全国10蔵が出展。専用コインでお酒と料理をその都度ご購入いただけます。'},
        {'key_en': 'TICKET', 'value': '前売 3,000円 - 当日 3,500円',
         'note': '早割2,500円[限定100枚]・30分先行入場3,000円[限定50枚]を販売中。'},
    ]),
    acf_repeater('event', 'tickets', [
        {'label_en': 'EARLY BIRD', 'name': '早割チケット', 'price': 2500, 'entry_time': '17:00',
         'limit_label': '限定100枚', 'is_featured': 1,
         'note': 'いちばんお得な早割。予定枚数に達し次第、販売終了します。',
         'purchase_url': 'https://peatix.com/event/5186533?utm_source=hp'},
        {'label_en': 'EARLY ENTRY', 'name': '30分先行入場', 'price': 3000, 'entry_time': '16:30',
         'limit_label': '限定50枚', 'is_featured': 0,
         'note': 'ひと足先に、気になる酒蔵を巡りたい方に。混み合う前にゆっくりと。',
         'purchase_url': 'https://peatix.com/event/5186533?utm_source=hp'},
        {'label_en': 'ADVANCE', 'name': '前売りチケット', 'price': 3000, 'entry_time': '17:00',
         'limit_label': '', 'is_featured': 0,
         'note': '当日券は3,500円。前売りでのご購入がおすすめです。',
         'purchase_url': 'https://peatix.com/event/5186533?utm_source=hp'},
    ]),
    acf_repeater('event', 'ticket_includes', [
        {'text': 'イベント専用500円コイン × 3枚', 'note': '［1,500円分］'},
        {'text': 'TORASAKEオリジナルグラス', 'note': ''},
    ]),
])

event_items = item(
    title='TORASAKE mini', name='torasake-mini', post_type='event', post_id=next_id(),
    excerpt='全国から選りすぐりの10蔵が虎ノ門ヒルズに集結。おいしい料理と一緒に、日本酒を楽しむ秋の夜を。',
    terms=[('event_edition', 'torasake-mini-2026', EDITIONS['torasake-mini-2026'])],
    metas=mini_meta,
)

event_items += item(
    title='TORASAKE -虎ノ酒- 2026', name='torasake-2026', post_type='event', post_id=next_id(),
    date='2026-06-27 10:00:00',
    excerpt='虎ノ門ヒルズ ステーションタワー 4F・B2Fの2フロアで開催。全国32蔵が集結し、盛況のうちに終了しました。',
    terms=[('event_edition', 'torasake-2026', EDITIONS['torasake-2026'])],
    metas=''.join([
        meta('_thumbnail_id', CROWD),
        acf('event', 'event_status', '終了'),
        acf('event', 'event_date', '20260627'),
        acf('event', 'venue_name', '虎ノ門ヒルズ ステーションタワー'),
    ]),
)

# ── 過去開催 ──────────────────────────────────────────────────
past_meta = ''.join([
    meta('_thumbnail_id', CROWD),
    acf('past_event', 'event_date', '20251008'),
    acf('past_event', 'venue_name', '虎ノ門ヒルズ ステーションタワー'),
    acf('past_event', 'report_heading', '当日の様子'),
    acf('past_event', 'report_body',
        '<p class="body">2025年10月、虎ノ門ヒルズ ステーションタワーにて開催した「日本酒虎の巻 Vol.2」には、'
        '全国から<strong>17の酒蔵</strong>が集結。当日は<strong>800名</strong>を超える日本酒ファンにお越しいただき、'
        '各蔵のブースを巡りながら、蔵人と直接語り合う熱気あふれる一日となりました。</p>'
        '<p class="body">会場内ではテイスティング、ペアリング、限定酒の販売など、多彩な体験コーナーを展開。'
        '蔵人と来場者が酒を介して言葉を交わす光景が、いたるところで見られました。</p>'),
    acf('past_event', 'closing_heading', 'そして、2026年へ'),
    acf('past_event', 'closing_body',
        '<p class="body">Vol.2 で得た学びと、来場者の皆様・蔵人の皆様からの温かいお声を糧に、'
        '私たちは次なるステージへ進みます。</p>'),
    acf_repeater('past_event', 'stats', [
        {'number': '17', 'unit_suffix': '蔵', 'label': '参加酒蔵'},
        {'number': '800', 'unit_suffix': '名', 'label': '来場者数'},
        {'number': '1', 'unit_suffix': '日', 'label': '熱い一日'},
    ]),
    acf_repeater('past_event', 'info_list', [
        {'key_en': 'EVENT', 'value': '日本酒虎の巻 Vol.2', 'url': ''},
        {'key_en': 'DATE', 'value': '2025年10月8日（水）', 'url': ''},
        {'key_en': 'VENUE', 'value': '虎ノ門ヒルズ ステーションタワー', 'url': ''},
        {'key_en': 'PARTICIPATING BREWERIES', 'value': '全国17蔵', 'url': ''},
        {'key_en': 'ATTENDANCE', 'value': '800名', 'url': ''},
        {'key_en': 'MEDIA', 'value': 'SAKETIMES（外部リンク）',
         'url': 'https://jp.sake-times.com/special/press/p_202509_nihonshu-toranomaki'},
    ]),
])

past_items = item(
    title='日本酒虎の巻 Vol.2', name='toranomaki-vol2', post_type='past_event',
    post_id=next_id(), date='2025-10-08 10:00:00',
    excerpt='前身イベント「日本酒虎の巻 Vol.2」の開催レポート。17蔵・800名で賑わいました。',
    metas=past_meta,
)

# ── タクソノミーの term 定義 ──────────────────────────────────
terms_xml = ''
for pref in sorted({b['pref'] for b in BREWERIES}):
    terms_xml += (f'\t<wp:term><wp:term_taxonomy>prefecture</wp:term_taxonomy>'
                  f'<wp:term_slug>{esc(PREF_SLUG[pref])}</wp:term_slug>'
                  f'<wp:term_name>{cdata(pref)}</wp:term_name></wp:term>\n')
for slug, name in EDITIONS.items():
    terms_xml += (f'\t<wp:term><wp:term_taxonomy>event_edition</wp:term_taxonomy>'
                  f'<wp:term_slug>{esc(slug)}</wp:term_slug>'
                  f'<wp:term_name>{cdata(name)}</wp:term_name></wp:term>\n')
for slug, name in CATEGORIES.items():
    terms_xml += (f'\t<wp:category><wp:category_nicename>{esc(slug)}</wp:category_nicename>'
                  f'<wp:cat_name>{cdata(name)}</wp:cat_name></wp:category>\n')

# ── 出力 ──────────────────────────────────────────────────────
xml = f'''<?xml version="1.0" encoding="UTF-8" ?>
<!--
  TORASAKE コンテンツ インポートファイル（WXR 1.2）
  tools/make-wxr.py が生成。手で編集せず、スクリプトを直して作り直すこと。

  読み込む前に必ずテーマを有効化すること。
  未登録の投稿タイプはインポーターに無視される。
-->
<rss version="2.0"
\txmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
\txmlns:content="http://purl.org/rss/1.0/modules/content/"
\txmlns:wfw="http://wellformedweb.org/CommentAPI/"
\txmlns:dc="http://purl.org/dc/elements/1.1/"
\txmlns:wp="http://wordpress.org/export/1.2/">
<channel>
\t<title>TORASAKE -虎ノ酒-</title>
\t<link>{SITE}</link>
\t<description>日本酒と出会う、継続開催イベントシリーズ</description>
\t<pubDate>{datetime.now(JST).strftime('%a, %d %b %Y %H:%M:%S +0900')}</pubDate>
\t<language>ja</language>
\t<wp:wxr_version>1.2</wp:wxr_version>
\t<wp:base_site_url>{SITE}</wp:base_site_url>
\t<wp:base_blog_url>{SITE}</wp:base_blog_url>
\t<wp:author><wp:author_id>1</wp:author_id><wp:author_login>{cdata(AUTHOR)}</wp:author_login>
\t\t<wp:author_email>{cdata('info@torasake.com')}</wp:author_email>
\t\t<wp:author_display_name>{cdata('TORASAKE')}</wp:author_display_name>
\t\t<wp:author_first_name>{cdata('')}</wp:author_first_name>
\t\t<wp:author_last_name>{cdata('')}</wp:author_last_name></wp:author>
{terms_xml}{attachment_items}{brewery_items}{news_items}{event_items}{past_items}</channel>
</rss>
'''

OUT.parent.mkdir(exist_ok=True)
OUT.write_text(xml, encoding='utf-8')

print(f'  ✓ {OUT.relative_to(ROOT)}  ({OUT.stat().st_size / 1024:.0f}KB)')
print(f'    添付画像 {len(IMAGES)}件 / 酒蔵 {len(BREWERIES)}件 / お知らせ {len(NEWS)}件'
      f'（公開{sum(1 for n in NEWS if n["status"] == "publish")}・下書き{sum(1 for n in NEWS if n["status"] == "draft")}）'
      f' / イベント 2件 / 過去開催 1件')
