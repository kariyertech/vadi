<?php
session_start();
require_once 'config.php';

// Debug information
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Status Column Check and Add
try {
    $checkStatusColumn = $db->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'projects' AND column_name = 'status'");
    if ($checkStatusColumn->rowCount() == 0) {
        $db->exec("ALTER TABLE projects ADD COLUMN status VARCHAR(20) DEFAULT 'unread'");
        echo "<div class='alert alert-success'>Status sütunu veritabanına eklendi!</div>";
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Veritabanı güncellemesi sırasında hata: " . $e->getMessage() . "</div>";
}

// Logout handling
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit();
}

// Login handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    try {
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debug information
        if ($user) {
            $verify = verify_password($password, $user['password']);
            if (!$verify) {
                $loginError = "Password verification failed. Please try again.";
            } else {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_email'] = $user['email'];
                header('Location: admin.php');
                exit();
            }
        } else {
            $loginError = "User not found. Please check your email.";
        }
    } catch (PDOException $e) {
        $loginError = "Database error: " . $e->getMessage();
    }
}

// Update project status
if (isset($_SESSION['admin_id']) && isset($_POST['update_status'])) {
    $projectId = sanitize($_POST['project_id']);
    $newStatus = sanitize($_POST['status']);
    
    $stmt = $db->prepare("UPDATE projects SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $projectId]);
}

// Fetch projects with filters
$where = [];
$params = [];

if (isset($_GET['status']) && $_GET['status']) {
    $where[] = "status = ?";
    $params[] = $_GET['status'];
}

if (isset($_GET['search']) && $_GET['search']) {
    $search = '%' . $_GET['search'] . '%';
    $where[] = "(project_name ILIKE ? OR email ILIKE ? OR array_to_string(keyword_tags, ',') ILIKE ?)";
    $params = array_merge($params, [$search, $search, $search]);
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$query = "SELECT * FROM projects $whereClause ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <?php if (!isset($_SESSION['admin_id'])): ?>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h2 class="mb-4">Admin Login</h2>
                    <?php if (isset($loginError)): ?>
                        <div class="alert alert-danger"><?php echo $loginError; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary">Login</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Project Management</h2>
                <form method="POST" class="ms-auto">
                    <button type="submit" name="logout" class="btn btn-danger">Logout</button>
                </form>
            </div>
            
            <!-- Filters -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="unread"<?php echo (isset($_GET['status']) && $_GET['status'] == 'unread') ? ' selected' : ''; ?>>Unread</option>
                        <option value="read"<?php echo (isset($_GET['status']) && $_GET['status'] == 'read') ? ' selected' : ''; ?>>Read</option>
                        <option value="approved"<?php echo (isset($_GET['status']) && $_GET['status'] == 'approved') ? ' selected' : ''; ?>>Approved</option>
                        <option value="denied"<?php echo (isset($_GET['status']) && $_GET['status'] == 'denied') ? ' selected' : ''; ?>>Denied</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search projects..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
            
            <!-- Projects Table -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Name Surname</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?php echo $project['id']; ?></td>
                                <td><?php echo htmlspecialchars($project['email']); ?></td>
                                <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                                <td>
                                    <?php 
                                    $type = htmlspecialchars($project['project_type']);
                                    if ($type == 'Paper') {
                                        echo 'Paper';
                                    } elseif ($type == 'Project') {
                                        echo 'Project';
                                    } else {
                                        echo $type;
                                    }
                                    ?>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="unread"<?php echo (isset($project['status']) && $project['status'] == 'unread') ? ' selected' : ''; ?>>Unread</option>
                                            <option value="read"<?php echo (isset($project['status']) && $project['status'] == 'read') ? ' selected' : ''; ?>>Read</option>
                                            <option value="approved"<?php echo (isset($project['status']) && $project['status'] == 'approved') ? ' selected' : ''; ?>>Approved</option>
                                            <option value="denied"<?php echo (isset($project['status']) && $project['status'] == 'denied') ? ' selected' : ''; ?>>Denied</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($project['created_at'])); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#projectModal<?php echo (int)$project['id']; ?>">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                            
                            <!-- Project Details Modal -->
                            <div class="modal fade" id="projectModal<?php echo (int)$project['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">Project Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-4">
                                                            <strong class="text-primary">Project Type:</strong>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <?php 
                                                            $type = htmlspecialchars($project['project_type']);
                                                            if ($type == 'Paper') {
                                                                echo 'Paper';
                                                            } elseif ($type == 'Project') {
                                                                echo 'Project';
                                                            } else {
                                                                echo $type;
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-md-4">
                                                            <strong class="text-primary">Purpose:</strong>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <?php echo nl2br(htmlspecialchars($project['project_goal'])); ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-md-4">
                                                            <strong class="text-primary">Description:</strong>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <?php echo nl2br(htmlspecialchars($project['project_description'])); ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-md-4">
                                                            <strong class="text-primary">Requirements:</strong>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <?php echo nl2br(htmlspecialchars($project['project_requirements'])); ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-md-4">
                                                            <strong class="text-primary">Keywords:</strong>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <?php 
                                                            $keywords = trim($project['keyword_tags'], '{}');
                                                            if (!empty($keywords)) {
                                                                $keywordArray = array_map('trim', explode(',', $keywords));
                                                                foreach ($keywordArray as $keyword) {
                                                                    echo '<span class="badge bg-secondary me-1 mb-1">' . htmlspecialchars($keyword) . '</span>';
                                                                }
                                                            } else {
                                                                echo '<em>Not specified</em>';
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if (!empty($project['file_path'])): ?>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <strong class="text-primary">Attachment:</strong>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <a href="<?php echo htmlspecialchars($project['file_path']); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                                <i class="bi bi-file-earmark"></i> View File
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="card">
                                                <div class="card-header bg-success text-white">
                                                    <strong>Status Information</strong>
                                                </div>
                                                <div class="card-body">
                                                    <form method="POST">
                                                        <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <strong>Current Status:</strong>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <select name="status" class="form-select" onchange="this.form.submit()">
                                                                    <option value="unread"<?php echo (isset($project['status']) && $project['status'] == 'unread') ? ' selected' : ''; ?>>Unread</option>
                                                                    <option value="read"<?php echo (isset($project['status']) && $project['status'] == 'read') ? ' selected' : ''; ?>>Read</option>
                                                                    <option value="approved"<?php echo (isset($project['status']) && $project['status'] == 'approved') ? ' selected' : ''; ?>>Approved</option>
                                                                    <option value="denied"<?php echo (isset($project['status']) && $project['status'] == 'denied') ? ' selected' : ''; ?>>Denied</option>
                                                                </select>
                                                                <input type="hidden" name="update_status" value="1">
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 