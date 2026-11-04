<?php
$summaryCards = $summaryCards ?? [];
$memberName = $memberName ?? 'Jordan Smith';
$memberNumber = $memberNumber ?? 'Member #28491';
$loanRows = $loanRows ?? [];
$feeRows = $feeRows ?? [];
$formBookOptions = $formBookOptions ?? [];
$formReaderOptions = $formReaderOptions ?? [];
$successMessage = $successMessage ?? '';
?>

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
                        <option value="Checked out">Checked out</option>
                        <option value="Due soon">Due soon</option>
                        <option value="On time">On time</option>
                        <option value="Overdue">Overdue</option>
                    </select>
                </label>
            </div>
            <button class="primary-button" type="submit">Create loan</button>
        </form>
    </div>

    <div class="tab-shell">
        <div class="tab-navigation" role="tablist" aria-label="Account sections">
            <button class="tab-button active" type="button" data-tab="loans">Current Loans</button>
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
                                <td><span class="badge <?php echo htmlspecialchars($loan['badge_class'] ?? 'badge-info'); ?>"><?php echo htmlspecialchars($loan['status'] ?? 'Checked out'); ?></span></td>
                                <td><?php echo (int) ($loan['renewals'] ?? 0); ?> of 2</td>
                                <td><button class="tiny-button primary" type="button">Renew</button></td>
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

<style>
    .account-page {
        width: 100%;
        min-height: 100vh;
        padding: 36px;
        background: #f5f7f8;
        color: #1d2a36;
    }

    .account-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding: 8px 2px;
    }

    .eyebrow {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #5b6d7d;
        margin-bottom: 6px;
    }

    .account-header h1 {
        font-size: 2.25rem;
        font-weight: 700;
    }

    .account-user {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fff;
        padding: 12px 16px;
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(27, 39, 51, 0.06);
    }

    .avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1fa37a, #2ec893);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        text-transform: uppercase;
    }

    .account-user strong,
    .account-user span {
        display: block;
    }

    .account-user span {
        color: #5b6d7d;
        font-size: 0.8rem;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 28px;
    }

    .summary-card {
        background: #fff;
        border-radius: 18px;
        padding: 22px 20px;
        box-shadow: 0 10px 24px rgba(29, 42, 54, 0.05);
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .summary-card.accent {
        background: linear-gradient(135deg, #e7f8f1, #f5fff9);
    }

    .summary-card.warning {
        background: linear-gradient(135deg, #fff4e3, #fffaf2);
    }

    .summary-card.success {
        background: linear-gradient(135deg, #ebf9f7, #f7fffe);
    }

    .summary-card .label {
        color: #5b6d7d;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .summary-card strong {
        font-size: 2rem;
        line-height: 1;
    }

    .summary-card small {
        color: #5b6d7d;
        font-size: 0.82rem;
    }

    .loan-form-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 10px 24px rgba(29, 42, 54, 0.05);
        padding: 22px;
        margin-bottom: 28px;
    }

    .loan-form-card h2 {
        margin-bottom: 16px;
        font-size: 1.4rem;
    }

    .loan-form-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 16px;
    }

    .loan-form-grid label {
        display: flex;
        flex-direction: column;
        gap: 8px;
        font-weight: 600;
        color: #395364;
        font-size: 0.9rem;
    }

    .loan-form-grid input,
    .loan-form-grid select {
        width: 100%;
        border: 1px solid #d7e1e7;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 0.95rem;
        background: #fff;
    }

    .primary-button {
        margin-top: 18px;
        border: none;
        border-radius: 10px;
        background: linear-gradient(135deg, #1fa37a, #2ec893);
        color: #fff;
        padding: 12px 18px;
        font-size: 0.96rem;
        font-weight: 700;
        cursor: pointer;
    }

    .notice {
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .notice.success {
        background: #dff9ef;
        color: #0a6d4a;
        border: 1px solid #b7e9d3;
    }

    .tab-shell {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 10px 24px rgba(29, 42, 54, 0.05);
        overflow: hidden;
    }

    .tab-navigation {
        display: flex;
        border-bottom: 1px solid #e7edf2;
        background: #f8fafb;
    }

    .tab-button {
        border: none;
        background: transparent;
        padding: 14px 18px;
        font-weight: 700;
        color: #5b6d7d;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .tab-button.active {
        color: #1fa37a;
        background: #eefaf4;
        border-bottom: 3px solid #1fa37a;
    }

    .tab-panel {
        display: none;
        padding: 20px;
    }

    .tab-panel.active {
        display: block;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 14px 12px;
        text-align: left;
        border-bottom: 1px solid #edf1f3;
    }

    th {
        color: #5b6d7d;
        font-size: 0.78rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    td {
        font-size: 0.95rem;
        color: #1d2a36;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        font-size: 0.76rem;
        font-weight: 700;
        border-radius: 999px;
    }

    .badge-info {
        background: #e9f3ff;
        color: #1d5ec7;
    }

    .badge-warning {
        background: #fff1d8;
        color: #a86100;
    }

    .badge-alert {
        background: #ffe5e5;
        color: #c61d1d;
    }

    .tiny-button {
        border: none;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
    }

    .tiny-button.primary {
        background: #e8f8f1;
        color: #0f7a5b;
    }

    .tiny-button.danger {
        background: #ffe6e6;
        color: #bb2d2d;
    }

    @media (max-width: 980px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .loan-form-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 620px) {
        .summary-grid,
        .loan-form-grid {
            grid-template-columns: 1fr;
        }

        .account-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
    }
</style>
