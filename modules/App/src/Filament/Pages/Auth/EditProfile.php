<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

final class EditProfile extends BaseEditProfile
{
    public static function getLabel(): string
    {
        return 'Mon profil';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar_url')
                    ->label('Photo de profil')
                    ->avatar()
                    ->image()
                    ->imageEditor()
                    ->circleCropper()
                    ->disk('public')
                    ->directory('avatars')
                    ->visibility('public')
                    ->maxSize(2048),
            ]);
    }
}
