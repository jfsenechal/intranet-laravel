<?php

declare(strict_types=1);

namespace AcMarche\Pst\Models;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Pst\Database\Factories\ActionFactory;
use AcMarche\Pst\Enums\ActionRoadmapEnum;
use AcMarche\Pst\Enums\ActionScopeEnum;
use AcMarche\Pst\Enums\ActionStateEnum;
use AcMarche\Pst\Enums\ActionSynergyEnum;
use AcMarche\Pst\Enums\ActionTypeEnum;
use AcMarche\Pst\Enums\RoleEnum;
use AcMarche\Pst\Enums\YesOrNoEnum;
use AcMarche\Pst\Models\Scopes\DepartmentScope;
use AcMarche\Pst\Models\Scopes\HasDepartmentScope;
use AcMarche\Pst\Observers\ActionObserver;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;
use Override;

#[ObservedBy([ActionObserver::class])]
#[UseFactory(ActionFactory::class)]
#[ScopedBy([DepartmentScope::class])]
#[Connection('maria-pst')]
#[Fillable([
    'name',
    'state',
    'state_percentage',
    'type',
    'roadmap',
    'note',
    'department',
    'due_date',
    'description',
    'evaluation_indicator',
    'work_plan',
    'budget_estimate',
    'financing_mode',
    'operational_objective_id',
    'user_add',
    'synergy',
    'position',
    'validated',
    'scope',
])]
final class Action extends Model
{
    use HasDepartmentScope, HasFactory, Notifiable, Searchable, SoftDeletes;

    #[Override]
    protected $casts = [
        'medias' => 'array',
        'due_date' => 'datetime',
        'department' => DepartmentEnum::class,
        'state' => ActionStateEnum::class,
        'type' => ActionTypeEnum::class,
        'synergy' => ActionSynergyEnum::class,
        'roadmap' => ActionRoadmapEnum::class,
        'validated' => YesOrNoEnum::class,
        'scope' => ActionScopeEnum::class,
    ];

    /**
     * @see ListActionsPst::class
     */
    #[Scope]
    public static function validated(Builder $query): void
    {
        $query->where('validated', true);
    }

    /**
     * @see ListActionsPst::class
     */
    #[Scope]
    public static function notValidated(Builder $query): void
    {
        $query->where('validated', false);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'department' => $this->department,
            'note' => $this->note,
        ];
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'pst_actions_index';
    }

    public function isInternal(): bool
    {
        return $this->scope === ActionScopeEnum::INTERNAL;
    }

    /**
     * Get the operational objective that owns the action.
     */
    public function operationalObjective(): BelongsTo
    {
        return $this->belongsTo(OperationalObjective::class);
    }

    /**
     * @see ActionForm::class
     */
    public function linkedActions(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'action_related',
            'action_id',
            'related_action_id'
        );
    }

    /**
     * @return BelongsToMany<Service>
     */
    public function leaderServices(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'action_service_leader');
    }

    /**
     * @return BelongsToMany<Service>
     */
    public function partnerServices(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'action_service_partner');
    }

    /**
     * Agents pilotes
     *
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'action_user',
            'action_id',
            'username',
            'id',
            'username'
        );
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function mandataries(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'action_mandatory',
            'action_id',
            'username',
            'id',
            'username'
        ); // ->withPivot('permission')
    }

    /**
     * Get mandataries (users with MANDATAIRE role) for this action.
     *
     * @return BelongsToMany<User>
     */
    public function mandataries22(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'action_mandatory',
            'action_id',
            'username',
            'id',
            'username'
        )->tap(function ($query): void {
            // Handle cross-database join by explicitly specifying the database
            $query->from(DB::raw('`intranet`.`users`'));
        })
            ->whereIn('id', function ($subquery): void {
                $subquery
                    ->select('intranet.users.id')
                    ->from(DB::raw('`intranet`.`users`'))
                    ->join(DB::raw('`intranet`.`role_user`'), 'intranet.users.id', '=', 'intranet.role_user.user_id')
                    ->join(DB::raw('`intranet`.`roles`'), 'intranet.role_user.role_id', '=', 'intranet.roles.id')
                    ->where('intranet.roles.name', RoleEnum::MANDATAIRE->value);
            });
    }

    /**
     * @return BelongsToMany<Partner>
     */
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class);
    }

    /**
     * @return BelongsToMany<Odd>
     */
    public function odds(): BelongsToMany
    {
        return $this->belongsToMany(Odd::class);
    }

    /**
     * @return HasMany<Media>
     */
    public function medias(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * Get the followups for the action.
     *
     * @return HasMany<FollowUp>
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    /**
     * Get the followups for the action.
     *
     * @return HasMany<History>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(History::class);
    }

    protected static function booted(): void
    {
        self::creating(function (self $model): void {
            if (Auth::check()) {
                $user = Auth::user();
                $model->user_add = $user->username;
            }
            if (! isset($model->validated)) {
                $model->validated = false;
            }
        });
    }
}
