<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Components;

use Filament\Infolists\Components\Entry;
use Override;

final class ProgressEntry extends Entry
{
    #[Override]
    protected string $view = 'components.progress-entry';
}
