<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Casts\EncryptedPersonalDataCast;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-email-management';

    /**
     * The personal data columns stored encrypted, see EncryptedPersonalDataCast.
     *
     * @var list<string>
     */
    private array $columns = [
        'employeeNumber',
        'postalAddress',
        'l',
        'recovery_email',
        'recovery_phone',
    ];

    /**
     * Run the migrations.
     *
     * An encrypted value is far longer than 255 characters, so the columns become text first.
     * Values that already decrypt are left alone, so a rerun never encrypts twice.
     */
    public function up(): void
    {
        Schema::connection('maria-email-management')->table('citoyens', function (Blueprint $table) {
            foreach ($this->columns as $column) {
                $table->text($column)->nullable()->change();
            }
        });

        $this->rewrite(fn (string $value): string => $this->isEncrypted($value) ? $value : EncryptedPersonalDataCast::encrypter()->encryptString($value));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->rewrite(fn (string $value): string => $this->isEncrypted($value) ? EncryptedPersonalDataCast::encrypter()->decryptString($value) : $value);

        Schema::connection('maria-email-management')->table('citoyens', function (Blueprint $table) {
            foreach ($this->columns as $column) {
                $table->string($column)->nullable()->change();
            }
        });
    }

    /**
     * @param  Closure(string): string  $transform
     */
    private function rewrite(Closure $transform): void
    {
        $connection = DB::connection('maria-email-management');

        $connection->transaction(function () use ($connection, $transform): void {
            $connection->table('citoyens')
                ->select(['id', ...$this->columns])
                ->orderBy('id')
                ->chunkById(500, function ($citoyens) use ($connection, $transform): void {
                    foreach ($citoyens as $citoyen) {
                        $values = [];

                        foreach ($this->columns as $column) {
                            if (filled($citoyen->{$column})) {
                                $values[$column] = $transform($citoyen->{$column});
                            }
                        }

                        if ($values !== []) {
                            $connection->table('citoyens')->where('id', $citoyen->id)->update($values);
                        }
                    }
                });
        });
    }

    private function isEncrypted(string $value): bool
    {
        try {
            EncryptedPersonalDataCast::encrypter()->decryptString($value);

            return true;
        } catch (DecryptException) {
            return false;
        }
    }
};
