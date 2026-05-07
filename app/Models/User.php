<?php

declare(strict_types=1);

namespace App\Models;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Courrier\Models\UserCourrierTrait;
use AcMarche\MailingList\Models\UserMailingListTrait;
use AcMarche\Pst\Models\UserPstTrait;
use AcMarche\Security\Enums\RolesEnum;
use AcMarche\Security\Ldap\UserLdap;
use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;

#[UseFactory(UserFactory::class)]
#[Fillable([
    'name',
    'first_name',
    'last_name',
    'phone',
    'extension',
    'mobile',
    'username',
    'uuid',
    'departments',
    'mandatory',
    'color_primary',
    'color_secondary',
    'email',
    'password',
    'is_administrator',
    'news_attachment',
])]
#[Hidden([
    'password',
    'remember_token',
    'app_authentication_secret',
    'app_authentication_recovery_codes',
])]
final class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, HasName
{
    use HasApiTokens, HasFactory, Notifiable, Searchable;
    use UserCourrierTrait, UserMailingListTrait,UserPstTrait;

    public static function generateDataFromLdap(UserLdap $userLdap): array
    {
        $email = $userLdap->getFirstAttribute('mail');

        $department = match (true) {
            str_contains((string) $email, 'cpas.marche') => DepartmentEnum::CPAS->value,
            str_contains((string) $email, 'ac.marche') => DepartmentEnum::VILLE->value,
            default => DepartmentEnum::VILLE->value,
        };

        return [
            'first_name' => $userLdap->getFirstAttribute('givenname'),
            'last_name' => $userLdap->getFirstAttribute('sn'),
            'email' => $email,
            'departments' => [$department],
            'mobile' => $userLdap->getFirstAttribute('mobile'),
            'phone' => $userLdap->getFirstAttribute('telephoneNumber'),
            'extension' => $userLdap->getFirstAttribute('ipPhone'),
            'uuid' => Str::Uuid()->toString(),
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'users_index';
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
        ];
    }

    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * The modules that belong to the user.
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class);
    }

    public function rolesByModule(int $moduleId): array|Collection
    {
        return $this->roles()
            ->where('module_id', $moduleId)
            ->get();
    }

    public function hasRole(string $roleToFind): bool
    {
        foreach ($this->roles()->get() as $role) {
            if ($role->name === $roleToFind) {
                return true;
            }
        }

        return false;
    }

    public function hasOneOfThisRoles(array $rolesToFind): bool
    {
        foreach ($this->roles()->get() as $role) {
            if (in_array($role->name, $rolesToFind)) {
                return true;
            }
        }

        return false;
    }

    public function addRole(Role $role): void
    {
        if (! $this->hasRole($role->name)) {
            $this->roles()->attach($role);
        }
    }

    public function hasModule(string $moduleToFind): bool
    {
        foreach ($this->modules()->get() as $module) {
            if ($module->name === $moduleToFind) {
                return true;
            }
        }

        return false;
    }

    public function addModule(Module $module): void
    {
        if (! $this->hasModule($module->name)) {
            $this->modules()->attach($module);
        }
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function isAdministrator(): bool
    {
        return $this->is_administrator;
    }

    /**  public function courrierDepartment(): ?DepartmentCourrierEnum
     * {
     * return match (true) {
     * $this->hasOneOfThisRoles([
     * RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value,
     * RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value,
     * RolesEnum::ROLE_INDICATEUR_VILLE_READ->value,
     * ]) => DepartmentCourrierEnum::VILLE,
     * $this->hasOneOfThisRoles([
     * RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value,
     * RolesEnum::ROLE_INDICATEUR_CPAS->value,
     * RolesEnum::ROLE_INDICATEUR_CPAS_INDEX->value,
     * RolesEnum::ROLE_INDICATEUR_CPAS_READ->value,
     * ]) => DepartmentCourrierEnum::CPAS,
     * $this->hasOneOfThisRoles([
     * RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_ADMIN->value,
     * RolesEnum::ROLE_INDICATEUR_BOURGMESTRE->value,
     * RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_INDEX->value,
     * RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_READ->value,
     * ]) => DepartmentCourrierEnum::BGM,
     * default => null,
     * };
     * }*/
    public function getAppAuthenticationSecret(): ?string
    {
        return $this->app_authentication_secret;
    }

    public function saveAppAuthenticationSecret(?string $secret): void
    {
        $this->app_authentication_secret = $secret;
        $this->save();
    }

    public function getAppAuthenticationHolderName(): string
    {
        return $this->email;
    }

    /** @phpstan-ignore-next-line */
    public function getAppAuthenticationRecoveryCodes(): ?array
    {
        /** @phpstan-ignore-next-line */
        return $this->app_authentication_recovery_codes;
    }

    public function saveAppAuthenticationRecoveryCodes(?array $codes): void
    {
        /** @phpstan-ignore-next-line */
        $this->app_authentication_recovery_codes = $codes;
        $this->save();
    }

    public function getFilamentName(): string
    {
        return $this->full_name;
    }

    public function fullNameAsString(): string
    {
        return $this->last_name.' '.$this->first_name;
    }

    public function canImpersonate()
    {
        if ($this->isAdministrator()) {
            return true;
        }

        return $this->hasOneOfThisRoles([RolesEnum::INTRANET_ADMIN->value]);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::saving(function ($model): void {
            // Unset the field so it doesn't save to the database
            if (isset($model->attributes['plainPassword'])) {
                $model->plainPassword = $model->attributes['plainPassword'];
                unset($model->attributes['plainPassword']);
            }
        });
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => $this->last_name.' '.$this->first_name);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'app_authentication_secret' => 'encrypted',
            'app_authentication_recovery_codes' => 'encrypted:array',
            'departments' => 'array',
            'is_administrator' => 'boolean',
            'news_attachment' => 'boolean',
        ];
    }
}
