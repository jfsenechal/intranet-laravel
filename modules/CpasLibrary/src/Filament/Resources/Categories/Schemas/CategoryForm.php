<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Categories\Schemas;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\CpasLibrary\Models\Category;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
                        'slug',
                        Str::slug($state ?? ''),
                    )),
                TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('parent_id')
                    ->label('Catégorie parente')
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query, ?Category $record) => $record
                            ? $query->where('id', '!=', $record->id)
                            : $query,
                    )
                    ->searchable()
                    ->preload(),
                TextInput::make('description')
                    ->label('Description')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('icon')
                    ->label('Icône')
                    ->native(false)
                    ->searchable()
                    ->allowHtml()
                    ->placeholder('Rechercher une icône…')
                    ->helperText('Icône Heroicon (compatible Filament)')
                    ->getSearchResultsUsing(fn (?string $search): array => self::heroiconOptions($search))
                    ->getOptionLabelUsing(fn (?string $value): ?string => $value === null
                        ? null
                        : self::heroiconLabel($value)),
                ColorPicker::make('color')
                    ->label('Couleur')
                    ->hex(),
                CheckboxList::make('departments')
                    ->label('Départements')
                    ->options(DepartmentEnum::class)
                    ->columns(2)
                    ->required()
                    ->columnSpanFull(),
                TagsInput::make('users')
                    ->label('Utilisateurs autorisés')
                    ->helperText('Usernames autorisés (laisser vide pour tous)')
                    ->columnSpanFull(),
                Toggle::make('public')
                    ->label('Public')
                    ->default(false),
            ]);
    }

    /**
     * Search the installed Heroicon outline set, returning Filament-compatible
     * icon names mapped to an HTML label that previews the rendered SVG.
     *
     * @return array<string, string>
     */
    private static function heroiconOptions(?string $search): array
    {
        $files = glob(base_path('vendor/blade-ui-kit/blade-heroicons/resources/svg/o-*.svg')) ?: [];
        $needle = Str::lower(mb_trim((string) $search));

        $options = [];
        foreach ($files as $file) {
            $name = 'heroicon-'.basename($file, '.svg');
            if ($needle !== '' && ! str_contains($name, $needle)) {
                continue;
            }
            $options[$name] = self::heroiconLabel($name);
            if (count($options) >= 50) {
                break;
            }
        }

        return $options;
    }

    private static function heroiconLabel(string $name): string
    {
        return '<span class="flex items-center gap-2">'
            .svg($name, 'w-5 h-5')->toHtml()
            .'<span>'.e($name).'</span></span>';
    }
}
