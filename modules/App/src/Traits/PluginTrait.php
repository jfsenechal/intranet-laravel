<?php

namespace AcMarche\App\Traits;

use ReflectionClass;

trait PluginTrait
{
    public function getPluginBasePath($path = null): string
    {
        $reflector = new ReflectionClass(get_class($this));

        return dirname($reflector->getFileName()).($path ?? '');
    }
}
