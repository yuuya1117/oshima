#!/usr/bin/env python3
"""assets/css/*.css が参照HTMLの逐語移植のままか確認する（CLAUDE.md #3）。

各CSSは「出典コメント → 参照の確定値 → （あれば）WordPress 実装で足した分」
の順に並んでいる。確定値の部分が参照とバイト一致するかだけを見る。
"""
import pathlib
import re
import sys

SRC = pathlib.Path("docs/design_handoff_wordpress/reference")
DST = pathlib.Path("themes/torasake/assets/css")
SEP = "/* ─────────────────────────────────────────────\n * WordPress 実装で足した分"


def style_of(name):
    html = (SRC / name).read_text(encoding="utf-8")
    return re.search(r"<style>(.*?)</style>", html, re.S).group(1).strip("\n")


def file_of(name):
    return (SRC / name).read_text(encoding="utf-8")


PAIRS = [
    ("front.css", "top-page.html", style_of),
    ("brewery-archive.css", "brewery-archive.html", style_of),
    ("brewery-single.css", "brewery-style.css", file_of),
    ("news-archive.css", "news-archive.html", style_of),
    ("news-single.css", "news-single-example.html", style_of),
    ("event-single.css", "event-single-template.html", style_of),
    ("past-event.css", "past-event-single.html", style_of),
    ("blog-single.css", "blog-single-template.html", style_of),
    ("sponsors.css", "sponsors-page.html", style_of),
]

failed = 0
for out, ref, get in PAIRS:
    want = get(ref).rstrip("\n")
    body = (DST / out).read_text(encoding="utf-8").split("*/", 1)[1].lstrip("\n")
    added = 0
    if SEP in body:
        body, extra = body.split(SEP, 1)
        added = extra.count("\n")

    ok = body.rstrip("\n") == want
    failed += not ok
    note = f"  (+WP追加 {added}行)" if added else ""
    print(f"  {'✓' if ok else '✗'} {out:22s} ← {ref:28s}"
          f" {'逐語一致' if ok else '確定値が変わっている'}{note}")

sys.exit(1 if failed else 0)
