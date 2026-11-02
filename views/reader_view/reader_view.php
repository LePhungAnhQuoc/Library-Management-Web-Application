<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Library management application</title>
    <style>
        <?php include 'reader_view.css'; ?>
    </style>
</head>

<body>
    <div class="page-content">
        <h1 class="page-title">Reader Management</h1>

        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="" class="reader-form">
                <input type="text" name="firstName" placeholder="Type a reader first name..." required>
                <input type="text" name="lastName" placeholder="Type a reader last name..." required>
                <input type="email" name="email" placeholder="Type a reader email..." required>
                <input type="date" name="membershipDate">
                <select name="status">
                    <option value="active" selected>Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button type="submit">Add new reader</button>
            </form>
        </div>

        <div class="card">
            <h2>Saved Readers</h2>
            <table class="reader-table">
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Membership Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($readers)): ?>
                        <?php foreach ($readers as $item): ?>
                            <tr>
                                <td class="reader-name"><?php echo htmlspecialchars($item['FirstName'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($item['LastName'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($item['Email'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($item['MembershipDate'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($item['Status'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-message">No readers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
