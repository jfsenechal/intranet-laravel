<?php

declare(strict_types=1);

namespace AcMarche\Security\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasUserAdd
{
    public static function bootHasUser(): void
    {
        static::creating(function (Model $model): void {
            if (Auth::check()) {
                $user = Auth::user();
                $model->user_add = $user->username;
            }
        });

        static::updating(function (Model $model): void {
            //
        });

        static::deleting(function (Model $model): void {
            if (in_array(SoftDeletes::class, class_uses($model))) {
                //
            }
        });
    }
}
