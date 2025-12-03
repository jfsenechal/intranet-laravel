<?php

namespace AcMarche\App\Traits;

use Filament\Support\Icons\Heroicon;
use ReflectionClass;

trait PluginTrait
{
    public function getPluginBasePath($path = null): string
    {
        $reflector = new ReflectionClass(get_class($this));
Heroicon::QueueList;
        return dirname($reflector->getFileName()).($path ?? '');
    }
}
