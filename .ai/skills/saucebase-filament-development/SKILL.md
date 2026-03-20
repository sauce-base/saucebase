# Saucebase Filament Development

## When to Use

Activate this skill when:

- Creating Filament resources (tables, forms, pages) inside any module
- Adding actions, filters, or bulk actions to Filament tables
- Registering navigation groups or panel configuration in a module
- Testing Filament resources

---

## Resource Structure

Filament resources in modules use separate files for Form, Table, and Pages:

```
app/Filament/
  <Name>Plugin.php
  Resources/
    FeatureResource.php
    FeatureResource/
      Schemas/
        FeatureForm.php        # Form schema
      Tables/
        FeatureTable.php       # Table schema
      Pages/
        ListFeatures.php
        CreateFeature.php
        EditFeature.php
```

---

## Plugin Registration

The plugin is **auto-discovered** by convention: `Modules\{Name}\Filament\{Name}Plugin`. No manual registration needed.

```php
<?php

namespace Modules\Feature\app\Filament;

use App\Filament\ModulePlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FeaturePlugin implements Plugin
{
    use ModulePlugin;

    public function getModuleName(): string
    {
        return 'Feature';
    }

    public function getId(): string
    {
        return 'feature';
    }

    public function boot(Panel $panel): void
    {
        $panel->navigationGroups([
            'Feature' => \Filament\Navigation\NavigationGroup::make('Feature')
                ->label('Feature'),
        ]);
    }
}
```

---

## Resource Pattern

### `FeatureResource.php`

```php
<?php

namespace Modules\Feature\app\Filament\Resources;

use Filament\Resources\Resource;
use Modules\Feature\app\Models\Feature;
use Modules\Feature\app\Filament\Resources\FeatureResource\Pages;
use Modules\Feature\app\Filament\Resources\FeatureResource\Schemas\FeatureForm;
use Modules\Feature\app\Filament\Resources\FeatureResource\Tables\FeatureTable;

class FeatureResource extends Resource
{
    protected static ?string $model = Feature::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Feature';

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return FeatureForm::configure($schema);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
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

### `Schemas/FeatureForm.php`

```php
<?php

namespace Modules\Feature\app\Filament\Resources\FeatureResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FeatureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),
        ]);
    }
}
```

### `Tables/FeatureTable.php`

```php
<?php

namespace Modules\Feature\app\Filament\Resources\FeatureResource\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeatureTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
```

---

## Creator Tracking

To automatically record who created a record, override `mutateFormDataBeforeCreate()` on the Create page:

```php
<?php

namespace Modules\Feature\app\Filament\Resources\FeatureResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Feature\app\Filament\Resources\FeatureResource;

class CreateFeature extends CreateRecord
{
    protected static string $resource = FeatureResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
```

---

## Correct Namespaces

| Component type                                      | Namespace                                |
| --------------------------------------------------- | ---------------------------------------- |
| Form fields (`TextInput`, `Select`, etc.)           | `Filament\Forms\Components\`             |
| Infolist entries (`TextEntry`, `IconEntry`, etc.)   | `Filament\Infolists\Components\`         |
| Layout components (`Grid`, `Section`, `Tabs`, etc.) | `Filament\Schemas\Components\`           |
| Schema utilities (`Get`, `Set`)                     | `Filament\Schemas\Components\Utilities\` |
| Actions (`DeleteAction`, `EditAction`, etc.)        | `Filament\Actions\`                      |
| Icons                                               | `Filament\Support\Icons\Heroicon` enum   |

**Never** use `Filament\Tables\Actions\`, `Filament\Forms\Actions\`, or other sub-namespaces for actions.

---

## Testing

Always authenticate before testing Filament panel functionality. Use `livewire()` helper:

```php
<?php

namespace Modules\Feature\Tests\Feature\Filament;

use App\Models\User;
use Modules\Feature\app\Filament\Resources\FeatureResource\Pages\ListFeatures;
use Modules\Feature\app\Filament\Resources\FeatureResource\Pages\CreateFeature;
use Modules\Feature\app\Models\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Livewire\livewire;

class FeatureResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
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

---

## Common Mistakes

- **File visibility**: file uploads are `private` by default — use `->visibility('public')` when public access is needed.
- **Column spans**: `Grid`, `Section`, and `Fieldset` do not span all columns by default — set spans explicitly.
- **Action namespaces**: always import actions from `Filament\Actions\`, never from sub-namespaces.
- **Plugin not discovered**: confirm the class is at `Modules\{Name}\Filament\{Name}Plugin` — casing must match exactly.
