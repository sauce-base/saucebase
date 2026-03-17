<?php

namespace Modules\Roadmap\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Roadmap\Enums\RoadmapStatus;
use Modules\Roadmap\Enums\RoadmapType;
use Modules\Roadmap\Models\RoadmapItem;
use Modules\Roadmap\Models\RoadmapVote;

class RoadmapController
{
    public function index(Request $request): Response
    {
        $sort = in_array($request->input('sort'), ['top', 'new', 'old'])
            ? $request->input('sort')
            : 'top';

        $items = RoadmapItem::public()
            ->when($sort === 'new', fn ($q) => $q->orderByDesc('created_at'))
            ->when($sort === 'old', fn ($q) => $q->orderBy('created_at'))
            ->when($sort === 'top', fn ($q) => $q->orderByRaw('(upvotes_count - downvotes_count) DESC'))
            ->limit(50)
            ->get();

        $userVotes = RoadmapVote::where('user_id', auth()->id())
            ->whereIn('roadmap_item_id', $items->pluck('id'))
            ->pluck('type', 'roadmap_item_id');

        return Inertia::render('Roadmap::Index', [
            'items' => $items->map(fn (RoadmapItem $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'status' => $item->status->value,
                'status_label' => $item->status->getLabel(),
                'type' => $item->type->value,
                'type_label' => $item->type->getLabel(),
                'net_score' => $item->upvotes_count - $item->downvotes_count,
                'user_vote' => $userVotes->get($item->id),
                'created_at' => $item->created_at->toDateString(),
            ]),
            'sort' => $sort,
            'types' => collect(RoadmapType::cases())->map(fn (RoadmapType $t) => [
                'value' => $t->value,
                'label' => $t->getLabel(),
                'color' => $t->getColor(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', Rule::enum(RoadmapType::class)],
        ]);

        RoadmapItem::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => RoadmapStatus::PendingApproval,
            'type' => RoadmapType::from($validated['type']),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('roadmap.index')
            ->with('toast', [
                'type' => 'success',
                'message' => __('Suggestion submitted!'),
                'description' => __('Your idea is pending review. We\'ll publish it once approved.'),
            ]);
    }
}
