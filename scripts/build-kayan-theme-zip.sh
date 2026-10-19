#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="$ROOT/kayan-theme"
OUT="$ROOT/dist/kayan-theme-kymeet-ui.zip"
mkdir -p "$ROOT/dist"
rm -f "$OUT"
( cd "$SRC/.." && zip -r "$OUT" kayan-theme -x '*.git*' -x '*__MACOSX*' )
echo "Built $OUT ($(du -h "$OUT" | cut -f1))"
