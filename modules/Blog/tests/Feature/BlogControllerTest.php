<?php

namespace Modules\Blog\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Blog\Models\Category;
use Modules\Blog\Models\Post;
use Tests\TestCase;

class BlogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_only_published_posts(): void
    {
        $published = Post::factory()->published()->create();
        Post::factory()->draft()->create();

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('posts.data.0.id', $published->id)
                ->where('posts.data', fn ($posts) => count($posts) === 1)
            );
    }

    public function test_index_returns_paginated_response(): void
    {
        Post::factory(3)->published()->create();

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('posts.data')
                ->has('posts.current_page')
                ->has('posts.last_page')
            );
    }

    public function test_show_resolves_post_by_slug_without_category(): void
    {
        $post = Post::factory()->published()->create(['category_id' => null]);

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('post.id', $post->id)
            );
    }

    public function test_show_resolves_post_by_category_and_slug(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->published()->create(['category_id' => $category->id]);

        $this->get(route('blog.show.category', [$category->slug, $post->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('post.id', $post->id)
            );
    }

    public function test_show_returns_404_for_draft_post(): void
    {
        $post = Post::factory()->draft()->create(['category_id' => null]);

        $this->get(route('blog.show', $post->slug))
            ->assertNotFound();
    }

    public function test_show_returns_404_for_unknown_slug(): void
    {
        $this->get(route('blog.show', 'nonexistent-slug'))
            ->assertNotFound();
    }

    public function test_show_returns_404_when_category_slug_mismatches(): void
    {
        $correctCategory = Category::factory()->create();
        $wrongCategory = Category::factory()->create();
        $post = Post::factory()->published()->create(['category_id' => $correctCategory->id]);

        $this->get(route('blog.show.category', [$wrongCategory->slug, $post->slug]))
            ->assertNotFound();
    }

    public function test_post_resource_includes_expected_fields(): void
    {
        $author = User::factory()->create();
        $category = Category::factory()->create();
        $post = Post::factory()->published()->create([
            'category_id' => $category->id,
            'author_id' => $author->id,
        ]);

        $this->get(route('blog.show.category', [$category->slug, $post->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('post.title')
                ->has('post.content')
                ->has('post.cover_url')
                ->has('post.category.name')
                ->has('post.author.name')
            );
    }
}
