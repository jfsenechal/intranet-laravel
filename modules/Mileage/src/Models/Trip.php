<?php

namespace AcMarche\Mileage\Models;

use AcMarche\Mileage\Observers\TripObserver;
use AcMarche\Security\Models\HasUserAdd;
use App\Models\User;
use Database\Factories\TripFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(TripFactory::class)]
#[ObservedBy([TripObserver::class])]
final class Trip extends Model
{
    use HasFactory;
    use HasUserAdd;

    protected $connection = 'maria-mileage';

    protected $fillable = [
        'declaration_id',
        'user_id',
        'distance',
        'departure_date',
        'arrival_date',
        'start_time',
        'end_time',
        'content',
        'rate',
        'omnium',
        'user_add',
        'type_movement',
        'departure_location',
        'arrival_location',
        'meal_expense',
        'train_expense',
    ];

    /**
     * @return BelongsTo<Declaration, Trip>
     */
    public function declaration(): BelongsTo
    {
        return $this->belongsTo(Declaration::class);
    }

    /**
     * @return BelongsTo<User, Trip>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted(): void
    {
        self::bootHasUser();
    }

    protected function casts(): array
    {
        return [
            'distance' => 'integer',
            'departure_date' => 'datetime',
            'arrival_date' => 'datetime',
            'rate' => 'decimal:2',
            'omnium' => 'decimal:2',
            'meal_expense' => 'decimal:2',
            'train_expense' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
