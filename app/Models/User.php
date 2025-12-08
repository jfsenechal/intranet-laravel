<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use AcMarche\Security\Constant\RoleEnum;
use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use Database\Factories\UserFactory;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use AcMarche\Security\Ldap\User as UserLdap;

#[UseFactory(UserFactory::class)]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'phone',
        'extension',
        'mobile',
        'username',
        'uuid',
        'mandatory',
        'color_primary',
        'color_secondary',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($model) {
            // Unset the field so it doesn't save to the database
            if (isset($model->attributes['plainPassword'])) {
                $model->plainPassword = $model->attributes['plainPassword'];
                unset($model->attributes['plainPassword']);
            }
        });
    }

    public function fullName(): string
    {
        return $this->last_name.' '.$this->first_name;
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

    public function addRole(Role $role): void
    {
        if (!$this->hasRole($role->name)) {
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
        if (!$this->hasModule($module->name)) {
            $this->modules()->attach($module);
        }
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return ($this->hasRole(RoleEnum::INTRANET_ADMIN->value));
        }

        return false;
    }

    public static function generateDataFromLdap(UserLdap $userLdap): array
    {
        $email = $userLdap->getFirstAttribute('mail');

        /*   $department = match (true) {
               str_contains($email, 'cpas.marche') => DepartmentEnum::CPAS->value,
               str_contains($email, 'ac.marche') => DepartmentEnum::VILLE->value,
               default => DepartmentEnum::VILLE->value,
           };*/

        $fullName = $userLdap->getFirstAttribute('givenname').' '.$userLdap->getFirstAttribute('sn');

        return [
            'name' => $fullName,
            'first_name' => $userLdap->getFirstAttribute('givenname'),
            'last_name' => $userLdap->getFirstAttribute('sn'),
            'email' => $email,
            // 'departments' => [$department],
            'mobile' => $userLdap->getFirstAttribute('mobile'),
            'phone' => $userLdap->getFirstAttribute('telephoneNumber'),
            'extension' => $userLdap->getFirstAttribute('ipPhone'),
            'uuid' => Str::uuid(),
        ];
    }
}
