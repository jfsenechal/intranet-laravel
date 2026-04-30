<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table): void {
            $table->id();
            $table->string('username', 100)->nullable();
            $table->string('name', 150);
            $table->string('action', 30);

            $table->string('color', 10)->default('#000000');
            $table->string('background_color', 10)->default('#FFFFFF');
            $table->unsignedSmallInteger('pixels')->default(400);
            $table->string('format', 10)->default('SVG');
            $table->string('style', 10)->default('square');
            $table->unsignedSmallInteger('margin')->default(10);

            $table->string('label_text', 150)->nullable();
            $table->string('label_color', 10)->default('#000000');
            $table->unsignedSmallInteger('label_size')->default(16);
            $table->string('label_alignment', 50)->default('center');

            $table->string('file_path', 250)->nullable();

            $table->string('message', 500)->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('subject', 150)->nullable();

            $table->string('iban', 50)->nullable();
            $table->string('amount', 30)->nullable();
            $table->string('recipient', 150)->nullable();

            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();

            $table->string('ssid', 100)->nullable();
            $table->string('password', 150)->nullable();
            $table->string('encryption', 10)->default('WPA');
            $table->boolean('network_hidden')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
