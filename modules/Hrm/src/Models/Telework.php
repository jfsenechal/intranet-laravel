<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Hrm\Database\Factories\TeleworkFactory;
use AcMarche\Hrm\Enums\DayTypeEnum;
use AcMarche\Hrm\Enums\LocationTypeEnum;
use AcMarche\Hrm\Enums\WeekdayEnum;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property bool $regulation_agreement
 * @property string|null $street
 * @property string|null $postal_code
 * @property string|null $locality
 * @property string $user_add
 * @property bool|null $manager_validated
 * @property \Carbon\CarbonImmutable|null $manager_validated_at
 * @property string|null $manager_validation_notes
 * @property \Carbon\CarbonImmutable|null $date_college
 * @property string|null $hr_notes
 * @property string|null $employee_notes
 * @property string|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property LocationTypeEnum $location_type
 * @property DayTypeEnum $day_type
 * @property WeekdayEnum|null $fixed_day
 * @property string|null $variable_day_reason
 * @property bool $it_agreement
 * @property string $uuid
 * @property string|null $manager_validator_name
 * @property string|null $hr_validator_name
 * @property-read Employee|null $employee
 */
#[Connection('maria-hrm')]
#[Fillable([
    'uuid',
    'regulation_agreement',
    'it_agreement',
    'street',
    'postal_code',
    'locality',
    'location_type',
    'day_type',
    'fixed_day',
    'variable_day_reason',
    'manager_validated',
    'manager_validated_at',
    'manager_validation_notes',
    'date_college',
    'hr_notes',
    'employee_notes',
    'manager_validator_name',
    'hr_validator_name',
    'user_add',
    'updated_by',
])]
#[Table(name: 'teleworks')]
#[UseFactory(TeleworkFactory::class)]
final class Telework extends Model
{
    use HasFactory;
    use HasUserAdd;

    /**
     * @return BelongsTo<Employee, Telework>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'user_add', 'username');
    }

    protected static function booted(): void
    {
        self::bootHasUser();

        self::creating(function (Telework $telework): void {
            if (empty($telework->uuid)) {
                $telework->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'regulation_agreement' => 'boolean',
            'it_agreement' => 'boolean',
            'manager_validated' => 'boolean',
            'manager_validated_at' => 'date',
            'date_college' => 'date',
            'location_type' => LocationTypeEnum::class,
            'day_type' => DayTypeEnum::class,
            'fixed_day' => WeekdayEnum::class,
        ];
    }
}
