<?php

namespace App\Http\Controllers;

use App\Models\ImportLog;
use App\Services\TrelloImporter;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    /**
     * Show import page
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
     * Handle file upload
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
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->path());
        $filename = $file->getClientOriginalName();

        $importer = new TrelloImporter();
        $result = $importer->import($content, $filename);

        if (!empty($result['errors'])) {
            return response()->json([
                'success' => false,
                'errors' => $result['errors'],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'imported' => $result['imported'],
            'updated' => $result['updated'],
            'labels' => $result['labels'],
        ]);
    }
}
