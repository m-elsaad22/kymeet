#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="$ROOT/kayan-theme"
OUT="$ROOT/dist/kayan-theme.zip"
mkdir -p "$ROOT/dist"
rm -f "$OUT"
(
  cd "$SRC"
  zip -r "$OUT" . \
    -x '*/docs/validation-artifacts/*' \
    -x '*/.git/*' \
    -x '*kymeet-store/manifest.json'
)
echo "Wrote $OUT"
