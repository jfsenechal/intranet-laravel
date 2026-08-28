<?php

declare(strict_types=1);

namespace AcMarche\Note\Models;

use AcMarche\Note\Database\Factories\NoteFactory;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * The `content` column holds either plaintext or a ciphertext blob, decided per record
 * by `is_encrypted`. In memory `$note->content` is always plaintext: the accessor
 * decrypts on read and the saving hook encrypts on write.
 *
 * A plain `encrypted` cast cannot express this, because whether the stored value needs
 * decrypting depends on a sibling attribute, and because toggling the flag alone has to
 * rewrite a body that was never re-assigned.
 */
#[Connection('maria-note')]
#[Fillable([
    'name',
    'content',
    'is_encrypted',
])]
final class Note extends Model
{
    use HasFactory;
    use HasUserAdd;

    /**
     * The plaintext body, whatever form the stored attribute currently holds.
     *
     * A freshly assigned value is still plaintext (there is no set mutator), so it is
     * returned untouched; anything else is read back through the *stored* flag, which
     * is what the persisted bytes were written with.
     */
    public function plainContent(): ?string
    {
        if (! $this->exists || $this->isDirty('content')) {
            return $this->attributes['content'] ?? null;
        }

        return self::decryptContent(
            $this->getOriginal('content'),
            (bool) $this->getOriginal('is_encrypted'),
        );
    }

    protected static function booted(): void
    {
        self::bootHasUser();

        self::saving(function (self $note): void {
            $plaintext = $note->plainContent();

            $note->attributes['content'] = ($note->is_encrypted && $plaintext !== null)
                ? Crypt::encryptString($plaintext)
                : $plaintext;
        });
    }

    protected static function newFactory(): NoteFactory
    {
        return NoteFactory::new();
    }

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    /**
     * Decrypt on read. A dirty or not-yet-persisted value is still plaintext and is
     * passed straight through, so that reading back a note before it is saved does not
     * try to decrypt something that was never encrypted.
     */
    protected function content(): Attribute
    {
        return Attribute::make(
            get: function (?string $value, array $attributes): ?string {
                if (! $this->exists || $this->isDirty('content')) {
                    return $value;
                }

                return self::decryptContent($value, (bool) ($attributes['is_encrypted'] ?? false));
            },
        );
    }

    private static function decryptContent(?string $value, bool $isEncrypted): ?string
    {
        if ($value === null || ! $isEncrypted) {
            return $value;
        }

        return Crypt::decryptString($value);
    }
}
