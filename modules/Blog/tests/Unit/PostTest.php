<?php

namespace Modules\Blog\Tests\Unit;

use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Models\Post;
use Tests\TestCase;

class PostTest extends TestCase
{
    public function test_sluggable_is_configured_to_source_from_title(): void
    {
        $post = new Post;
        $config = $post->sluggable();

        $this->assertArrayHasKey('slug', $config);
        $this->assertSame('title', $config['slug']['source']);
    }

    public function test_cover_media_collection_is_registered_as_single_file(): void
    {
        $post = new Post;
        $post->registerMediaCollections();

        $collection = $post->getMediaCollection('cover');

        $this->assertNotNull($collection);
        $this->assertTrue($collection->singleFile);
    }

    public function test_published_scope_excludes_drafts(): void
    {
        $sql = Post::published()->toSql();

        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('published_at', $sql);
    }

    public function test_post_status_enum_has_draft_and_published(): void
    {
        $this->assertSame('draft', PostStatus::Draft->value);
        $this->assertSame('published', PostStatus::Published->value);
    }
}
