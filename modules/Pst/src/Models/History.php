<?php

declare(strict_types=1);

namespace AcMarche\Pst\Models;

use AcMarche\Pst\Database\Factories\HistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

#[UseFactory(HistoryFactory::class)]
#[Connection('maria-pst')]
#[Fillable(['action_id', 'body', 'property', 'old_value', 'new_value', 'user_add'])]
final class History extends Model
{
    use HasFactory;

    /**
     * Get the action that owns the followup
     */
    public function actions(): BelongsTo
    {
        return $this->belongsTo(Action::class);
    }

    protected static function booted(): void
    {
        self::creating(function (self $model): void {
            if (Auth::check()) {
                $model->user_add = Auth::user()->username;
            }
        });
    }
}
