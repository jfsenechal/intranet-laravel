<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * The "Partager avec" select used to be keyed by user id, so shares were saved
     * with the id in the username column. Replace each id by the matching username.
     * A share that would duplicate an existing correct one is dropped instead, and
     * ids matching no user (or values that are a real username) are left alone.
     *
     * No $connection on purpose: the migrator would make maria-mailing-list the
     * default connection while running, and `users` lives on the default one.
     */
    public function up(): void
    {
        if (! Schema::connection('maria-mailing-list')->hasTable('address_book_shares')) {
            return;
        }

        $shares = DB::connection('maria-mailing-list')
            ->table('address_book_shares')
            ->get(['id', 'address_book_id', 'username'])
            ->filter(fn (object $share): bool => ctype_digit((string) $share->username));

        if ($shares->isEmpty()) {
            return;
        }

        $values = $shares->pluck('username')->unique()->all();

        $existingUsernames = DB::table('users')
            ->whereIn('username', $values)
            ->pluck('username')
            ->all();

        $usernamesById = DB::table('users')
            ->whereIn('id', $values)
            ->pluck('username', 'id');

        foreach ($shares as $share) {
            if (in_array($share->username, $existingUsernames, true)) {
                continue;
            }

            $username = $usernamesById[(int) $share->username] ?? null;

            if ($username === null) {
                continue;
            }

            $alreadyShared = DB::connection('maria-mailing-list')
                ->table('address_book_shares')
                ->where('address_book_id', $share->address_book_id)
                ->where('username', $username)
                ->exists();

            $query = DB::connection('maria-mailing-list')
                ->table('address_book_shares')
                ->where('id', $share->id);

            if ($alreadyShared) {
                $query->delete();

                continue;
            }

            $query->update(['username' => $username]);
        }
    }

    public function down(): void
    {
        // The wrong ids are not worth restoring: they never matched any user.
    }
};
