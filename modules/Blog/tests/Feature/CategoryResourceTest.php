<?php

namespace Modules\Blog\Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Blog\Filament\Resources\Blog\Pages\CreateCategory;
use Modules\Blog\Filament\Resources\Blog\Pages\EditCategory;
use Modules\Blog\Filament\Resources\Blog\Pages\ListCategories;
use Modules\Blog\Models\Category;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['email_verified_at' => now()]);
        $this->admin->assignRole(Role::ADMIN);
    }

    public function test_admin_can_list_categories(): void
    {
        $categories = Category::factory(3)->create();

        $this->actingAs($this->admin);

        Livewire::test(ListCategories::class)
            ->assertCanSeeTableRecords($categories);
    }

    public function test_admin_can_create_category(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateCategory::class)
            ->fillForm(['name' => 'Test Category'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('blog_categories', ['name' => 'Test Category']);
    }

    public function test_admin_can_edit_category(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin);

        Livewire::test(EditCategory::class, ['record' => $category->id])
            ->fillForm(['name' => 'Renamed Category'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('blog_categories', ['id' => $category->id, 'name' => 'Renamed Category']);
    }

    public function test_admin_can_delete_category(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin);

        Livewire::test(EditCategory::class, ['record' => $category->id])
            ->callAction('delete');

        $this->assertDatabaseMissing('blog_categories', ['id' => $category->id]);
    }
}
