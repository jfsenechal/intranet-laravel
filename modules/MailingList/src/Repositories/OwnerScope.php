<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class OwnerScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $currentUser = auth()->user();
        $username = $currentUser->username;
        $table = $model->getTable();

        $builder->where(function (Builder $query) use ($model, $table, $username): void {
            // Show items owned by the user
            $query->where($table.'.username', '=', $username);

            // Show items shared with the user
            $shareTable = $this->getShareTable($table);
            if ($shareTable) {
                $pkeyName = $this->getPrimaryKeyName($table);
                $query->orWhereIn(
                    $table.'.id',
                    $model->getConnection()
                        ->table($shareTable)
                        ->select($pkeyName)
                        ->where('username', '=', $username)
                );
            }
        });
    }

    private function getShareTable(string $table): ?string
    {
        return match ($table) {
            'contacts' => 'contact_shares',
            'address_books' => 'address_book_shares',
            default => null,
        };
    }

    private function getPrimaryKeyName(string $table): string
    {
        return match ($table) {
            'contacts' => 'contact_id',
            'address_books' => 'address_book_id',
            default => 'id',
        };
    }
}
