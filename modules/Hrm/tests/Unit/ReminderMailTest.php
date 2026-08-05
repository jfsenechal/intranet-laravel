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

it('includes the record name before the employee name in the subject', function () use ($record): void {
    $mail = new ReminderMail(
        reminderType: 'Échéance',
        record: $record,
        url: 'https://example.test',
        employeeName: 'Doe John',
        recordName: 'Visite médicale',
    );

    expect($mail->subject)->toBe('[GRH] Rappel - Échéance - Visite médicale - Doe John');
});

it('includes the record name in the subject without an employee', function () use ($record): void {
    $mail = new ReminderMail(
        reminderType: 'Échéance',
        record: $record,
        url: 'https://example.test',
        employeeName: null,
        recordName: 'Visite médicale',
    );

    expect($mail->subject)->toBe('[GRH] Rappel - Échéance - Visite médicale');
});

it('omits the record name from the subject when empty', function () use ($record): void {
    $mail = new ReminderMail(
        reminderType: 'Échéance',
        record: $record,
        url: 'https://example.test',
        employeeName: 'Doe John',
        recordName: '',
    );

    expect($mail->subject)->toBe('[GRH] Rappel - Échéance - Doe John');
});
