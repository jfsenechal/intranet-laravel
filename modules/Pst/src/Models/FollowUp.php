<?php

declare(strict_types=1);

namespace AcMarche\Pst\Models;

use AcMarche\Pst\Database\Factories\FollowUpFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

#[UseFactory(FollowUpFactory::class)]
#[Connection('maria-pst')]
#[Fillable(['content', 'user_add'])]
final class FollowUp extends Model
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
