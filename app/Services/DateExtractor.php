<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Date Extractor Service
 * 
 * Tiered date extraction: title → description → duedate
 */
class DateExtractor
{
    /**
     * Portuguese month names
     */
    private const MONTHS_PT = [
        'janeiro' => 1, 'fevereiro' => 2, 'março' => 3, 'marco' => 3,
        'abril' => 4, 'maio' => 5, 'junho' => 6, 'julho' => 7,
        'agosto' => 8, 'setembro' => 9, 'outubro' => 10,
        'novembro' => 11, 'dezembro' => 12,
        'jan' => 1, 'fev' => 2, 'mar' => 3, 'abr' => 4,
        'mai' => 5, 'jun' => 6, 'jul' => 7, 'ago' => 8,
        'set' => 9, 'out' => 10, 'nov' => 11, 'dez' => 12,
    ];

    /**
     * Extract date from card with tiered priority
     */
    public function extract(array $card): ?string
    {
        // 1. Title (highest priority)
        if ($date = $this->parseFromText($card['title'] ?? '')) {
            return $date;
        }

        // 2. Description
        if ($date = $this->parseFromText($card['description'] ?? '')) {
            return $date;
        }

        // 3. Due date fallback
        $dueDate = $card['duedate'] ?? $card['due'] ?? null;
        if ($dueDate) {
            try {
                return Carbon::parse($dueDate)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Parse date from text
     */
    public function parseFromText(string $text): ?string
    {
        if (empty(trim($text))) {
            return null;
        }

        // DD/MM/YYYY or DD-MM-YYYY
        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $text, $m)) {
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = (int) $m[3];
            
            if (checkdate($month, $day, $year) && $year >= 2000 && $year <= 2100) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        // YYYY-MM-DD (ISO)
        if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $text, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            $day = (int) $m[3];
            
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        // "15 de Janeiro de 2025"
        $pattern = '/(\d{1,2})\s*(?:de\s+)?([a-záéíóúâêôãõç]+)\s*(?:de\s+)?(\d{4})/iu';
        if (preg_match($pattern, $text, $m)) {
            $day = (int) $m[1];
            $monthName = mb_strtolower(trim($m[2]));
            $year = (int) $m[3];
            
            if (isset(self::MONTHS_PT[$monthName])) {
                $month = self::MONTHS_PT[$monthName];
                if (checkdate($month, $day, $year)) {
                    return sprintf('%04d-%02d-%02d', $year, $month, $day);
                }
            }
        }

        return null;
    }

    /**
     * Convert week number to date range
     */
    public function weekToDateRange(int $year, int $week): string
    {
        $dto = Carbon::now()->setISODate($year, $week, 1);
        $start = $dto->format('Y-m-d');
        $end = $dto->addDays(6)->format('Y-m-d');

        return "$start a $end";
    }

    /**
     * Format week key for display
     */
    public function formatWeekKey(string $weekKey): string
    {
        if (preg_match('/(\d{4})-S(\d+)/i', $weekKey, $m)) {
            return $this->weekToDateRange((int) $m[1], (int) $m[2]);
        }
        return $weekKey;
    }
}
