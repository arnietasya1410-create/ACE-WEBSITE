<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Services — ACE | Advancement and Continuing Education</title>
    <meta name="description" content="Services and features offered by ACE — slideshows + descriptions.">

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
            position: fixed; top:0; left:0; right:0; z-index:1100;
            background: rgba(255,255,255,0.92); border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .brand-logos img { max-height:72px; width:auto; margin-right:8px; }
        .page-hero { 
            min-height: 40vh; 
            background-image: url('/ACE/images/features-bg-.jpg'); 
            background-size:cover; 
            background-position:center; 
            display:flex; 
            align-items:center; 
            color:#fff; 
        }
        .page-hero .overlay { 
            background: rgba(0,0,0,0.45); 
            padding:40px; 
            border-radius:8px; 
        }
        .section-card { padding:48px 0; }
        .service-card { padding:18px; background:#fff; border-radius:8px; box-shadow:0 6px 18px rgba(20,20,30,0.05); }
        .service-title { color:var(--accent); font-weight:600; }
        footer { background:var(--accent); color:#fff; padding:28px 0; font-size:0.95rem; }
        @media (max-width:767px){ .brand-logos img{max-height:40px} .page-hero .overlay{padding:20px} .service-carousel img{height:240px; object-fit:cover} }

        /* Make Bootstrap primary use the purple accent */
        .btn-primary{ background-color:var(--accent) !important; border-color:var(--accent) !important; }
        .btn-primary:hover, .btn-primary:focus{ background-color:var(--accent-600) !important; border-color:var(--accent-600) !important; }
        nav a.active, nav a:hover { color: var(--accent) !important; }

        /* Interlaced rows: alternate image/text order on lg+ screens - FIXED */
        .service-row .service-media { order: 1; }
        .service-row .service-body { order: 2; }
        
        @media (min-width:992px){
            /* Force media (image) on the left and body on the right for all rows */
            .service-row .service-media { order: 1 !important; }
            .service-row .service-body { order: 2 !important; }

            /* Allow forcing an individual row to have media on the left (no-op with global rule) */
            .service-row.media-left .service-media { order: 1 !important; }
            .service-row.media-left .service-body { order: 2 !important; }
        }

        .service-carousel .carousel-inner img { width:100%; height:320px; object-fit:cover; border-radius:6px; }
        .service-badges .badge { background: rgba(111,66,193,0.12); color: var(--accent); font-weight:600; }

        /* Disclaimer shown in-flow below Event Space Rental (no longer fixed) */
        .service-disclaimer {
            position: static;
            background: #fff;
            border-top: 4px solid var(--accent);
            box-shadow: none;
            padding: 18px 0;
            margin-top: 24px;
        }
        .service-disclaimer .container { display:flex; gap:12px; align-items:center; }
        .service-disclaimer strong { color: var(--accent); font-weight:700; }
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
<section class="page-hero">
    <div class="container">
        <div class="overlay">
            <h1 class="display-6">Our Services</h1>
            <p class="lead mb-0">Practical tools, training and analytics to support dengue risk detection, community outreach and professional development.</p>
        </div>
    </div>
</section>

<!-- Services: interlaced rows with carousel + description -->
<section class="section-card">
    <div class="container">
        <!-- Service 1 - Short Courses -->
        <div id="short-courses" class="row align-items-center mb-5 service-row">
            <div class="col-lg-6 service-media">
                <div id="svcCarousel1" class="carousel slide service-carousel" data-bs-ride="carousel">
                  <div class="carousel-indicators">
                    <button type="button" data-bs-target="#svcCarousel1" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#svcCarousel1" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#svcCarousel1" data-bs-slide-to="2" aria-label="Slide 3"></button>
                  </div>
                  <div class="carousel-inner">
                    <div class="carousel-item active">
                      <img src="/ACE/images/service1-1.jpg" class="d-block w-100" alt="Self-assessment screenshot 1">
                    </div>
                    <div class="carousel-item">
                      <img src="/ACE/images/service1-2.jpg" class="d-block w-100" alt="Self-assessment screenshot 2">
                    </div>
                    <div class="carousel-item">
                      <img src="/ACE/images/service1-3.jpg" class="d-block w-100" alt="Self-assessment screenshot 3">
                    </div>
                  </div>
                  <button class="carousel-control-prev" type="button" data-bs-target="#svcCarousel1" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#svcCarousel1" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                  </button>
                </div>
            </div>

            <div class="col-lg-6 service-body mt-4 mt-lg-0">
                <div class="service-card">
                    <div class="service-badges mb-2"><span class="badge py-2 px-3">Short Courses</span></div>
                    <h3 class="service-title">Short Courses (Customised Programmes)</h3>
                    <p>Gain practical knowledge and skills that you can apply immediately at work. Our courses are flexible and tailored to fit your personal and professional needs.</p>
                    <ul>
                        <li>Learn from expert trainers in Higher Technical and Vocational Education and Training (HTVET)</li>
                        <li>Stay ahead in your field.</li>
                    </ul>
                    <p>Join our short courses today and grow your career!</p>
                    <a href="courses.php" class="btn btn-primary">See Short Courses List</a>
                </div>
            </div>
        </div>

        <hr>

        <!-- Service 2 - Consultancy -->
        <div id="consultancy" class="row align-items-center mb-5 service-row">
            <div class="col-lg-6 service-media">
                <div id="svcCarousel2" class="carousel slide service-carousel" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    <div class="carousel-item active">
                      <img src="/ACE/images/service2-1.jpeg" class="d-block w-100" alt="Consultancy Programme 1">
                    </div>
                    <div class="carousel-item">
                      <img src="/ACE/images/service2-2.jpg" class="d-block w-100" alt="Consultancy Programme 2">
                    </div>
                  </div>
                  <button class="carousel-control-prev" type="button" data-bs-target="#svcCarousel2" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#svcCarousel2" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                  </button>
                </div>
            </div>

            <div class="col-lg-6 service-body mt-4 mt-lg-0">
                <div class="service-card">
                    <div class="service-badges mb-2"><span class="badge py-2 px-3">Consultancy Programme</span></div>
                    <h3 class="service-title">Consultancy Programme</h3>
                    <p>We provide tailored consultancy services to help businesses achieve their goals.</p>
                    <ul>
                        <li>Team of experienced academicians and industry professionals.</li>
                        <li>We offer expert guidance, strategic insights and practical solutions.</li>
                    </ul>
                    <p>Leveraging our strong network of international and industrial partnerships, we work closely with you to identify areas for improvement and drive success</p>
                    <a href="service_inquiry.php?service=consultancy" class="btn btn-primary">Request Consultancy</a>
                </div>
            </div>
        </div>



        <hr>

        <!-- Service 3 - Certificate -->
        <div id="certificate" class="row align-items-center mb-5 service-row">
            <div class="col-lg-6 service-media">
                <div id="svcCarousel3" class="carousel slide service-carousel" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    <div class="carousel-item active">
                      <img src="/ACE/images/service3-1.jpg" class="d-block w-100" alt="Certificate 1">
                    </div>
                    <div class="carousel-item">
                      <img src="/ACE/images/service3-2.jpeg" class="d-block w-100" alt="Certificate 2">
                    </div>
                    <div class="carousel-item">
                      <img src="/ACE/images/service3-3.jpeg" class="d-block w-100" alt="Certificate 3">
                    </div>  
                  </div>
                  <button class="carousel-control-prev" type="button" data-bs-target="#svcCarousel3" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#svcCarousel3" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                  </button>
                </div>
            </div>

            <div class="col-lg-6 service-body mt-4 mt-lg-0">
                <div class="service-card">
                    <div class="service-badges mb-2"><span class="badge py-2 px-3">Certificate</span></div>
                    <h3 class="service-title">Professional Certificate</h3>
                    <p>Elevate your professional competencies and expand your expertise with our accredited professional certificates. </p>
                    <ul>
                        <li>Recognized by authoritative bodies that uphold industry standards.</li>
                        <li>These certifications validate your knowledge and proficiency,</li>
                        <li>Ensuring you remain competitive in your field</li>
                    </ul>
                    <p>Enhance your career trajectory with credentials that demonstrate your commitment to excellence and continuous professional development. </p>
                    <a href="certificate_programmes.php" class="btn btn-primary">View Certificate Programmes</a>
                </div>
            </div>
        </div>

        <hr>

        <!-- Service 4 - Specialized -->
        <div id="specialized" class="row align-items-center mb-5 service-row">
            <div class="col-lg-6 service-media">
                <div id="svcCarousel4" class="carousel slide service-carousel" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    <div class="carousel-item active">
                      <img src="/ACE/images/service4-1.jpg" class="d-block w-100" alt="Specialized Course dashboard 1">
                    </div>
                    <div class="carousel-item">
                      <img src="/ACE/images/service4-2.jpeg" class="d-block w-100" alt="Specialized Course dashboard 2">
                    </div>
                    <div class="carousel-item">
                      <img src="/ACE/images/service4-3.jpeg" class="d-block w-100" alt="Specialized Course dashboard 3">
                    </div>
                  </div>
                  <button class="carousel-control-prev" type="button" data-bs-target="#svcCarousel4" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#svcCarousel4" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                  </button>
                </div>
            </div>

            <div class="col-lg-6 service-body mt-4 mt-lg-0">
                <div class="service-card">
                    <div class="service-badges mb-2"><span class="badge py-2 px-3">Specialized Course</span></div>
                    <h3 class="service-title">Specialized Course</h3>
                    <p>This programme offers tailored, industry-relevant training designed to meet workforce demands.</p>
                    <ul>
                        <li>By integrating practical skill development with current market needs.</li>
                        <li>Ensures participants are well prepared for employment, enhance job readiness, and support career growth across key industries sector. </li>
                    </ul>
                    <a href="service_inquiry.php?service=specialized_course" class="btn btn-primary">Request Specialized Course</a>
                </div>
            </div>
        </div>

        <hr>

        <!-- Service 5 - ODL -->
        <div id="odl" class="row align-items-center mb-5 service-row">
            <div class="col-lg-6 service-media">
                <div id="svcCarousel5" class="carousel slide service-carousel" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    <div class="carousel-item active">
                      <img src="/ACE/images/service5-1.png" class="d-block w-100" alt="Service 5 image 1">
                    </div>
                    <div class="carousel-item">
                      <img src="/ACE/images/service5-2.png" class="d-block w-100" alt="Service 5 image 2">
                    </div>
                    <div class="carousel-item">
                      <img src="/ACE/images/service5-3.png" class="d-block w-100" alt="Service 5 image 3">
                    </div>
                  </div>
                  <button class="carousel-control-prev" type="button" data-bs-target="#svcCarousel5" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#svcCarousel5" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                  </button>
                </div>
            </div>

            <div class="col-lg-6 service-body mt-4 mt-lg-0">
                <div class="service-card">
                    <div class="service-badges mb-2"><span class="badge py-2 px-3">ODL</span></div>
                    <h3 class="service-title">Open and Distance Learning (ODL)</h3>
                    <p>ODL offers a flexible and accessible approach to education.</p>
                    <ul>
                        <li>Enabling learners to pursue academic and professional advancement remotely</li>
                        <li>Designed to support self-paced study</li>
                        <li>ODL bridges academia and industry by accommodating diverse learning needs without the requirement for on-campus attendance</li>
                    </ul>
                    <a href="odl_programmes.php" class="btn btn-primary">View ODL Programmes</a>
                </div>
            </div>
        </div>

        <hr>

        <!-- Service 7 - APEL -->
        <div id="apel" class="row align-items-center mb-5 service-row">
            <div class="col-lg-6 service-media">
                <div id="svcCarousel7" class="carousel slide service-carousel" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    <div class="carousel-item active">
                      <img src="/ACE/images/service3-1.jpg" class="d-block w-100" alt="APEL image 1">
                    </div>
                    <div class="carousel-item">
                      <img src="/ACE/images/service3-2.jpg" class="d-block w-100" alt="APEL image 2">
                    </div>
                    <div class="carousel-item">
                      <img src="/ACE/images/service3-3.jpg" class="d-block w-100" alt="APEL image 3">
                    </div>
                  </div>
                  <button class="carousel-control-prev" type="button" data-bs-target="#svcCarousel7" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#svcCarousel7" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                  </button>
                </div>
            </div>

            <div class="col-lg-6 service-body mt-4 mt-lg-0">
                <div class="service-card">
                    <div class="service-badges mb-2"><span class="badge py-2 px-3">APEL</span></div>
                    <h3 class="service-title">Accreditation of Prior Experiental learning (APEL)</h3>
                    <p> For APEL , we recognizes your work experience for academic advancement, It allows you to gain entry into programmes, earn course credits, and fast-track qualification under the Malaysian Qualifications Framework (MQF)</p>
                    <a href="apel.php" class="btn btn-primary">View APEL Programmes</a>              
                  </div>
            </div>
        </div>
        
        <hr>

        <!-- Service 6 - Micro-Credential -->
        <div id="micro-credential" class="row align-items-center mb-5 service-row">
            <div class="col-lg-6 service-media">
                <div id="svcCarousel6" class="carousel slide service-carousel" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    <div class="carousel-item active">
                      <img src="/ACE/images/service3-1.jpg" class="d-block w-100" alt="Micro-Credential image 1">
                    </div>
                    <div class="carousel-item">
                      <img src="/ACE/images/service3-2.jpg" class="d-block w-100" alt="Micro-Credential image 2">
                    </div>
                    <div class="carousel-item">
                      <img src="/ACE/images/service3-3.jpg" class="d-block w-100" alt="Micro-Credential image 3">
                    </div>
                  </div>
                  <button class="carousel-control-prev" type="button" data-bs-target="#svcCarousel6" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#svcCarousel6" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                  </button>
                </div>
            </div>

            <div class="col-lg-6 service-body mt-4 mt-lg-0">
                <div class="service-card">
                    <div class="service-badges mb-2"><span class="badge py-2 px-3">Micro-Credential</span></div>
                    <h3 class="service-title">Micro-Credential</h3>
                    <p>At UniKL RCMP, we offer tailored Micro-Credential programme designed to meet your specific needs. With string industry and academic expertise, we provide customixed solutions to help organizations achieve their goals</p>
                    <a href="micro_credential.php" class="btn btn-primary">View Micro-Credentials</a>                
                  </div>
            </div>
        </div>

                <hr>

        <!-- Service 2.5 - Center of Excellence (CoE) -->
        <div id="coe" class="row align-items-center mb-5 service-row">
            <div class="col-lg-6 service-media">
                <div id="svcCarouselCoE" class="carousel slide service-carousel" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    <div class="carousel-item active">
                      <img src="/ACE/images/coe-1.jpg" class="d-block w-100" alt="CoE 1">
                    </div>
                    <div class="carousel-item">
                      <img src="/ACE/images/coe-2.jpg" class="d-block w-100" alt="CoE 2">
                    </div>
                  </div>
                  <button class="carousel-control-prev" type="button" data-bs-target="#svcCarouselCoE" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#svcCarouselCoE" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                  </button>
                </div>
            </div>

            <div class="col-lg-6 service-body mt-4 mt-lg-0">
                <div class="service-card">
                    <div class="service-badges mb-2"><span class="badge py-2 px-3">Center of Excellence</span></div>
                    <h3 class="service-title">Center of Excellence (CoE)</h3>
                    <p>Our Centres of Excellence provide specialized support and services across regulatory, product development and halal certification areas. Choose the centre you need and request a tailored engagement.</p>

                    <div class="mb-3">
                        <label for="coeSelect" class="form-label">Choose Centre</label>
                        <select id="coeSelect" class="form-select">
                            <option value="">Select a Centre...</option>
                            <option value="halal" data-desc="Halal certification support and advisory">HALAL CENTER OF EXCELLENCE</option>
                            <option value="regulatory" data-desc="Regulatory compliance advisory and support">CENTER FOR REGULATORY COMPLIANCE</option>
                            <option value="product" data-desc="Product development & toxicity testing services">CENTER FOR PRODUCT DEVELOPMENT & TOXICITY TESTING</option>
                            <option value="language" data-desc="Language training, academic language services and linguistics research">CENTER OF LANGUAGE</option>
                            <option value="warisan" data-desc="Heritage & history research focused on Perak's cultural heritage">PUSAT KAJIAN WARISAN & SEJARAH PERAK</option>
                        </select>
                    </div>

                    <div id="coeMessage" class="alert alert-info" style="display:none;"></div>

                    <a href="service_inquiry.php?service=coe" id="btnRequestCoE" class="btn btn-primary">Request CoE</a>
                </div>
            </div>
        </div>

        <hr>

        <!-- Service - Event Space Rental (moved under CoE) -->
        <div id="event-space-rental" class="row align-items-center mb-5 service-row">
            <div class="col-lg-6 service-media">
                <div id="svcCarouselEvent" class="carousel slide service-carousel" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    <div class="carousel-item active">
                      <img src="/ACE/images/EV-rental.jpeg" class="d-block w-100" alt="Event Space Rental 1">
                    </div>
                  </div>
                  <button class="carousel-control-prev" type="button" data-bs-target="#svcCarouselEvent" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#svcCarouselEvent" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                  </button>
                </div>
            </div>

            <div class="col-lg-6 service-body mt-4 mt-lg-0">
                <div class="service-card">
                    <div class="service-badges mb-2"><span class="badge py-2 px-3">Event Space Rental</span></div>
                    <h3 class="service-title">Event Space Rental</h3>
                    <p>Host your conferences, workshops and seminars in our flexible event spaces. We provide AV support and optional catering—contact us for availability and rates.</p>
                    <a href="service_inquiry.php?service=event_space_rental" class="btn btn-primary">Request Event Space Rental</a>
                </div>
            </div>
        </div>
    </div>
    <hr>
</section>

<!-- Persistent Disclaimer (non-dismissible) -->
<div class="service-disclaimer" role="note" aria-live="polite">
  <div class="container">
    <strong>Disclaimer</strong>
    <div class="ms-3">The University reserves the right to change without notice any programs, policies, requirements, or regulations published in this catalogue. The catalogue is not to be regarded as a contract.</div>
  </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function(){
      const header = document.querySelector('header');
      if(header) document.documentElement.style.scrollPaddingTop = header.offsetHeight + 'px';

      // Pause carousels on mouseenter, resume on mouseleave
      document.querySelectorAll('.service-carousel').forEach(function(car){
        car.addEventListener('mouseenter', function(){ bootstrap.Carousel.getInstance(car)?.pause(); });
        car.addEventListener('mouseleave', function(){ bootstrap.Carousel.getInstance(car)?.cycle(); });
      });
      // Center of Excellence selector
      const coeSelect = document.getElementById('coeSelect');
      const coeMsg = document.getElementById('coeMessage');
      const coeBtn = document.getElementById('btnRequestCoE');

      if (coeSelect) {
        function updateCoE() {
          const val = coeSelect.value;
          const opt = coeSelect.options[coeSelect.selectedIndex];
          if (!val) {
            coeMsg.style.display = 'none';
            coeBtn.href = 'service_inquiry.php?service=coe';
            return;
          }
          const label = opt.text;
          const desc = opt.getAttribute('data-desc') || '';
          coeMsg.innerHTML = '<strong>Selected:</strong> ' + label + (desc ? '<br><small class="text-muted">' + desc + '</small>' : '');
          coeMsg.style.display = 'block';
          coeBtn.href = 'service_inquiry.php?service=coe&subject=' + encodeURIComponent('Center of Excellence: ' + label);
        }

        coeSelect.addEventListener('change', updateCoE);

        coeBtn.addEventListener('click', function(e){
          if (!coeSelect.value) {
            e.preventDefault();
            alert('Please select a Centre of Excellence before requesting.');
            coeSelect.focus();
          }
        });

        // initialize
        updateCoE();
      }



    })();
</script>
</body>
</html>