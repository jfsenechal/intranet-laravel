<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Database\Seeders;

use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\Contact;
use AcMarche\MailingList\Models\Sender;
use App\Models\User;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => config('app.default_user.name'),
            'email' => config('app.default_user.email'),
            'password' => bcrypt(config('app.default_user.password')),
        ]);

        $otherUsers = User::factory(3)->create();

        // Create contacts for admin
        $adminContacts = Contact::factory(20)->create(['username' => $admin->username]);

        // Create address books for admin and attach contacts
        $adminAddressBooks = AddressBook::factory(3)->create(['username' => $admin->username]);
        foreach ($adminAddressBooks as $addressBook) {
            $addressBook->contacts()->attach(
                $adminContacts->random(random_int(5, 10))->pluck('id')
            );
        }

        // Create senders for admin
        Sender::factory(2)->create(['username' => $admin->username]);

        // Share first address book with another user (read access)
        $adminAddressBooks->first()->sharedWithUsers()->attach($otherUsers->first()->username, ['permission' => 'read']);

        // Create data for other users
        foreach ($otherUsers as $user) {
            $contacts = Contact::factory(10)->create(['username' => $user->username]);
            $addressBooks = AddressBook::factory(2)->create(['username' => $user->username]);

            foreach ($addressBooks as $addressBook) {
                $addressBook->contacts()->attach(
                    $contacts->random(random_int(3, 7))->pluck('id')
                );
            }

            Sender::factory(1)->create(['username' => $user->username]);
        }
    }
}
