<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\TrainingTypeEnum;
use AcMarche\Hrm\Filament\Resources\Trainings\Pages\CreateTraining;
use AcMarche\Hrm\Filament\Resources\Trainings\Pages\EditTraining;
use AcMarche\Hrm\Filament\Resources\Trainings\Pages\ListTrainings;
use AcMarche\Hrm\Filament\Resources\Trainings\Pages\ViewTraining;
use AcMarche\Hrm\Models\Training;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
    $this->adminUser = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($this->adminUser);
});

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListTrainings::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateTraining::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = Training::factory()->create();

        Livewire::test(ViewTraining::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Training::factory()->create();

        Livewire::test(EditTraining::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('crud operations', function (): void {
    it('can update a training', function (): void {
        $record = Training::factory()->create();

        Livewire::test(EditTraining::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => 'Updated Training Name',
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Training::class, [
            'id' => $record->id,
            'name' => 'Updated Training Name',
        ]);
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        Livewire::test(CreateTraining::class)
            ->fillForm([
                'name' => 'Some Training',
                'training_type' => TrainingTypeEnum::TYPE1->value,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 150 characters' => [['name' => Str::random(151)], ['name' => 'max']],
        '`training_type` is required' => [['training_type' => null], ['training_type' => 'required']],
    ]);
});

describe('model behavior', function (): void {
    it('formats duration correctly', function (): void {
        expect(Training::formatDuration(0))->toBe('');
        expect(Training::formatDuration(30))->toBe('30min');
        expect(Training::formatDuration(60))->toBe('1h');
        expect(Training::formatDuration(90))->toBe('1h 30min');
        expect(Training::formatDuration(125))->toBe('2h 05min');
    });

    it('casts training_type to TrainingTypeEnum', function (): void {
        $training = Training::factory()->create(['training_type' => TrainingTypeEnum::TYPE1->value]);

        expect($training->training_type)->toBe(TrainingTypeEnum::TYPE1);
    });

    it('casts is_closed as boolean', function (): void {
        $training = Training::factory()->create(['is_closed' => true]);

        expect($training->is_closed)->toBeTrue();
    });
});
