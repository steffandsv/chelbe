<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\ImportLog;
use App\Services\DateExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Main dashboard view
     */
    public function index()
    {
        $stats = Card::getStats();
        
        $recentCards = Card::with('labels')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return view('dashboard.index', [
            'stats' => $stats,
            'recentCards' => $recentCards,
        ]);
    }

    /**
     * Analytics page
     */
    public function analytics()
    {
        return view('dashboard.analytics');
    }

    /**
     * Get weekly stats for chart (API)
     */
    public function weeklyStats()
    {
        $extractor = new DateExtractor();
        
        $weeks = Card::selectRaw('
            YEAR(extracted_date) as year,
            WEEK(extracted_date, 1) as week_num,
            COUNT(*) as total,
            SUM(CASE WHEN user_status = "won" THEN 1 ELSE 0 END) as won,
            SUM(CASE WHEN user_status = "lost" THEN 1 ELSE 0 END) as lost
        ')
        ->whereNotNull('extracted_date')
        ->groupByRaw('YEAR(extracted_date), WEEK(extracted_date, 1)')
        ->orderByDesc('year')
        ->orderByDesc('week_num')
        ->limit(12)
        ->get()
        ->reverse()
        ->values()
        ->map(function($w) use ($extractor) {
            $wonLost = $w->won + $w->lost;
            return [
                'label' => $extractor->weekToDateRange($w->year, $w->week_num),
                'week_key' => $w->year . '-S' . str_pad($w->week_num, 2, '0', STR_PAD_LEFT),
                'total' => $w->total,
                'won' => $w->won,
                'lost' => $w->lost,
                'win_rate' => $wonLost > 0 ? round($w->won / $wonLost * 100, 1) : 0,
            ];
        });

        return response()->json($weeks);
    }

    /**
     * Get monthly stats for chart (API)
     */
    public function monthlyStats()
    {
        $monthNames = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 
                       'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        
        $months = Card::selectRaw('
            YEAR(extracted_date) as year,
            MONTH(extracted_date) as month,
            COUNT(*) as total,
            SUM(CASE WHEN user_status = "won" THEN 1 ELSE 0 END) as won,
            SUM(CASE WHEN user_status = "lost" THEN 1 ELSE 0 END) as lost
        ')
        ->whereNotNull('extracted_date')
        ->groupByRaw('YEAR(extracted_date), MONTH(extracted_date)')
        ->orderByDesc('year')
        ->orderByDesc('month')
        ->limit(12)
        ->get()
        ->reverse()
        ->values()
        ->map(function($m) use ($monthNames) {
            $wonLost = $m->won + $m->lost;
            return [
                'label' => $monthNames[$m->month] . '/' . $m->year,
                'total' => $m->total,
                'won' => $m->won,
                'lost' => $m->lost,
                'win_rate' => $wonLost > 0 ? round($m->won / $wonLost * 100, 1) : 0,
            ];
        });

        return response()->json($months);
    }

    /**
     * Get analyst ranking (API)
     */
    public function analystStats()
    {
        $analysts = Card::selectRaw('
            analyst,
            COUNT(*) as total_cards,
            SUM(CASE WHEN user_status = "won" THEN 1 ELSE 0 END) as won,
            SUM(CASE WHEN user_status = "lost" THEN 1 ELSE 0 END) as lost
        ')
        ->whereNotNull('analyst')
        ->groupBy('analyst')
        ->orderByDesc('total_cards')
        ->limit(10)
        ->get()
        ->map(function($a) {
            $wonLost = $a->won + $a->lost;
            return [
                'analyst' => $a->analyst,
                'total_cards' => $a->total_cards,
                'won' => $a->won,
                'lost' => $a->lost,
                'win_rate' => $wonLost > 0 ? round($a->won / $wonLost * 100, 1) : 0,
            ];
        });

        return response()->json($analysts);
    }

    /**
     * Get portal stats (API)
     */
    public function portalStats()
    {
        $portals = Card::selectRaw('
            COALESCE(portal, "Não identificado") as portal,
            COUNT(*) as total
        ')
        ->groupBy(DB::raw('COALESCE(portal, "Não identificado")'))
        ->orderByDesc('total')
        ->limit(10)
        ->get();

        return response()->json($portals);
    }

    /**
     * Get metrics distribution (API)
     */
    public function metricsStats()
    {
        $fields = [
            'viabilidade_tatica', 
            'complexidade_operacional',
            'lucratividade_potencial', 
            'chance_vitoria', 
            'risco_operacional'
        ];
        
        $metrics = [];
        
        foreach ($fields as $field) {
            $metrics[$field] = Card::selectRaw("$field as value, COUNT(*) as count")
                ->whereNotNull($field)
                ->groupBy($field)
                ->orderByDesc('count')
                ->get();
        }

        return response()->json($metrics);
    }
}
