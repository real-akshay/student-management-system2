<?php
require_once '../config.php';

if (!is_admin_logged_in()) {
    redirect('../login.php?role=admin');
    exit;
}

$student_model = new Student();

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;

if ($search) {
    $students = $student_model->search($search, $page, $limit);
    $total_records = $student_model->getSearchTotalCount($search);
} else {
    $students = $student_model->getAll($page, $limit);
    $total_records = $student_model->getTotalCount();
}

$total_pages = ceil($total_records / $limit);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/bodhivaas.css">
</head>

<body>
    <div class="container-fluid app-shell">
        <div class="app-topbar">
            <div class="brand"><div class="logo">B</div><div class="d-none d-md-block">Bodhivaas Admin</div></div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary" data-toggle-sidebar><i class="bi bi-list"></i></button>
                <button class="btn btn-sm btn-outline-secondary" data-toggle-theme><i class="bi bi-moon-fill"></i></button>
                <div class="dropdown">
                    <a class="text-muted text-decoration-none dropdown-toggle" href="#" data-bs-toggle="dropdown"><?php echo $_SESSION['admin_username']; ?></a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="app-layout">
            <aside class="app-sidebar">
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> <span class="nav-label">Dashboard</span></a>
                    <a class="nav-link active" href="manage_students.php"><i class="bi bi-people"></i> <span class="nav-label">Students</span></a>
                    <a class="nav-link" href="manage_courses.php"><i class="bi bi-book"></i> <span class="nav-label">Courses</span></a>
                    <a class="nav-link" href="enrollments.php"><i class="bi bi-clipboard-check"></i> <span class="nav-label">Enrollments</span></a>
                    <a class="nav-link" href="notifications.php"><i class="bi bi-bell"></i> <span class="nav-label">Notifications</span></a>
                </nav>
            </aside>

            <main class="app-main">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Manage Students</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus-circle me-1"></i>Add Student
                    </button>
                </div>

                <?php if (isset($_SESSION['success'])): show_alert($_SESSION['success'], 'success'); unset($_SESSION['success']); endif; ?>
                <?php if (isset($_SESSION['error'])): show_alert($_SESSION['error'], 'danger'); unset($_SESSION['error']); endif; ?>

                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" class="row g-2">
                            <div class="col-md-10">
                                <input type="text" class="form-control" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-modern mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($students) > 0): ?>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                                <td><?php echo htmlspecialchars($student['phone'] ?? '-'); ?></td>
                                                <td><?php echo format_date($student['created_at']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateModal" onclick='editStudent(<?php echo json_encode($student); ?>)'>
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form action="actions/student_actions.php" method="POST" class="d-inline" onsubmit="return confirm('Delete this student?')">
                                                        <input type="hidden" name="delete_student" value="<?= htmlspecialchars($student['id']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No students found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if($total_pages > 1): ?>
                    <div class="card-footer">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php if($i == $page) echo 'active'; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="actions/student_actions.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="admin_student_crud" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="actions/student_actions.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="student_id" id="up_student_id">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="up_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="up_email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" id="up_phone">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" id="up_address" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="admin_student_crud" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editStudent(student) {
            document.getElementById('up_student_id').value = student.id;
            document.getElementById('up_name').value = student.name;
            document.getElementById('up_email').value = student.email;
            document.getElementById('up_phone').value = student.phone;
            document.getElementById('up_address').value = student.address;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/bodhivaas.js"></script>
</body>
</html>