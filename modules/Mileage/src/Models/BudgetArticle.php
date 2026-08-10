<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Models;

use AcMarche\Mileage\Database\Factories\BudgetArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(BudgetArticleFactory::class)]
#[Connection('maria-mileage')]
#[Fillable([
    'name',
    'functional_code',
    'economic_code',
    'department',
])]
#[Table(name: 'budget_articles')]
final class BudgetArticle extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Select options labelled with {@see self::displayName()}.
     *
     * @param  string  $key  The column to key the options by: the id, or the name as stored on a declaration.
     * @return array<int|string, string>
     */
    public static function displayNameOptions(string $key = 'name'): array
    {
        return self::query()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (self $budgetArticle): array => [
                $budgetArticle->{$key} => $budgetArticle->display_name,
            ])
            ->all();
    }

    /**
     * @return HasMany<Declaration>
     */
    public function declarations(): HasMany
    {
        return $this->hasMany(Declaration::class, 'article_budgetaire', 'nom');
    }

    /**
     * The article as it must be shown to the user: `functional_code - economic_code name`.
     *
     * @return Attribute<string, never>
     */
    protected function displayName(): Attribute
    {
        return Attribute::get(function (): string {
            $codes = array_filter([$this->functional_code, $this->economic_code], filled(...));

            return mb_trim(implode(' - ', $codes).' '.$this->name);
        });
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
