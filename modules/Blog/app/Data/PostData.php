<?php

namespace Modules\Blog\Data;

use Modules\Blog\Models\Post;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class PostData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public ?string $excerpt,
        public string $cover_url,
        public ?string $published_at,
        public ?CategoryData $category,
        public ?AuthorData $author,
        public string $url,
        public ?string $content = null,
    ) {}

    public static function fromPost(Post $post): static
    {
        return new static(
            id: $post->id,
            title: $post->title,
            slug: $post->slug,
            excerpt: $post->excerpt,
            cover_url: $post->getFirstMediaUrl('cover'),
            published_at: $post->published_at?->toDateString(),
            category: $post->category ? CategoryData::from($post->category) : null,
            author: $post->author ? AuthorData::from($post->author) : null,
            url: $post->category
                ? route('blog.show.category', [$post->category->slug, $post->slug])
                : route('blog.show', $post->slug),
        );
    }

    public function withContent(string $content): static
    {
        return new static(
            id: $this->id,
            title: $this->title,
            slug: $this->slug,
            excerpt: $this->excerpt,
            cover_url: $this->cover_url,
            published_at: $this->published_at,
            category: $this->category,
            author: $this->author,
            url: $this->url,
            content: $content,
        );
    }
}
