<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    /**
     * Legacy attachment paths were backfilled with a hard-coded `indicateur/`
     * prefix, but the files live under the configured storage directory
     * (`courrier/` by default). Rewrite the leading prefix so `path` matches the
     * real on-disk location. Only the leading segment is replaced.
     */
    public function up(): void
    {
        $this->rewritePrefix('indicateur/', config('courrier.storage.directory').'/');
    }

    public function down(): void
    {
        $directory = config('courrier.storage.directory');

        // Revert legacy-style paths only, leaving new `<dir>/attachments/*`
        // uploads untouched.
        $this->rewritePrefix($directory.'/', 'indicateur/', $directory.'/attachments/');
    }

    /**
     * Replace the leading `$from` segment of `attachments.path` with `$to`,
     * optionally skipping rows whose path starts with `$exclude`.
     */
    private function rewritePrefix(string $from, string $to, ?string $exclude = null): void
    {
        $connection = DB::connection('maria-courrier');
        $fromLength = mb_strlen($from);

        if (in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            $sql = 'UPDATE attachments SET path = CONCAT(?, SUBSTRING(path, ?)) WHERE path LIKE ?';
            $bindings = [$to, $fromLength + 1, $from.'%'];

            if ($exclude !== null) {
                $sql .= ' AND path NOT LIKE ?';
                $bindings[] = $exclude.'%';
            }

            $connection->statement($sql, $bindings);

            return;
        }

        $query = $connection->table('attachments')->where('path', 'like', $from.'%');

        if ($exclude !== null) {
            $query->where('path', 'not like', $exclude.'%');
        }

        $query->orderBy('id')->chunkById(2000, function ($rows) use ($connection, $to, $fromLength): void {
            foreach ($rows as $row) {
                $connection->table('attachments')
                    ->where('id', $row->id)
                    ->update(['path' => $to.mb_substr($row->path, $fromLength)]);
            }
        });
    }
};
