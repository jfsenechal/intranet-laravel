<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Models\Employe;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function moveEmployesMigration(): object
{
    return require __DIR__.'/../../database/migrations/2026_11_05_110356_move_employes_to_intranet_database.php';
}

it('keeps the staff directory off the mail server', function (): void {
    $employe = Employe::factory()->create();

    $this->assertDatabaseHas('employes', ['id' => $employe->id], DB::getDefaultConnection());

    expect(Schema::connection('maria-email-management')->hasTable('employes'))->toBeFalse();
});

it('moves every employe from the mail server to the intranet database', function (): void {
    $migration = moveEmployesMigration();
    $employe = Employe::factory()->create(['last_connection' => '2026-11-01'])->refresh()->toArray();
    $migration->down();

    expect(Schema::hasTable('employes'))->toBeFalse()
        ->and(DB::connection('maria-email-management')->table('employes')->count())->toBe(1);

    $migration->up();

    expect(Schema::connection('maria-email-management')->hasTable('employes'))->toBeFalse()
        ->and(Employe::sole()->toArray())->toBe($employe);
});

it('keeps the mail server copy when the move fails', function (): void {
    $migration = moveEmployesMigration();
    $employe = Employe::factory()->create();
    $migration->down();
    Schema::create('employes', function (Blueprint $table): void {
        $table->id();
    });

    expect(fn () => $migration->up())->toThrow(QueryException::class)
        ->and(DB::connection('maria-email-management')->table('employes')->pluck('dn')->all())->toBe([$employe->dn]);
});
