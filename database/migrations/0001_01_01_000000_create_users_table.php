<?php

use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use AcMarche\Security\Models\Tab;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    protected $connection = 'mariadb';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_name')->nullable(false);
            $table->string('first_name')->nullable(false);
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('extension')->nullable();
            $table->string('username')->unique()->nullable(false);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('color_primary')->nullable();
            $table->string('color_secondary')->nullable();
            $table->string('uuid')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('tabs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('icon')->nullable();
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('url')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_external')->default(false);
            $table->boolean('is_public')->default(false);
            $table->string('icon')->default(null);
            $table->string('color')->default(null);
            $table->timestamps();
            $table->foreignIdFor(Tab::class);
        });

        Schema::create('module_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Module::class)->constrained('modules')->cascadeOnDelete();
            $table->unique(['user_id', 'module_id']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->foreignIdFor(Module::class)->nullable()->constrained('modules')->cascadeOnDelete();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Role::class)->nullable()->constrained('roles')->cascadeOnDelete();
            $table->unique(['user_id', 'role_id']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

};
