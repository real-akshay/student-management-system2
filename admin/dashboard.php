<?php
require_once '../config.php';
if (!is_admin_logged_in()) redirect('../login.php?role=admin');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$r = $conn->query("SELECT COUNT(*) AS c FROM students");               $total_students    = $r ? (int)$r->fetch_assoc()['c'] : 0;
$r = $conn->query("SELECT COUNT(*) AS c FROM courses");                $total_courses     = $r ? (int)$r->fetch_assoc()['c'] : 0;
$r = $conn->query("SELECT COUNT(*) AS c FROM enrollments WHERE status='active'"); $total_enrollments = $r ? (int)$r->fetch_assoc()['c'] : 0;
$r = $conn->query("SELECT COUNT(*) AS c FROM students WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"); $new_this_month = $r ? (int)$r->fetch_assoc()['c'] : 0;

$recent_students = [];
$r = $conn->query("SELECT id, name, email, created_at FROM students ORDER BY created_at DESC LIMIT 5");
if ($r) while ($row = $r->fetch_assoc()) $recent_students[] = $row;

$recent_enrollments = [];
$r = $conn->query("SELECT e.id, s.name AS student_name, c.course_name, c.course_code, e.enrollment_date
  FROM enrollments e JOIN students s ON e.student_id=s.id JOIN courses c ON e.course_id=c.id
  ORDER BY e.enrollment_date DESC LIMIT 6");
if ($r) while ($row = $r->fetch_assoc()) $recent_enrollments[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard — Bodhivaas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/bodhivaas.css">
  <style>
    .quick-action{
      background:var(--glass);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);
      border:1px solid var(--glass-border);border-radius:var(--radius);
      padding:18px;text-align:center;text-decoration:none;color:inherit;
      transition:transform .25s var(--ease-spring),box-shadow .25s;display:block;
    }
    .quick-action:hover{transform:translateY(-5px);box-shadow:var(--shadow-lg);color:var(--brand);}
    .quick-action i{font-size:1.6rem;color:var(--brand);}
    .quick-action .qa-label{font-size:.82rem;font-weight:600;margin-top:8px;color:var(--muted);}
    .enroll-row{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid rgba(108,92,231,0.06);}
    .enroll-row:last-child{border-bottom:none;}
    .enroll-avatar{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,var(--brand),var(--brand-2));display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;font-weight:700;flex-shrink:0;}
  </style>
</head>
<body>
<div class="container-fluid app-shell">
  <!-- Topbar -->
  <div class="app-topbar">
    <div class="brand"><div class="logo">B</div><span class="d-none d-md-block">Bodhivaas Admin</span></div>
    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-sm btn-outline-secondary rounded-pill" data-toggle-sidebar>
        <i class="bi bi-layout-sidebar"></i>
      </button>
      <button class="btn btn-sm btn-outline-secondary rounded-pill" data-toggle-theme>
        <i class="bi bi-moon-fill"></i>
      </button>
      <div class="dropdown">
        <button class="btn btn-sm d-flex align-items-center gap-2 border-0" data-bs-toggle="dropdown"
          style="background:rgba(108,92,231,0.08);border-radius:999px;padding:6px 14px;">
          <div style="width:26px;height:26px;border-radius:7px;background:linear-gradient(135deg,var(--brand),var(--brand-2));display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;">
            <?php echo strtoupper(substr($_SESSION['admin_username'],0,1)); ?>
          </div>
          <span class="d-none d-md-inline small fw-600"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
          <i class="bi bi-chevron-down" style="font-size:.7rem;"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius:var(--radius);backdrop-filter:blur(16px);background:var(--glass);">
          <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="app-layout">
    <!-- Sidebar -->
    <aside class="app-sidebar">
      <nav class="nav flex-column gap-1">
        <a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2"></i><span class="nav-label ms-1"> Dashboard</span></a>
        <a class="nav-link" href="manage_students.php"><i class="bi bi-people"></i><span class="nav-label ms-1"> Students</span></a>
        <a class="nav-link" href="manage_courses.php"><i class="bi bi-book"></i><span class="nav-label ms-1"> Courses</span></a>
        <a class="nav-link" href="enrollments.php"><i class="bi bi-clipboard-check"></i><span class="nav-label ms-1"> Enrollments</span></a>
        <a class="nav-link" href="notifications.php"><i class="bi bi-bell"></i><span class="nav-label ms-1"> Notifications</span></a>
        <hr style="border-color:rgba(108,92,231,0.1);margin:8px 0;">
        <a class="nav-link" href="profile.php"><i class="bi bi-person-circle"></i><span class="nav-label ms-1"> Profile</span></a>
        <a class="nav-link text-danger" href="../logout.php"><i class="bi bi-box-arrow-right"></i><span class="nav-label ms-1"> Logout</span></a>
      </nav>
    </aside>

    <main class="app-main">
      <!-- Page header -->
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
          <h4 class="mb-0 fw-800">Dashboard</h4>
          <small class="muted">Good <?php echo date('G') < 12 ? 'morning' : (date('G') < 17 ? 'afternoon' : 'evening'); ?>, <?php echo htmlspecialchars($_SESSION['admin_username']); ?> 👋</small>
        </div>
        <div class="d-flex gap-2">
          <a href="manage_students.php" class="btn btn-sm btn-primary rounded-pill"><i class="bi bi-plus me-1"></i>Add Student</a>
          <button class="btn btn-sm btn-outline-secondary rounded-pill"><i class="bi bi-download me-1"></i>Export</button>
        </div>
      </div>

      <!-- Stat cards -->
      <div class="dash-cards">
        <div class="dash-card">
          <div><div class="meta">Total Students</div><div class="value"><?php echo $total_students; ?></div></div>
          <div class="iconbox"><i class="bi bi-people-fill"></i></div>
        </div>
        <div class="dash-card">
          <div><div class="meta">Total Courses</div><div class="value"><?php echo $total_courses; ?></div></div>
          <div class="iconbox" style="background:linear-gradient(135deg,#00cec9,#00b894);"><i class="bi bi-book-fill"></i></div>
        </div>
        <div class="dash-card">
          <div><div class="meta">Active Enrollments</div><div class="value"><?php echo $total_enrollments; ?></div></div>
          <div class="iconbox" style="background:linear-gradient(135deg,#fdcb6e,#e17055);"><i class="bi bi-clipboard-check-fill"></i></div>
        </div>
        <div class="dash-card">
          <div><div class="meta">New This Month</div><div class="value"><?php echo $new_this_month; ?></div></div>
          <div class="iconbox" style="background:linear-gradient(135deg,#fd79a8,#e84393);"><i class="bi bi-calendar2-plus-fill"></i></div>
        </div>
      </div>

      <!-- Quick actions -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <a href="manage_students.php" class="quick-action">
            <i class="bi bi-person-plus"></i>
            <div class="qa-label">Add Student</div>
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="manage_courses.php" class="quick-action">
            <i class="bi bi-journal-plus"></i>
            <div class="qa-label">Add Course</div>
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="enrollments.php" class="quick-action">
            <i class="bi bi-clipboard-plus"></i>
            <div class="qa-label">Enroll Student</div>
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="notifications.php" class="quick-action">
            <i class="bi bi-megaphone"></i>
            <div class="qa-label">Send Notice</div>
          </a>
        </div>
      </div>

      <!-- Charts & tables row -->
      <div class="row g-3">
        <div class="col-lg-7">
          <!-- Chart card -->
          <div class="card glass-card mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="mb-0 fw-700">Attendance Trend</h6>
              <span class="pill">Last 14 days</span>
            </div>
            <canvas id="attendanceChart" height="160"></canvas>
          </div>

          <!-- Recent students table -->
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-700">Recent Students</h6>
              <a href="manage_students.php" class="btn btn-sm btn-primary rounded-pill">View All</a>
            </div>
            <div class="table-responsive">
              <table class="table table-modern mb-0">
                <thead><tr><th>Name</th><th>Email</th><th>Joined</th><th>Status</th></tr></thead>
                <tbody>
                  <?php if (!empty($recent_students)): foreach ($recent_students as $s): ?>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="profile-avatar" style="width:32px;height:32px;font-size:.8rem;border-radius:8px;flex-shrink:0;">
                            <?php echo strtoupper(substr($s['name'],0,1)); ?>
                          </div>
                          <?php echo htmlspecialchars($s['name']); ?>
                        </div>
                      </td>
                      <td class="muted small"><?php echo htmlspecialchars($s['email']); ?></td>
                      <td class="muted small"><?php echo date('d M Y', strtotime($s['created_at'])); ?></td>
                      <td><span class="badge bg-success">Active</span></td>
                    </tr>
                  <?php endforeach; else: ?>
                    <tr><td colspan="4" class="text-center muted py-4">No students yet</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <!-- Recent enrollments -->
          <div class="card glass-card mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="mb-0 fw-700">Recent Enrollments</h6>
              <a href="enrollments.php" class="btn btn-sm btn-outline-primary rounded-pill">All</a>
            </div>
            <?php if (!empty($recent_enrollments)): foreach ($recent_enrollments as $en): ?>
              <div class="enroll-row">
                <div class="enroll-avatar"><?php echo strtoupper(substr($en['student_name'],0,1)); ?></div>
                <div style="flex:1;min-width:0;">
                  <div class="fw-600 small text-truncate"><?php echo htmlspecialchars($en['student_name']); ?></div>
                  <div class="muted" style="font-size:.75rem;"><?php echo htmlspecialchars($en['course_name']); ?></div>
                </div>
                <span class="pill"><?php echo htmlspecialchars($en['course_code']); ?></span>
              </div>
            <?php endforeach; else: ?>
              <p class="muted small">No enrollments yet.</p>
            <?php endif; ?>
          </div>

          <!-- Progress rings card -->
          <div class="card glass-card">
            <h6 class="mb-3 fw-700">Enrollment Overview</h6>
            <canvas id="enrollChart" height="160"></canvas>
          </div>
        </div>
      </div>
    </main>
  </div><!-- /app-layout -->
</div><!-- /app-shell -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../assets/js/bodhivaas.js"></script>
<script>
  // Line chart
  const lctx  = document.getElementById('attendanceChart').getContext('2d');
  const labels = Array.from({length:14},(_,i)=>{const d=new Date();d.setDate(d.getDate()-(13-i));return d.toLocaleDateString('en-IN',{day:'2-digit',month:'short'});});
  const data   = Array.from({length:14},()=>Math.floor(Math.random()*25)+72);
  createLineChart(lctx, labels, data, {label:'Attendance %'});

  // Doughnut chart
  const dctx = document.getElementById('enrollChart').getContext('2d');
  new Chart(dctx, {
    type:'doughnut',
    data:{
      labels:['Active','Pending','Completed'],
      datasets:[{data:[<?php echo $total_enrollments; ?>, 2, 1], backgroundColor:['rgba(108,92,231,0.8)','rgba(162,155,254,0.6)','rgba(0,206,201,0.7)'], borderWidth:0, hoverOffset:6}]
    },
    options:{plugins:{legend:{position:'bottom',labels:{boxWidth:10,padding:12,font:{size:11}}}},cutout:'65%'}
  });
</script>
</body></html>
