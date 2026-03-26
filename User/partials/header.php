<?php

$current = basename($_SERVER['PHP_SELF'] ?? '');
function nav_active($name, $current){ return $name === $current ? ' active' : ''; }
?>
<header class="navbar-fixed">
  <div class="container d-flex align-items-center justify-content-between py-2">
    <div class="d-flex align-items-center">
      <a href="index.php" class="me-2 brand-logos d-flex align-items-center">
        <img src="/ACE/images/ACE.png" alt="ACE LOGO" class="brand-logo img-fluid">
        <img src="/ACE/images/uniklrcmp.png" alt="UniKL RCMP" class="brand-logo img-fluid">
        <img src="/ACE/images/hrdcorp.png" alt="HRD corp" class="brand-logo img-fluid">
      </a>
    </div>

    <nav class="nav-main d-none d-md-flex align-items-center">
      <a href="index.php" class="<?= nav_active('index.php', $current) ?>">Home</a>
      
      <div class="dropdown position-relative d-inline-block">
        <a href="#" class="position-relative <?= in_array($current, ['about.php','IIP.php']) ? 'active' : '' ?>" id="aboutDropdown" role="button">
          About
          <i class="bi bi-chevron-down ms-1" style="font-size: 0.75rem;"></i>
        </a>
        <ul class="dropdown-menu" id="aboutMenu">
          <li><a class="dropdown-item" href="IIP.php">About IIP</a></li>
          <li><a class="dropdown-item" href="about.php">Our Team (ACE)</a></li>
        </ul>
      </div>
      
      <!-- Services Dropdown -->
      <div class="dropdown position-relative d-inline-block">
        <a href="#" class="position-relative <?= in_array($current, ['features.php', 'courses.php', 'certificate_programmes.php', 'odl_programmes.php', 'micro_credential.php', 'apel.php']) ? 'active' : '' ?>" 
           id="servicesDropdown" role="button">
          Our Services
          <i class="bi bi-chevron-down ms-1" style="font-size: 0.75rem;"></i>
        </a>
        <ul class="dropdown-menu" id="servicesMenu">
          <li><a class="dropdown-item" href="features.php">All Services</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="courses.php">Short Courses</a></li>
          <li><a class="dropdown-item" href="features.php#consultancy">Consultancy Programme</a></li>
          <li><a class="dropdown-item" href="micro_credential.php">Micro-Credential</a></li> 
          <li><a class="dropdown-item" href="certificate_programmes.php">Professional Certificate</a></li>
          <li><a class="dropdown-item" href="features.php#specialized">Specialized Course</a></li>
          <li><a class="dropdown-item" href="odl_programmes.php">Open and Distance Learning</a></li>
          <li><a class="dropdown-item" href="apel.php">APEL</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="features.php#coe">Center of Excellence (CoE)</a></li>
          <li><a class="dropdown-item" href="features.php#event-space-rental">Event Space Rental</a></li>
        </ul>
      </div> 
      
      <div class="dropdown position-relative d-inline-block">
        <a href="#" class="position-relative <?= in_array($current, ['newsletters.php', 'calendar.php']) ? 'active' : '' ?>" 
           id="eventsDropdown" role="button">
          Events
          <i class="bi bi-chevron-down ms-1" style="font-size: 0.75rem;"></i>
        </a>
        <ul class="dropdown-menu" id="eventsMenu">
          <li><a class="dropdown-item" href="calendar.php">Events Calendar</a></li>
          <li><a class="dropdown-item" href="newsletters.php">Newsletters</a></li>
        </ul>
      </div>
      <a href="service_inquiry.php" class="<?= nav_active('service_inquiry.php', $current) ?>">Contact</a>
    </nav>

    <div class="d-md-none">
      <button class="btn btn-outline-secondary btn-sm offcanvas-toggle-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">Menu</button>
    </div>
  </div>
</header>

<!-- Mobile offcanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNav">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">ACE</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <a class="d-block py-2" href="index.php">Home</a>
    
    <div class="accordion accordion-flush" id="mobileAboutAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#mobileAbout">
            About
          </button>
        </h2>
        <div id="mobileAbout" class="accordion-collapse collapse" data-bs-parent="#mobileAboutAccordion">
          <div class="accordion-body ps-4">
            <a class="d-block py-2" href="IIP.php">About IIP</a>
            <a class="d-block py-2" href="about.php">Our Team (ACE)</a>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Mobile Services Accordion -->
    <div class="accordion accordion-flush" id="mobileServicesAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#mobileServices">
            Services
          </button>
        </h2>
        <div id="mobileServices" class="accordion-collapse collapse" data-bs-parent="#mobileServicesAccordion">
          <div class="accordion-body ps-4">
            <a class="d-block py-2" href="service_inquiry.php">Contact Us</a>
          </div>
        </div>
      </div>
    </div>
    <div id="mobileEventsAccordion" class="accordion accordion-flush">
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#mobileEvents">
            Events
          </button>
        </h2>
        <div id="mobileEvents" class="accordion-collapse collapse" data-bs-parent="#mobileEventsAccordion">
          <div class="accordion-body ps-4">
            <a class="d-block py-2" href="calendar.php">Events Calendar</a>
            <a class="d-block py-2" href="newsletters.php">Newsletters</a>
          </div>
        </div>
      </div>
    </div>
    <a class="d-block py-2" href="service_inquiry.php">Contact</a>
  </div>
</div>

<style>
/* Dropdown styling */
.dropdown {
  position: relative;
}

.dropdown-menu {
  display: none;
  position: absolute;
  top: 100%;
  left: 0;
  z-index: 1000;
  min-width: 250px;
  padding: 0.5rem 0;
  margin: 0;
  font-size: 1rem;
  color: #212529;
  text-align: left;
  list-style: none;
  background-color: #fff;
  background-clip: padding-box;
  border: 1px solid rgba(0,0,0,.08);
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,.1);
}

.dropdown-menu.show {
  display: block;
}

.dropdown-item {
  display: block;
  width: 100%;
  padding: 0.5rem 1.25rem;
  clear: both;
  font-weight: 400;
  color: #333;
  text-align: inherit;
  text-decoration: none;
  white-space: nowrap;
  background-color: transparent;
  border: 0;
  transition: all 0.2s;
}

.dropdown-item:hover {
  background-color: rgba(111,66,193,0.08);
  color: var(--accent, #6f42c1);
  padding-left: 1.5rem;
}

.dropdown-divider {
  height: 0;
  margin: 0.5rem 0;
  overflow: hidden;
  border-top: 1px solid rgba(0,0,0,.1);
}

.nav-main a {
  color: #333;
  text-decoration: none;
  padding: 0.5rem 1rem;
  transition: color 0.2s;
  display: inline-block;
}

.nav-main a:hover,
.nav-main a.active {
  color: var(--accent, #6f42c1);
}

#servicesDropdown .bi-chevron-down,
#eventsDropdown .bi-chevron-down {
  transition: transform 0.2s;
}

/* Mobile accordion styling */
.offcanvas-body .accordion-button {
  background: transparent;
  border: none;
  padding-left: 0;
}

.offcanvas-body .accordion-button:not(.collapsed) {
  background: transparent;
  color: var(--accent, #6f42c1);
  box-shadow: none;
}

.offcanvas-body .accordion-body {
  padding-top: 0;
}
</style>

<script>
// Manual dropdown toggle helper
document.addEventListener('DOMContentLoaded', function() {
  function setupDropdown(toggleId, menuId) {
    const toggle = document.getElementById(toggleId);
    const menu = document.getElementById(menuId);
    if (!toggle || !menu) return;

    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      menu.classList.toggle('show');

      const arrow = toggle.querySelector('.bi-chevron-down');
      if (arrow) {
        arrow.style.transform = menu.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
      }
    });

    // Close this dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!toggle.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove('show');
        const arrow = toggle.querySelector('.bi-chevron-down');
        if (arrow) arrow.style.transform = 'rotate(0deg)';
      }
    });
  }

  setupDropdown('aboutDropdown', 'aboutMenu');
  setupDropdown('servicesDropdown', 'servicesMenu');
  setupDropdown('eventsDropdown', 'eventsMenu');
});
</script>