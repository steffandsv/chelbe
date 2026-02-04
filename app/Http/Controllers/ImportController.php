<?php

namespace App\Http\Controllers;

use App\Models\ImportLog;
use App\Services\DeckImporter;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    /**
     * Show import page with import history
     */
    public function index()
    {
        $logs = ImportLog::orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('import.index', [
            'logs' => $logs,
        ]);
    }

    /**
     * Handle file upload and import
     */
    public function store(Request $request)
    {
        // Check if the upload exceeded post_max_size
        if (empty($_FILES) && empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
            return response()->json([
                'success' => false,
                'errors' => ['O arquivo enviado excede o limite do servidor (post_max_size).'],
            ], 422);
        }

        $request->validate([
            'file' => 'required|file|mimes:json,txt|max:51200', // 50MB
            'import_type' => 'required|in:lost,won,tracking',
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->path());
        $filename = $file->getClientOriginalName();
        $importType = $request->input('import_type');

        // Decode JSON
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'success' => false,
                'errors' => ['Arquivo JSON inválido: ' . json_last_error_msg()],
            ], 400);
        }

        // Import data
        $importer = new DeckImporter();
        $result = $importer->import($data, $importType, $filename);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'errors' => [$result['error'] ?? 'Erro desconhecido durante a importação.'],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'board_name' => $result['board_name'],
            'cards_created' => $result['cards_created'],
            'cards_updated' => $result['cards_updated'],
            'cards_skipped' => $result['cards_skipped'],
            'labels_processed' => $result['labels_processed'],
            'users_processed' => $result['users_processed'],
        ]);
    }

    /**
     * Preview import without making changes
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json,txt|max:51200',
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->path());

        // Decode JSON
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'success' => false,
                'errors' => ['Arquivo JSON inválido: ' . json_last_error_msg()],
            ], 400);
        }

        $importer = new DeckImporter();
        $preview = $importer->preview($data);

        return response()->json([
            'success' => true,
            'preview' => $preview,
        ]);
    }
}
