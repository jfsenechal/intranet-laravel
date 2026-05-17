<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Categories\Schemas;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\CpasLibrary\Models\Categorie;
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

final class CategorieForm
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
                        modifyQueryUsing: fn (Builder $query, ?Categorie $record) => $record
                            ? $query->where('id', '!=', $record->id)
                            : $query,
                    )
                    ->searchable()
                    ->preload(),
                TextInput::make('description')
                    ->label('Description')
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('icon')
                    ->label('Icône')
                    ->maxLength(255)
                    ->placeholder('fa-solid fa-folder')
                    ->helperText('Classe FontAwesome'),
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
}
