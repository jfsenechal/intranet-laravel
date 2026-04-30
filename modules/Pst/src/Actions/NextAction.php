<?php

declare(strict_types=1);

namespace AcMarche\Pst\Actions;

use Filament\Actions\Action;

final class NextAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->hiddenLabel()
            ->icon('heroicon-o-arrow-right')
            ->outlined()
            ->tooltip("Next {$this->getRecordTitle()}");
    }

    public static function getDefaultName(): string
    {
        return 'next';
    }
}
