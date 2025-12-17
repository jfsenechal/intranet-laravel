<?php

namespace AcMarche\Security\Models;

use AcMarche\Security\Database\Factories\RoleFactory;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[UseFactory(RoleFactory::class)]
final class Role extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['name', 'description', 'module_id'];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * The users that belong to the role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    /**
     * To resolve name
     * static::resolveFactoryName($modelName);
     */
    protected static function newFactory()
    {
        return RoleFactory::new();
    }
}
