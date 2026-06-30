<?php

declare(strict_types=1);

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Security\Database\Seeders\DatabaseSeeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('backfills users whose departments column holds invalid json', function (): void {
    $brokenA = User::factory()->create();
    $brokenB = User::factory()->create();
    $valid = User::factory()->create(['departments' => [DepartmentEnum::CPAS->value]]);

    // Simulate the broken production rows whose empty-string `departments` fails
    // MariaDB's implicit json_valid CHECK on the next update of the row.
    DB::table('users')->whereIn('id', [$brokenA->id, $brokenB->id])
        ->update(['departments' => '']);

    $seeder = new DatabaseSeeder;
    (fn () => $this->backfillInvalidDepartments())->call($seeder);

    foreach ([$brokenA, $brokenB] as $repaired) {
        $departments = DB::table('users')->where('id', $repaired->id)->value('departments');
        expect(json_decode((string) $departments, true))->toBe([DepartmentEnum::VILLE->value]);
    }

    // Already-valid rows are left untouched.
    expect($valid->fresh()->departments)->toBe([DepartmentEnum::CPAS->value]);
});
