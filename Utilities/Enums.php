<?php
enum LoanStatus: string
{
    case CHECKED_OUT = 'Checked out';
    case DUE_SOON = 'Due soon';
    case ON_HOLD = 'On hold';
    case ON_TIME = 'On time';
    case OVERDUE = 'Overdue';
    case RETURNED = 'Returned';
    case RETURNED_LATE = 'Returned (Late)';

    public static function fromValue(?string $status): self
    {
        if ($status === null) {
            return self::CHECKED_OUT;
        }

        $normalized = strtolower(trim($status));

        foreach (self::cases() as $case) {
            if (strtolower($case->value) === $normalized) {
                return $case;
            }
        }

        return self::CHECKED_OUT;
    }

    public function badgeClass(): BadgeClass
    {
        return match ($this) {
            self::OVERDUE, self::RETURNED_LATE => BadgeClass::ALERT,
            self::DUE_SOON, self::ON_HOLD => BadgeClass::WARNING,
            default => BadgeClass::INFO,
        };
    }
}

enum BadgeClass: string
{
    case INFO = 'badge-info';
    case WARNING = 'badge-warning';
    case ALERT = 'badge-alert';
}

enum FeeType: string
{
    case LATE_RETURN = 'Late return fee';
    case LOAN_ADJUSTMENT = 'Loan adjustment';
    case NO_CURRENT_FEES = 'No current fees';

    public static function fromLoanStatus(?string $status): self
    {
        return (strtolower(trim((string) $status)) === strtolower(LoanStatus::RETURNED_LATE->value))
            ? self::LATE_RETURN
            : self::LOAN_ADJUSTMENT;
    }
}