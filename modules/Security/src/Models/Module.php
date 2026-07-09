<?php

declare(strict_types=1);

namespace AcMarche\Security\Models;

use AcMarche\Security\Database\Factories\ModuleFactory;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

// https://github.com/lukas-frey/filament-icon-picker
#[UseFactory(ModuleFactory::class)]
#[Fillable([
    'name',
    'url',
    'description',
    'role_description',
    'extern',
    'is_public',
    'is_external',
    'icon',
    'color',
    'allow_multiple_roles',
])]
final class Module extends Model
{
    use HasFactory;

    /**
     * Modules never shown in the launcher (legacy indicators, unmigrated tools).
     *
     * @var list<int>
     */
    public const array MODULES_TO_SKIP = [1, 2, 23, 49];

    #[Override]
    public $timestamps = false;

    #[Override]
    protected $casts = [
        'is_public' => 'boolean',
        'is_external' => 'boolean',
        'allow_multiple_roles' => 'boolean',
    ];

    /**
     * Modules the given user may launch, sorted by name.
     *
     * Public modules are always included; non-public modules require the user to
     * hold at least one role belonging to that module. Administrators see every
     * non-skipped module.
     */
    public function scopeAccessibleTo(Builder $query, User $user): Builder
    {
        $query->whereNotIn('id', self::MODULES_TO_SKIP);

        if (! $user->isAdministrator()) {
            $query->where(function (Builder $query) use ($user): void {
                $query->where('is_public', true)
                    ->orWhereHas('roles.users', function (Builder $subQuery) use ($user): void {
                        $subQuery->where('users.id', $user->id);
                    });
            });
        }

        return $query->orderBy('name');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * The users that belong to the role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return BelongsTo<Tab>
     */
    public function tab(): BelongsTo
    {
        return $this->belongsTo(Tab::class, 'tab_id');
    }

    /**
     * To resolve name
     * static::resolveFactoryName($modelName);
     */
    protected static function newFactory()
    {
        return ModuleFactory::new();
    }

    /**
     * A module is reachable once it holds a URL: external modules always do,
     * internal modules once their Filament URL has been persisted.
     */
    protected function migrated(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->is_external || $this->url !== '',
        );
    }
}
