<?php

declare(strict_types=1);

namespace AcMarche\News\Listeners;

use AcMarche\News\Enums\DepartmentEnum;
use AcMarche\News\Events\NewsProcessed;
use AcMarche\News\Mail\NewsEmail;
use AcMarche\News\Models\News;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class NewsNotification
{
    /**
     * Notify the relevant users about a freshly published news.
     *
     * A "common" news reaches every user; otherwise only the users belonging to the
     * news department are notified. Users who opted in to attachments receive the
     * medias directly, others get a notice with a link to the intranet instead.
     */
    public function handle(NewsProcessed $event): void
    {
        $news = $event->news();

        foreach ($this->recipientsFor($news) as $user) {
            try {
                Mail::to($user->email)
                    ->send(new NewsEmail($news, attachMedias: (bool) $user->news_attachment));
            } catch (Exception $e) {
                Log::error('Failed to send news notification email', [
                    'news_id' => $news->id,
                    'user_id' => $user->id,
                    'exception' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Resolve the users who should be notified about the given news.
     *
     * @return Collection<int, User>
     */
    private function recipientsFor(News $news): Collection
    {
        $department = $this->departmentValue($news);

        $query = User::query()->whereNotNull('email');

        if ($department !== DepartmentEnum::COMMON->value) {
            $query->whereJsonContains('departments', $department);
        }

        return $query->get();
    }

    /**
     * Normalize the news department to its enum string value, tolerating both a
     * DepartmentEnum instance (as set by the Filament form) and a raw string.
     */
    private function departmentValue(News $news): string
    {
        if ($news->department instanceof DepartmentEnum) {
            return $news->department->value;
        }

        return mb_strtoupper((string) $news->department);
    }
}
