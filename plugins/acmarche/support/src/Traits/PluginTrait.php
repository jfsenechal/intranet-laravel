<?php

namespace AcMarche\Support\Traits;

trait PluginTrait
{
    public function getPluginBasePath($path = null): string
    {
        $reflector = new \ReflectionClass(get_class($this));

        return dirname($reflector->getFileName()).($path ?? '');
    }
}
