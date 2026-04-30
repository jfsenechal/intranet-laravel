<?php

declare(strict_types=1);

use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\CreateIncomingMail;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\EditIncomingMail;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Sender;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
});

describe('Save Sender from IncomingMail form', function (): void {
    test('creating incoming mail with save_sender checked saves sender to senders table', function (): void {
        $admin = User::factory()->create(['is_administrator' => true]);
        $this->actingAs($admin);

        livewire(CreateIncomingMail::class)
            ->fillForm([
                'reference_number' => 'TEST-SENDER-001',
                'mail_date' => now()->format('Y-m-d'),
                'sender' => 'Nouvelle Entreprise SA',
                'description' => 'Test description',
                'save_sender' => true,
                'attachment_file' => UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Sender::where('name', 'Nouvelle Entreprise SA')->exists())->toBeTrue();
    });

    test('creating incoming mail without save_sender does not save sender', function (): void {
        $admin = User::factory()->create(['is_administrator' => true]);
        $this->actingAs($admin);

        livewire(CreateIncomingMail::class)
            ->fillForm([
                'reference_number' => 'TEST-SENDER-002',
                'mail_date' => now()->format('Y-m-d'),
                'sender' => 'Entreprise Non Sauvée',
                'description' => 'Test description',
                'save_sender' => false,
                'attachment_file' => UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Sender::where('name', 'Entreprise Non Sauvée')->exists())->toBeFalse();
    });

    test('save_sender does not create duplicate if sender already exists', function (): void {
        $admin = User::factory()->create(['is_administrator' => true]);
        $this->actingAs($admin);

        Sender::factory()->create(['name' => 'Existing Sender']);

        livewire(CreateIncomingMail::class)
            ->fillForm([
                'reference_number' => 'TEST-SENDER-003',
                'mail_date' => now()->format('Y-m-d'),
                'sender' => 'Existing Sender',
                'description' => 'Test description',
                'save_sender' => true,
                'attachment_file' => UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Sender::where('name', 'Existing Sender')->count())->toBe(1);
    });

    test('editing incoming mail with save_sender checked saves sender to senders table', function (): void {
        $admin = User::factory()->create(['is_administrator' => true]);
        $this->actingAs($admin);

        $mail = IncomingMail::factory()->create([
            'sender' => 'Edited Sender SA',
        ]);

        livewire(EditIncomingMail::class, ['record' => $mail->getRouteKey()])
            ->fillForm([
                'save_sender' => true,
                'attachment_file' => UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect(Sender::where('name', 'Edited Sender SA')->exists())->toBeTrue();
    });
});
