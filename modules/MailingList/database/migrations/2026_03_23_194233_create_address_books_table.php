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
        if (Schema::connection('maria-mailing-list')->hasTable('address_books')) {
            return;
        }
        Schema::create('address_books', function (Blueprint $table): void {
            $table->id();
            $table->string('username')->index();
            $table->string('name');
            $table->timestamps();
        });
    }
};
