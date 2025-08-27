<?php
/**
 * Direct Admin Dashboard
 * This page bypasses the API and works directly with the database
 */

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check session
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Redirect non-admin users
    header('Location: login.php');
    exit;
}

// Include database connection
require_once __DIR__ . '/config/database.php';

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDBConnection();
    
    if (isset($_POST['action'])) {
        // Handle user operations
        if ($_POST['action'] === 'add_user') {
            // Add new user
            try {
                $username = $_POST['username'];
                $email = $_POST['email'];
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $fullName = $_POST['full_name'];
                $role = $_POST['role'];
                
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $password, $fullName, $role]);
                
                $message = '<div class="alert alert-success">User added successfully!</div>';
            } catch (Exception $e) {
                $message = '<div class="alert alert-danger">Error adding user: ' . $e->getMessage() . '</div>';
            }
        } elseif ($_POST['action'] === 'edit_user') {
            // Update existing user
            try {
                $id = $_POST['id'];
                $username = $_POST['username'];
                $email = $_POST['email'];
                $fullName = $_POST['full_name'];
                $role = $_POST['role'];
                
                // Create SQL based on whether password is provided
                if (!empty($_POST['password'])) {
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, full_name = ?, role = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $password, $fullName, $role, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, role = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $fullName, $role, $id]);
                }
                
                $message = '<div class="alert alert-success">User updated successfully!</div>';
            } catch (Exception $e) {
                $message = '<div class="alert alert-danger">Error updating user: ' . $e->getMessage() . '</div>';
            }
        } elseif ($_POST['action'] === 'delete_user') {
            // Delete user
            try {
                $id = $_POST['id'];
                
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                
                $message = '<div class="alert alert-success">User deleted successfully!</div>';
            } catch (Exception $e) {
                $message = '<div class="alert alert-danger">Error deleting user: ' . $e->getMessage() . '</div>';
            }
        }
    }
}

// Get user to edit if ID is provided
$editUser = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error loading user: ' . $e->getMessage() . '</div>';
    }
}

// Get all users for display
$users = [];
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("
        SELECT 
            u.id, u.username, u.email, u.full_name, u.role, u.status, u.created_at,
            COUNT(r.id) AS total_reservations
        FROM 
            users u
        LEFT JOIN 
            reservations r ON u.id = r.user_id
        GROUP BY 
            u.id, u.username, u.email, u.full_name, u.role, u.status, u.created_at
        ORDER BY 
            u.id
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = '<div class="alert alert-danger">Error loading users: ' . $e->getMessage() . '</div>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct Admin Dashboard - Marsa Maroc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --primary-color: #003366;
            --secondary-color: #0055a4;
            --accent-color: #e63946;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: #f0f2f5;
            min-height: 100vh;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: var(--primary-color);
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .header-right {
            display: flex;
            align-items: center;
        }
        
        .user-info {
            margin-right: 20px;
            text-align: right;
        }
        
        main {
            padding: 20px;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        h1, h2, h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .alert {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        
        .btn:hover {
            background: var(--secondary-color);
        }
        
        .btn-danger {
            background: var(--accent-color);
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table, th, td {
            border: 1px solid #dee2e6;
        }
        
        th {
            background-color: var(--primary-color);
            color: white;
            padding: 12px;
            text-align: left;
        }
        
        td {
            padding: 10px;
            vertical-align: middle;
        }
        
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        
        .badge-admin {
            background-color: var(--accent-color);
        }
        
        .badge-manager {
            background-color: var(--secondary-color);
        }
        
        .badge-user {
            background-color: var(--gray-color);
        }
        
        .badge-active {
            background-color: #28a745;
        }
        
        .badge-inactive {
            background-color: var(--gray-color);
        }
        
        .badge-suspended {
            background-color: var(--accent-color);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        
        .form-actions {
            margin-top: 20px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            overflow: auto;
        }
        
        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 20px;
            width: 50%;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: var(--accent-color);
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Marsa Maroc - Direct Admin Dashboard</div>
        <div class="header-right">
            <div class="user-info">
                <div>Admin User</div>
                <small><?php echo $_SESSION['username'] ?? 'Admin'; ?></small>
            </div>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </header>
    
    <main class="container">
        <?php if (!empty($message)) echo $message; ?>
        
        <div class="card">
            <h1>User Management</h1>
            <p>This direct dashboard bypasses the API and connects directly to the database.</p>
            
            <div class="form-actions">
                <button class="btn" onclick="openAddUserForm()">Add New User</button>
            </div>
            
            <h2>Users</h2>
            
            <?php if (count($users) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Reservations</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo htmlspecialchars($user['role']); ?>">
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo htmlspecialchars($user['status']); ?>">
                                        <?php echo htmlspecialchars($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($user['created_at']))); ?></td>
                                <td><?php echo htmlspecialchars($user['total_reservations']); ?></td>
                                <td class="actions">
                                    <button class="btn" onclick="openEditUserForm(<?php echo $user['id']; ?>)">Edit</button>
                                    <form method="post" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addUserModal')">&times;</span>
            <h2>Add New User</h2>
            
            <form method="post">
                <input type="hidden" name="action" value="add_user">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" class="form-control">
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="user" selected>User</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Add User</button>
                    <button type="button" class="btn btn-danger" onclick="closeModal('addUserModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editUserModal')">&times;</span>
            <h2>Edit User</h2>
            
            <form method="post" id="editUserForm">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label for="edit_username">Username</label>
                    <input type="text" id="edit_username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_password">Password (leave empty to keep current)</label>
                    <input type="password" id="edit_password" name="password" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="edit_full_name">Full Name</label>
                    <input type="text" id="edit_full_name" name="full_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_role">Role</label>
                    <select id="edit_role" name="role" class="form-control">
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="user">User</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Save Changes</button>
                    <button type="button" class="btn btn-danger" onclick="closeModal('editUserModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Modal functionality
        function openModal(modalId) {
            document.getElementById(modalId).style.display = "block";
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }
        
        // Open add user form
        function openAddUserForm() {
            openModal('addUserModal');
        }
        
        // Open edit user form with data
        function openEditUserForm(userId) {
            // Fetch user data
            fetch('get-user.php?id=' + userId)
                .then(response => response.json())
                .then(user => {
                    // Fill form with user data
                    document.getElementById('edit_id').value = user.id;
                    document.getElementById('edit_username').value = user.username;
                    document.getElementById('edit_email').value = user.email;
                    document.getElementById('edit_full_name').value = user.full_name;
                    document.getElementById('edit_role').value = user.role;
                    
                    // Open modal
                    openModal('editUserModal');
                })
                .catch(error => {
                    console.error('Error fetching user data:', error);
                    alert('Error loading user data. Please try again.');
                });
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = "none";
            }
        }
        
        // Handle edit user directly through form submission
        <?php if ($editUser): ?>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('edit_id').value = '<?php echo $editUser['id']; ?>';
                document.getElementById('edit_username').value = '<?php echo $editUser['username']; ?>';
                document.getElementById('edit_email').value = '<?php echo $editUser['email']; ?>';
                document.getElementById('edit_full_name').value = '<?php echo $editUser['full_name']; ?>';
                document.getElementById('edit_role').value = '<?php echo $editUser['role']; ?>';
                
                openModal('editUserModal');
            });
        <?php endif; ?>
    </script>
</body>
</html>
