<?php

namespace Modules\Blog\Filament\Resources\Blog\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Blog\Filament\Resources\Blog\PostResource;
use Modules\Blog\Models\Post;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_post')
                ->label(__('View Post'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(function (): string {
                    /** @var Post $post */
                    $post = $this->getRecord()->load('category');

                    return $post->category
                        ? route('blog.show.category', [$post->category->slug, $post->slug])
                        : route('blog.show', $post->slug);
                })
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }
}
