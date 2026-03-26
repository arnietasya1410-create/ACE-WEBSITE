<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About — ACE | Advancement and Continuing Education</title>
    <meta name="description" content="About ACE — Dengue Risk Detection System by UniKL RCMP. Mission, team and contact.">

    <!-- Bootstrap + icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Local stylesheet -->
    <link rel="stylesheet" href="front.css">

    <style>
        /* Purple accent */
        :root{--accent:#6f42c1;--accent-600:#5a35a8;--muted:#f4f4f7}
        body { font-family: 'Poppins', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; margin:0; padding-top:78px; background-color:var(--muted); }
        header.navbar-fixed {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1100;
            background: rgba(255,255,255,0.92);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .brand-logos img { max-height:72px; width:auto; margin-right:8px; }
        .hero {
            min-height: 40vh;
            background-image: url('/ACE/images/about-bg.jpeg');
            background-size: cover;
            background-position: center;
            display:flex;
            align-items:center;
            color:#fff;
        }
        .hero .overlay { background: rgba(0,0,0,0.45); padding:40px; border-radius:8px; }
        .section-card { padding:48px 0; }
        .team-member { text-align:center; }
        .team-member img { width:120px; height:120px; object-fit:cover; border-radius:50%; }
        footer { background:var(--accent); color:#fff; padding:28px 0; font-size:0.95rem; }
        @media (max-width:767px){ .brand-logos img{max-height:40px} .hero .overlay{padding:20px} }
        /* Make Bootstrap primary use the purple accent */
        .btn-primary{
            background-color:var(--accent) !important;
            border-color:var(--accent) !important;
        }
        .btn-primary:hover, .btn-primary:focus{
            background-color:var(--accent-600) !important;
            border-color:var(--accent-600) !important;
        }
        nav a.active, nav a:hover { color: var(--accent) !important; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/partials/header.php'; ?>


<!-- mobile offcanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNav">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">ACE</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <a class="d-block py-2" href="index.php">Home</a>
    <a class="d-block py-2" href="about.php">About</a>
    <a class="d-block py-2" href="features.php">Services</a>
    <a class="d-block py-2" href="index.php">Short Courses</a>
    <a class="d-block py-2" href="contact.php">Contact</a>
  </div>
</div>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <div class="overlay">
            <p class="lead mb-0">Empowering learners and professionals through flexible, industry-driven continuing education that supports lifelong growth and career advancement.</p>
        </div>
    </div>
</section>

<!-- About content -->
<section class="section-card">
    <div class="container">
        <div class="row align-items-center mb-4">
            <div class="col-12">
                <h2>About ACE</h2>
                <p>ACE (Advancement and Continuing Education) at Universiti Kuala Lumpur Royal College of Medicine Perak (UniKL RCMP) is committed to empowering professionals and communities through lifelong learning, practical training and evidence-based public health initiatives. We deliver industry-relevant courses and capacity-building programmes that balance academic rigour with real-world application. Our work supports workforce upskilling, promotes cross‑sector collaboration and accelerates knowledge transfer between academia and industry. We prioritise accessibility and flexibility so learners can progress alongside work commitments, while maintaining high standards through recognised accreditations and partnerships.</p>
                <ul>
                    <li>Deliver flexible, modular learning to accommodate working professionals.</li>
                    <li>Partner with industry and healthcare providers to ensure relevance.</li>
                    <li>Support community health initiatives, including dengue risk awareness and early-detection tools.</li>
                    <li>Maintain recognised provider status (HRD Corp claimable programmes) and procurement registration to support public-sector access.</li>
                </ul>
                <p>Through training, outreach and collaborative research, ACE aims to improve public health outcomes and strengthen the capacity of organisations to respond to local health challenges.</p>
            </div>
        </div>

        <hr>

        <div class="row mb-4">
                <h3>Mission</h3>
                <p>Our holistic healthcare and science programs at UniKL RCMP focus on transforming working adults into successful professionals and scientists, instilling them with altruistic and compassionate leadership qualities through an integrated curriculum that incorporates the essential elements of Social, Physical, Intellectual, Career, Emotional, and Spiritual (SPICES) development, empowering them for a fulfilling life.</p>
        <hr>


</section>

<!-- Call to action -->
<section class="section-card bg-white">
    <div class="container text-center py-4">
        <h4>Want to collaborate or learn more?</h4>
        <p class="mb-3">Contact our team for partnerships, research or deployment support.</p>
        <a href="service_inquiry.php" class="btn btn-primary">Contact Us</a>
    </div>
</section>


<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function(){
      const header = document.querySelector('header');
      if(header) {
        const headerHeight = header.offsetHeight;
        document.documentElement.style.scrollPaddingTop = headerHeight + 'px';
      }
    })();
</script>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>