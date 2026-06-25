<?php
require_once 'config.php';
$role = isset($_GET['role']) ? sanitize_input($_GET['role']) : 'student';
if (!in_array($role, ['admin', 'student'])) $role = 'student';
if (is_admin_logged_in())   redirect('admin/dashboard.php');
elseif (is_student_logged_in()) redirect('student/dashboard.php');
$isAdmin = $role === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo ucfirst($role); ?> Login — Bodhivaas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/bodhivaas.css">
  <style>
    body{min-height:100vh;display:flex;flex-direction:column;background:var(--bg);}
    .login-bg{
      position:fixed;inset:0;z-index:0;
      background:
        radial-gradient(ellipse 80% 60% at 10% 0%,   rgba(108,92,231,0.22) 0%, transparent 55%),
        radial-gradient(ellipse 60% 50% at 90% 100%, rgba(162,155,254,0.18) 0%, transparent 55%),
        radial-gradient(ellipse 40% 40% at 50% 50%,  rgba(253,121,168,0.08) 0%, transparent 60%),
        var(--bg);
    }
    .login-wrap{position:relative;z-index:1;flex:1;display:flex;align-items:center;justify-content:center;padding:20px;}
    .login-panel{
      width:100%;max-width:440px;
      background:var(--glass);
      backdrop-filter:blur(28px) saturate(200%);
      -webkit-backdrop-filter:blur(28px) saturate(200%);
      border:1px solid var(--glass-border);
      border-radius:var(--radius-lg);
      padding:40px 36px;
      box-shadow:var(--shadow-lg),inset 0 1px 0 rgba(255,255,255,0.8);
      animation:panelIn .5s cubic-bezier(.34,1.56,.64,1) both;
    }
    @keyframes panelIn{from{opacity:0;transform:translateY(30px) scale(.97)}to{opacity:1;transform:none}}
    .role-tabs{display:flex;gap:8px;margin-bottom:28px;background:rgba(108,92,231,0.06);border-radius:999px;padding:4px;}
    .role-tab{flex:1;text-align:center;padding:8px;border-radius:999px;font-size:.88rem;font-weight:600;cursor:pointer;text-decoration:none;color:var(--muted);transition:all .25s;}
    .role-tab.active{background:linear-gradient(135deg,var(--brand),var(--brand-2));color:#fff;box-shadow:0 4px 14px rgba(108,92,231,0.35);}
    .login-icon{width:62px;height:62px;border-radius:16px;background:linear-gradient(135deg,var(--brand),var(--brand-2));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.7rem;box-shadow:0 8px 24px rgba(108,92,231,0.4);margin-bottom:20px;}
    .floating-label{position:relative;}
    .floating-label label{position:absolute;top:50%;left:14px;transform:translateY(-50%);color:var(--muted);font-size:.88rem;pointer-events:none;transition:all .2s;background:transparent;padding:0 4px;}
    .floating-label input:focus + label,
    .floating-label input:not(:placeholder-shown) + label{top:0;font-size:.72rem;color:var(--brand);background:white;border-radius:4px;}
    .input-group-glass{position:relative;}
    .input-group-glass .input-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.95rem;pointer-events:none;}
    .input-group-glass .form-control{padding-left:42px;}
    .input-group-glass .toggle-pass{position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--muted);cursor:pointer;background:none;border:none;font-size:.95rem;padding:0;}
    .demo-badge{background:rgba(108,92,231,0.06);border:1px solid rgba(108,92,231,0.15);border-radius:var(--radius-sm);padding:12px 14px;font-size:.8rem;}
  </style>
</head>
<body>
<div class="login-bg"></div>

<!-- Topbar -->
<header style="position:relative;z-index:10;padding:14px 24px;display:flex;align-items:center;justify-content:space-between;">
  <a href="index.php" class="brand text-decoration-none">
    <div class="logo">B</div><span>Bodhivaas</span>
  </a>
  <button class="btn btn-sm btn-outline-secondary rounded-pill" data-toggle-theme>
    <i class="bi bi-moon-fill me-1"></i> Theme
  </button>
</header>

<div class="login-wrap">
  <div class="login-panel">
    <!-- Role switcher -->
    <div class="role-tabs">
      <a href="login.php?role=student" class="role-tab <?php echo !$isAdmin ? 'active' : ''; ?>">
        <i class="bi bi-person me-1"></i> Student
      </a>
      <a href="login.php?role=admin" class="role-tab <?php echo $isAdmin ? 'active' : ''; ?>">
        <i class="bi bi-shield-check me-1"></i> Admin
      </a>
    </div>

    <!-- Icon + heading -->
    <div class="login-icon">
      <i class="bi bi-<?php echo $isAdmin ? 'shield-lock' : 'mortarboard'; ?>"></i>
    </div>
    <h4 class="fw-800 mb-1"><?php echo $isAdmin ? 'Admin Portal' : 'Student Login'; ?></h4>
    <p class="muted small mb-4">Secure access to your <?php echo $isAdmin ? 'management' : 'learning'; ?> dashboard</p>

    <?php if (isset($_SESSION['error'])): show_alert($_SESSION['error'], 'danger'); unset($_SESSION['error']); endif; ?>

    <form method="POST" action="php/code.php">
      <input type="hidden" name="role" value="<?php echo $role; ?>">

      <div class="mb-3">
        <label class="form-label small fw-600"><?php echo $isAdmin ? 'Username' : 'Email Address'; ?></label>
        <div class="input-group-glass">
          <i class="input-icon bi bi-<?php echo $isAdmin ? 'person' : 'envelope'; ?>"></i>
          <input type="<?php echo $isAdmin ? 'text' : 'email'; ?>" name="email"
            class="form-control"
            placeholder="<?php echo $isAdmin ? 'Enter username' : 'you@example.com'; ?>"
            required autocomplete="username">
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label small fw-600">Password</label>
        <div class="input-group-glass">
          <i class="input-icon bi bi-lock"></i>
          <input type="password" name="password" id="passField"
            class="form-control"
            placeholder="Enter password"
            required autocomplete="current-password">
          <button type="button" class="toggle-pass" onclick="togglePass()">
            <i class="bi bi-eye" id="eyeIcon"></i>
          </button>
        </div>
      </div>

      <div class="d-grid mb-3">
        <button type="submit" class="btn btn-primary btn-lg" style="border-radius:999px;">
          Sign In <i class="bi bi-arrow-right ms-1"></i>
        </button>
      </div>

      <div class="d-flex justify-content-between small muted mb-4">
        <?php if (!$isAdmin): ?>
          <a href="register.php" class="text-decoration-none" style="color:var(--brand);">Create account</a>
        <?php else: ?><span></span><?php endif; ?>
        <a href="index.php" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i>Home</a>
      </div>
    </form>

    <div class="demo-badge">
      <i class="bi bi-info-circle me-1" style="color:var(--brand);"></i>
      <strong>Demo credentials —</strong>
      <?php if ($isAdmin): ?>
        Username: <code>admin</code> &nbsp;|&nbsp; Password: <code>admin123</code>
      <?php else: ?>
        Email: <code>rahul.sharma@student.com</code> &nbsp;|&nbsp; Password: <code>student123</code>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/bodhivaas.js"></script>
<script>
  function togglePass() {
    const f = document.getElementById('passField');
    const i = document.getElementById('eyeIcon');
    if (f.type === 'password') { f.type = 'text';     i.className = 'bi bi-eye-slash'; }
    else                       { f.type = 'password'; i.className = 'bi bi-eye'; }
  }
</script>
</body>
</html>
