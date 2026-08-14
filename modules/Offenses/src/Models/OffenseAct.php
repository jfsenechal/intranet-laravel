<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Connection('maria-offenses')]
#[Fillable(['name', 'user_add'])]
final class OffenseAct extends Model
{
    use HasUserAdd;

    public function offenses(): HasMany
    {
        return $this->hasMany(Offense::class);
    }

    public function isInUse(): bool
    {
        return $this->offenses()->exists();
    }

    protected static function booted(): void
    {
        self::bootHasUser();

        /**
         * An act is only a label: the offenses referencing it must never be deleted or orphaned,
         * so a used act refuses to be deleted. This backs the UI guard for every other code path,
         * and matches the ON DELETE RESTRICT foreign key on `offenses.offense_act_id`.
         */
        self::deleting(fn (self $act): bool => ! $act->isInUse());
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
