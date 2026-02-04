<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Exibe a página de análise de derrotas.
     */
    public function index()
    {
        return view('dashboard.analytics');
    }

    /**
     * Processa o arquivo JSON de derrotas e gera estatísticas.
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json|max:10240',
        ]);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $content = file_get_contents($file->getPathname());
        $data = json_decode($content, true);

        if (!$data || !is_array($data)) {
            return back()->withErrors(['file' => 'Arquivo JSON inválido.']);
        }

        $stats = $this->calculateStats($data);
        $stats['filename'] = $filename;

        return view('dashboard.analytics', [
            'stats' => $stats,
            'filename' => $filename,
        ]);
    }

    /**
     * Calcula estatísticas a partir dos dados de derrotas.
     */
    private function calculateStats(array $data): array
    {
        $totalLosses = count($data);
        $totalValue = 0;
        $timeline = [];
        $hourly = array_fill(0, 24, 0);
        $dates = [];

        foreach ($data as $item) {
            // Extrair valor se existir
            if (isset($item['value'])) {
                $value = preg_replace('/[^\d,.]/', '', $item['value']);
                $value = (float) str_replace(',', '.', $value);
                $totalValue += $value;
            }

            // Extrair data/hora
            $dateStr = $item['date'] ?? $item['dateLastActivity'] ?? $item['created_at'] ?? null;
            if ($dateStr) {
                try {
                    $date = Carbon::parse($dateStr);
                    $dayKey = $date->format('Y-m-d');
                    $timeline[$dayKey] = ($timeline[$dayKey] ?? 0) + 1;
                    $hourly[$date->hour]++;
                    $dates[] = $date;
                } catch (\Exception $e) {
                    // Ignora datas inválidas
                }
            }
        }

        // Ordenar timeline
        ksort($timeline);

        // Hora crítica (mais derrotas)
        $criticalHour = array_search(max($hourly), $hourly);

        // Calcular máximo de derrotas consecutivas por dia
        $maxConsecutive = !empty($timeline) ? max($timeline) : 0;

        // Comparação semanal
        $weeklyComparison = [
            'current' => 0,
            'last' => 0,
        ];

        foreach ($dates as $date) {
            if ($date->isCurrentWeek()) {
                $weeklyComparison['current']++;
            } elseif ($date->isLastWeek()) {
                $weeklyComparison['last']++;
            }
        }

        // Recomendar pausa se muitas derrotas consecutivas ou alta densidade
        $recommendPause = $maxConsecutive >= 5 || $totalLosses > 20;

        return [
            'total_losses' => $totalLosses,
            'total_value' => $totalValue,
            'critical_hour' => $criticalHour . 'h',
            'max_consecutive' => $maxConsecutive,
            'timeline' => $timeline,
            'hourly' => $hourly,
            'weekly_comparison' => $weeklyComparison,
            'recommend_pause' => $recommendPause,
        ];
    }
}
