#!/usr/bin/env bash
#
# Builds the distributable Statview Satellite WordPress plugin zip.
#
# The archive contains a single top-level `statview-satellite/` directory (the
# plugin slug WordPress expects) holding only the runtime files. Development-only
# files — tests, vendor, CI config, build tooling — are deliberately excluded so
# the shipped plugin matches what end users install.
#
# Usage: bin/build-zip.sh [output-path]
#        Defaults to ./statview-satellite.zip in the current working directory.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PKG_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"

OUT="${1:-$PWD/statview-satellite.zip}"
SLUG="statview-satellite"

# Runtime files that make up the installable plugin. The main file registers a
# lightweight PSR-4 autoloader, so no vendor/ directory is needed at runtime.
INCLUDE=(
  "statview-satellite.php"
  "uninstall.php"
  "readme.txt"
  "src"
)

STAGE="$(mktemp -d)"
trap 'rm -rf "$STAGE"' EXIT

mkdir -p "$STAGE/$SLUG"
for path in "${INCLUDE[@]}"; do
  cp -R "$PKG_DIR/$path" "$STAGE/$SLUG/"
done

rm -f "$OUT"
( cd "$STAGE" && zip -rq "$OUT" "$SLUG" -x '*/.DS_Store' )

echo "Built $OUT"
