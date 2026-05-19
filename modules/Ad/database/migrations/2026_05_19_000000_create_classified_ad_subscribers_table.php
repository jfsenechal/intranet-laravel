<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-ad';

    public function up(): void
    {
        if (Schema::connection('maria-ad')->hasTable('classified_ad_subscribers')) {
            return;
        }

        Schema::connection('maria-ad')->create('classified_ad_subscribers', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->timestamps();
        });
    }
};
