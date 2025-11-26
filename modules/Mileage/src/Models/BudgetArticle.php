<?php

namespace AcMarche\Mileage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetArticle extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'maria-mileage';

    protected $table = 'budget_articles';

    protected $fillable = [
        'name',
        'functional_code',
        'economic_code',
        'department',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<Declaration>
     */
    public function declarations(): HasMany
    {
        return $this->hasMany(Declaration::class, 'article_budgetaire', 'nom');
    }
}
