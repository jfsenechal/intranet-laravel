<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Models;

use AcMarche\Mileage\Database\Factories\DeclarationFactory;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(DeclarationFactory::class)]
#[Connection('maria-mileage')]
#[Fillable([
    'omnium',
    'iban',
    'car_license_plate1',
    'car_license_plate2',
    'last_name',
    'first_name',
    'street',
    'postal_code',
    'city',
    'rate',
    'rate_omnium',
    'user_add',
    'type_movement',
    'college_date',
    'budget_article',
    'departments',
])]
final class Declaration extends Model
{
    use HasFactory;
    use HasUserAdd;
    use SoftDeletes;

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

    /**
     * The up to date profile of the declarant, matched on the username stored in `user_add`.
     *
     * @return BelongsTo<PersonalInformation, Declaration>
     */
    public function personalInformation(): BelongsTo
    {
        return $this->belongsTo(PersonalInformation::class, 'user_add', 'username');
    }

    /**
     * Whether the personal information holds an account number that differs from the one the declaration was made with.
     */
    public function hasOutdatedIban(): bool
    {
        $currentIban = $this->fromPersonalInformation('iban');

        if ($currentIban === null || blank($this->iban)) {
            return false;
        }

        return self::normalizeIban($currentIban) !== self::normalizeIban((string) $this->iban);
    }

    /**
     * Whether this declaration belongs to the C.P.A.S. (as opposed to the Ville).
     *
     * When a declaration references both entities (e.g. a finance admin), the Ville takes precedence.
     */
    public function isCpas(): bool
    {
        $departments = mb_strtolower((string) $this->departments);

        return str_contains($departments, 'cpas') && ! str_contains($departments, 'ville');
    }

    protected static function booted(): void
    {
        self::bootHasUser();
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function displayStreet(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->fromPersonalInformation('street') ?? $this->street);
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function displayPostalCode(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->fromPersonalInformation('postal_code') ?? $this->postal_code);
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function displayCity(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->fromPersonalInformation('city') ?? $this->city);
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function displayIban(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->fromPersonalInformation('iban') ?? $this->iban);
    }

    protected function casts(): array
    {
        return [
            'omnium' => 'boolean',
            'rate' => 'decimal:4',
            'rate_omnium' => 'decimal:4',
            'college_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    private static function normalizeIban(string $iban): string
    {
        return mb_strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $iban));
    }

    /**
     * Read an attribute from the personal information, ignoring values that are not filled in.
     */
    private function fromPersonalInformation(string $attribute): ?string
    {
        $value = $this->personalInformation?->{$attribute};

        return filled($value) ? (string) $value : null;
    }
}
