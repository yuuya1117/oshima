#!/usr/bin/env sh
# テーマの静的検証をまとめて走らせる。
#
# WordPress 本体を起動せずにテンプレートを実行する（tests/stubs.php が WP 関数を
# 最小限スタブする）。挙動の正しさではなく「致命的エラーが出ないこと」と
# CLAUDE.md の各ルールが守られていることを見る。実機での確認の代わりにはならない。
#
#   sh tests/run-all.sh
set -e
cd "$(dirname "$0")/.."

status=0
say() { printf '\n\033[1m%s\033[0m\n' "$1"; }

say "PHP 構文"
n=0
for f in $(find themes -name '*.php' | sort); do
  php -l "$f" > /dev/null || status=1
  n=$((n + 1))
done
echo "  ✓ ${n} ファイル"

say "JS 構文"
if command -v node > /dev/null 2>&1; then
  for f in themes/torasake/assets/js/*.js; do
    node --check "$f" > /dev/null || status=1
  done
  echo "  ✓ $(ls themes/torasake/assets/js/*.js | wc -l | tr -d ' ') ファイル"
else
  echo "  - node が無いのでスキップ"
fi

say "ACF JSON"
python3 tests/check_acf.py || status=1

say "テンプレート階層（has_archive）"
php tests/check_hierarchy.php || status=1

say "テンプレート実行"
php tests/run.php || status=1
php tests/run2.php || status=1
php tests/run3.php || status=1
php tests/run_fallback.php || status=1

say "CLAUDE.md #1 ─ 酒蔵ページに価格を出さない"
php tests/check_price.php || status=1
php tests/check_jsonld.php | tail -1 || status=1

say "CLAUDE.md #2 ─ 方式A（既存URLを変えない）"
php tests/check_urls.php || status=1

say "CLAUDE.md #3 ─ CSS は参照HTMLの逐語移植"
python3 tests/check_css.py || status=1

say "各テンプレートの内容"
php tests/check_new.php || status=1
php tests/check_new2.php || status=1

if [ "$status" -eq 0 ]; then
  printf '\n\033[32m全項目パス\033[0m\n'
else
  printf '\n\033[31m失敗あり\033[0m\n'
fi
exit "$status"
