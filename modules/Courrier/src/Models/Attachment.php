<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

#[Connection('maria-courrier')]
#[Fillable([
    'incoming_mail_id',
    'file_name',
    'mime',
    'path',
])]
final class Attachment extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * Build the legacy on-disk path of an attachment, relative to the public
     * directory. Legacy files were stored under
     * `public/data/indicateur/<department>/<courrier id>/<file>`.
     */
    public static function legacyPath(string $department, int $legacyCourrierId, string $fileName): string
    {
        return 'data/indicateur/'.mb_strtolower($department).'/'.$legacyCourrierId.'/'.$fileName;
    }

    /**
     * Populate `path` for every attachment from its mail's department and legacy
     * courrier id. Migrated CPAS/BGM mail carries the legacy id in `old_id`;
     * VILLE mail was transformed in place, so its current `id` is the legacy id.
     */
    public static function backfillLegacyPaths(): void
    {
        $connection = DB::connection('maria-courrier');

        if (in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            $connection->statement(
                "UPDATE attachments a
                 JOIN incoming_mails m ON m.id = a.incoming_mail_id
                 SET a.path = CONCAT('data/indicateur/', LOWER(m.department), '/', COALESCE(m.old_id, m.id), '/', a.file_name)
                 WHERE m.department IS NOT NULL"
            );

            return;
        }

        $connection->table('attachments')
            ->join('incoming_mails', 'incoming_mails.id', '=', 'attachments.incoming_mail_id')
            ->whereNotNull('incoming_mails.department')
            ->orderBy('attachments.id')
            ->select([
                'attachments.id',
                'attachments.file_name',
                'incoming_mails.department',
                'incoming_mails.old_id',
                'incoming_mails.id as mail_id',
            ])
            ->chunk(2000, function ($rows) use ($connection): void {
                foreach ($rows as $row) {
                    $connection->table('attachments')
                        ->where('id', $row->id)
                        ->update([
                            'path' => self::legacyPath(
                                $row->department,
                                (int) ($row->old_id ?? $row->mail_id),
                                $row->file_name,
                            ),
                        ]);
                }
            });
    }

    public function incomingMail(): BelongsTo
    {
        return $this->belongsTo(IncomingMail::class);
    }
}
