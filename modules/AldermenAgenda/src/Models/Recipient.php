<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[Connection('maria-aldermen-agenda')]
#[Fillable(['slug', 'last_name', 'first_name', 'email', 'ics'])]
final class Recipient extends Model
{
    use HasFactory;
    use HasSlug;

    protected $table = 'recipients';

    public function __toString(): string
    {
        return $this->last_name.' '.$this->first_name;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['last_name', 'first_name'])
            ->saveSlugsTo('slug');
    }

    protected function casts(): array
    {
        return [
            'ics' => 'boolean',
        ];
    }
}
