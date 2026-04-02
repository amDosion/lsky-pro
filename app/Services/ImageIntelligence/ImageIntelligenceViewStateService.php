<?php

namespace App\Services\ImageIntelligence;

use App\Models\ImageIntelligenceRecord;

class ImageIntelligenceViewStateService
{
    /**
     * @return array<string, mixed>
     */
    public function buildListPayload(?ImageIntelligenceRecord $record): array
    {
        $fallback = $this->isFallbackRecord($record);
        $status = $record ? trim((string) $record->status) : '';
        $source = $record ? trim((string) $record->source) : '';
        $analyzedAt = $record && $record->analyzed_at
            ? $record->analyzed_at->toDateTimeString()
            : null;
        $summary = $record ? $record->summary : null;
        $caption = $record ? $record->caption : null;
        $ocrText = $record ? $record->ocr_text : null;

        return [
            'available' => $record !== null,
            'status' => $status !== '' ? $status : 'missing',
            'source' => $source,
            'fallback' => $fallback,
            'ready' => $this->isReadyRecord($record),
            'analyzed_at' => $analyzedAt,
            'display_summary' => $fallback
                ? null
                : $this->buildDisplaySummary($summary, $caption, $ocrText),
        ];
    }

    public function isReadyRecord(?ImageIntelligenceRecord $record): bool
    {
        return $record !== null
            && trim((string) $record->status) === 'ready'
            && ! $this->isFallbackRecord($record);
    }

    public function isFallbackRecord(?ImageIntelligenceRecord $record): bool
    {
        if (! $record) {
            return false;
        }

        $metadata = is_array($record->metadata) ? $record->metadata : [];

        return (bool) ($metadata['fallback'] ?? false)
            || trim((string) $record->source) === 'metadata_placeholder';
    }

    public function buildDisplaySummary(?string $summary, ?string $caption, ?string $ocrText): ?string
    {
        foreach ([$summary, $caption] as $candidate) {
            $normalized = $this->normalizeText($candidate);
            if ($normalized !== '') {
                return $this->truncate($normalized);
            }
        }

        $normalizedOcr = $this->normalizeText($ocrText);
        if (! $this->looksLikeMeaningfulOcr($normalizedOcr)) {
            return null;
        }

        return $this->truncate($normalizedOcr);
    }

    private function normalizeText(?string $text): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim((string) $text));

        return trim((string) $normalized);
    }

    private function looksLikeMeaningfulOcr(string $text): bool
    {
        if ($text === '' || mb_strlen($text) < 6) {
            return false;
        }

        if (preg_match('/\p{Han}{2,}/u', $text) === 1) {
            return true;
        }

        preg_match_all('/\b[A-Za-z]\b/u', $text, $singleLetterMatches);
        if (count($singleLetterMatches[0] ?? []) >= 3) {
            return false;
        }

        preg_match_all('/[A-Za-z0-9]{4,}/u', $text, $wordMatches);
        $longWordCount = count($wordMatches[0] ?? []);
        if ($longWordCount >= 2) {
            return true;
        }

        $segments = preg_split('/\s+/u', $text) ?: [];
        $multiCharSegments = array_filter($segments, function (string $segment): bool {
            $clean = preg_replace('/[^\p{L}\p{N}]+/u', '', $segment);

            return mb_strlen((string) $clean) >= 2;
        });
        if ($longWordCount === 0 || count($multiCharSegments) < 2) {
            return false;
        }

        $meaningfulChars = preg_replace('/[^\p{L}\p{N}]+/u', '', $text);

        return mb_strlen((string) $meaningfulChars) >= 8;
    }

    private function truncate(string $text, int $limit = 140): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit).'...';
    }
}
