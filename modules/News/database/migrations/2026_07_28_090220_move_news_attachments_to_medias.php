<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Legacy columns holding one bare file name each, in display order.
     *
     * @var array<int, string>
     */
    private const LEGACY_COLUMNS = ['attach1Name', 'attach2Name', 'attach3Name'];

    /**
     * Directory the legacy files live in on the public disk. Hardcoded rather
     * than read from news.uploads.medias so this migration keeps producing the
     * same paths if that config value ever changes.
     */
    private const DIRECTORY = 'news';

    protected $connection = 'maria-news';

    /**
     * Move the legacy attachment file names into the medias JSON column, then
     * drop the columns. Rows that already carry medias are left untouched.
     */
    public function up(): void
    {
        $columns = $this->existingLegacyColumns();

        if ($columns === []) {
            return;
        }

        DB::connection($this->connection)
            ->table('news')
            ->select(array_merge(['id', 'medias'], $columns))
            ->chunkById(500, function (iterable $rows) use ($columns): void {
                foreach ($rows as $row) {
                    if (! in_array($row->medias, [null, '', '[]'], true)) {
                        continue;
                    }

                    $paths = $this->pathsFrom($row, $columns);

                    if ($paths === []) {
                        continue;
                    }

                    DB::connection($this->connection)
                        ->table('news')
                        ->where('id', $row->id)
                        ->update(['medias' => json_encode($paths, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
                }
            });

        Schema::connection($this->connection)->table('news', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }

    /**
     * Restore the columns and put the first three medias back into them.
     *
     * Best effort only: medias written after this migration ran are
     * indistinguishable from the backfilled ones, so a row holding more than
     * three files loses the extras and every restored row has its medias
     * cleared.
     */
    public function down(): void
    {
        if ($this->existingLegacyColumns() !== []) {
            return;
        }

        Schema::connection($this->connection)->table('news', function (Blueprint $table): void {
            foreach (self::LEGACY_COLUMNS as $column) {
                $table->string($column)->nullable();
            }
        });

        DB::connection($this->connection)
            ->table('news')
            ->select(['id', 'medias'])
            ->whereNotNull('medias')
            ->chunkById(500, function (iterable $rows): void {
                foreach ($rows as $row) {
                    $medias = json_decode((string) $row->medias, true);

                    if (! is_array($medias) || $medias === []) {
                        continue;
                    }

                    $names = array_slice(array_map(
                        static fn (string $path): string => basename($path),
                        array_values(array_filter($medias, 'is_string')),
                    ), 0, count(self::LEGACY_COLUMNS));

                    if ($names === []) {
                        continue;
                    }

                    $values = ['medias' => null];

                    foreach (self::LEGACY_COLUMNS as $index => $column) {
                        $values[$column] = $names[$index] ?? null;
                    }

                    DB::connection($this->connection)
                        ->table('news')
                        ->where('id', $row->id)
                        ->update($values);
                }
            });
    }

    /**
     * @param  array<int, string>  $columns
     * @return array<int, string>
     */
    private function pathsFrom(object $row, array $columns): array
    {
        $paths = [];

        foreach ($columns as $column) {
            $name = mb_trim((string) ($row->{$column} ?? ''));

            if ($name === '') {
                continue;
            }

            $paths[] = self::DIRECTORY.'/'.$name;
        }

        return $paths;
    }

    /**
     * @return array<int, string>
     */
    private function existingLegacyColumns(): array
    {
        return array_values(array_filter(
            self::LEGACY_COLUMNS,
            fn (string $column): bool => Schema::connection($this->connection)->hasColumn('news', $column),
        ));
    }
};
