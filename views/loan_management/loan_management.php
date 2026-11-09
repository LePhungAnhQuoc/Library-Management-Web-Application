<?php
$formBookOptions = $formBookOptions ?? [];
$formReaderOptions = $formReaderOptions ?? [];
$loanRows = $loanRows ?? [];
$successMessage = $successMessage ?? '';

require_once __DIR__ . '/loan_slip_table.php';

$loanSlipTable = new LoanSlipTable();
$loanSlipTable->loanRows = $loanRows;
?>

<link rel="stylesheet" href="../views/patron_dashboard/patron_dashboard.css?v=<?php echo filemtime(__DIR__ . '/../patron_dashboard/patron_dashboard.css'); ?>">

<div class="account-page">
    <div class="account-header">
        <div>
            <p class="eyebrow">Library operations</p>
            <h1>Loan Management</h1>
        </div>
    </div>

    <?php if ($successMessage !== ''): ?>
        <div class="notice success"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>

    <div class="loan-form-card">
        <h2>Create a new loan</h2>
        <form method="POST" action="index.php?action=dashboard&page=loan">
            <div class="loan-form-grid">
                <label>
                    <span>Reader</span>
                    <select name="reader_id" required>
                        <?php foreach ($formReaderOptions as $reader): ?>
                            <option
                                value="<?php echo htmlspecialchars((string) ($reader['id'] ?? '')); ?>">
                                <?php echo htmlspecialchars(trim(($reader['first_name'] ?? '') . ' ' . ($reader['last_name'] ?? '')) ?: 'Reader'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    <span>Book</span>
                    <select name="loan_book_id" required>
                        <option value="">Select a book</option>
                        <?php foreach ($formBookOptions as $book): ?>
                            <option value="<?php echo htmlspecialchars((string) ($book['id'] ?? '')); ?>">
                                <?php echo htmlspecialchars($book['title'] ?? 'Untitled'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    <span>Due date</span>
                    <input type="date" name="due_date"
                        value="<?php echo date('Y-m-d', strtotime('+' . Constants::LOAN_DAYS . ' days')); ?>" required>
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
                                <option value="<?php echo htmlspecialchars($status->value); ?>">
                                    <?php echo htmlspecialchars($status->value); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <button class="primary-button" type="submit">Create loan</button>
        </form>
    </div>

    <?php $loanSlipTable->render(); ?>
</div>