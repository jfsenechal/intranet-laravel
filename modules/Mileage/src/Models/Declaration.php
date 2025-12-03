<?php

namespace AcMarche\Mileage\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Declaration extends Model
{
    use HasFactory;
    use HasUserAdd;
    use SoftDeletes;

    protected $connection = 'maria-mileage';

    protected $fillable = [
        'omnium',
        'iban',
        'car_license_plate1',
        'car_license_plate2',
        'last_name',
        'first_name',
        'street',
        'postal_code',
        'locality',
        'rate',
        'rate_omnium',
        'user_add',
        'type_movement',
        'college_date',
        'budget_article',
        'departments',
    ];

    /**
     * @return BelongsTo<BudgetArticle, Declaration>
     */
    public function budgetArticle(): BelongsTo
    {
        return $this->belongsTo(BudgetArticle::class, 'budget_article', 'name');
    }

    /**
     * @return HasMany<Trip>
     */
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();
    }

    protected function casts(): array
    {
        return [
            'omnium' => 'boolean',
            'rate' => 'decimal:2',
            'rate_omnium' => 'decimal:2',
            'college_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
