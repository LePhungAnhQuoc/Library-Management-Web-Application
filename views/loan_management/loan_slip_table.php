<?php
require_once __DIR__ . '/../../Utilities/Enums.php';

class LoanSlipTable
{
    public array $loanRows = [];
    public string $title = 'Active loan slips';
    public string $emptyMessage = 'No active loans found.';
    public array $columns = [
        ['label' => 'Title', 'key' => 'title'],
        ['label' => 'Reader', 'key' => 'reader'],
        ['label' => 'Due date', 'key' => 'due_date'],
        ['label' => 'Status', 'key' => 'status', 'type' => 'badge'],
        ['label' => 'Renewals', 'key' => 'renewals', 'suffix' => ' of 2'],
        ['label' => 'Action', 'type' => 'action'],
    ];

    public function render(): void
    {
        require __DIR__ . '/loan_slip_table_view.php';
    }

    private function getActionLabel(array $loan): string
    {
        return LoanStatus::fromValue($loan['status'] ?? null) === LoanStatus::ON_HOLD
            ? 'Release'
            : 'Return';
    }
}
