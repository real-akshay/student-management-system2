<?php
require_once 'config.php';

if (is_admin_logged_in()) {
    redirect('admin/dashboard.php');
} elseif (is_student_logged_in()) {
    redirect('student/dashboard.php');
}

$total_students = 0; $total_courses = 0; $total_enrollments = 0; $success_rate = 0;
$course_highlights = []; $testimonials = [];

if (isset($conn) && $conn instanceof mysqli) {
    $r = $conn->query("SELECT COUNT(*) AS c FROM students");
    $total_students = $r ? (int)$r->fetch_assoc()['c'] : 0;
    $r = $conn->query("SELECT COUNT(*) AS c FROM courses");
    $total_courses = $r ? (int)$r->fetch_assoc()['c'] : 0;
    $r = $conn->query("SELECT COUNT(*) AS c FROM enrollments WHERE status='active'");
    $total_enrollments = $r ? (int)$r->fetch_assoc()['c'] : 0;
    $r = $conn->query("SELECT COUNT(DISTINCT student_id) AS c FROM enrollments");
    $students_with_enroll = $r ? (int)$r->fetch_assoc()['c'] : 0;
    $success_rate = $total_students > 0 ? round(($students_with_enroll / $total_students) * 100, 1) : 0;
    $r = $conn->query("SELECT id, course_name, course_code, description FROM courses ORDER BY created_at DESC LIMIT 6");
    if ($r) { while ($row = $r->fetch_assoc()) $course_highlights[] = $row; }
    $r = $conn->query("SELECT id, name, email FROM students ORDER BY created_at DESC LIMIT 4");
    if ($r) { while ($row = $r->fetch_assoc()) $testimonials[] = $row; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bodhivaas — Smart Student ERP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;500;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/bodhivaas.css">
  <style>
    /* ── Canvas particles ── */
    #particles-canvas{position:fixed;inset:0;z-index:0;pointer-events:none;opacity:.6;}

    /* ── Page wrapper ── */
    .page-wrap{position:relative;z-index:1;}

    /* ── Navbar ── */
    .landing-nav{
      position:sticky;top:0;z-index:200;
      padding:14px 0;
      background:rgba(240,242,255,0.55);
      backdrop-filter:blur(20px) saturate(200%);
      -webkit-backdrop-filter:blur(20px) saturate(200%);
      border-bottom:1px solid rgba(108,92,231,0.1);
      transition:background .3s;
    }
    .landing-nav.scrolled{background:rgba(240,242,255,0.85);box-shadow:0 4px 24px rgba(108,92,231,0.08);}
    .nav-pill{display:flex;align-items:center;gap:6px;padding:6px 16px;border-radius:999px;color:var(--muted);font-size:.9rem;font-weight:500;text-decoration:none;transition:all .2s;}
    .nav-pill:hover{background:rgba(108,92,231,0.08);color:var(--brand);}

    /* ── Hero ── */
    .hero{padding:100px 0 60px;position:relative;overflow:hidden;}
    .hero-eyebrow{
      display:inline-block;
      background:linear-gradient(135deg,rgba(108,92,231,0.12),rgba(162,155,254,0.1));
      color:var(--brand);
      padding:6px 16px;border-radius:999px;
      font-weight:600;font-size:.85rem;letter-spacing:.5px;
      margin-bottom:20px;
      border:1px solid rgba(108,92,231,0.25);
    }
    .hero h1{font-size:3.2rem;font-weight:900;line-height:1.15;margin-bottom:20px;}
    .hero p.lead{font-size:1.15rem;color:var(--muted);margin-bottom:28px;}
    .hero .stat-mini{
      background:var(--glass);
      backdrop-filter:blur(18px) saturate(180%);
      -webkit-backdrop-filter:blur(18px) saturate(180%);
      border:1px solid var(--glass-border);
      border-radius:14px;padding:16px;
      box-shadow:var(--shadow);
      transition:transform .3s var(--ease-spring),box-shadow .3s;
    }
    .hero .stat-mini:hover{transform:translateY(-6px) scale(1.03);box-shadow:var(--shadow-lg);}
    .hero .stat-mini .label{font-size:.7rem;color:var(--muted);font-weight:600;letter-spacing:.5px;text-transform:uppercase;}
    .hero .stat-mini .value{font-size:1.6rem;font-weight:800;margin-top:4px;}
    .hero-image{border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);transform:perspective(1000px) rotateY(-5deg);transition:transform .5s var(--ease-spring);}
    .hero-image:hover{transform:perspective(1000px) rotateY(0deg) scale(1.02);}

    /* ── Feature cards ── */
    .feature-section{padding:90px 0;}
    .section-badge{
      display:inline-block;
      background:rgba(108,92,231,0.08);
      color:var(--brand);
      border:1px solid rgba(108,92,231,0.2);
      border-radius:999px;padding:5px 16px;
      font-size:.78rem;font-weight:700;letter-spacing:.8px;text-transform:uppercase;
      margin-bottom:14px;
    }
    .feature-card{
      background:var(--glass);
      backdrop-filter:blur(18px) saturate(180%);
      -webkit-backdrop-filter:blur(18px) saturate(180%);
      border:1px solid var(--glass-border);
      border-radius:var(--radius-lg);
      padding:32px 28px;
      box-shadow:var(--shadow);
      transition:transform .3s var(--ease-spring),box-shadow .3s var(--ease);
      position:relative;overflow:hidden;height:100%;
    }
    .feature-card::after{
      content:'';position:absolute;top:-60px;right:-60px;
      width:160px;height:160px;border-radius:50%;
      background:radial-gradient(circle,rgba(108,92,231,0.1),transparent 70%);
      pointer-events:none;
    }
    .feature-card:hover{transform:translateY(-8px);box-shadow:var(--shadow-lg);}
    .feat-icon{
      width:56px;height:56px;border-radius:14px;
      display:flex;align-items:center;justify-content:center;
      font-size:1.5rem;margin-bottom:18px;
      transition:transform .3s var(--ease-spring);
    }
    .feature-card:hover .feat-icon{transform:scale(1.15) rotate(-8deg);}
    .feat-icon.purple{background:linear-gradient(135deg,var(--brand),var(--brand-2));color:#fff;box-shadow:0 6px 18px rgba(108,92,231,0.35);}
    .feat-icon.teal  {background:linear-gradient(135deg,#00cec9,#00b894);color:#fff;box-shadow:0 6px 18px rgba(0,206,201,0.35);}
    .feat-icon.orange{background:linear-gradient(135deg,#fdcb6e,#e17055);color:#fff;box-shadow:0 6px 18px rgba(253,203,110,0.4);}
    .feat-icon.pink  {background:linear-gradient(135deg,#fd79a8,#e84393);color:#fff;box-shadow:0 6px 18px rgba(253,121,168,0.35);}
    .feat-icon.blue  {background:linear-gradient(135deg,#74b9ff,#0984e3);color:#fff;box-shadow:0 6px 18px rgba(116,185,255,0.35);}
    .feat-icon.green {background:linear-gradient(135deg,#55efc4,#00b894);color:#fff;box-shadow:0 6px 18px rgba(85,239,196,0.35);}

    /* ── Course cards ── */
    .course-card{
      background:var(--glass);
      backdrop-filter:blur(18px) saturate(180%);
      -webkit-backdrop-filter:blur(18px) saturate(180%);
      border:1px solid var(--glass-border);
      border-radius:var(--radius);
      overflow:hidden;
      box-shadow:var(--shadow);
      transition:transform .3s var(--ease-spring),box-shadow .3s;
      height:100%;
    }
    .course-card:hover{transform:translateY(-8px) scale(1.01);box-shadow:var(--shadow-lg);}
    .course-header{
      height:110px;
      background:linear-gradient(135deg,var(--brand),var(--brand-2));
      position:relative;overflow:hidden;
      display:flex;align-items:center;justify-content:center;
      font-size:2.5rem;color:rgba(255,255,255,0.25);
    }
    .course-header-2{background:linear-gradient(135deg,#00cec9,#0984e3);}
    .course-header-3{background:linear-gradient(135deg,#fd79a8,var(--brand));}
    .course-body{padding:20px;}

    /* ── Testimonials ── */
    .testimonial-card{
      background:var(--glass);
      backdrop-filter:blur(18px) saturate(180%);
      -webkit-backdrop-filter:blur(18px) saturate(180%);
      border:1px solid var(--glass-border);
      border-radius:var(--radius-lg);
      padding:28px;
      box-shadow:var(--shadow);
    }
    .stars{color:#fdcb6e;letter-spacing:2px;font-size:.9rem;}

    /* ── Why section ── */
    .why-item{
      background:var(--glass);
      backdrop-filter:blur(16px);
      -webkit-backdrop-filter:blur(16px);
      border:1px solid var(--glass-border);
      border-radius:var(--radius);
      padding:28px;
      box-shadow:var(--shadow);
      transition:transform .25s var(--ease-spring),box-shadow .25s;
      text-align:center;
    }
    .why-item:hover{transform:translateY(-6px);box-shadow:var(--shadow-lg);}

    /* ── CTA ── */
    .cta-section{
      padding:90px 0;
      position:relative;
      overflow:hidden;
    }
    .cta-inner{
      background:linear-gradient(135deg,#6c5ce7 0%,#a29bfe 50%,#fd79a8 100%);
      background-size:300% 300%;
      animation:gradShift 6s ease infinite;
      border-radius:28px;
      padding:70px 40px;
      text-align:center;
      position:relative;
      overflow:hidden;
      box-shadow:0 20px 60px rgba(108,92,231,0.35);
    }
    @keyframes gradShift{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
    .cta-inner .cta-orb1{position:absolute;width:300px;height:300px;border-radius:50%;background:rgba(255,255,255,0.07);top:-80px;right:-80px;pointer-events:none;}
    .cta-inner .cta-orb2{position:absolute;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,0.05);bottom:-60px;left:-60px;pointer-events:none;}
    .cta-badge{display:inline-block;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.45);border-radius:999px;padding:5px 18px;font-size:.8rem;font-weight:700;letter-spacing:.6px;color:#fff;margin-bottom:18px;}
    .cta-title{font-size:2.6rem;font-weight:900;color:#fff;margin-bottom:14px;line-height:1.2;}
    .cta-sub{font-size:1.05rem;color:rgba(255,255,255,0.88);margin-bottom:36px;}
    .cta-btns{display:flex;gap:14px;justify-content:center;flex-wrap:wrap;}
    .cta-btn-white{background:#fff;color:#6c5ce7;border:none;border-radius:999px;padding:14px 40px;font-size:1rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px;box-shadow:0 6px 20px rgba(0,0,0,0.15);transition:transform .2s,box-shadow .2s;}
    .cta-btn-white:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,0.2);color:#6c5ce7;text-decoration:none;}
    .cta-btn-ghost{background:rgba(255,255,255,0.15);color:#fff;border:1.5px solid rgba(255,255,255,0.5);border-radius:999px;padding:14px 40px;font-size:1rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px;backdrop-filter:blur(10px);transition:transform .2s,background .2s;}
    .cta-btn-ghost:hover{transform:translateY(-3px);background:rgba(255,255,255,0.25);color:#fff;text-decoration:none;}

    /* ── Footer ── */
    .site-footer{background:var(--bg-2);border-top:1px solid rgba(108,92,231,0.08);padding:60px 0 30px;}
    .footer-brand{font-size:1.1rem;font-weight:800;color:var(--brand);}

    /* ── Misc ── */
    .back-to-top{position:fixed;right:20px;bottom:24px;display:none;z-index:999;width:40px;height:40px;border-radius:50%;box-shadow:var(--shadow);}
    .loader{position:fixed;inset:0;background:linear-gradient(135deg,#f0f2ff,#e8ecff);display:flex;align-items:center;justify-content:center;z-index:2000;}
    .spinner-ring{width:48px;height:48px;border:3px solid rgba(108,92,231,0.2);border-top-color:var(--brand);border-radius:50%;animation:spin .9s linear infinite;}
    @keyframes spin{to{transform:rotate(360deg)}}
  </style>
</head>
<body>
<canvas id="particles-canvas"></canvas>
<div class="loader" id="pageLoader"><div class="spinner-ring"></div></div>
<div class="page-wrap">

<!-- ══ NAVBAR ══════════════════════════════════════ -->
<nav class="landing-nav" id="mainNav">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="brand text-decoration-none">
        <div class="logo">B</div>
        <span>Bodhivaas</span>
      </a>
      <div class="d-none d-md-flex align-items-center gap-1">
        <a href="#features" class="nav-pill">Features</a>
        <a href="#courses"  class="nav-pill">Courses</a>
        <a href="#why"      class="nav-pill">Why Us</a>
        <a href="#testimonials" class="nav-pill">Reviews</a>
      </div>
      <div class="d-none d-md-flex align-items-center gap-2">
        <a href="login.php?role=student" class="btn btn-outline-primary btn-sm">Student Login</a>
        <a href="login.php?role=admin"   class="btn btn-primary   btn-sm">Admin Portal</a>
      </div>
      <button class="btn btn-sm d-md-none" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
        <i class="fa fa-bars"></i>
      </button>
    </div>
  </div>
</nav>

<!-- mobile menu -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu">
  <div class="offcanvas-header">
    <div class="brand"><div class="logo">B</div><span>Bodhivaas</span></div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <a class="d-block mb-3 nav-pill" href="#features">Features</a>
    <a class="d-block mb-3 nav-pill" href="#courses">Courses</a>
    <a class="d-block mb-3 nav-pill" href="#why">Why Us</a>
    <a class="d-block mb-3 nav-pill" href="#testimonials">Reviews</a>
    <hr>
    <a class="btn btn-primary w-100 mb-2"         href="login.php?role=student">Student Login</a>
    <a class="btn btn-outline-primary w-100 mb-2" href="login.php?role=admin">Admin Portal</a>
    <a class="btn btn-outline-secondary w-100"     href="register.php">Register</a>
  </div>
</div>

<!-- ══ HERO ════════════════════════════════════════ -->
<section class="hero">
  <div class="container">
    <div class="row align-items-center gy-5">
      <div class="col-lg-6" data-aos="fade-up">
        <div class="hero-eyebrow"><i class="fa fa-star me-1"></i> Smart Education ERP Platform</div>
        <h1>Manage your <span class="text-gradient">campus</span> smarter, not harder</h1>
        <p class="lead">Bodhivaas brings admissions, enrollments, analytics, and student life together in one elegant, glass-smooth dashboard.</p>
        <div class="d-flex flex-wrap gap-3 mb-4">
          <a href="register.php"        class="btn btn-primary btn-lg px-4">Get Started Free <i class="fa fa-arrow-right ms-2"></i></a>
          <a href="login.php?role=admin" class="btn btn-outline-primary btn-lg px-4">Admin Demo</a>
        </div>
        <div class="d-flex flex-wrap gap-3 mt-2">
          <div class="hero stat-mini" style="min-width:110px;" data-aos="zoom-in" data-aos-delay="100">
            <div class="label">Students</div>
            <div class="value text-gradient" data-counter><?php echo $total_students; ?></div>
          </div>
          <div class="hero stat-mini" style="min-width:110px;" data-aos="zoom-in" data-aos-delay="160">
            <div class="label">Courses</div>
            <div class="value text-gradient" data-counter><?php echo $total_courses; ?></div>
          </div>
          <div class="hero stat-mini" style="min-width:110px;" data-aos="zoom-in" data-aos-delay="220">
            <div class="label">Enrollments</div>
            <div class="value text-gradient" data-counter><?php echo $total_enrollments; ?></div>
          </div>
          <div class="hero stat-mini" style="min-width:110px;" data-aos="zoom-in" data-aos-delay="280">
            <div class="label">Success Rate</div>
            <div class="value text-gradient" data-counter><?php echo $success_rate; ?><span style="font-size:1rem">%</span></div>
          </div>
        </div>
      </div>
      <div class="col-lg-6 text-center" data-aos="zoom-in" data-aos-delay="100">
        <div class="hero-image" style="position:relative;">
          <img src="https://images.unsplash.com/photo-1531746790731-6c087fecd65a?q=80&w=1200&auto=format&fit=crop"
               alt="Bodhivaas dashboard" class="img-fluid w-100">
          <div style="position:absolute;inset:0;background:linear-gradient(180deg,transparent 60%,rgba(108,92,231,0.12));pointer-events:none;border-radius:var(--radius-lg);"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ FEATURES ════════════════════════════════════ -->
<section id="features" class="feature-section">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <div class="section-badge">Features</div>
      <h2 class="fw-800" style="font-size:2.2rem;">Everything you need to run a<br><span class="text-gradient">modern institution</span></h2>
      <p class="muted mt-2" style="max-width:520px;margin:auto;">Built for administrators and students alike — powerful tools wrapped in a clean interface.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="0">
        <div class="feature-card">
          <div class="feat-icon purple"><i class="fa fa-users"></i></div>
          <h5 class="fw-bold mb-2">Student Management</h5>
          <p class="muted small mb-0">Centralized profiles, secure authentication, and fine-grained role-based access control.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="80">
        <div class="feature-card">
          <div class="feat-icon teal"><i class="fa fa-book-open"></i></div>
          <h5 class="fw-bold mb-2">Course & Enrollment</h5>
          <p class="muted small mb-0">Create courses, manage syllabi, enroll students with one click, and track progress.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="160">
        <div class="feature-card">
          <div class="feat-icon orange"><i class="fa fa-chart-line"></i></div>
          <h5 class="fw-bold mb-2">Smart Analytics</h5>
          <p class="muted small mb-0">Real-time dashboards give you instant insight into performance, attendance, and growth.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="60">
        <div class="feature-card">
          <div class="feat-icon pink"><i class="fa fa-bell"></i></div>
          <h5 class="fw-bold mb-2">Notifications</h5>
          <p class="muted small mb-0">Push announcements to students instantly. Keep everyone aligned and informed.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="120">
        <div class="feature-card">
          <div class="feat-icon blue"><i class="fa fa-shield-halved"></i></div>
          <h5 class="fw-bold mb-2">Secure by Default</h5>
          <p class="muted small mb-0">Password hashing, prepared statements, and session security built into every layer.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="feature-card">
          <div class="feat-icon green"><i class="fa fa-mobile-screen"></i></div>
          <h5 class="fw-bold mb-2">Fully Responsive</h5>
          <p class="muted small mb-0">Works beautifully on desktop, tablet, and mobile — anywhere students learn.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ WHY CHOOSE US ═══════════════════════════════ -->
<section id="why" class="feature-section" style="background:var(--bg-2);">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <div class="section-badge">Why Bodhivaas</div>
      <h2 class="fw-800" style="font-size:2.2rem;">Built on proven<br><span class="text-gradient">engineering principles</span></h2>
    </div>
    <div class="row g-4">
      <div class="col-md-4" data-aos="zoom-in" data-aos-delay="0">
        <div class="why-item">
          <div class="feat-icon purple mx-auto"><i class="fa fa-bolt"></i></div>
          <h5 class="fw-bold mt-3 mb-2">Blazing Fast</h5>
          <p class="muted small mb-0">Optimized queries, minimal JS, and smart caching for instant interactions.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="zoom-in" data-aos-delay="80">
        <div class="why-item">
          <div class="feat-icon teal mx-auto"><i class="fa fa-lock"></i></div>
          <h5 class="fw-bold mt-3 mb-2">Production Ready</h5>
          <p class="muted small mb-0">Secure sessions, parameterized queries, and production-grade error handling.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="zoom-in" data-aos-delay="160">
        <div class="why-item">
          <div class="feat-icon orange mx-auto"><i class="fa fa-wrench"></i></div>
          <h5 class="fw-bold mt-3 mb-2">Easy to Extend</h5>
          <p class="muted small mb-0">Modular codebase makes it simple to add features or integrate third-party tools.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ COURSES ═════════════════════════════════════ -->
<section id="courses" class="feature-section">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
      <div>
        <div class="section-badge">Courses</div>
        <h3 class="fw-bold mb-0">Explore our course catalog</h3>
      </div>
      <a href="login.php?role=student" class="btn btn-primary btn-sm">View All <i class="fa fa-arrow-right ms-1"></i></a>
    </div>
    <div class="row g-4">
      <?php if (!empty($course_highlights)): $i=0; foreach ($course_highlights as $c): $hclass=['course-header','course-header-2','course-header-3'][$i%3]; ?>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?php echo $i*60; ?>">
          <div class="course-card">
            <div class="<?php echo $hclass; ?>"><i class="fa fa-graduation-cap"></i></div>
            <div class="course-body">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($c['course_name']); ?></h6>
                <span class="badge bg-primary"><?php echo htmlspecialchars($c['course_code']); ?></span>
              </div>
              <p class="muted small mb-3"><?php echo htmlspecialchars(substr($c['description'],0,90)); ?>...</p>
              <a href="login.php?role=student" class="btn btn-sm btn-outline-primary w-100">Enroll Now</a>
            </div>
          </div>
        </div>
      <?php $i++; endforeach; else: ?>
        <div class="col-12 text-center"><p class="muted">No courses available yet.</p></div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ══ TESTIMONIALS ════════════════════════════════ -->
<section id="testimonials" class="feature-section" style="background:var(--bg-2);">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <div class="section-badge">Testimonials</div>
      <h2 class="fw-800" style="font-size:2.2rem;">What students <span class="text-gradient">say about us</span></h2>
    </div>
    <div class="row g-4 justify-content-center">
      <?php
        $quotes = [
          "Bodhivaas made managing my courses simple and intuitive. The dashboard is clean and incredibly fast.",
          "I love how easy it is to track my enrollments and get notified of announcements in one place.",
          "The platform is modern and beautifully designed. Best ERP I've used in my academic journey.",
          "Registration and enrollment used to take hours — now it takes minutes. Bodhivaas is a game changer."
        ];
        if (!empty($testimonials)): $i=0; foreach ($testimonials as $t):
      ?>
        <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="<?php echo $i*70; ?>">
          <div class="testimonial-card h-100">
            <div class="stars mb-3">★★★★★</div>
            <p class="small mb-3" style="line-height:1.7;">"<?php echo $quotes[$i % count($quotes)]; ?>"</p>
            <div class="d-flex align-items-center gap-2 mt-auto">
              <div class="profile-avatar" style="width:38px;height:38px;font-size:.95rem;border-radius:9px;">
                <?php echo strtoupper(substr($t['name'],0,1)); ?>
              </div>
              <div>
                <div class="fw-bold small"><?php echo htmlspecialchars($t['name']); ?></div>
                <div class="muted" style="font-size:.72rem;">Student</div>
              </div>
            </div>
          </div>
        </div>
      <?php $i++; endforeach; else: ?>
        <div class="col-12 text-center"><p class="muted">No testimonials yet. Be the first!</p></div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ══ CTA ═════════════════════════════════════════ -->
<section class="cta-section">
  <div class="container">
    <div class="cta-inner" data-aos="zoom-in">
      <div class="cta-orb1"></div>
      <div class="cta-orb2"></div>
      <div class="cta-badge">✦ Ready to start?</div>
      <div class="cta-title">Transform your institution today</div>
      <div class="cta-sub">Join Bodhivaas and experience the future of student management.</div>
      <div class="cta-btns">
        <a href="register.php" class="cta-btn-white">
          <i class="fa fa-rocket"></i> Get Started Free
        </a>
        <a href="login.php?role=admin" class="cta-btn-ghost">
          <i class="fa fa-shield-halved"></i> Admin Demo
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ══ FOOTER ══════════════════════════════════════ -->
<footer class="site-footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="footer-brand mb-2"><div class="brand"><div class="logo">B</div><span>Bodhivaas</span></div></div>
        <p class="muted small mt-2">Premium Student Management ERP. Built for modern educational institutions.</p>
        <div class="mt-3 d-flex gap-2">
          <a href="#" class="btn btn-sm btn-outline-primary rounded-circle" style="width:34px;height:34px;padding:0;display:flex;align-items:center;justify-content:center;"><i class="fab fa-twitter"></i></a>
          <a href="#" class="btn btn-sm btn-outline-primary rounded-circle" style="width:34px;height:34px;padding:0;display:flex;align-items:center;justify-content:center;"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="btn btn-sm btn-outline-primary rounded-circle" style="width:34px;height:34px;padding:0;display:flex;align-items:center;justify-content:center;"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <h6 class="fw-bold mb-3">Product</h6>
        <ul class="list-unstyled muted small">
          <li class="mb-1"><a href="#features" class="text-muted text-decoration-none">Features</a></li>
          <li class="mb-1"><a href="#courses"  class="text-muted text-decoration-none">Courses</a></li>
          <li class="mb-1"><a href="register.php" class="text-muted text-decoration-none">Register</a></li>
        </ul>
      </div>
      <div class="col-md-2 col-6">
        <h6 class="fw-bold mb-3">Access</h6>
        <ul class="list-unstyled muted small">
          <li class="mb-1"><a href="login.php?role=student" class="text-muted text-decoration-none">Student Login</a></li>
          <li class="mb-1"><a href="login.php?role=admin"   class="text-muted text-decoration-none">Admin Login</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h6 class="fw-bold mb-3">Contact</h6>
        <div class="muted small">
          <div class="mb-1"><i class="fa fa-envelope me-2 text-primary"></i>hello@bodhivaas.example</div>
          <div><i class="fa fa-phone me-2 text-primary"></i>+91 99999 99999</div>
        </div>
      </div>
    </div>
    <hr class="mt-4" style="border-color:rgba(108,92,231,0.1);">
    <div class="d-flex flex-wrap justify-content-between align-items-center muted small">
      <span>&copy; <?php echo date('Y'); ?> Bodhivaas. All rights reserved.</span>
      <span class="mt-2 mt-md-0">Made with <i class="fa fa-heart text-danger"></i> for learners</span>
    </div>
  </div>
</footer>

<button class="btn btn-primary back-to-top" id="backToTop"><i class="fa fa-chevron-up"></i></button>
</div><!-- /page-wrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script src="assets/js/bodhivaas.js"></script>
<script>
  AOS.init({ duration: 700, once: true, offset: 60 });

  // Page loader
  window.addEventListener('load', () => { document.getElementById('pageLoader').style.display = 'none'; });

  // Sticky nav shade
  const nav = document.getElementById('mainNav');
  window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 40);
    document.getElementById('backToTop').style.display = window.scrollY > 300 ? 'block' : 'none';
  });

  // Back to top
  document.getElementById('backToTop').addEventListener('click', () =>
    window.scrollTo({ top: 0, behavior: 'smooth' })
  );

  // Smooth anchor scroll
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const t = document.querySelector(a.getAttribute('href'));
      if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });
  });

  // Animated counters
  document.querySelectorAll('[data-counter]').forEach(el => {
    const raw  = el.textContent.replace('%','').trim();
    const val  = parseFloat(raw) || 0;
    const isFloat = raw.includes('.');
    el.textContent = '0';
    let start = 0;
    const dur  = 1400, steps = dur / 16;
    const inc  = val / steps;
    const iv = setInterval(() => {
      start += inc;
      if (start >= val) { el.childNodes[0].textContent = isFloat ? val.toFixed(1) : val; clearInterval(iv); }
      else el.childNodes[0].textContent = isFloat ? start.toFixed(1) : Math.floor(start);
    }, 16);
  });

  // ── Particle canvas ──────────────────────────────
  (function() {
    const cvs = document.getElementById('particles-canvas');
    const ctx = cvs.getContext('2d');
    let W, H, particles = [];

    function resize() { W = cvs.width = window.innerWidth; H = cvs.height = window.innerHeight; }
    window.addEventListener('resize', resize); resize();

    const COLORS = ['rgba(108,92,231,', 'rgba(162,155,254,', 'rgba(253,121,168,', 'rgba(0,206,201,'];

    function rand(a, b) { return a + Math.random() * (b - a); }
    for (let i = 0; i < 55; i++) {
      particles.push({
        x: rand(0, W), y: rand(0, H),
        r: rand(1.5, 4.5),
        dx: rand(-0.3, 0.3), dy: rand(-0.3, 0.3),
        alpha: rand(0.15, 0.55),
        color: COLORS[Math.floor(Math.random() * COLORS.length)]
      });
    }

    function draw() {
      ctx.clearRect(0, 0, W, H);
      particles.forEach(p => {
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fillStyle = p.color + p.alpha + ')';
        ctx.fill();
        p.x += p.dx; p.y += p.dy;
        if (p.x < -10) p.x = W + 10;
        if (p.x > W + 10) p.x = -10;
        if (p.y < -10) p.y = H + 10;
        if (p.y > H + 10) p.y = -10;
      });
      requestAnimationFrame(draw);
    }
    draw();
  })();
</script>
</body>
</html>
