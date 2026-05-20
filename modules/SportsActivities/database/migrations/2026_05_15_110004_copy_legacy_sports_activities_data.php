<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-rescam';

    /**
     * Copy rows from the legacy French singular tables into the new
     * English tables, preserving primary keys and foreign keys.
     */
    public function up(): void
    {
        $schema = Schema::connection('maria-rescam');
        $db = DB::connection('maria-rescam');

        if (! $schema->hasTable('activite')) {
            return;
        }

        if ($db->table('activities')->exists()) {
            return;
        }

        $db->statement('INSERT INTO `activities` (id, name, description, archived, created_at, updated_at)
            SELECT id, nom, description, COALESCE(archive, 0), createdAt, updatedAt FROM `activite`');

        $db->statement('INSERT INTO `members` (id, last_name, first_name, birth_date, street, postal_code, city, phone, mobile, email, comment, created_at, updated_at)
            SELECT id, nom, prenom, ne_le, rue, code_postal, localite, telephone, gsm, email, remarque, createdAt, updatedAt FROM `sportif`');

        $db->statement('INSERT INTO `groups` (id, activity_id, day, time, location, age, price, description, comment, created_at, updated_at)
            SELECT id, activite_id, jour, heure, lieux, age, COALESCE(prix, 0), description, remarque, createdAt, updatedAt FROM `groupe`');

        $db->statement('INSERT INTO `registrations` (id, activity_id, group_id, member_id, price, comment, user_add, created_at, updated_at)
            SELECT id, activite_id, groupe_id, sportif_id, prix, remarque, user_add, createdAt, updatedAt FROM `inscription`');
    }
};
