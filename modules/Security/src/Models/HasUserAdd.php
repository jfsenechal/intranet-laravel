<?php

namespace AcMarche\Security\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasUserAdd
{
    public static function bootHasUser(): void
    {
        static::creating(function (Model $model) {
            if (Auth::check()) {
                $user = Auth::user();
                $model->user_add = $user->username;
            }
        });

        static::updating(function (Model $model) {
            //
        });

        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses($model))) {
                //
            }
        });
    }
}
