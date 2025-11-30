<?php

use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use AcMarche\Security\Models\Tab;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mariadb';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::connection('mariadb')->hasTable('heading')) {
            Schema::create('tabs', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('icon')->nullable();
            });
        }

        if (! Schema::connection('mariadb')->hasTable('module')) {
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
        } else {
            Schema::connection('mariadb')->table('module', function (Blueprint $table) {
                $table->rename('modules');
            });
            // Rename columns in modules table
            Schema::connection('mariadb')->table('modules', function (Blueprint $table) {
                $table->renameColumn('nom', 'name');
                $table->renameColumn('exterieur', 'is_external');
                $table->renameColumn('public', 'is_public');
                $table->renameColumn('icone', 'icon');
                $table->renameColumn('heading_id', 'tab_id');
                $table->string('color')->default(null);
            });
        }

        Schema::create('module_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(Module::class);
            $table->unique(['user_id', 'module_id']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->foreignIdFor(Module::class)->nullable();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(Role::class);
            $table->unique(['user_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security');
    }
};
