<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Categories\Schemas;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\CpasLibrary\Models\Category;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                Select::make('parent_id')
                    ->label('Catégorie parente')
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query, ?Category $record) => $query
                            ->whereNull('parent_id')
                            ->when($record, fn (Builder $query) => $query->where('id', '!=', $record->id)),
                    )
                    ->searchable()
                    ->preload(),
                Textarea::make('description')
                    ->label('Description')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('icon')
                    ->label('Icône')
                    ->native(false)
                    ->helperText('Recherchez par mot clef en anglais')
                    ->searchable()
                    ->allowHtml()
                    ->placeholder('Rechercher une icône…')
                    ->getSearchResultsUsing(fn (?string $search): array => self::heroiconOptions($search))
                    ->getOptionLabelUsing(fn (?string $value): ?string => $value === null
                        ? null
                        : self::heroiconLabel($value)),
                ColorPicker::make('color')
                    ->label('Couleur')
                    ->hex(),
                // Forced to CPAS for now; will become a user-selectable field later.
                Hidden::make('departments')
                    ->default([DepartmentEnum::CPAS->value])
                    ->dehydrateStateUsing(fn (): array => [DepartmentEnum::CPAS->value]),
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
