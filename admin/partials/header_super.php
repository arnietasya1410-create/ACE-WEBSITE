<?php

$current = basename($_SERVER['PHP_SELF'] ?? '');
function nav_active($name, $current){ return $name === $current ? ' active' : ''; }

// Get locked admins count
$locked_count = 0;
if (isset($conn) && $conn instanceof mysqli) {
    $result = $conn->query("SELECT COUNT(*) as count FROM admins WHERE is_locked = 1");
    if ($result) {
        $locked_count = $result->fetch_assoc()['count'];
    }
}
?>
<link rel="stylesheet" href="/ACE/User/front.css">
<header class="navbar-fixed">
  <div class="container-fluid d-flex align-items-center justify-content-between py-2 px-3">
    <div class="d-flex align-items-center">
      <a href="/ACE/admin/super_admin/dashboard.php" class="me-2 brand-logos d-flex align-items-center">
        <img src="/ACE/images/ACE.png" alt="ACE LOGO" style="height:65px;">
        <img src="/ACE/images/uniklrcmp.png" alt="UniKL RCMP" style="height:65px;">
        <img src="/ACE/images/hrdcorp.png" alt="HRD corp" style="height:65px;">
      </a>
    </div>

    <nav class="nav-main d-none d-lg-flex">
      <a href="/ACE/admin/super_admin/dashboard.php" class="<?= nav_active('dashboard.php', $current) ?>">Dashboard</a>
      <a href="/ACE/admin/super_admin/admin_management.php" class="<?= nav_active('admin_management.php', $current) ?> position-relative">
        Admins
        <?php if ($locked_count > 0): ?>
          <span class="badge bg-danger ms-1" style="font-size: 0.7rem; vertical-align: middle;">
            <?= $locked_count ?>
          </span>
        <?php endif; ?>
      </a>
      <a href="/ACE/admin/program_list.php" class="<?= nav_active('program_list.php', $current) ?>">Programmes</a>
      <a href="/ACE/admin/news_edit.php" class="<?= nav_active('news_edit.php', $current) ?>">Newsletters</a>
      <a href="/ACE/admin/super_admin/activity_logs.php" class="<?= nav_active('activity_logs.php', $current) ?>">Logs</a>
      <a href="/ACE/admin/logout.php" class="text-danger">Logout</a>
    </nav>

    <div class="d-lg-none">
      <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#superAdminMobileNav">Menu</button>
    </div>
  </div>
</header>

<!-- Mobile offcanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="superAdminMobileNav">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">🛡️ Super Admin</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <a class="d-block py-2" href="/ACE/admin/super_admin/dashboard.php">Dashboard</a>
    <a class="d-block py-2" href="/ACE/admin/super_admin/admin_management.php">Admins <?php if ($locked_count > 0) echo "($locked_count)"; ?></a>
    <a class="d-block py-2" href="/ACE/admin/program_list.php">Programmes</a>
    <a class="d-block py-2" href="/ACE/admin/news_edit.php">Newsletters</a>
    <a class="d-block py-2" href="/ACE/admin/super_admin/activity_logs.php">Logs</a>
    <a class="d-block py-2 text-danger" href="/ACE/admin/logout.php">Logout</a>
  </div>
</div>

<style>
  .nav-main {
    gap: 0.5rem;
  }
  
  .nav-main a {
    font-size: 0.95rem;
    padding: 0.5rem 0.75rem;
    white-space: nowrap;
  }
  
  .dropdown {
    position: relative;
  }
  
  .dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1000;
    min-width: 200px;
    padding: 0.5rem 0;
    margin: 0;
    font-size: 0.95rem;
    color: #212529;
    text-align: left;
    list-style: none;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0,0,0,.15);
    border-radius: 0.25rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,.175);
  }
  
  .dropdown-menu.show {
    display: block;
  }
  
  .dropdown-item {
    display: block;
    width: 100%;
    padding: 0.5rem 1rem;
    clear: both;
    font-weight: 400;
    color: #212529;
    text-align: inherit;
    text-decoration: none;
    white-space: nowrap;
    background-color: transparent;
    border: 0;
  }
  
  .dropdown-item:hover {
    background-color: #f8f9fa;
  }
  
  #questionsDropdown .bi-chevron-down {
    transition: transform 0.2s;
  }
  
  #questionsDropdown.active .bi-chevron-down,
  .dropdown-menu.show ~ #questionsDropdown .bi-chevron-down {
    transform: rotate(180deg);
  }
  
  #totalQuestionsBadge,
  #queriesBadgeDropdown,
  #inquiriesBadgeDropdown {
    animation: pulse 2s infinite;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.7rem;
  }
  
  @keyframes pulse {
    0%, 100% {
      opacity: 1;
      transform: scale(1);
    }
    50% {
      opacity: 0.8;
      transform: scale(1.05);
    }
  }
</style>

<script>
// Manual dropdown toggle
document.addEventListener('DOMContentLoaded', function() {
  const dropdownToggle = document.getElementById('questionsDropdown');
  const dropdownMenu = document.getElementById('questionsMenu');
  
  if (dropdownToggle && dropdownMenu) {
    dropdownToggle.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      dropdownMenu.classList.toggle('show');
      
      // Rotate arrow
      const arrow = dropdownToggle.querySelector('.bi-chevron-down');
      if (arrow) {
        arrow.style.transform = dropdownMenu.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
      }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
        const arrow = dropdownToggle.querySelector('.bi-chevron-down');
        if (arrow) {
          arrow.style.transform = 'rotate(0deg)';
        }
      }
    });
  }
});

// Auto-refresh notification badges every 30 seconds
setInterval(function() {
  // Fetch queries count
  fetch('/ACE/admin/get_pending_count.php')
    .then(response => response.json())
    .then(data => {
      updateBadge('queriesBadgeDropdown', data.count);
      updateTotalBadge();
      
      if (data.count > (window.lastPendingCount || 0)) {
        showBrowserNotification('query', data.count);
      }
      window.lastPendingCount = data.count;
    })
    .catch(err => console.error('Failed to fetch pending count:', err));

  // Fetch inquiries count
  fetch('/ACE/admin/get_pending_inquiries_count.php')
    .then(response => response.json())
    .then(data => {
      updateBadge('inquiriesBadgeDropdown', data.count);
      updateTotalBadge();
      
      if (data.count > (window.lastPendingInquiries || 0)) {
        showBrowserNotification('inquiry', data.count);
      }
      window.lastPendingInquiries = data.count;
    })
    .catch(err => console.error('Failed to fetch pending inquiries count:', err));
}, 30000);

function updateBadge(badgeId, count) {
  const badge = document.getElementById(badgeId);
  const parentItem = badge?.closest('.dropdown-item');
  
  if (count > 0) {
    if (badge) {
      badge.textContent = count;
    } else if (parentItem) {
      const newBadge = document.createElement('span');
      newBadge.id = badgeId;
      newBadge.className = 'badge bg-warning text-dark';
      newBadge.textContent = count;
      parentItem.appendChild(newBadge);
    }
  } else {
    if (badge) badge.remove();
  }
}

function updateTotalBadge() {
  const queriesBadge = document.getElementById('queriesBadgeDropdown');
  const inquiriesBadge = document.getElementById('inquiriesBadgeDropdown');
  const totalBadge = document.getElementById('totalQuestionsBadge');
  
  const queriesCount = queriesBadge ? parseInt(queriesBadge.textContent) : 0;
  const inquiriesCount = inquiriesBadge ? parseInt(inquiriesBadge.textContent) : 0;
  const total = queriesCount + inquiriesCount;
  
  if (total > 0) {
    if (totalBadge) {
      totalBadge.textContent = total;
    } else {
      const dropdown = document.getElementById('questionsDropdown');
      if (dropdown) {
        const newBadge = document.createElement('span');
        newBadge.id = 'totalQuestionsBadge';
        newBadge.className = 'badge bg-danger ms-1';
        newBadge.style.cssText = 'font-size: 0.7rem; vertical-align: middle;';
        newBadge.textContent = total;
        dropdown.appendChild(newBadge);
      }
    }
  } else {
    if (totalBadge) totalBadge.remove();
  }
}

function showBrowserNotification(type, count) {
  if ('Notification' in window && Notification.permission === 'granted') {
    const title = type === 'query' ? 'New Query Received' : 'New Inquiry Received';
    const body = `You have ${count} pending ${type === 'query' ? (count === 1 ? 'query' : 'queries') : (count === 1 ? 'inquiry' : 'inquiries')}`;
    
    new Notification(title, {
      body: body,
      icon: '/ACE/images/ACE.png',
      tag: type + '-notification',
      requireInteraction: false
    });
  }
}

function requestNotificationPermission() {
  if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
  }
}

window.addEventListener('load', requestNotificationPermission);
</script>
