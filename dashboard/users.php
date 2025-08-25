<?php include 'header.php'; ?>
<body>

  
  <?php
include '../config.php'; // DB connection


// Fetch all users
$result = $conn->query("SELECT * FROM users");
?>

<div class="content">
    <div class="users-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="index.php">Home</a> / <span>User Management</span>
        </div>

        <!-- Header with search + add -->
        <div class="users-header">
            <input type="text" id="searchUser" placeholder="Search user...">
            <button class="btn-add" onclick="document.getElementById('addUserModal').style.display='block'">+ ADD USER</button>
        </div>

        <!-- Table -->
       
                <tbody>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['username'] ?></td>
                <td><?= $row['name'] ?></td> <!-- instead of fullname -->
                <td><?= $row['user_type'] ?></td> <!-- instead of role -->
                <td>
                    <button class="btn-action view">👁</button>
                    <button class="btn-action edit">✏️</button>
                    <button class="btn-action delete">🗑</button>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="4" class="no-users">No users found.</td></tr>
    <?php endif; ?>
</tbody>

        </table>

        <!-- Table footer -->
        <div class="table-footer">
            <span>Showing 1–<?= $result->num_rows ?> of <?= $result->num_rows ?></span>
            <div class="pagination">
                <button disabled>◀</button>
                <button class="active">1</button>
                <button disabled>▶</button>
            </div>
        </div>
    </div>
</div>

<!-- ADD USER MODAL -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <span onclick="document.getElementById('addUserModal').style.display='none'" class="close">&times;</span>
        <h2>Add User</h2>
        <form action="save_user.php" method="POST">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Full Name</label>
            <input type="text" name="fullname" required>

            <label>Role</label>
            <select name="role" required>
                <option value="Admin">Admin</option>
                <option value="User">User</option>
            </select>

            <button type="submit" class="btn-submit">Save</button>
        </form>
    </div>
</div>
