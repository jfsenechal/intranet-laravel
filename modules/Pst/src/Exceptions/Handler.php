<?php

declare(strict_types=1);

namespace AcMarche\Pst\Exceptions;

use AcMarche\Pst\Mail\ExceptionMail;
use App;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (Throwable $e): void {
            $this->sendErrorMail($e);
        });
    }

    protected function sendErrorMail(Throwable $exception): void
    {
        if (App::isLocal()) {
            return;
        }

        $email = config('MAIL_IT_ADDRESS');

        if ($email) {
            $body = "An error occurred: \n".$exception->getMessage()."\n".$exception->getTraceAsString();
            try {
                Mail::to($email)->send(new ExceptionMail($body));
            } catch (Throwable $th) {
                Log::error('Failed to send exception email', [
                    'error' => $th->getMessage(),
                    'original_exception' => $exception->getMessage(),
                ]);
            }
        }
    }
}
