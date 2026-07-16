<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Allows an employe to exist without a mailbox.
 *
 * A share of the staff accounts in Active Directory carry no mail attribute, and the
 * mirror is meant to reflect the directory rather than only its mailbox holders. The
 * unique index is kept: MySQL and sqlite both permit repeated NULLs under one.
 */
return new class extends Migration
{
    protected $connection = 'maria-email-management';

    public function up(): void
    {
        $schema = Schema::connection('maria-email-management');

        if (! $schema->hasColumn('employes', 'mail')) {
            return;
        }

        $schema->table('employes', function (Blueprint $table): void {
            $table->string('mail')->nullable()->change();
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('maria-email-management');

        if (! $schema->hasColumn('employes', 'mail')) {
            return;
        }

        $schema->table('employes', function (Blueprint $table): void {
            $table->string('mail')->nullable(false)->change();
        });
    }
};
