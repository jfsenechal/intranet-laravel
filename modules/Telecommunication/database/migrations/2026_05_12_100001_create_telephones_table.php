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
        if (Schema::connection('maria-telecommunication')->hasTable('telephones')) {
            return;
        }
        Schema::create('telephones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('line_type_id')
                ->nullable()
                ->constrained('line_types')
                ->nullOnDelete();
            $table->string('slug', 70)->unique();
            $table->string('user_name');
            $table->string('number');
            $table->boolean('archived')->default(false);
            $table->string('mobistar')->nullable();
            $table->string('proximus')->nullable();
            $table->string('service')->nullable();
            $table->string('department')->nullable();
            $table->string('budget_article')->nullable();
            $table->string('location')->nullable();
            $table->string('fixed_cost')->nullable();
            $table->longText('note')->nullable();
            $table->timestamps();
        });
    }
};
