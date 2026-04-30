<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-agent';

    public function up(): void
    {
        Schema::connection('maria-agent')->table('profiles', function (Blueprint $table): void {
            $table->boolean('no_mail')->default(false)->change();
        });
    }
};
