<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;

final class Settings extends Cluster
{
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-squares-2x2';
}
