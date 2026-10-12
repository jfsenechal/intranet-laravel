<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Links a mailing-list model to its user through the `username` column.
 *
 * Users live in the default database: User has no connection of its own, so a
 * plain belongsTo() would inherit the mailing-list connection and query a
 * `users` table that does not exist there.
 */
trait BelongsToUserTrait
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        $user = (new User)->setConnection(config('database.default'));

        return $this->newBelongsTo($user->newQuery(), $this, 'username', 'username', 'user');
    }
}
