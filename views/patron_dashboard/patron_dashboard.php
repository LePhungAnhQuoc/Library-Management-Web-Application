<?php
$summaryCards = $summaryCards ?? [];
$memberName = $memberName ?? 'Jordan Smith';
$memberNumber = $memberNumber ?? 'Member #28491';
$loanRows = $loanRows ?? [];
$holdRows = $holdRows ?? [];
$historyRows = $historyRows ?? [];
$feeRows = $feeRows ?? [];
$successMessage = $successMessage ?? '';

require_once __DIR__ . '/../loan_management/loan_slip_table.php';

$currentLoansTable = new LoanSlipTable();
$currentLoansTable->title = '';
$currentLoansTable->loanRows = $loanRows;
$currentLoansTable->emptyMessage = 'No active loans found.';
$currentLoansTable->columns = [
    ['label' => 'Title', 'key' => 'title'],
    ['label' => 'Due date', 'key' => 'due_date'],
    ['label' => 'Status', 'key' => 'status', 'type' => 'badge'],
    ['label' => 'Renewals', 'key' => 'renewals', 'suffix' => ' of 2'],
    ['label' => 'Action', 'type' => 'action'],
];

$activeHoldsTable = new LoanSlipTable();
$activeHoldsTable->title = '';
$activeHoldsTable->loanRows = $holdRows;
$activeHoldsTable->emptyMessage = 'No active holds found.';
$activeHoldsTable->columns = [
    ['label' => 'Title', 'key' => 'title'],
    ['label' => 'Held on', 'key' => 'held_on'],
    ['label' => 'Due date', 'key' => 'due_date'],
    ['label' => 'Status', 'key' => 'status', 'type' => 'badge'],
    ['label' => 'Action', 'type' => 'action'],
];

$returnedItemsTable = new LoanSlipTable();
$returnedItemsTable->title = '';
$returnedItemsTable->loanRows = $historyRows;
$returnedItemsTable->emptyMessage = 'No returned items yet.';
$returnedItemsTable->columns = [
    ['label' => 'Title', 'key' => 'title'],
    ['label' => 'Due date', 'key' => 'due_date'],
    ['label' => 'Returned', 'key' => 'returned_at'],
    ['label' => 'Status', 'key' => 'status', 'type' => 'badge'],
    ['label' => 'Fee', 'key' => 'amount'],
];
?>

<link rel="stylesheet" href="../views/patron_dashboard/patron_dashboard.css">

<div class="account-page">
    <div class="account-header">
        <div>
            <p class="eyebrow">Patron dashboard</p>
            <h1>My Account</h1>
        </div>
        <div class="account-user">
            <div class="avatar"><?php echo htmlspecialchars(substr($memberName, 0, 2)); ?></div>
            <div>
                <strong><?php echo htmlspecialchars($memberName); ?></strong>
                <span><?php echo htmlspecialchars($memberNumber); ?></span>
            </div>
        </div>
    </div>

    <?php if ($successMessage !== ''): ?>
        <div class="notice success"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>

    <div class="summary-grid">
        <?php foreach ($summaryCards as $card): ?>
            <div class="summary-card <?php echo htmlspecialchars($card['class'] ?? ''); ?>">
                <span class="label"><?php echo htmlspecialchars($card['label'] ?? ''); ?></span>
                <strong><?php echo htmlspecialchars($card['value'] ?? 0); ?></strong>
                <small><?php echo htmlspecialchars($card['caption'] ?? ''); ?></small>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="tab-shell">
        <div class="tab-navigation" role="tablist" aria-label="Account sections">
            <button class="tab-button active" type="button" data-tab="loans">Current Loans</button>
            <button class="tab-button" type="button" data-tab="holds">Active Holds</button>
            <button class="tab-button" type="button" data-tab="history">Returned Items</button>
            <button class="tab-button" type="button" data-tab="fees">Fines / Fees</button>
        </div>

        <section class="tab-panel active" id="panel-loans" data-panel="loans">
            <?php $currentLoansTable->render(); ?>
        </section>

        <section class="tab-panel" id="panel-holds" data-panel="holds">
            <?php $activeHoldsTable->render(); ?>
        </section>

        <section class="tab-panel" id="panel-history" data-panel="history">
            <?php $returnedItemsTable->render(); ?>
        </section>

        <section class="tab-panel" id="panel-fees" data-panel="fees">
            <table>
                <thead>
                    <tr>
                        <th>Fee</th>
                        <th>Reason</th>
                        <th>Due date</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($feeRows)): ?>
                        <tr>
                            <td colspan="4">No fees on file.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($feeRows as $fee): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fee['fee'] ?? 'Fee'); ?></td>
                                <td><?php echo htmlspecialchars($fee['reason'] ?? 'No reason listed'); ?></td>
                                <td><?php echo htmlspecialchars($fee['due_date'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars($fee['amount'] ?? '$0.00'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.tab-button');
        const panels = document.querySelectorAll('.tab-panel');

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                const target = this.dataset.tab;

                buttons.forEach(function (tab) {
                    tab.classList.toggle('active', tab === button);
                });

                panels.forEach(function (panel) {
                    panel.classList.toggle('active', panel.dataset.panel === target);
                });
            });
        });
    });
</script>
