<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\CreateIncomingMail;
use AcMarche\Courrier\Models\Attachment;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
    Storage::fake(config('courrier.storage.disk'));

    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
    $user->roles()->attach($role);
    $this->actingAs($user);
});

it('stores the file path on the attachment created through the upload form', function (): void {
    livewire(CreateIncomingMail::class)
        ->fillForm([
            'reference_number' => 'TEST-PATH-1',
            'mail_date' => today(),
            'sender' => 'ACME',
            'attachment_file' => UploadedFile::fake()->create('rapport.pdf', 10, 'application/pdf'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $mail = IncomingMail::query()->where('reference_number', 'TEST-PATH-1')->firstOrFail();
    $attachment = Attachment::query()->where('incoming_mail_id', $mail->id)->firstOrFail();

    $expectedPath = config('courrier.storage.directory').'/attachments/'.$attachment->file_name;

    expect($attachment->path)->toBe($expectedPath);
    Storage::disk(config('courrier.storage.disk'))->assertExists($expectedPath);
});
