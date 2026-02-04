<?php
/**
 * Test script to check 'done' field in all sample JSON files
 */

$files = glob(__DIR__ . '/amostra/*.json');

foreach ($files as $file) {
    echo "=== " . basename($file) . " ===" . PHP_EOL;
    
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "  JSON Error: " . json_last_error_msg() . PHP_EOL;
        continue;
    }
    
    $boards = $data['boards'] ?? $data;
    if (!isset($boards[0])) {
        $boards = [$boards];
    }
    
    $doneValues = [];
    $found = 0;
    
    foreach ($boards as $board) {
        $stacks = $board['stacks'] ?? [];
        foreach ($stacks as $stack) {
            if (!is_array($stack)) {
                continue;
            }
            $cards = $stack['cards'] ?? [];
            foreach ($cards as $card) {
                $done = $card['done'] ?? null;
                if ($done !== null) {
                    $found++;
                    $key = gettype($done) . ':' . (is_bool($done) ? ($done ? 'true' : 'false') : substr((string)$done, 0, 30));
                    if (!isset($doneValues[$key])) {
                        $doneValues[$key] = ['count' => 0, 'sample' => $done, 'type' => gettype($done)];
                    }
                    $doneValues[$key]['count']++;
                }
            }
        }
    }
    
    if (empty($doneValues)) {
        echo "  No 'done' values found (all null)" . PHP_EOL;
    } else {
        echo "  Found $found cards with 'done' value:" . PHP_EOL;
        foreach ($doneValues as $key => $info) {
            echo "    - Type: {$info['type']}, Sample: " . var_export($info['sample'], true) . " ({$info['count']} occurrences)" . PHP_EOL;
        }
    }
    echo PHP_EOL;
}

echo "=== CONCLUSION ===" . PHP_EOL;
echo "If 'done' is a string (datetime), it needs to be converted to boolean or stored differently." . PHP_EOL;
