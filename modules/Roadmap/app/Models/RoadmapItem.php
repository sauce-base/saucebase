<?php

namespace Modules\Roadmap\Models;

use App\Models\User;
use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Roadmap\Enums\RoadmapStatus;
use Modules\Roadmap\Enums\RoadmapType;
use Modules\Roadmap\Enums\VoteType;
use Modules\Roadmap\Events\StatusChanged;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property RoadmapStatus $status
 * @property RoadmapType $type
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int $upvotes_count
 * @property-read int $downvotes_count
 */
class RoadmapItem extends Model
{
    use HasFactory, Sluggable;

    protected static function booted(): void
    {
        static::updated(function (RoadmapItem $item): void {
            if ($item->wasChanged('status')) {
                StatusChanged::dispatch($item);
            }
        });
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
        ];
    }

    protected $fillable = [
        'title',
        'slug',
        'description',
        'status',
        'type',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => RoadmapStatus::class,
            'type' => RoadmapType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(RoadmapVote::class);
    }

    public function voters(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'roadmap_votes')->withTimestamps();
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query
            ->whereIn('status', RoadmapStatus::publicStatuses())
            ->withCount([
                'votes as upvotes_count' => fn ($q) => $q->where('type', VoteType::Up),
                'votes as downvotes_count' => fn ($q) => $q->where('type', VoteType::Down),
            ]);
    }
}
