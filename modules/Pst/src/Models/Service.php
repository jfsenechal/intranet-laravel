<?php

declare(strict_types=1);

namespace AcMarche\Pst\Models;

use AcMarche\Pst\Database\Factories\ServiceFactory;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;

#[UseFactory(ServiceFactory::class)]
#[Connection('maria-pst')]
#[Fillable([
    'name',
    'initials',
])]
final class Service extends Model
{
    use HasFactory, Notifiable;
    use Searchable;

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
            'description' => $this->initials,
        ];
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'pst_services_index';
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'service_user',
            'service_id',
            'username',
            'id',
            'username'
        )->tap(function ($query): void {
            // Handle cross-database join by explicitly specifying the database
            $query->from(DB::raw('`intranet`.`users`'));
        });
    }

    public function leadingActions(): BelongsToMany
    {
        return $this->belongsToMany(Action::class, 'action_service_leader');
    }

    public function partneringActions(): BelongsToMany
    {
        return $this->belongsToMany(Action::class, 'action_service_partner');
    }

    public function leadingActionsForDepartment(): BelongsToMany
    {
        return $this->leadingActions()->forSelectedDepartment();
    }

    public function partneringActionsForDepartment(): BelongsToMany
    {
        return $this->partneringActions()->forSelectedDepartment();
    }

    /**
     * Get all actions (leading + partnering) filtered by selected department.
     *
     * @return Builder<Action>
     */
    public function actionsForDepartment(): Builder
    {
        $serviceId = $this->id;

        return Action::query()
            ->forSelectedDepartment()
            ->where(function ($query) use ($serviceId): void {
                $query->whereHas('leaderServices', fn ($q) => $q->where('services.id', $serviceId))
                    ->orWhereHas('partnerServices', fn ($q) => $q->where('services.id', $serviceId));
            });
    }
}
