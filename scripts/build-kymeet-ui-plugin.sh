#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_SRC="$ROOT/plugin/kymeet-perfumes-ui"
OUT="$ROOT/dist"
ZIP="$OUT/kymeet-perfumes-ui.zip"
mkdir -p "$OUT"
rm -f "$ZIP"
(
  cd "$PLUGIN_SRC"
  zip -rq "$ZIP" . -x '*.DS_Store'
)
echo "Built $ZIP"
