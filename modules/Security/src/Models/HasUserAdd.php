<?php

namespace AcMarche\Security\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasUserAdd
{
    public static function bootHasUser(): void
    {
        static::creating(function(Model $model) {
            $model->created_by = Auth::id();
        });

        static::updating(function(Model $model) {
            $model->updated_by = Auth::id();
        });

        static::deleting(function(Model $model) {
            if (in_array(SoftDeletes::class, class_uses($model))) {
                $model->updated_by = Auth::id();
                $model->save();
            }
        });
    }

    public function userAdd(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function userUpdate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
