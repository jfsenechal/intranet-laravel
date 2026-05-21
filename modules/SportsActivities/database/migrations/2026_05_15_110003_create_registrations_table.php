<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    protected $connection = 'maria-rescam';

    public function up(): void
    {
        if (Schema::connection('maria-rescam')->hasTable('inscription')) {
            Schema::connection('maria-rescam')->table('inscription', function (Blueprint $table): void {
                $table->rename('registrations');
            });
            Schema::connection('maria-rescam')->table('registrations', function (Blueprint $table): void {
                $table->renameColumn('activite_id', 'activity_id');
                $table->renameColumn('groupe_id', 'group_id');
                $table->renameColumn('sportif_id', 'member_id');
                $table->renameColumn('remarque', 'comment');
                $table->renameColumn('user', 'user_add');
            });

            return;
        }

        Schema::connection('maria-rescam')->create('registrations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities');
            $table->foreignId('group_id')->constrained('groups');
            $table->foreignId('member_id')->constrained('members');
            $table->double('price')->nullable();
            $table->longText('comment')->nullable();
            $table->string('user_add', 255);
            $table->timestamps();

            $table->unique(['activity_id', 'group_id', 'member_id'], 'registration_idx');
        });
    }
};
