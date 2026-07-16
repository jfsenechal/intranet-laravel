<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Models;

use AcMarche\CpasLibrary\Database\Factories\FicheFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[UseFactory(FicheFactory::class)]
#[Connection('maria-cpas-library')]
#[Fillable([
    'category_id',
    'type',
    'source',
    'date_promulgation',
    'date_publication',
    'name',
    'description',
    'userAdd',
    'mimeType',
    'createdAt',
    'updatedAt',
    'fileName',
    'fileSize',
    'slug',
    'date_rappel',
    'type_document',
    'date_begin',
    'date_end',
])]
final class Fiche extends Model
{
    use HasFactory;

    public const CREATED_AT = 'createdAt';

    public const UPDATED_AT = 'updatedAt';

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'fiche_tag', 'fiche_id', 'tag_id');
    }

    protected static function booted(): void
    {
        self::creating(function (self $fiche): void {
            if (empty($fiche->userAdd) && auth()->check()) {
                $fiche->userAdd = (string) (auth()->user()->username ?? auth()->user()->email);
            }
        });

        self::saved(function (self $fiche): void {
            if (empty($fiche->slug) && ! empty($fiche->name)) {
                $fiche->slug = Str::slug($fiche->name).'-'.$fiche->id;
                $fiche->saveQuietly();
            }
        });

        self::updating(function (self $fiche): void {
            if (! $fiche->isDirty('fileName')) {
                return;
            }

            $fiche->deleteFile((string) $fiche->getOriginal('fileName'));
        });

        self::deleting(function (self $fiche): void {
            $fiche->deleteFile((string) $fiche->fileName);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_promulgation' => 'date',
            'date_publication' => 'date',
            'date_rappel' => 'date',
            'date_begin' => 'date',
            'date_end' => 'date',
            'createdAt' => 'datetime',
            'updatedAt' => 'datetime',
            'fileSize' => 'integer',
        ];
    }

    /**
     * Remove a file this fiche no longer references, so replaced and deleted
     * fiches do not leave their uploads behind on the disk.
     */
    private function deleteFile(string $fileName): void
    {
        if ($fileName === '') {
            return;
        }

        Storage::disk('cpas-library')->delete($fileName);
    }
}
