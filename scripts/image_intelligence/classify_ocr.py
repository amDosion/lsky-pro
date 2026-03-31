#!/usr/bin/env python3
import argparse
import json
import os
import re
import subprocess
import sys
import time
from dataclasses import dataclass
from pathlib import Path

import torch
from PIL import Image
from transformers import BlipForConditionalGeneration, BlipProcessor

MODEL_ID = os.getenv("LSKY_LOCAL_VISION_MODEL", "Salesforce/blip-image-captioning-base")
HF_HOME = os.getenv("HF_HOME", "/opt/models/huggingface")
TESSERACT_LANG = os.getenv("LSKY_LOCAL_OCR_LANG", "chi_sim+eng")
DEFAULT_TOP = 3


@dataclass(frozen=True)
class LexiconEntry:
    en: str
    zh: str
    aliases: tuple[str, ...] = ()
    category: str | None = None


LEXICON = [
    LexiconEntry("sock", "袜子", ("socks", "stocking", "stockings"), "服饰"),
    LexiconEntry("shoe", "鞋子", ("shoes", "sneaker", "sneakers", "boot", "boots", "slipper", "slippers", "sandal", "sandals"), "服饰"),
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
    LexiconEntry("woman", "女性", ("girl", "lady"), "人物"),
    LexiconEntry("man", "男性", ("boy", "gentleman"), "人物"),
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
    "a", "an", "the", "and", "of", "on", "in", "with", "for", "to", "from",
    "at", "by", "is", "are", "be", "this", "that", "these", "those", "pair",
    "set", "close", "up", "image", "photo", "picture", "there", "it", "its",
}

_PROCESSOR = None
_MODEL = None


def load_model():
    global _PROCESSOR, _MODEL
    if _PROCESSOR is None or _MODEL is None:
        _PROCESSOR = BlipProcessor.from_pretrained(
            MODEL_ID,
            cache_dir=HF_HOME,
            local_files_only=True,
        )
        _MODEL = BlipForConditionalGeneration.from_pretrained(
            MODEL_ID,
            cache_dir=HF_HOME,
            local_files_only=True,
        )
        _MODEL.eval()
    return _PROCESSOR, _MODEL


def generate_caption(image_path: Path) -> str:
    processor, model = load_model()
    image = Image.open(image_path).convert("RGB")
    inputs = processor(images=image, return_tensors="pt")
    with torch.no_grad():
        tokens = model.generate(
            **inputs,
            max_new_tokens=32,
            num_beams=3,
        )
    return processor.decode(tokens[0], skip_special_tokens=True).strip()


def run_ocr(image_path: Path) -> str:
    result = subprocess.run(
        ["tesseract", str(image_path), "stdout", "-l", TESSERACT_LANG, "--psm", "6"],
        capture_output=True,
        text=True,
        timeout=60,
    )
    if result.returncode != 0:
        return ""

    return normalize_whitespace(result.stdout)


def normalize_whitespace(value: str) -> str:
    return re.sub(r"\s+", " ", value).strip()


def extract_caption_tokens(caption: str) -> list[str]:
    tokens = re.findall(r"[a-z0-9][a-z0-9_-]*", caption.lower())
    return [token for token in tokens if token not in STOPWORDS]


def extract_ocr_tokens(ocr_text: str) -> list[str]:
    lower = ocr_text.lower()
    tokens = re.findall(r"[a-z0-9][a-z0-9_-]*", lower)
    chinese = re.findall(r"[\u4e00-\u9fff]{2,8}", ocr_text)
    return tokens + chinese


def match_entries(text: str, top: int) -> tuple[list[str], list[str], list[dict[str, object]]]:
    haystack = f" {text.lower()} "
    labels: list[str] = []
    keywords: list[str] = []
    classifications: list[dict[str, object]] = []

    for entry in LEXICON:
        needles = (entry.en,) + entry.aliases
        matched = False
        for needle in needles:
            pattern = f" {needle.lower()} "
            if pattern in haystack or needle.lower() in haystack:
                matched = True
                keywords.append(needle.lower())
        if not matched:
            continue

        if entry.zh not in labels:
            labels.append(entry.zh)
        if entry.category and entry.category not in labels:
            labels.append(entry.category)

        classifications.append({
            "zh": entry.zh,
            "en": entry.en,
            "confidence": max(0.55, 0.92 - (len(classifications) * 0.08)),
        })

        if len(classifications) >= top:
            break

    return unique(labels), unique(keywords), classifications


def unique(values: list[str]) -> list[str]:
    seen: set[str] = set()
    result: list[str] = []
    for value in values:
        normalized = normalize_whitespace(value)
        if not normalized or normalized in seen:
            continue
        seen.add(normalized)
        result.append(normalized)
    return result


def build_caption(raw_caption: str, labels: list[str]) -> str:
    if labels:
        return f"一张包含{'、'.join(labels[:3])}的图片"
    return raw_caption


def build_keywords(
    labels: list[str],
    matched_keywords: list[str],
    caption_tokens: list[str],
    ocr_tokens: list[str],
) -> list[str]:
    values = []
    values.extend(labels)
    values.extend(matched_keywords)
    values.extend(caption_tokens[:8])
    values.extend(ocr_tokens[:8])
    return unique(values)[:12]


def analyze(image_path: Path, top: int) -> dict[str, object]:
    start = time.time()
    raw_caption = generate_caption(image_path)
    ocr_text = run_ocr(image_path)
    caption_tokens = extract_caption_tokens(raw_caption)
    ocr_tokens = extract_ocr_tokens(ocr_text)

    labels, matched_keywords, classifications = match_entries(
        f"{raw_caption} {' '.join(ocr_tokens)}",
        top,
    )

    caption = build_caption(raw_caption, labels)
    keywords = build_keywords(labels, matched_keywords, caption_tokens, ocr_tokens)

    return {
        "caption": caption,
        "raw_caption": raw_caption,
        "labels": labels[:top if top > 0 else DEFAULT_TOP],
        "keywords": keywords,
        "ocr_text": ocr_text[:10000],
        "classifications": classifications[:top if top > 0 else DEFAULT_TOP],
        "elapsed_ms": int((time.time() - start) * 1000),
    }


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Local image intelligence via BLIP caption + Tesseract OCR")
    parser.add_argument("image_path", help="Absolute path to the image file")
    parser.add_argument("--top", type=int, default=DEFAULT_TOP, help="Maximum number of top labels/classifications")
    return parser.parse_args()


def main() -> int:
    torch.set_num_threads(max(1, min(os.cpu_count() or 1, 4)))
    args = parse_args()
    image_path = Path(args.image_path)

    if not image_path.is_file():
        print(f"image file not found: {image_path}", file=sys.stderr)
        return 1

    try:
        payload = analyze(image_path, max(args.top, 1))
    except Exception as exc:  # noqa: BLE001
        print(str(exc), file=sys.stderr)
        return 1

    print(json.dumps(payload, ensure_ascii=False))
    return 0


if __name__ == "__main__":
    sys.exit(main())
