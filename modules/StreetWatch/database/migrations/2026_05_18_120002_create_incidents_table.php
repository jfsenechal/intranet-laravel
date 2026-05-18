<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-street-watch';

    public function up(): void
    {
        if (Schema::connection('maria-street-watch')->hasTable('incidents')) {
            return;
        }
        Schema::connection('maria-street-watch')->create('incidents', function (Blueprint $table): void {
            $table->id();
            $table->string('place', 255);
            $table->string('object', 255);
            $table->longText('description');
            $table->longText('response')->nullable();
            $table->string('user_add', 255);
            $table->dateTime('occurred_date')->nullable();
            $table->dateTime('createdAt')->nullable();
            $table->dateTime('updatedAt')->nullable();
            $table->integer('requestBy_id');
            $table->integer('typeIncident_id');

            $table->foreign('requestBy_id')
                ->references('id')->on('requests_by')
                ->restrictOnDelete();
            $table->foreign('typeIncident_id')
                ->references('id')->on('types')
                ->restrictOnDelete();
            $table->index('requestBy_id');
            $table->index('typeIncident_id');
        });
    }
};
