<?php if ($this->title !== ''): ?>
    <section class="tab-shell loan-table-shell">
        <h2 class="loan-table-title"><?php echo htmlspecialchars($this->title); ?></h2>
<?php endif; ?>
    <div class="loan-table-scroll">
        <table>
            <thead>
                <tr>
                    <?php foreach ($this->columns as $column): ?>
                        <th><?php echo htmlspecialchars($column['label'] ?? ''); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($this->loanRows)): ?>
                    <tr>
                        <td colspan="<?php echo max(count($this->columns), 1); ?>">
                            <?php echo htmlspecialchars($this->emptyMessage); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($this->loanRows as $loan): ?>
                        <tr>
                            <?php foreach ($this->columns as $column): ?>
                                <?php
                                $type = $column['type'] ?? 'text';
                                $key = $column['key'] ?? '';
                                $value = $key !== '' ? ($loan[$key] ?? '') : '';
                                ?>
                                <td>
                                    <?php if ($type === 'badge'): ?>
                                        <span class="badge <?php echo htmlspecialchars($loan['badge_class'] ?? BadgeClass::INFO->value); ?>">
                                            <?php echo htmlspecialchars((string) ($value ?: '—')); ?>
                                        </span>
                                    <?php elseif ($type === 'action'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="return_loan_id" value="<?php echo (int) ($loan['id'] ?? 0); ?>">
                                            <button class="tiny-button primary" type="submit">
                                                <?php echo htmlspecialchars($this->getActionLabel($loan)); ?>
                                            </button>
                                        </form>
                                    <?php elseif (array_key_exists('suffix', $column)): ?>
                                        <?php echo htmlspecialchars((string) $value . $column['suffix']); ?>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars((string) ($value !== '' ? $value : '—')); ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php if ($this->title !== ''): ?>
    </section>
<?php endif; ?>
