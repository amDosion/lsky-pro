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
LOCAL_TAGGER_BACKEND="${LSKY_LOCAL_TAGGER_BACKEND:-wd_tagger}"
LOCAL_TAGGER_MODEL="${LSKY_LOCAL_TAGGER_MODEL:-$HF_CACHE_ROOT/wd-vit-tagger-v3}"
LOCAL_LEGACY_VISION_MODEL="${LSKY_LOCAL_LEGACY_VISION_MODEL:-${LSKY_LOCAL_VISION_MODEL:-Salesforce/blip-image-captioning-base}}"

model_snapshots_root() {
  local model_slug="$1"
  if [ -d "$HF_CACHE_ROOT/models--$model_slug/snapshots" ]; then
    printf '%s/models--%s/snapshots\n' "$HF_CACHE_ROOT" "$model_slug"
    return 0
  fi

  if [ -d "$HF_CACHE_ROOT/hub/models--$model_slug/snapshots" ]; then
    printf '%s/hub/models--%s/snapshots\n' "$HF_CACHE_ROOT" "$model_slug"
    return 0
  fi

  return 1
}

model_dir_has_files() {
  local model_dir="$1"
  shift

  [ -d "$model_dir" ] || return 1

  local required
  for required in "$@"; do
    [ -f "$model_dir/$required" ] || return 1
  done

  return 0
}

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
import os
import sys

backend = os.getenv("LSKY_LOCAL_TAGGER_BACKEND", "wd_tagger").strip().lower() or "wd_tagger"
required = ["PIL", "pytesseract"]
if backend == "wd_tagger":
    required.extend(["numpy", "onnxruntime"])
elif backend == "blip_legacy":
    required.extend(["torch", "transformers"])
else:
    print("[FAIL] unsupported local tagger backend: " + backend)
    sys.exit(1)

missing = [name for name in required if importlib.util.find_spec(name) is None]
if missing:
    print("[FAIL] missing local image intelligence Python packages: " + " ".join(missing))
    sys.exit(1)

if backend == "blip_legacy":
    import torch
    import transformers

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

if [[ "$LOCAL_TAGGER_BACKEND" == "wd_tagger" ]]; then
  if model_dir_has_files "$LOCAL_TAGGER_MODEL" model.onnx selected_tags.csv; then
    echo "[OK] local wd_tagger model directory present: $LOCAL_TAGGER_MODEL"
  else
    if [[ "$LOCAL_TAGGER_MODEL" = /* ]]; then
      echo "[FAIL] local wd_tagger model directory missing or incomplete: $LOCAL_TAGGER_MODEL"
      exit 1
    fi

    WD_SNAPSHOTS_ROOT="$(model_snapshots_root 'SmilingWolf--wd-vit-tagger-v3' || true)"
    if [ -z "$WD_SNAPSHOTS_ROOT" ]; then
      echo "[FAIL] local wd_tagger model cache missing: $LOCAL_TAGGER_MODEL or $HF_CACHE_ROOT/models--SmilingWolf--wd-vit-tagger-v3/snapshots or $HF_CACHE_ROOT/hub/models--SmilingWolf--wd-vit-tagger-v3/snapshots"
      exit 1
    fi

    if ! find "$WD_SNAPSHOTS_ROOT" \( -type f -o -type l \) -name 'model.onnx' | grep -q .; then
      echo "[FAIL] local wd_tagger ONNX cache missing under: $WD_SNAPSHOTS_ROOT"
      exit 1
    fi

    if ! find "$WD_SNAPSHOTS_ROOT" \( -type f -o -type l \) -name 'selected_tags.csv' | grep -q .; then
      echo "[FAIL] local wd_tagger tag CSV cache missing under: $WD_SNAPSHOTS_ROOT"
      exit 1
    fi
  fi
elif [[ "$LOCAL_TAGGER_BACKEND" == "blip_legacy" ]]; then
  if model_dir_has_files "$LOCAL_LEGACY_VISION_MODEL" preprocessor_config.json tokenizer_config.json config.json; then
    if ! find "$LOCAL_LEGACY_VISION_MODEL" \( -type f -o -type l \) \( -name 'model.safetensors' -o -name 'pytorch_model.bin' \) | grep -q .; then
      echo "[FAIL] local BLIP model weights missing under: $LOCAL_LEGACY_VISION_MODEL"
      exit 1
    fi
  else
    if [[ "$LOCAL_LEGACY_VISION_MODEL" = /* ]]; then
      echo "[FAIL] local BLIP model directory missing or incomplete: $LOCAL_LEGACY_VISION_MODEL"
      exit 1
    fi

    BLIP_SNAPSHOTS_ROOT="$(model_snapshots_root 'Salesforce--blip-image-captioning-base' || true)"
    if [ -z "$BLIP_SNAPSHOTS_ROOT" ]; then
      echo "[FAIL] local BLIP model cache missing: $LOCAL_LEGACY_VISION_MODEL or $HF_CACHE_ROOT/models--Salesforce--blip-image-captioning-base/snapshots or $HF_CACHE_ROOT/hub/models--Salesforce--blip-image-captioning-base/snapshots"
      exit 1
    fi

    if ! find "$BLIP_SNAPSHOTS_ROOT" \( -type f -o -type l \) -name 'preprocessor_config.json' | grep -q .; then
      echo "[FAIL] local BLIP processor config missing under: $BLIP_SNAPSHOTS_ROOT"
      exit 1
    fi

    if ! find "$BLIP_SNAPSHOTS_ROOT" \( -type f -o -type l \) -name 'tokenizer_config.json' | grep -q .; then
      echo "[FAIL] local BLIP tokenizer config missing under: $BLIP_SNAPSHOTS_ROOT"
      exit 1
    fi

    if ! find "$BLIP_SNAPSHOTS_ROOT" \( -type f -o -type l \) -name 'config.json' | grep -q .; then
      echo "[FAIL] local BLIP config missing under: $BLIP_SNAPSHOTS_ROOT"
      exit 1
    fi

    if ! find "$BLIP_SNAPSHOTS_ROOT" \( -type f -o -type l \) \( -name 'model.safetensors' -o -name 'pytorch_model.bin' \) | grep -q .; then
      echo "[FAIL] local BLIP model weights missing under: $BLIP_SNAPSHOTS_ROOT"
      exit 1
    fi
  fi
else
  echo "[FAIL] unsupported local tagger backend: $LOCAL_TAGGER_BACKEND"
  exit 1
fi

echo "[OK] local image intelligence runtime present"

AUTO_BOOTSTRAP="${INIT_AUTO_BOOTSTRAP:-false}"
if [[ "$AUTO_BOOTSTRAP" == "true" ]]; then
  echo "[INFO] auto bootstrap is handled by entrypoint; health check remains read-only"
fi

echo "startup health check passed"
