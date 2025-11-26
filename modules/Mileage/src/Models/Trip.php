<?php

namespace AcMarche\Mileage\Models;

use AcMarche\Security\Models\HasUserAdd;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Trip extends Model
{
    use HasFactory;
    use HasUserAdd;

    protected $connection = 'maria-mileage';

    protected $fillable = [
        'declaration_id',
        'utilisateur_id',
        'distance',
        'date_depart',
        'date_arrive',
        'heure_start',
        'heure_end',
        'content',
        'tarif',
        'omnium',
        'user_add',
        'type_deplacement',
        'lieu_depart',
        'lieu_arrive',
        'repas',
        'train',
    ];

    protected function casts(): array
    {
        return [
            'distance' => 'integer',
            'date_depart' => 'datetime',
            'date_arrive' => 'datetime',
            'tarif' => 'decimal:2',
            'omnium' => 'decimal:2',
            'repas' => 'decimal:2',
            'train' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (Auth::check()) {
                $model->user = Auth::user()->username ?? Auth::user()->email;
                $model->utilisateur_id = Auth::id();
            }
        });
    }

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
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}
