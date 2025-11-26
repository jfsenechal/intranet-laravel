<?php

namespace AcMarche\Mileage\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Declaration extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUserAdd;

    protected $connection = 'maria-mileage';

    protected $fillable = [
        'omnium',
        'iban',
        'plaque1',
        'plaque2',
        'nom',
        'prenom',
        'rue',
        'code_postal',
        'localite',
        'tarif',
        'tarif_omnium',
        'user_add',
        'type_deplacement',
        'date_college',
        'article_budgetaire',
        'departments',
    ];

    protected function casts(): array
    {
        return [
            'omnium' => 'boolean',
            'tarif' => 'decimal:2',
            'tarif_omnium' => 'decimal:2',
            'date_college' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (Auth::check()) {
                $model->user = Auth::user()->username ?? Auth::user()->email;
            }
        });
    }

    /**
     * @return BelongsTo<BudgetArticle, Declaration>
     */
    public function budgetArticle(): BelongsTo
    {
        return $this->belongsTo(BudgetArticle::class, 'article_budgetaire', 'nom');
    }

    /**
     * @return HasMany<Trip>
     */
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
}
