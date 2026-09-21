<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class SubscriptionService
{
    public const KEY_DUE_DATE = 'subscription_due_date';

    public const KEY_GRACE_DAYS = 'subscription_lock_grace_days';

    public const KEY_MANUALLY_LOCKED = 'subscription_manually_locked';

    public static function dueDate(): ?CarbonInterface
    {
        $value = Setting::get(self::KEY_DUE_DATE);

        return $value ? CarbonImmutable::parse($value) : null;
    }

    public static function graceDays(): int
    {
        return max(0, (int) Setting::get(self::KEY_GRACE_DAYS, 0));
    }

    public static function lockDate(): ?CarbonInterface
    {
        return self::dueDate()?->addDays(self::graceDays())->endOfDay();
    }

    public static function isManuallyLocked(): bool
    {
        return (bool) Setting::get(self::KEY_MANUALLY_LOCKED, false);
    }

    public static function isLocked(): bool
    {
        if (self::isManuallyLocked()) {
            return true;
        }

        return self::lockDate() !== null && now()->isAfter(self::lockDate());
    }

    public static function lockReason(): ?string
    {
        if (self::isManuallyLocked()) {
            return 'manual';
        }

        if (self::lockDate() !== null && now()->isAfter(self::lockDate())) {
            return 'overdue';
        }

        return null;
    }

    public static function daysRemaining(): ?int
    {
        $lockDate = self::lockDate();

        return $lockDate
            ? (int) now()->startOfDay()->diffInDays($lockDate->startOfDay(), false)
            : null;
    }

    public static function setDueDate(string $dueDate): void
    {
        Setting::set(self::KEY_DUE_DATE, CarbonImmutable::parse($dueDate)->toDateString());
    }

    public static function setGraceDays(int $graceDays): void
    {
        Setting::set(self::KEY_GRACE_DAYS, max(0, $graceDays));
    }

    public static function setManuallyLocked(bool $locked): void
    {
        Setting::set(self::KEY_MANUALLY_LOCKED, $locked ? '1' : '0');
    }
}
