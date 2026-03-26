<?php
$current = basename($_SERVER['PHP_SELF'] ?? '');
function nav_active($name, $current){ return $name === $current ? ' active' : ''; }
?>
<link rel="stylesheet" href="/ACE/User/front.css">
<header class="navbar-fixed">
  <div class="container d-flex align-items-center justify-content-between py-2">
    <div class="d-flex align-items-center">
      <a href="/ACE/admin/dashboard.php" class="me-2 brand-logos d-flex align-items-center">
        <img src="/ACE/images/ACE.png" alt="ACE LOGO" style="height:72px;">
        <img src="/ACE/images/uniklrcmp.png" alt="UniKL RCMP" style="height:72px;">
        <img src="/ACE/images/hrdcorp.png" alt="HRD corp" style="height:72px;">
      </a>
      <div class="ms-3">
        <div class="d-flex align-items-center gap-2">
          <span style="font-weight:600;color:var(--accent)">ACE — <?= htmlspecialchars($admin_username) ?> Admin</span>
        </div>
        <small class="text-muted">Management console</small>
      </div>
    </div>

    <nav class="nav-main d-none d-md-flex">
      <a href="/ACE/admin/dashboard.php" class="<?= nav_active('dashboard.php', $current) ?>">Dashboard</a>
      <a href="/ACE/admin/program_list.php" class="<?= nav_active('program_list.php', $current) ?>">Programmes</a>
      <a href="/ACE/admin/news_edit.php" class="<?= nav_active('news_edit.php', $current) ?>">Newsletters</a>
      <a href="/ACE/admin/logout.php" class="text-danger">Logout</a>
    </nav>

    <div class="d-md-none">
      <button class="btn btn-outline-secondary btn-sm offcanvas-toggle-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminMobileNav">Menu</button>
    </div>
  </div>
</header>

<!-- Mobile offcanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="adminMobileNav">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">ACE Admin</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <a class="d-block py-2" href="/ACE/admin/dashboard.php">Dashboard</a>
    <a class="d-block py-2" href="/ACE/admin/program_list.php">Programmes</a>
    <a class="d-block py-2" href="/ACE/admin/news_edit.php">Newsletters</a>
    <a class="d-block py-2 text-danger" href="/ACE/admin/logout.php">Logout</a>
  </div>
</div>

<style>
  .dropdown-toggle {
    text-decoration: none;
    color: inherit;
  }
  
  .dropdown-toggle::after {
    margin-left: 0.3em;
    vertical-align: middle;
  }
  
  #totalQuestionsBadge,
  #queriesBadgeDropdown,
  #inquiriesBadgeDropdown {
    animation: pulse 2s infinite;
    padding: 2px 6px;
    border-radius: 10px;
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
// Manual dropdown toggle (no Bootstrap dependency)
document.addEventListener('DOMContentLoaded', function() {
  const dropdownToggle = document.getElementById('questionsDropdown');
  const dropdownMenu = dropdownToggle?.nextElementSibling;
  
  if (dropdownToggle && dropdownMenu) {
    dropdownToggle.addEventListener('click', function(e) {
      e.preventDefault();
      dropdownMenu.classList.toggle('show');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
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