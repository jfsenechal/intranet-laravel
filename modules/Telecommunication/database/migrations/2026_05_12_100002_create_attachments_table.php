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
        if (Schema::connection('maria-telecommunication')->hasTable('attachments')) {
            return;
        }
        Schema::create('attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('telephone_id')
                ->constrained('telephones')
                ->cascadeOnDelete();
            $table->string('file_name');
            $table->timestamps();
        });
    }
};
