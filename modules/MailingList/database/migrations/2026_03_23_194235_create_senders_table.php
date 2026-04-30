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
        // $connection = Schema::connection('maria-mailing-list')->getConnection();
        // $connection->statement('SET FOREIGN_KEY_CHECKS=0');
        if (Schema::connection('maria-mailing-list')->hasTable('senders')) {
            return;
        }
        Schema::connection('maria-mailing-list')->dropIfExists('senders');
        Schema::connection('maria-mailing-list')->create('senders', function (Blueprint $table): void {
            $table->id();
            $table->string('username')->index();
            $table->string('name');
            $table->string('email');
            $table->text('footer')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        // $connection->statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
