<?php
$summaryCards = $summaryCards ?? [];
$memberName = $memberName ?? 'Jordan Smith';
$memberNumber = $memberNumber ?? 'Member #28491';
$loanRows = $loanRows ?? [];
$holdRows = $holdRows ?? [];
$historyRows = $historyRows ?? [];
$feeRows = $feeRows ?? [];
$formBookOptions = $formBookOptions ?? [];
$formReaderOptions = $formReaderOptions ?? [];
$successMessage = $successMessage ?? '';
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

    <div class="loan-form-card">
        <h2>Book loan form</h2>
        <form method="POST" action="index.php?action=dashboard&page=account">
            <div class="loan-form-grid">
                <label>
                    <span>Reader</span>
                    <select name="reader_id" required>
                        <?php foreach ($formReaderOptions as $reader): ?>
                            <option value="<?php echo htmlspecialchars((string) ($reader['Id'] ?? $reader['id'] ?? '')); ?>">
                                <?php echo htmlspecialchars(trim(($reader['FirstName'] ?? '') . ' ' . ($reader['LastName'] ?? '')) ?: 'Reader'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    <span>Book</span>
                    <select name="loan_book_id" required>
                        <option value="">Select a book</option>
                        <?php foreach ($formBookOptions as $book): ?>
                            <option value="<?php echo htmlspecialchars((string) ($book['id'] ?? $book['Id'] ?? '')); ?>">
                                <?php echo htmlspecialchars($book['title'] ?? 'Untitled'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    <span>Due date</span>
                    <input type="date" name="due_date" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" required>
                </label>

                <label>
                    <span>Renewals</span>
                    <input type="number" name="renewals" min="0" max="2" value="0">
                </label>

                <label>
                    <span>Status</span>
                    <select name="status">
                        <?php foreach (LoanStatus::cases() as $status): ?>
                            <?php if ($status !== LoanStatus::RETURNED && $status !== LoanStatus::RETURNED_LATE): ?>
                                <option value="<?php echo htmlspecialchars($status->value); ?>"><?php echo htmlspecialchars($status->value); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <button class="primary-button" type="submit">Create loan</button>
        </form>
    </div>

    <div class="tab-shell">
        <div class="tab-navigation" role="tablist" aria-label="Account sections">
            <button class="tab-button active" type="button" data-tab="loans">Current Loans</button>
            <button class="tab-button" type="button" data-tab="holds">Active Holds</button>
            <button class="tab-button" type="button" data-tab="history">Returned Items</button>
            <button class="tab-button" type="button" data-tab="fees">Fines / Fees</button>
        </div>

        <section class="tab-panel active" id="panel-loans" data-panel="loans">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Due date</th>
                        <th>Status</th>
                        <th>Renewals</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($loanRows)): ?>
                        <tr>
                            <td colspan="5">No active loans found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($loanRows as $loan): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($loan['title'] ?? 'Untitled'); ?></td>
                                <td><?php echo htmlspecialchars($loan['due_date'] ?? '—'); ?></td>
                                <td><span class="badge <?php echo htmlspecialchars($loan['badge_class'] ?? BadgeClass::INFO->value); ?>"><?php echo htmlspecialchars($loan['status'] ?? 'Checked out'); ?></span></td>
                                <td><?php echo (int) ($loan['renewals'] ?? 0); ?> of 2</td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="return_loan_id" value="<?php echo (int) ($loan['id'] ?? 0); ?>">
                                        <button class="tiny-button primary" type="submit">Return</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="tab-panel" id="panel-holds" data-panel="holds">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Held on</th>
                        <th>Due date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($holdRows)): ?>
                        <tr>
                            <td colspan="5">No active holds found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($holdRows as $hold): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($hold['title'] ?? 'Untitled'); ?></td>
                                <td><?php echo htmlspecialchars($hold['held_on'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars($hold['due_date'] ?? '—'); ?></td>
                                <td><span class="badge <?php echo htmlspecialchars($hold['badge_class'] ?? BadgeClass::WARNING->value); ?>"><?php echo htmlspecialchars($hold['status'] ?? 'On hold'); ?></span></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="return_loan_id" value="<?php echo (int) ($hold['id'] ?? 0); ?>">
                                        <button class="tiny-button primary" type="submit">Release</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="tab-panel" id="panel-history" data-panel="history">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Due date</th>
                        <th>Returned</th>
                        <th>Status</th>
                        <th>Fee</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historyRows)): ?>
                        <tr>
                            <td colspan="5">No returned items yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($historyRows as $entry): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($entry['title'] ?? 'Untitled'); ?></td>
                                <td><?php echo htmlspecialchars($entry['due_date'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars($entry['returned_at'] ?? '—'); ?></td>
                                <td><span class="badge <?php echo htmlspecialchars(BadgeClass::INFO->value); ?>"><?php echo htmlspecialchars($entry['status'] ?? 'Returned'); ?></span></td>
                                <td><?php echo htmlspecialchars($entry['amount'] ?? '$0.00'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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
