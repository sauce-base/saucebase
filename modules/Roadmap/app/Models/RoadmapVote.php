<?php

namespace Modules\Roadmap\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Roadmap\Enums\VoteType;

class RoadmapVote extends Model
{
    protected $fillable = [
        'roadmap_item_id',
        'user_id',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => VoteType::class,
        ];
    }

    public function roadmapItem(): BelongsTo
    {
        return $this->belongsTo(RoadmapItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
