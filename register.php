<?php
require_once 'config.php';

if (is_student_logged_in()) {
    redirect('student/dashboard.php');
}

$error = isset($_GET['error']) ? sanitize_input($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="assets/css/bodhivaas.css">
</head>

<body>
        <div class="container-fluid app-shell">
            <div class="app-topbar">
                <div class="brand"><div class="logo">B</div><div class="d-none d-md-block">Bodhivaas</div></div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-secondary" data-toggle-theme><i class="bi bi-moon-fill"></i></button>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-center" style="min-height:calc(100vh - 64px);">
                <div style="max-width:720px;width:100%;padding:1rem;">
                    <div class="card glass-card border-0">
                        <div class="row g-0">
                            <div class="col-md-6 p-4 d-flex flex-column justify-content-center">
                                <div class="text-start mb-3">
                                    <h3 class="mb-1">Create your account</h3>
                                    <p class="muted small mb-0">Join Bodhivaas to manage courses, results, and your profile.</p>
                                </div>

                                <?php if ($error): show_alert($error, 'danger'); endif; ?>

                                <form method="POST" action="php/code.php">
                                    <input type="hidden" name="action" value="register">
                                    <div class="mb-2">
                                        <label class="form-label small muted">Full name</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small muted">Email</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small muted">Phone</label>
                                        <input type="tel" class="form-control" name="phone">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small muted">Address</label>
                                        <textarea class="form-control" name="address" rows="2"></textarea>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small muted">Password</label>
                                        <input type="password" class="form-control" name="password" minlength="6" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small muted">Confirm Password</label>
                                        <input type="password" class="form-control" name="confirm_password" minlength="6" required>
                                    </div>
                                    <div class="d-grid mb-2">
                                        <button type="submit" name="register" class="btn btn-primary">Create account</button>
                                    </div>
                                    <div class="small muted">Already a user? <a href="login.php?role=student">Sign in</a></div>
                                </form>
                            </div>
                            <div class="col-md-6 p-4 d-none d-md-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,var(--brand),var(--brand-2));border-top-right-radius:14px;border-bottom-right-radius:14px;color:white;">
                                <div class="text-center">
                                    <h4 class="mb-1">Welcome to Bodhivaas</h4>
                                    <p class="small mb-0">A modern, responsive Student ERP designed for clarity and speed.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/bodhivaas.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>

</html>