#!/usr/bin/env python3
"""acf-json/ の妥当性チェック。

- JSON として読めるか
- フィールドキーが全グループで一意か（重複するとACFが片方を捨てる）
- brewery グループに価格系フィールドが紛れていないか（CLAUDE.md #1）
"""
import collections
import glob
import json
import sys

ACF = "themes/torasake/acf-json/*.json"
PRICE_WORDS = ("price", "cost", "fee", "amount", "yen", "価格", "円", "料金")

keys = []
groups = 0
bad = []


def walk(fields, sink, path=""):
    for field in fields:
        sink.append((path + field["name"], field["label"], field["key"]))
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

dups = [k for k, c in collections.Counter(keys).items() if c > 1]

print(f"  ✓ {groups}グループ / キー{len(keys)}")
if dups:
    print(f"  ✗ キー重複: {dups}")
if bad:
    print(f"  ✗ brewery に価格系フィールド: {bad}")
else:
    print("  ✓ brewery に価格系フィールドなし")

sys.exit(1 if (dups or bad) else 0)
