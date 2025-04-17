<?php

namespace AcMarche\App\Models;

//https://github.com/lukas-frey/filament-icon-picker
class Module
{
    protected $connection = 'mariadb';

    protected $fillable = [
        'name',
        'url',
        'description',
        'extern',
        'is_public',
        'is_external',
        'icon',
        'color',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_external' => 'boolean',
    ];

}
