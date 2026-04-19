---
name: saucebase-filament-development
description: "Guides Filament resource development inside Saucebase modules. Activate when creating Filament resources, tables, forms, infolists, or pages inside a module, adding actions/filters/bulk actions, registering navigation groups, or testing Filament resources."
license: MIT
metadata:
  author: saucebase
---

# Saucebase Filament Development

## When to Use

Activate this skill when:

- Creating Filament resources (tables, forms, infolists, pages) inside any module
- Adding actions, filters, or bulk actions to Filament tables
- Registering navigation groups or panel configuration in a module
- Testing Filament resources

---

## Resource Directory Structure

Resources in modules are split into separate files for Form, Table, Infolist, and Pages. Each resource lives in its own subdirectory:

```
modules/<Name>/app/Filament/
  <Name>Plugin.php
  Resources/
    <Model>Resource/
      <Model>Resource.php
      Schemas/
        <Model>Form.php         # Form schema (create/edit)
        <Model>Infolist.php     # Infolist schema (view — optional)
      Tables/
        <Model>Table.php        # Table schema
      Pages/
        List<Models>.php
        Create<Model>.php
        Edit<Model>.php
        View<Model>.php         # optional
```

---

## Plugin Pattern

The plugin is **auto-discovered** by convention at `Modules\{Name}\Filament\{Name}Plugin` — no manual registration needed.

```php
namespace Modules\Feature\Filament;

use App\Filament\ModulePlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FeaturePlugin implements Plugin
{
    use ModulePlugin;

    public function getModuleName(): string { return 'Feature'; }

    public function getId(): string { return 'feature'; }

    public function boot(Panel $panel): void
    {
        // Optional: register navigation groups, custom pages, etc.
    }
}
```

The `ModulePlugin` trait auto-discovers and registers all Resources, Pages, Widgets, and Livewire components inside `app/Filament/`.

---

## Resource Pattern

```php
namespace Modules\Feature\Filament\Resources\FeatureResource;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Feature\Filament\Resources\FeatureResource\Pages;
use Modules\Feature\Filament\Resources\FeatureResource\Schemas\FeatureForm;
use Modules\Feature\Filament\Resources\FeatureResource\Tables\FeatureTable;
use Modules\Feature\Models\Feature;

class FeatureResource extends Resource
{
    protected static ?string $model = Feature::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return FeatureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeatureTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFeatures::route('/'),
            'create' => Pages\CreateFeature::route('/create'),
            'edit'   => Pages\EditFeature::route('/{record}/edit'),
        ];
    }
}
```

---

## Form Schema Pattern

```php
namespace Modules\Feature\Filament\Resources\FeatureResource\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeatureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        RichEditor::make('description')
                            ->nullable()
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->default(true),
                        DateTimePicker::make('starts_at')
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }
}
```

Use `->components([...])` (not `->schema([...])`). Layout components (`Grid`, `Section`) come from `Filament\Schemas\Components\`.

---

## Table Schema Pattern

```php
namespace Modules\Feature\Filament\Resources\FeatureResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FeatureTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

**Filament v5 API:** use `->recordActions()` for row actions and `->toolbarActions()` for bulk/header actions. Never use the old `->actions()` / `->bulkActions()`.

---

## Infolist Schema Pattern

Used for read-only view pages. Infolist entries come from `Filament\Infolists\Components\`.

```php
namespace Modules\Feature\Filament\Resources\FeatureResource\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeatureInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make()
                    ->inlineLabel()
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('created_at')->dateTime(),
                        IconEntry::make('is_active')->boolean()
                            ->trueColor('success')->falseColor('danger'),
                    ])
                    ->columnSpan(1),
            ]);
    }
}
```

---

## Pages Pattern

**List page:**
```php
namespace Modules\Feature\Filament\Resources\FeatureResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Feature\Filament\Resources\FeatureResource\FeatureResource;

class ListFeatures extends ListRecords
{
    protected static string $resource = FeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
```

**Create page (with creator tracking):**
```php
class CreateFeature extends CreateRecord
{
    protected static string $resource = FeatureResource::class;

    /** @param array<string, mixed> $data @return array<string, mixed> */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }
}
```

**Edit page:**
```php
class EditFeature extends EditRecord
{
    protected static string $resource = FeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->requiresConfirmation(),
        ];
    }
}
```

---

## Correct Namespaces

| Component type | Namespace |
|---|---|
| Form fields (`TextInput`, `Select`, `Toggle`, etc.) | `Filament\Forms\Components\` |
| Infolist entries (`TextEntry`, `IconEntry`, etc.) | `Filament\Infolists\Components\` |
| Layout (`Grid`, `Section`, `Tabs`, `Wizard`, `Text`) | `Filament\Schemas\Components\` |
| Schema utilities (`Get`, `Set`) | `Filament\Schemas\Components\Utilities\` |
| Actions (all — `EditAction`, `DeleteAction`, `BulkActionGroup`, etc.) | `Filament\Actions\` |
| Table columns | `Filament\Tables\Columns\` |
| Table filters | `Filament\Tables\Filters\` |
| Icons | `Filament\Support\Icons\Heroicon` enum |

**Never** use `Filament\Tables\Actions\`, `Filament\Forms\Actions\`, or other sub-namespaces for actions.

---

## Testing

Always authenticate before testing panel functionality. Tests must be PHPUnit classes:

```php
namespace Modules\Feature\Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Feature\Filament\Resources\FeatureResource\Pages\CreateFeature;
use Modules\Feature\Filament\Resources\FeatureResource\Pages\ListFeatures;
use Modules\Feature\Models\Feature;
use Tests\TestCase;

class FeatureResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create()->assignRole('admin'));
    }

    public function test_can_list_features(): void
    {
        $features = Feature::factory()->count(3)->create();

        livewire(ListFeatures::class)
            ->assertCanSeeTableRecords($features);
    }

    public function test_can_create_feature(): void
    {
        livewire(CreateFeature::class)
            ->fillForm(['name' => 'Test Feature'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('features', ['name' => 'Test Feature']);
    }

    public function test_validates_required_fields(): void
    {
        livewire(CreateFeature::class)
            ->fillForm(['name' => null])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }
}
```

Run with:
```bash
php -d memory_limit=2048M artisan test --testsuite=Modules --filter='^Modules\Feature\Tests'
```

---

## Common Mistakes

- **Wrong namespace**: module Filament classes live at `Modules\Feature\Filament\` — never `Modules\Feature\app\Filament\`.
- **Old table API**: use `->recordActions()` / `->toolbarActions()`, never `->actions()` / `->bulkActions()`.
- **Action namespaces**: always import from `Filament\Actions\`, never from sub-namespaces.
- **Layout components**: `Grid`, `Section`, `Tabs` come from `Filament\Schemas\Components\`, not `Filament\Forms\Components\`.
- **Plugin not discovered**: class must be exactly at `Modules\{Name}\Filament\{Name}Plugin` — casing must match the module folder name.
- **File visibility**: file uploads are `private` by default — use `->visibility('public')` when public access is needed.
- **Column spans**: `Grid` and `Section` do not span all columns by default — set `->columnSpanFull()` or `->columnSpan(n)` explicitly.
