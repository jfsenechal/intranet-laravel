<?php

declare(strict_types=1);

use AcMarche\Hrm\Mail\ReminderMail;
use Illuminate\Database\Eloquent\Model;

$record = new class extends Model {};

it('includes the employee name in the subject when provided', function () use ($record): void {
    $mail = new ReminderMail(
        reminderType: 'Absence',
        record: $record,
        url: 'https://example.test',
        employeeName: 'Doe John',
    );

    expect($mail->subject)->toBe('[GRH] Rappel - Absence - Doe John');
});

it('omits the employee name from the subject when null', function () use ($record): void {
    $mail = new ReminderMail(
        reminderType: 'Absence',
        record: $record,
        url: 'https://example.test',
        employeeName: null,
    );

    expect($mail->subject)->toBe('[GRH] Rappel - Absence');
});

it('omits the employee name from the subject when empty', function () use ($record): void {
    $mail = new ReminderMail(
        reminderType: 'Absence',
        record: $record,
        url: 'https://example.test',
        employeeName: '',
    );

    expect($mail->subject)->toBe('[GRH] Rappel - Absence');
});
