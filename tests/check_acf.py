#!/usr/bin/env python3
"""acf-json/ の妥当性チェック。

- JSON として読めるか
- フィールドキーが全グループで一意か（重複するとACFが片方を捨てる）
- brewery グループに価格系フィールドが紛れていないか（CLAUDE.md #1）
- ラベル・説明文に生のHTMLタグが混ざっていないか

  最後の項目は実害があって入れた。説明文に書いた `<title>` がそのまま
  管理画面に出力され、ブラウザがそれ以降のページ全体を「タイトルの文字列」
  として飲み込み、編集画面が真っ白になった。PHPエラーもJSエラーも出ないので
  原因が分かりにくい。タグを書きたいときは全角の山括弧（＜br＞）を使う。
"""
import collections
import glob
import json
import re
import sys

ACF = "themes/torasake/acf-json/*.json"
PRICE_WORDS = ("price", "cost", "fee", "amount", "yen", "価格", "円", "料金")
TAG_RE = re.compile(r"<[a-zA-Z/!][^>]*>")

keys = []
groups = 0
bad = []


raw_html = []


def walk(fields, sink, path=""):
    for field in fields:
        sink.append((path + field["name"], field["label"], field["key"]))
        # ラベル・説明文に生のHTMLタグがあると管理画面の描画を壊す
        for attr in ("label", "instructions"):
            for tag in TAG_RE.findall(field.get(attr, "")):
                raw_html.append(f"{path}{field['name']}.{attr}: {tag}")
        walk(field.get("sub_fields", []), sink, path + field["name"] + ".")


for path in sorted(glob.glob(ACF)):
    try:
        data = json.loads(open(path, encoding="utf-8").read())
    except json.JSONDecodeError as exc:
        print(f"  ✗ {path}: {exc}")
        sys.exit(1)

    groups += 1
    flat = []
    walk(data["fields"], flat)
    keys.append(data["key"])
    keys.extend(k for _, _, k in flat)

    # CLAUDE.md #1: brewery に価格系フィールドを作らない
    if data["key"] == "group_brewery":
        for name, label, _ in flat:
            if any(w in name.lower() or w in label for w in PRICE_WORDS):
                bad.append(f"{name} ({label})")

    for tag in TAG_RE.findall(data.get("description", "")):
        raw_html.append(f"{data['key']}.description: {tag}")

dups = [k for k, c in collections.Counter(keys).items() if c > 1]

print(f"  ✓ {groups}グループ / キー{len(keys)}")
if dups:
    print(f"  ✗ キー重複: {dups}")
if bad:
    print(f"  ✗ brewery に価格系フィールド: {bad}")
else:
    print("  ✓ brewery に価格系フィールドなし")

if raw_html:
    print(f"  ✗ ラベル・説明文に生のHTMLタグ（管理画面が壊れる）: {len(raw_html)}箇所")
    for r in raw_html[:10]:
        print(f"      {r}")
    print("      → 全角の山括弧に直すこと（例: ＜br＞）")
else:
    print("  ✓ ラベル・説明文に生のHTMLタグなし")

sys.exit(1 if (dups or bad or raw_html) else 0)
