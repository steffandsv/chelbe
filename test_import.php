<?php
/**
 * Test script to validate JSON structure against expected database columns
 * Run with: php test_import.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$json = file_get_contents(__DIR__ . '/amostra/❌Derrotas❌ (1).json');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Error: " . json_last_error_msg() . PHP_EOL;
    exit(1);
}

echo "=== JSON STRUCTURE ANALYSIS ===" . PHP_EOL . PHP_EOL;

// Check root structure
$boards = $data['boards'] ?? $data;
if (!isset($boards[0])) {
    $boards = [$boards];
}

foreach ($boards as $boardIndex => $board) {
    echo "BOARD $boardIndex: " . ($board['title'] ?? 'Unknown') . PHP_EOL;
    echo "  - id: " . var_export($board['id'] ?? null, true) . PHP_EOL;
    echo "  - owner: " . var_export($board['owner'] ?? null, true) . PHP_EOL;
    echo "  - color: " . var_export($board['color'] ?? null, true) . PHP_EOL;
    echo "  - archived: " . var_export($board['archived'] ?? null, true) . " (type: " . gettype($board['archived'] ?? null) . ")" . PHP_EOL;
    echo PHP_EOL;

    $stacks = $board['stacks'] ?? [];
    $stackCount = 0;
    foreach ($stacks as $stackId => $stack) {
        if (!is_array($stack)) {
            continue;
        }
        $stackCount++;
        if ($stackCount <= 2) {
            echo "  STACK $stackId: " . ($stack['title'] ?? 'Unknown') . PHP_EOL;
            echo "    - id: " . var_export($stack['id'] ?? null, true) . PHP_EOL;
            echo "    - boardId: " . var_export($stack['boardId'] ?? null, true) . PHP_EOL;
            echo "    - order: " . var_export($stack['order'] ?? null, true) . PHP_EOL;
        }

        $cards = $stack['cards'] ?? [];
        foreach ($cards as $cardIndex => $card) {
            if ($stackCount <= 2 && $cardIndex < 1) {
                echo PHP_EOL . "    CARD SAMPLE:" . PHP_EOL;
                echo "      - id: " . var_export($card['id'] ?? null, true) . PHP_EOL;
                echo "      - title: " . mb_substr($card['title'] ?? '', 0, 50) . "..." . PHP_EOL;
                echo "      - stackId: " . var_export($card['stackId'] ?? null, true) . PHP_EOL;
                echo "      - order: " . var_export($card['order'] ?? null, true) . " (type: " . gettype($card['order'] ?? null) . ")" . PHP_EOL;
                echo "      - archived: " . var_export($card['archived'] ?? null, true) . " (type: " . gettype($card['archived'] ?? null) . ")" . PHP_EOL;
                echo "      - done: " . var_export($card['done'] ?? null, true) . " (type: " . gettype($card['done'] ?? null) . ")" . PHP_EOL;
                echo "      - notified: " . var_export($card['notified'] ?? null, true) . " (type: " . gettype($card['notified'] ?? null) . ")" . PHP_EOL;
                echo "      - commentsCount: " . var_export($card['commentsCount'] ?? null, true) . PHP_EOL;
                echo "      - createdAt: " . var_export($card['createdAt'] ?? null, true) . " (type: " . gettype($card['createdAt'] ?? null) . ")" . PHP_EOL;
                echo "      - lastModified: " . var_export($card['lastModified'] ?? null, true) . " (type: " . gettype($card['lastModified'] ?? null) . ")" . PHP_EOL;
                echo "      - duedate: " . var_export($card['duedate'] ?? null, true) . " (type: " . gettype($card['duedate'] ?? null) . ")" . PHP_EOL;
                echo "      - ETag: " . var_export($card['ETag'] ?? null, true) . PHP_EOL;

                if (isset($card['owner'])) {
                    echo "      - owner.uid: " . var_export($card['owner']['uid'] ?? null, true) . PHP_EOL;
                    echo "      - owner.displayname: " . var_export($card['owner']['displayname'] ?? null, true) . PHP_EOL;
                }

                if (isset($card['assignedUsers']) && count($card['assignedUsers']) > 0) {
                    echo "      - assignedUsers[0]:" . PHP_EOL;
                    $au = $card['assignedUsers'][0];
                    echo "        - id: " . var_export($au['id'] ?? null, true) . PHP_EOL;
                    echo "        - cardId: " . var_export($au['cardId'] ?? null, true) . PHP_EOL;
                    if (isset($au['participant'])) {
                        echo "        - participant.uid: " . var_export($au['participant']['uid'] ?? null, true) . PHP_EOL;
                        echo "        - participant.displayname: " . var_export($au['participant']['displayname'] ?? null, true) . PHP_EOL;
                    }
                }
            }
        }
    }
    echo PHP_EOL . "  Total stacks: $stackCount" . PHP_EOL;
}

echo PHP_EOL . "=== POTENTIAL ISSUES ===" . PHP_EOL;

// Check for problematic values
$issues = [];
foreach ($boards as $board) {
    $stacks = $board['stacks'] ?? [];
    foreach ($stacks as $stack) {
        if (!is_array($stack)) {
            continue;
        }
        $cards = $stack['cards'] ?? [];
        foreach ($cards as $card) {
            // Check 'done' field - should be boolean but might be datetime
            $done = $card['done'] ?? null;
            if ($done !== null && !is_bool($done)) {
                $issues['done_not_boolean'][] = [
                    'card_id' => $card['id'] ?? 'unknown',
                    'value' => $done,
                    'type' => gettype($done)
                ];
            }

            // Check 'duedate' field
            $duedate = $card['duedate'] ?? null;
            if ($duedate !== null && !is_string($duedate)) {
                $issues['duedate_not_string'][] = [
                    'card_id' => $card['id'] ?? 'unknown',
                    'value' => $duedate,
                    'type' => gettype($duedate)
                ];
            }
        }
    }
}

if (isset($issues['done_not_boolean'])) {
    echo PHP_EOL . "⚠️  'done' field is NOT always boolean:" . PHP_EOL;
    foreach (array_slice($issues['done_not_boolean'], 0, 3) as $issue) {
        echo "   Card {$issue['card_id']}: {$issue['value']} ({$issue['type']})" . PHP_EOL;
    }
    echo "   ... " . count($issues['done_not_boolean']) . " total occurrences" . PHP_EOL;
}

if (empty($issues)) {
    echo "✅ No obvious issues detected" . PHP_EOL;
}

echo PHP_EOL . "=== DONE ===" . PHP_EOL;
