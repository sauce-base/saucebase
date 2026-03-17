<?php

namespace Modules\Roadmap\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Roadmap\Enums\RoadmapStatus;
use Modules\Roadmap\Enums\VoteType;
use Modules\Roadmap\Models\RoadmapItem;

class RoadmapVoteController
{
    public function store(Request $request, RoadmapItem $item): RedirectResponse
    {
        if (! in_array($item->status, RoadmapStatus::publicStatuses())) {
            abort(404);
        }

        $validated = $request->validate([
            'type' => ['required', Rule::enum(VoteType::class)],
        ]);

        $type = VoteType::from($validated['type']);

        DB::transaction(function () use ($item, $type): void {
            $existing = $item->votes()->where('user_id', auth()->id())->lockForUpdate()->first();

            if ($existing) {
                if ($existing->type === $type) {
                    $existing->delete();
                } else {
                    $existing->update(['type' => $type]);
                }
            } else {
                $item->votes()->create(['user_id' => auth()->id(), 'type' => $type]);
            }
        });

        return back();
    }
}
