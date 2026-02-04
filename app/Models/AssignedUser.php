<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignedUser extends Model
{
    protected $fillable = [
        'card_id',
        'uid',
        'displayname',
        'participant_type',
        'deck_assignment_id',
    ];

    protected $casts = [
        'participant_type' => 'integer',
        'deck_assignment_id' => 'integer',
    ];

    /**
     * Get the card this user is assigned to
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    /**
     * Sync assigned users for a card
     */
    public static function syncForCard(int $cardId, array $assignedUsers): void
    {
        // Get existing UIDs
        $existingUids = static::where('card_id', $cardId)->pluck('uid')->toArray();
        $newUids = [];

        foreach ($assignedUsers as $user) {
            $uid = $user['participant']['uid'] ?? null;
            if (!$uid) {
                continue;
            }

            $newUids[] = $uid;

            static::updateOrCreate(
                [
                    'card_id' => $cardId,
                    'uid' => $uid,
                ],
                [
                    'displayname' => $user['participant']['displayname'] ?? null,
                    'participant_type' => $user['participant']['type'] ?? 0,
                    'deck_assignment_id' => $user['id'] ?? null,
                ]
            );
        }

        // Remove users no longer assigned
        $toRemove = array_diff($existingUids, $newUids);
        if (!empty($toRemove)) {
            static::where('card_id', $cardId)
                ->whereIn('uid', $toRemove)
                ->delete();
        }
    }
}
