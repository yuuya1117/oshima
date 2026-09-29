#!/usr/bin/env sh
# 本番／ステージングの全URLに200が返るか確認する。
#
#   sh tools/check-urls.sh https://torasake.com            # 切り替え後
#   sh tools/check-urls.sh https://torasake.com before     # 切り替え前（新URLのみ）
#   sh tools/check-urls.sh https://staging.torasake.com "" user:pass
#
# 第2引数に before を渡すと、旧HTMLがまだ残っている段階で見られる
# 新URLだけを確認する（付録A-5）。

BASE="${1:?使い方: sh tools/check-urls.sh <ベースURL> [before] [user:pass]}"
PHASE="${2:-after}"
AUTH="${3:-}"

[ -n "$AUTH" ] && CURL_AUTH="-u $AUTH" || CURL_AUTH=""

# 切り替え前でも見える新URL
NEW_URLS="/breweries/
/breweries/taka.html
/breweries/daishinshu.html
/events/
/events/torasake-mini.html
/blog/
/sponsors/
/index.php"

# 切り替え後に見るべき既存URL（1本も失ってはいけない）
LEGACY_URLS="/
/news/
/news/2026-07-03-event-report.html
/news/2026-06-27-当日券.html
/news/2026-05-28-matching.html
/news/2026-05-25-event-system.html
/news/2026-05-21-開催決定.html
/past/
/uploads/keyvisual.jpg
/uploads/staff.jpg"

if [ "$PHASE" = "before" ]; then
  URLS="$NEW_URLS"
  echo "切り替え前チェック（新URLのみ）: $BASE"
else
  URLS="$LEGACY_URLS
$NEW_URLS"
  echo "切り替え後チェック（全URL）: $BASE"
fi

echo
fail=0
echo "$URLS" | while IFS= read -r path; do
  [ -z "$path" ] && continue
  out=$(curl -sS $CURL_AUTH -o /dev/null -w '%{http_code} %{redirect_url}' "$BASE$path" 2>/dev/null)
  code=$(echo "$out" | cut -d' ' -f1)
  dest=$(echo "$out" | cut -d' ' -f2-)
  case "$code" in
    200)     mark='✓' ;;
    301|302) mark='→' ;;
    *)       mark='✗'; fail=1 ;;
  esac
  printf '  %s %-46s %s %s\n' "$mark" "$path" "$code" "$dest"
done

echo
echo "  ✓=200  →=リダイレクト（最終到達先が200か確認）  ✗=要対応"
