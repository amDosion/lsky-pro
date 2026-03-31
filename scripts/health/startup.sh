#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

PHP_BIN="${PHP_BIN:-php}"
REQUIRED_PHP="8.1.0"

version_ge() {
  [ "$1" = "$(printf '%s\n%s\n' "$1" "$2" | sort -V | tail -n1)" ]
}

if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
  echo "[FAIL] php binary not found: $PHP_BIN"
  exit 1
fi

PHP_VERSION="$($PHP_BIN -r 'echo PHP_VERSION;' 2>/dev/null || true)"
if [[ -z "$PHP_VERSION" ]]; then
  echo "[FAIL] cannot read PHP version"
  exit 1
fi

if ! version_ge "$PHP_VERSION" "$REQUIRED_PHP"; then
  echo "[FAIL] PHP >= $REQUIRED_PHP required, current: $PHP_VERSION"
  exit 1
fi

echo "[OK] PHP version: $PHP_VERSION"

required_ext=(bcmath ctype dom fileinfo json mbstring openssl pdo tokenizer xml)
missing=()
for ext in "${required_ext[@]}"; do
  if ! "$PHP_BIN" -m | awk '{print tolower($0)}' | grep -qx "$ext"; then
    missing+=("$ext")
  fi
done

if [ ${#missing[@]} -gt 0 ]; then
  echo "[FAIL] missing PHP extensions: ${missing[*]}"
  exit 1
fi

echo "[OK] required PHP extensions present"

for path in storage bootstrap/cache; do
  if [ ! -d "$path" ]; then
    echo "[FAIL] missing directory: $path"
    exit 1
  fi
  if [ ! -w "$path" ]; then
    echo "[FAIL] directory not writable: $path"
    exit 1
  fi
  echo "[OK] writable directory: $path"
done

if [ ! -f .env ]; then
  echo "[WARN] .env not found, creating from .env.example"
  cp .env.example .env
fi

"$PHP_BIN" artisan --version >/dev/null
echo "[OK] artisan bootstrap successful"

LOCAL_SCRIPT="$ROOT_DIR/scripts/image_intelligence/classify_ocr.py"
if [ ! -f "$LOCAL_SCRIPT" ]; then
  echo "[FAIL] local image intelligence script missing: $LOCAL_SCRIPT"
  exit 1
fi

HF_CACHE_ROOT="${HF_HOME:-/opt/models/huggingface}"
BLIP_MODEL_ROOT="$HF_CACHE_ROOT/hub/models--Salesforce--blip-image-captioning-base"
if [ ! -d "$BLIP_MODEL_ROOT/snapshots" ]; then
  echo "[FAIL] local BLIP model cache missing: $BLIP_MODEL_ROOT/snapshots"
  exit 1
fi

if ! find "$BLIP_MODEL_ROOT/snapshots" -type f -name 'preprocessor_config.json' | grep -q .; then
  echo "[FAIL] local BLIP preprocessor cache missing under: $BLIP_MODEL_ROOT/snapshots"
  exit 1
fi

if ! find "$BLIP_MODEL_ROOT/snapshots" -type f \( -name 'pytorch_model.bin' -o -name 'model.safetensors' \) | grep -q .; then
  echo "[FAIL] local BLIP weight cache missing under: $BLIP_MODEL_ROOT/snapshots"
  exit 1
fi

if ! command -v python3 >/dev/null 2>&1; then
  echo "[FAIL] python3 not found for local image intelligence"
  exit 1
fi

if ! command -v tesseract >/dev/null 2>&1; then
  echo "[FAIL] tesseract not found for local image intelligence"
  exit 1
fi

python3 - <<'PY'
import importlib.util
import sys

required = ["torch", "transformers", "PIL", "pytesseract"]
missing = [name for name in required if importlib.util.find_spec(name) is None]
if missing:
    print("[FAIL] missing local image intelligence Python packages: " + " ".join(missing))
    sys.exit(1)

import torch
import transformers
from transformers import BlipForConditionalGeneration, BlipProcessor

expected_transformers = "4.26.1"

if transformers.__version__ != expected_transformers:
    print(
        "[FAIL] unexpected transformers version: "
        + transformers.__version__
        + " (expected "
        + expected_transformers
        + ")"
    )
    sys.exit(1)

if not transformers.is_torch_available():
    print(
        "[FAIL] transformers cannot use installed torch: "
        + transformers.__version__
        + " / "
        + torch.__version__
    )
    sys.exit(1)
PY

echo "[OK] local image intelligence runtime present"

AUTO_BOOTSTRAP="${INIT_AUTO_BOOTSTRAP:-false}"
if [[ "$AUTO_BOOTSTRAP" == "true" ]]; then
  echo "[INFO] auto bootstrap is handled by entrypoint; health check remains read-only"
fi

echo "startup health check passed"
