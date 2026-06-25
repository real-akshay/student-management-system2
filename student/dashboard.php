<?php
require_once '../config.php';
if (!isset($_SESSION['student_id'])) { header('Location: ../login.php?role=student'); exit; }

$student_id = $_SESSION['student_id'];

$student = null;
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id); $stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) $student = $result->fetch_assoc();
$stmt->close();

$enrollments = [];
$stmt = $conn->prepare("SELECT * FROM enrollments WHERE student_id = ? AND status = 'active'");
$stmt->bind_param("i", $student_id); $stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $enrollments[] = $row;
$stmt->close();

$notifications = [];
$result = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 4");
while ($row = $result->fetch_assoc()) $notifications[] = $row;

$course_names = [];
if (count($enrollments) > 0) {
    $course_ids = array_map(fn($e) => $e['course_id'], $enrollments);
    $in = implode(',', array_fill(0, count($course_ids), '?'));
    $types = str_repeat('i', count($course_ids));
    $stmt = $conn->prepare("SELECT id, course_name, course_code FROM courses WHERE id IN ($in)");
    $stmt->bind_param($types, ...$course_ids); $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $course_names[$row['id']] = $row;
    $stmt->close();
}

$avatarLetter = strtoupper(substr($_SESSION['student_name'], 0, 1));
$greeting = date('G') < 12 ? 'Good morning' : (date('G') < 17 ? 'Good afternoon' : 'Good evening');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard — Bodhivaas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/bodhivaas.css">
  <style>
    .course-pill{
      background:var(--glass);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
      border:1px solid var(--glass-border);border-radius:var(--radius);
      padding:18px 20px;display:flex;align-items:center;gap:16px;
      transition:transform .25s var(--ease-spring),box-shadow .25s;
    }
    .course-pill:hover{transform:translateX(6px) scale(1.01);box-shadow:var(--shadow);}
    .course-pill .c-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;flex-shrink:0;}
    .notif-item{padding:14px 0;border-bottom:1px solid rgba(108,92,231,0.07);}
    .notif-item:last-child{border-bottom:none;}
    .notif-dot{width:8px;height:8px;border-radius:50%;background:var(--brand);flex-shrink:0;margin-top:5px;}
    .profile-info-row{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(108,92,231,0.06);}
    .profile-info-row:last-child{border-bottom:none;}
    .hero-banner{
      background:linear-gradient(135deg,var(--brand) 0%,var(--brand-2) 60%,var(--brand-3) 100%);
      border-radius:var(--radius-lg);padding:28px 32px;color:#fff;margin-bottom:24px;
      position:relative;overflow:hidden;
    }
    .hero-banner::before{content:'';position:absolute;right:-40px;top:-40px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,0.06);}
    .hero-banner::after{content:'';position:absolute;right:60px;bottom:-60px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,0.04);}
  </style>
</head>
<body>
<div class="container-fluid app-shell">
  <!-- Topbar -->
  <div class="app-topbar">
    <div class="brand"><div class="logo">B</div><span class="d-none d-md-block">Bodhivaas</span></div>
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
            <?php echo $avatarLetter; ?>
          </div>
          <span class="d-none d-md-inline small fw-600"><?php echo htmlspecialchars($_SESSION['student_name']); ?></span>
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
        <a class="nav-link" href="my_courses.php"><i class="bi bi-book"></i><span class="nav-label ms-1"> My Courses</span></a>
        <a class="nav-link" href="enroll_course.php"><i class="bi bi-plus-circle"></i><span class="nav-label ms-1"> Enroll</span></a>
        <a class="nav-link" href="profile.php"><i class="bi bi-person"></i><span class="nav-label ms-1"> Profile</span></a>
        <hr style="border-color:rgba(108,92,231,0.1);margin:8px 0;">
        <a class="nav-link text-danger" href="../logout.php"><i class="bi bi-box-arrow-right"></i><span class="nav-label ms-1"> Logout</span></a>
      </nav>
    </aside>

    <main class="app-main">
      <!-- Hero banner -->
      <div class="hero-banner">
        <div class="position-relative">
          <div style="font-size:.8rem;opacity:.8;font-weight:600;letter-spacing:.5px;"><?php echo $greeting; ?></div>
          <h4 class="fw-800 mb-1 mt-1"><?php echo htmlspecialchars($_SESSION['student_name']); ?> 👋</h4>
          <p style="opacity:.8;font-size:.9rem;margin:0;">Here's your academic overview for today</p>
          <a href="enroll_course.php" class="btn btn-sm btn-light rounded-pill mt-3 fw-600">
            <i class="bi bi-plus me-1"></i>Enroll in a course
          </a>
        </div>
      </div>

      <!-- Stat cards -->
      <div class="dash-cards">
        <div class="dash-card">
          <div><div class="meta">Active Courses</div><div class="value"><?php echo count($enrollments); ?></div></div>
          <div class="iconbox"><i class="bi bi-journal-bookmark-fill"></i></div>
        </div>
        <div class="dash-card">
          <div><div class="meta">Announcements</div><div class="value"><?php echo count($notifications); ?></div></div>
          <div class="iconbox" style="background:linear-gradient(135deg,#fd79a8,#e84393);"><i class="bi bi-bell-fill"></i></div>
        </div>
        <div class="dash-card">
          <div><div class="meta">Member Since</div><div class="value" style="font-size:1.1rem;"><?php echo $student ? date('M Y', strtotime($student['created_at'])) : '-'; ?></div></div>
          <div class="iconbox" style="background:linear-gradient(135deg,#00cec9,#00b894);"><i class="bi bi-calendar-check-fill"></i></div>
        </div>
      </div>

      <div class="row g-3">
        <!-- Course list -->
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-700"><i class="bi bi-book me-2 text-primary"></i>My Enrolled Courses</h6>
              <a href="enroll_course.php" class="btn btn-sm btn-primary rounded-pill">+ Enroll More</a>
            </div>
            <div class="p-3">
              <?php
              $gradients = [
                'linear-gradient(135deg,#6c5ce7,#a29bfe)',
                'linear-gradient(135deg,#00cec9,#00b894)',
                'linear-gradient(135deg,#fd79a8,#e84393)',
                'linear-gradient(135deg,#fdcb6e,#e17055)',
                'linear-gradient(135deg,#74b9ff,#0984e3)',
              ];
              if (count($enrollments) > 0): $gi=0; foreach ($enrollments as $e): $cn = $course_names[$e['course_id']] ?? null; ?>
                <div class="course-pill mb-2">
                  <div class="c-icon" style="background:<?php echo $gradients[$gi%count($gradients)]; ?>;">
                    <i class="bi bi-book"></i>
                  </div>
                  <div style="flex:1;min-width:0;">
                    <div class="fw-600 small"><?php echo $cn ? htmlspecialchars($cn['course_name']) : 'Course'; ?></div>
                    <div class="muted" style="font-size:.75rem;"><?php echo $cn ? htmlspecialchars($cn['course_code']) : '—'; ?> · Enrolled <?php echo format_date($e['enrollment_date']); ?></div>
                  </div>
                  <span class="badge bg-success">Active</span>
                </div>
              <?php $gi++; endforeach; else: ?>
                <div class="text-center py-5">
                  <i class="bi bi-journal-x text-muted" style="font-size:2.5rem;"></i>
                  <p class="muted mt-2">No courses enrolled yet.</p>
                  <a href="enroll_course.php" class="btn btn-primary btn-sm rounded-pill">Browse Courses</a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Right column -->
        <div class="col-lg-4">
          <!-- Notifications -->
          <div class="card glass-card mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1" style="padding:0 0 8px;">
              <h6 class="mb-0 fw-700"><i class="bi bi-megaphone me-2 text-primary"></i>Announcements</h6>
              <span class="badge bg-primary"><?php echo count($notifications); ?></span>
            </div>
            <?php if (count($notifications) > 0): foreach ($notifications as $n): ?>
              <div class="notif-item">
                <div class="d-flex gap-2">
                  <div class="notif-dot mt-1"></div>
                  <div>
                    <div class="fw-600 small"><?php echo htmlspecialchars($n['title']); ?></div>
                    <div class="muted" style="font-size:.75rem;"><?php echo htmlspecialchars(substr($n['message'],0,65)); ?>...</div>
                    <div style="font-size:.68rem;color:var(--muted);margin-top:3px;"><i class="bi bi-clock me-1"></i><?php echo format_date($n['created_at']); ?></div>
                  </div>
                </div>
              </div>
            <?php endforeach; else: ?>
              <p class="muted small text-center py-2">No announcements yet.</p>
            <?php endif; ?>
          </div>

          <!-- Profile mini card -->
          <div class="card glass-card">
            <h6 class="mb-3 fw-700"><i class="bi bi-person-circle me-2 text-primary"></i>My Profile</h6>
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="profile-avatar"><?php echo $avatarLetter; ?></div>
              <div>
                <div class="fw-700"><?php echo htmlspecialchars($_SESSION['student_name']); ?></div>
                <div class="muted small"><?php echo htmlspecialchars($_SESSION['student_email']); ?></div>
              </div>
            </div>
            <?php if ($student): ?>
              <div class="profile-info-row">
                <span class="muted small">Phone</span>
                <span class="small fw-500"><?php echo htmlspecialchars($student['phone'] ?? '—'); ?></span>
              </div>
              <div class="profile-info-row">
                <span class="muted small">Joined</span>
                <span class="small fw-500"><?php echo format_date($student['created_at']); ?></span>
              </div>
            <?php endif; ?>
            <a href="profile.php" class="btn btn-sm btn-outline-primary w-100 mt-3 rounded-pill">Edit Profile</a>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../assets/js/bodhivaas.js"></script>
</body></html>
