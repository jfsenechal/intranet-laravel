<?php

declare(strict_types=1);

namespace AcMarche\WhoIsWho\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Link between a user and an HRM employee they marked as favorite in the
 * directory. The employee is referenced by id only: employees live on the
 * `maria-hrm` connection while this table sits on the default one, so the
 * relation is resolved by the repository rather than by Eloquent.
 *
 * @property int $id
 * @property int $user_id
 * @property int $employee_id
 */
#[Table(name: 'employee_user_favorites')]
#[Fillable([
    'user_id',
    'employee_id',
])]
final class FavoriteEmployee extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
