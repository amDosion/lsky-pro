#!/usr/bin/env python3
from __future__ import annotations

import argparse
import csv
import json
import os
import re
import subprocess
import sys
import tempfile
import time
from dataclasses import dataclass
from pathlib import Path
from typing import Any

os.environ.setdefault("TOKENIZERS_PARALLELISM", "false")
os.environ.setdefault("TRANSFORMERS_NO_ADVISORY_WARNINGS", "1")
os.environ.setdefault("HF_HUB_DISABLE_TELEMETRY", "1")

HF_HOME = os.getenv("HF_HOME", "/opt/models/huggingface")
DEFAULT_TOP = 3
DEFAULT_BACKEND = (os.getenv("LSKY_LOCAL_TAGGER_BACKEND", "wd_tagger").strip().lower() or "wd_tagger")
WD_MODEL_ID = os.getenv("LSKY_LOCAL_TAGGER_MODEL", f"{HF_HOME}/wd-vit-tagger-v3")
BLIP_MODEL_ID = os.getenv(
    "LSKY_LOCAL_LEGACY_VISION_MODEL",
    os.getenv("LSKY_LOCAL_VISION_MODEL", "Salesforce/blip-image-captioning-base"),
)
TESSERACT_LANG = os.getenv("LSKY_LOCAL_OCR_LANG", "chi_sim+eng")
OCR_MAX_DIMENSION = max(int(os.getenv("LSKY_LOCAL_OCR_MAX_DIMENSION", "1280")), 512)
OCR_TIMEOUT_SECONDS = max(int(os.getenv("LSKY_LOCAL_OCR_TIMEOUT", "10")), 5)
CAPTION_MAX_DIMENSION = max(int(os.getenv("LSKY_LOCAL_CAPTION_MAX_DIMENSION", "224")), 160)
CAPTION_MAX_NEW_TOKENS = max(int(os.getenv("LSKY_LOCAL_CAPTION_MAX_NEW_TOKENS", "10")), 6)
CAPTION_NUM_BEAMS = max(int(os.getenv("LSKY_LOCAL_CAPTION_NUM_BEAMS", "1")), 1)
WD_GENERAL_THRESHOLD = max(min(float(os.getenv("LSKY_LOCAL_TAGGER_GENERAL_THRESHOLD", "0.34")), 0.95), 0.05)
WD_MAX_VISUAL_TAGS = max(int(os.getenv("LSKY_LOCAL_TAGGER_MAX_TAGS", "12")), 3)
MODEL_LOCAL_ONLY = os.getenv("LSKY_LOCAL_MODEL_LOCAL_ONLY", "true").strip().lower() in {
    "1",
    "true",
    "yes",
    "on",
}


@dataclass(frozen=True)
class LexiconEntry:
    en: str
    zh: str
    aliases: tuple[str, ...] = ()
    category: str | None = None


@dataclass(frozen=True)
class VisualTag:
    name: str
    score: float


LEXICON = [
    LexiconEntry("sock", "袜子", ("socks", "stocking", "stockings", "legwear", "hosiery", "thighhighs", "ankle socks", "kneehighs"), "服饰"),
    LexiconEntry("shoe", "鞋子", ("shoes", "sneaker", "sneakers", "boot", "boots", "slipper", "slippers", "sandal", "sandals", "heels", "high heels", "loafers"), "服饰"),
    LexiconEntry("shirt", "上衣", ("t-shirt", "tshirt", "tee", "blouse", "top"), "服饰"),
    LexiconEntry("jacket", "外套", ("coat", "hoodie", "sweater"), "服饰"),
    LexiconEntry("pants", "裤子", ("trousers", "jeans", "shorts"), "服饰"),
    LexiconEntry("skirt", "裙子", ("dress", "gown"), "服饰"),
    LexiconEntry("hat", "帽子", ("cap",), "服饰"),
    LexiconEntry("bag", "包", ("handbag", "backpack", "wallet"), "配饰"),
    LexiconEntry("watch", "手表", ("clock",), "配饰"),
    LexiconEntry("glasses", "眼镜", ("sunglasses", "eyewear"), "配饰"),
    LexiconEntry("ring", "戒指", (), "配饰"),
    LexiconEntry("necklace", "项链", (), "配饰"),
    LexiconEntry("bracelet", "手链", (), "配饰"),
    LexiconEntry("bottle", "瓶子", ("flask",), "物品"),
    LexiconEntry("cup", "杯子", ("mug",), "物品"),
    LexiconEntry("plate", "盘子", ("bowl",), "物品"),
    LexiconEntry("book", "书籍", ("notebook", "magazine"), "物品"),
    LexiconEntry("box", "盒子", ("package", "carton"), "物品"),
    LexiconEntry("toy", "玩具", ("doll",), "物品"),
    LexiconEntry("laptop", "笔记本电脑", ("computer", "notebook computer"), "数码"),
    LexiconEntry("phone", "手机", ("smartphone", "cell phone", "mobile phone"), "数码"),
    LexiconEntry("tablet", "平板", ("ipad",), "数码"),
    LexiconEntry("keyboard", "键盘", (), "数码"),
    LexiconEntry("mouse", "鼠标", (), "数码"),
    LexiconEntry("monitor", "显示器", ("screen", "display"), "数码"),
    LexiconEntry("cat", "猫", ("kitten",), "动物"),
    LexiconEntry("dog", "狗", ("puppy",), "动物"),
    LexiconEntry("bird", "鸟", (), "动物"),
    LexiconEntry("flower", "花", ("flowers", "rose", "bouquet"), "植物"),
    LexiconEntry("plant", "植物", ("leaf", "leaves"), "植物"),
    LexiconEntry("tree", "树", (), "植物"),
    LexiconEntry("table", "桌子", ("desk",), "家具"),
    LexiconEntry("chair", "椅子", (), "家具"),
    LexiconEntry("mirror", "镜子", (), "家具"),
    LexiconEntry("painting", "画", ("poster", "artwork", "picture"), "装饰"),
    LexiconEntry("diagram", "图表", ("chart", "graph", "workflow", "network"), "图表"),
    LexiconEntry("screenshot", "截图", ("interface", "ui"), "图表"),
    LexiconEntry("invoice", "发票", ("receipt", "document"), "文档"),
    LexiconEntry("person", "人物", ("people", "model"), "人物"),
    LexiconEntry("woman", "女性", ("girl", "lady", "1girl"), "人物"),
    LexiconEntry("man", "男性", ("boy", "gentleman", "1boy"), "人物"),
    LexiconEntry("product", "商品图", ("merchandise",), "商品"),
    LexiconEntry("pink", "粉色"),
    LexiconEntry("blue", "蓝色"),
    LexiconEntry("red", "红色"),
    LexiconEntry("green", "绿色"),
    LexiconEntry("yellow", "黄色"),
    LexiconEntry("black", "黑色"),
    LexiconEntry("white", "白色"),
    LexiconEntry("gray", "灰色", ("grey",)),
    LexiconEntry("orange", "橙色"),
    LexiconEntry("purple", "紫色"),
    LexiconEntry("brown", "棕色"),
    LexiconEntry("beige", "米色"),
]

STOPWORDS = {
    "a",
    "an",
    "the",
    "and",
    "of",
    "on",
    "in",
    "with",
    "for",
    "to",
    "from",
    "at",
    "by",
    "is",
    "are",
    "be",
    "this",
    "that",
    "these",
    "those",
    "pair",
    "set",
    "close",
    "up",
    "image",
    "photo",
    "picture",
    "there",
    "it",
    "its",
    "background",
    "simple",
    "solo",
    "looking",
    "viewer",
}

LOW_SIGNAL_TERMS = {
    "person",
    "woman",
    "man",
    "pink",
    "blue",
    "red",
    "green",
    "yellow",
    "black",
    "white",
    "gray",
    "grey",
    "orange",
    "purple",
    "brown",
    "beige",
}

VISUAL_STOP_TAGS = {
    "general",
    "sensitive",
    "questionable",
    "explicit",
    "solo",
    "simple background",
    "white background",
    "transparent background",
    "looking at viewer",
    "open mouth",
    "closed mouth",
    "signature",
    "watermark",
    "text",
    "english text",
    "chinese text",
}

_BLIP_PROCESSOR = None
_BLIP_MODEL = None
_WD_SESSION = None
_WD_INPUT_NAME = None
_WD_LAYOUT = None
_WD_TARGET_SIZE = None
_WD_TAG_ROWS = None
_WD_MODEL_SOURCE = None


def candidate_snapshot_roots(model_id: str) -> list[Path]:
    suffix = Path(f"models--{model_id.replace('/', '--')}") / "snapshots"
    return [
        Path(HF_HOME) / suffix,
        Path(HF_HOME) / "hub" / suffix,
    ]


def normalize_whitespace(value: str) -> str:
    return re.sub(r"\s+", " ", value).strip()


def unique(values: list[str]) -> list[str]:
    seen: set[str] = set()
    result: list[str] = []
    for value in values:
        normalized = normalize_whitespace(value)
        if not normalized:
            continue
        marker = normalized.lower()
        if marker in seen:
            continue
        seen.add(marker)
        result.append(normalized)
    return result


def import_pillow():
    try:
        from PIL import Image, ImageOps
    except Exception as exc:  # noqa: BLE001
        raise RuntimeError("Pillow runtime is unavailable") from exc

    return Image, ImageOps


def resolve_cached_snapshot(model_id: str, required_files: tuple[str, ...]) -> str | None:
    snapshots: list[Path] = []
    for snapshots_root in candidate_snapshot_roots(model_id):
        if not snapshots_root.is_dir():
            continue

        for snapshot in snapshots_root.iterdir():
            if not snapshot.is_dir():
                continue
            if all((snapshot / relative).is_file() for relative in required_files):
                snapshots.append(snapshot)

    if not snapshots:
        return None

    snapshots.sort(key=lambda path: path.stat().st_mtime, reverse=True)
    return str(snapshots[0])


def resolve_hf_model_dir(model_id: str, required_files: tuple[str, ...]) -> str:
    candidate = Path(model_id)
    if candidate.exists():
        for relative in required_files:
            if not (candidate / relative).is_file():
                raise RuntimeError(f"model path missing required file: {candidate / relative}")
        return str(candidate)

    if candidate.is_absolute():
        raise RuntimeError(f"local tagger model path is missing: {candidate}")

    cached = resolve_cached_snapshot(model_id, required_files)
    if cached is not None:
        return cached

    if MODEL_LOCAL_ONLY:
        raise RuntimeError(f"local tagger model is missing: {model_id}")

    raise RuntimeError(f"online model download is disabled for local tagger: {model_id}")


def open_image(image_path: Path, max_dimension: int):
    image_module, _ = import_pillow()
    image = image_module.open(image_path).convert("RGB")
    if max(image.size) <= max_dimension:
        return image

    resampling = image_module.Resampling.LANCZOS if hasattr(image_module, "Resampling") else image_module.LANCZOS
    image.thumbnail((max_dimension, max_dimension), resampling)
    return image


def run_ocr(image_path: Path) -> str:
    image = open_image(image_path, OCR_MAX_DIMENSION)
    try:
        with tempfile.NamedTemporaryFile(suffix=".png") as handle:
            image.save(handle.name, format="PNG", optimize=True)
            try:
                result = subprocess.run(
                    ["tesseract", handle.name, "stdout", "-l", TESSERACT_LANG, "--psm", "6"],
                    capture_output=True,
                    text=True,
                    timeout=OCR_TIMEOUT_SECONDS,
                )
            except subprocess.TimeoutExpired:
                return ""
    finally:
        image.close()

    if result.returncode != 0:
        return ""

    return normalize_whitespace(result.stdout)


def extract_caption_tokens(caption: str) -> list[str]:
    tokens = re.findall(r"[a-z0-9][a-z0-9_-]*", caption.lower())
    return [token for token in tokens if token not in STOPWORDS]


def extract_ocr_tokens(ocr_text: str) -> list[str]:
    lower = ocr_text.lower()
    tokens = re.findall(r"[a-z0-9][a-z0-9_-]*", lower)
    chinese = re.findall(r"[\u4e00-\u9fff]{2,8}", ocr_text)
    return tokens + chinese


def extract_origin_name_tokens(origin_name: str) -> list[str]:
    if not origin_name:
        return []

    stem = Path(origin_name).stem.replace("-", " ").replace("_", " ")
    return extract_caption_tokens(stem)


def match_entries(text: str, top: int) -> tuple[list[str], list[str], list[dict[str, object]]]:
    haystack = f" {text.lower()} "
    labels: list[str] = []
    keywords: list[str] = []
    classifications: list[dict[str, object]] = []

    for entry in LEXICON:
        needles = (entry.en,) + entry.aliases
        matched = False
        for needle in needles:
            normalized_needle = normalize_whitespace(needle.replace("_", " ").lower())
            pattern = f" {normalized_needle} "
            if pattern in haystack or normalized_needle in haystack:
                matched = True
                keywords.append(normalized_needle)
        if not matched:
            continue

        if entry.zh not in labels:
            labels.append(entry.zh)
        if entry.category and entry.category not in labels:
            labels.append(entry.category)

        classifications.append(
            {
                "zh": entry.zh,
                "en": entry.en,
                "confidence": max(0.55, 0.92 - (len(classifications) * 0.08)),
            }
        )

        if len(classifications) >= top:
            break

    return unique(labels), unique(keywords), classifications


def has_confident_seed_signal(classifications: list[dict[str, object]]) -> bool:
    for item in classifications:
        token = normalize_whitespace(str(item.get("en", "")).lower())
        if token and token not in LOW_SIGNAL_TERMS:
            return True
    return False


def should_keep_visual_tag(name: str) -> bool:
    normalized = normalize_whitespace(name.replace("_", " ").lower())
    if normalized in VISUAL_STOP_TAGS:
        return False
    if normalized in STOPWORDS:
        return False
    if re.fullmatch(r"\d+(girl|girls|boy|boys)", normalized):
        return False
    if re.fullmatch(r"score_\d+", normalized):
        return False
    return len(normalized) >= 2


def normalize_visual_tag_name(name: str) -> str:
    return normalize_whitespace(name.replace("_", " "))


def load_blip_model():
    global _BLIP_PROCESSOR, _BLIP_MODEL
    if _BLIP_PROCESSOR is not None and _BLIP_MODEL is not None:
        return _BLIP_PROCESSOR, _BLIP_MODEL

    try:
        import torch
        from transformers import BlipForConditionalGeneration, BlipProcessor, logging as hf_logging
    except Exception as exc:  # noqa: BLE001
        raise RuntimeError("BLIP legacy backend requires torch and transformers") from exc

    hf_logging.set_verbosity_error()

    source = resolve_hf_model_dir(
        BLIP_MODEL_ID,
        ("preprocessor_config.json", "tokenizer_config.json", "config.json"),
    )
    kwargs: dict[str, Any] = {
        "cache_dir": HF_HOME,
        "local_files_only": True,
    }

    _BLIP_PROCESSOR = BlipProcessor.from_pretrained(source, **kwargs)
    _BLIP_MODEL = BlipForConditionalGeneration.from_pretrained(source, **kwargs)
    _BLIP_MODEL.eval()
    return _BLIP_PROCESSOR, _BLIP_MODEL


def generate_caption(image_path: Path) -> str:
    processor, model = load_blip_model()
    try:
        import torch
    except Exception as exc:  # noqa: BLE001
        raise RuntimeError("torch runtime is unavailable for BLIP legacy backend") from exc

    image = open_image(image_path, CAPTION_MAX_DIMENSION)
    try:
        inputs = processor(images=image, return_tensors="pt")
        with torch.no_grad():
            tokens = model.generate(
                **inputs,
                max_new_tokens=CAPTION_MAX_NEW_TOKENS,
                num_beams=CAPTION_NUM_BEAMS,
            )
        return processor.decode(tokens[0], skip_special_tokens=True).strip()
    finally:
        image.close()


def load_wd_tagger():
    global _WD_SESSION, _WD_INPUT_NAME, _WD_LAYOUT, _WD_TARGET_SIZE, _WD_TAG_ROWS, _WD_MODEL_SOURCE
    if _WD_SESSION is not None:
        return _WD_SESSION, _WD_INPUT_NAME, _WD_LAYOUT, _WD_TARGET_SIZE, _WD_TAG_ROWS, _WD_MODEL_SOURCE

    try:
        import numpy as np
        import onnxruntime as ort
    except Exception as exc:  # noqa: BLE001
        raise RuntimeError("wd_tagger backend requires numpy and onnxruntime") from exc

    model_dir = resolve_hf_model_dir(
        WD_MODEL_ID,
        ("model.onnx", "selected_tags.csv"),
    )
    model_path = Path(model_dir) / "model.onnx"
    tags_path = Path(model_dir) / "selected_tags.csv"

    options = ort.SessionOptions()
    options.intra_op_num_threads = 1
    options.inter_op_num_threads = 1

    session = ort.InferenceSession(str(model_path), sess_options=options, providers=["CPUExecutionProvider"])
    input_tensor = session.get_inputs()[0]
    shape = list(input_tensor.shape)
    if len(shape) != 4:
        raise RuntimeError(f"unsupported wd_tagger input shape: {shape}")

    if shape[-1] == 3:
        layout = "NHWC"
        target_size = int(shape[1])
    elif shape[1] == 3:
        layout = "NCHW"
        target_size = int(shape[2])
    else:
        raise RuntimeError(f"unsupported wd_tagger channel layout: {shape}")

    with tags_path.open("r", encoding="utf-8") as handle:
        rows = list(csv.DictReader(handle))

    _WD_SESSION = session
    _WD_INPUT_NAME = input_tensor.name
    _WD_LAYOUT = layout
    _WD_TARGET_SIZE = target_size
    _WD_TAG_ROWS = rows
    _WD_MODEL_SOURCE = model_dir
    return _WD_SESSION, _WD_INPUT_NAME, _WD_LAYOUT, _WD_TARGET_SIZE, _WD_TAG_ROWS, _WD_MODEL_SOURCE


def prepare_wd_image(image_path: Path, target_size: int, layout: str):
    image_module, image_ops = import_pillow()
    try:
        import numpy as np
    except Exception as exc:  # noqa: BLE001
        raise RuntimeError("numpy is unavailable for wd_tagger image preprocessing") from exc

    image = image_module.open(image_path).convert("RGBA")
    try:
        background = image_module.new("RGBA", image.size, (255, 255, 255, 255))
        background.alpha_composite(image)
        rgb = background.convert("RGB")
        method = image_module.Resampling.BICUBIC if hasattr(image_module, "Resampling") else image_module.BICUBIC
        fitted = image_ops.pad(rgb, (target_size, target_size), method=method, color=(255, 255, 255))
        array = np.asarray(fitted).astype("float32") / 255.0
        array = (array - 0.5) / 0.5
        if layout == "NCHW":
            array = np.transpose(array, (2, 0, 1))
        return np.expand_dims(array, 0)
    finally:
        image.close()


def predict_wd_tags(image_path: Path) -> tuple[list[VisualTag], str]:
    session, input_name, layout, target_size, tag_rows, model_source = load_wd_tagger()
    tensor = prepare_wd_image(image_path, target_size, layout)
    scores = session.run(None, {input_name: tensor})[0][0]

    tags: list[VisualTag] = []
    for row, score in zip(tag_rows, scores):
        category = int(row.get("category", "0"))
        if category != 0:
            continue
        confidence = float(score)
        if confidence < WD_GENERAL_THRESHOLD:
            continue

        name = normalize_visual_tag_name(str(row.get("name", "")))
        if not should_keep_visual_tag(name):
            continue
        tags.append(VisualTag(name=name, score=confidence))

    tags.sort(key=lambda item: item.score, reverse=True)
    return tags[:WD_MAX_VISUAL_TAGS], str(model_source)


def build_caption(labels: list[str], visual_tokens: list[str], raw_caption: str) -> str:
    if labels:
        return f"一张包含{'、'.join(labels[:3])}的图片"
    if visual_tokens:
        return f"一张可能包含{'、'.join(visual_tokens[:3])}的图片"
    return raw_caption


def build_summary(labels: list[str], classifications: list[dict[str, object]], backend: str) -> str:
    if labels:
        return "识别结果：" + "、".join(labels[:4])
    if classifications:
        parts: list[str] = []
        for item in classifications[:3]:
            zh = normalize_whitespace(str(item.get("zh", "")))
            en = normalize_whitespace(str(item.get("en", "")))
            confidence = float(item.get("confidence", 0))
            if zh and zh != en:
                parts.append(f"{zh}({en}) {confidence * 100:.1f}%")
            elif zh:
                parts.append(f"{zh} {confidence * 100:.1f}%")
            elif en:
                parts.append(f"{en} {confidence * 100:.1f}%")
        if parts:
            return "识别结果：" + "、".join(parts)
    if backend == "wd_tagger":
        return "识别结果：本地标签模型未命中可映射标签。"
    return ""


def build_keywords(
    labels: list[str],
    matched_keywords: list[str],
    visual_tokens: list[str],
    caption_tokens: list[str],
    ocr_tokens: list[str],
    origin_name_tokens: list[str],
) -> list[str]:
    values: list[str] = []
    values.extend(labels)
    values.extend(matched_keywords)
    values.extend(visual_tokens[:8])
    values.extend(caption_tokens[:8])
    values.extend(ocr_tokens[:8])
    values.extend(origin_name_tokens[:8])
    return unique(values)[:12]


def enrich_classifications_from_visual_tags(
    classifications: list[dict[str, object]],
    visual_tags: list[VisualTag],
    top: int,
) -> list[dict[str, object]]:
    if len(classifications) >= top:
        return classifications[:top]

    seen = {normalize_whitespace(str(item.get("en", "")).lower()) for item in classifications}
    result = list(classifications)
    for tag in visual_tags:
        normalized = normalize_whitespace(tag.name.lower())
        if not normalized or normalized in seen:
            continue
        result.append(
            {
                "zh": tag.name,
                "en": normalized,
                "confidence": round(tag.score, 4),
            }
        )
        seen.add(normalized)
        if len(result) >= top:
            break
    return result


def analyze(image_path: Path, top: int, origin_name: str = "") -> dict[str, object]:
    start = time.time()
    backend = DEFAULT_BACKEND
    ocr_text = ""
    ocr_error = ""

    try:
        ocr_text = run_ocr(image_path)
    except Exception as exc:  # noqa: BLE001
        ocr_error = str(exc)

    ocr_tokens = extract_ocr_tokens(ocr_text)
    origin_name_tokens = extract_origin_name_tokens(origin_name)
    labels, matched_keywords, classifications = match_entries(
        f"{' '.join(ocr_tokens)} {' '.join(origin_name_tokens)}",
        top,
    )

    raw_caption = ""
    caption_tokens: list[str] = []
    visual_tags: list[VisualTag] = []
    visual_tokens: list[str] = []
    model_source = ""

    if not has_confident_seed_signal(classifications):
        if backend == "wd_tagger":
            visual_tags, model_source = predict_wd_tags(image_path)
            visual_tokens = [tag.name for tag in visual_tags]
            labels, matched_keywords, mapped_classifications = match_entries(
                f"{' '.join(visual_tokens)} {' '.join(ocr_tokens)} {' '.join(origin_name_tokens)}",
                top,
            )
            classifications = enrich_classifications_from_visual_tags(mapped_classifications, visual_tags, top)
        elif backend == "blip_legacy":
            raw_caption = generate_caption(image_path)
            caption_tokens = extract_caption_tokens(raw_caption)
            labels, matched_keywords, classifications = match_entries(
                f"{raw_caption} {' '.join(ocr_tokens)} {' '.join(origin_name_tokens)}",
                top,
            )
            model_source = BLIP_MODEL_ID
        else:
            raise RuntimeError(f"unsupported local tagger backend: {backend}")

    caption = build_caption(labels, visual_tokens, raw_caption)
    summary = build_summary(labels, classifications, backend)
    keywords = build_keywords(labels, matched_keywords, visual_tokens, caption_tokens, ocr_tokens, origin_name_tokens)

    metadata: dict[str, object] = {
        "backend": backend,
        "provider_label": "WD ViT Tagger v3 + Tesseract OCR" if backend == "wd_tagger" else "BLIP-base + Tesseract OCR",
        "model": WD_MODEL_ID if backend == "wd_tagger" else BLIP_MODEL_ID,
        "model_source": model_source,
        "visual_tags": [
            {
                "name": tag.name,
                "score": round(tag.score, 4),
            }
            for tag in visual_tags[:8]
        ],
        "generated_by": "image_intelligence.local.v2",
    }
    if ocr_error:
        metadata["ocr_error"] = ocr_error

    return {
        "caption": caption,
        "raw_caption": raw_caption,
        "summary": summary,
        "labels": labels[: top if top > 0 else DEFAULT_TOP],
        "keywords": keywords,
        "ocr_text": ocr_text[:10000],
        "classifications": classifications[: top if top > 0 else DEFAULT_TOP],
        "metadata": metadata,
        "elapsed_ms": int((time.time() - start) * 1000),
    }


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Local image intelligence via OCR + wd_tagger/blip_legacy")
    parser.add_argument("image_path", help="Absolute path to the image file")
    parser.add_argument("--top", type=int, default=DEFAULT_TOP, help="Maximum number of top labels/classifications")
    parser.add_argument("--origin-name", default="", help="Original filename hint for local intelligence")
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    image_path = Path(args.image_path)

    if not image_path.is_file():
        print(f"image file not found: {image_path}", file=sys.stderr)
        return 1

    try:
        payload = analyze(image_path, max(args.top, 1), str(args.origin_name or ""))
    except Exception as exc:  # noqa: BLE001
        print(str(exc), file=sys.stderr)
        return 1

    print(json.dumps(payload, ensure_ascii=False))
    return 0


if __name__ == "__main__":
    sys.exit(main())
