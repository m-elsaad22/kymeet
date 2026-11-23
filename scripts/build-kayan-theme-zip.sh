#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="$ROOT/kayan-theme"
OUT="$ROOT/dist/kayan-theme.zip"
mkdir -p "$ROOT/dist"
rm -f "$OUT"
# WordPress expects a folder (e.g. kayan-theme/style.css) inside the archive.
(
  cd "$ROOT"
  zip -r "$OUT" kayan-theme \
    -x 'kayan-theme/docs/validation-artifacts/*' \
    -x 'kayan-theme/components/packs/kymeet-store/manifest.json'
)
echo "Wrote $OUT ($(unzip -l "$OUT" | rg 'style.css' | head -1))"
