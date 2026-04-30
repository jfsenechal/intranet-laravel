<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-mailing-list';

    public function up(): void
    {
        if (Schema::connection('maria-mailing-list')->hasTable('contact_shares')) {
            return;
        }
        Schema::create('contact_shares', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('username');
            $table->string('permission')->default('read');
            $table->timestamps();

            $table->unique(['contact_id', 'username']);
        });
    }
};
