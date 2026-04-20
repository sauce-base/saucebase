<?php

namespace Modules\Blog\Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Filament\Resources\Blog\Pages\CreatePost;
use Modules\Blog\Filament\Resources\Blog\Pages\EditPost;
use Modules\Blog\Filament\Resources\Blog\Pages\ListPosts;
use Modules\Blog\Models\Post;
use Tests\TestCase;

class PostResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['email_verified_at' => now()]);
        $this->admin->assignRole(Role::ADMIN);
    }

    public function test_admin_can_list_posts(): void
    {
        $posts = Post::factory(3)->create();

        $this->actingAs($this->admin);

        Livewire::test(ListPosts::class)
            ->assertCanSeeTableRecords($posts);
    }

    public function test_admin_can_create_post_with_author_defaulting_to_self(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Test Post',
                'content' => '<p>Test content</p>',
                'status' => PostStatus::Published->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('blog_posts', [
            'title' => 'Test Post',
            'author_id' => $this->admin->id,
        ]);
    }

    public function test_admin_can_edit_post(): void
    {
        $post = Post::factory()->create(['author_id' => $this->admin->id]);

        $this->actingAs($this->admin);

        Livewire::test(EditPost::class, ['record' => $post->id])
            ->fillForm(['title' => 'Updated Title'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('blog_posts', ['id' => $post->id, 'title' => 'Updated Title']);
    }

    public function test_admin_can_delete_post(): void
    {
        $post = Post::factory()->create();

        $this->actingAs($this->admin);

        Livewire::test(EditPost::class, ['record' => $post->id])
            ->callAction('delete');

        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
    }

    public function test_status_filter_works(): void
    {
        $published = Post::factory()->published()->create();
        $draft = Post::factory()->draft()->create();

        $this->actingAs($this->admin);

        Livewire::test(ListPosts::class)
            ->filterTable('status', PostStatus::Draft->value)
            ->assertCanSeeTableRecords([$draft])
            ->assertCanNotSeeTableRecords([$published]);
    }
}
