<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-telecommunication';

    public function up(): void
    {
        if (Schema::connection('maria-telecommunication')->hasTable('line_types')) {
            return;
        }
        Schema::create('line_types', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 70)->unique();
            $table->string('name');
        });
    }
};
