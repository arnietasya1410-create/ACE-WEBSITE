<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IIP — ACE | Advancement and Continuing Education</title>
    <meta name="description" content="Institute for Industrial Partnerships — content coming soon.">

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
            min-height: 60vh;
            background-image: url('/ACE/images/IIIP-bg.png');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            display:flex;
            align-items:center;
            color:#fff;
        }
        .hero .overlay { background: rgba(0,0,0,0.45); padding:40px; border-radius:8px; }
        .section-card { padding:48px 0; }
        footer { background:var(--accent); color:#fff; padding:28px 0; font-size:0.95rem; }
        @media (max-width:767px){ .brand-logos img{max-height:40px} .hero .overlay{padding:20px} }
        .btn-primary{ background-color:var(--accent) !important; border-color:var(--accent) !important; }
        .btn-primary:hover, .btn-primary:focus{ background-color:var(--accent-600) !important; border-color:var(--accent-600) !important; }
        nav a.active, nav a:hover { color: var(--accent) !important; }

        /* IIIP Infographic (mind map layout: 3 up, 2 down) */
        .infographic { padding: 48px 0; position: relative; }
        .mindmap {
            position: relative;
            max-width:1100px;
            margin: 0 auto;
            padding: 36px 12px;
        }
        .mindmap-svg {
            position:absolute;
            inset:0;
            pointer-events:none;
            z-index:0;
        }
        .mindmap-grid {
            position:relative;
            z-index:1;
            display:grid;
            grid-template-columns: repeat(3, 1fr);
            /* rows: top nodes, spacer for hub, middle nodes, bottom centered CWAL */
            grid-template-rows: auto 18px auto auto;
            gap: 40px 48px; /* more breathing room */
            align-items:center;
            width:100%;
        }
        /* place nodes explicitly into grid cells to create: top-left, top-right, hub center, middle-left, middle-right, bottom-center */
        .mindmap-grid .mindmap-node { justify-self:center; }
        .mindmap-grid .node-ace { grid-column: 1; grid-row: 1; justify-self:start; }
        .mindmap-grid .node-il { grid-column: 3; grid-row: 1; justify-self:end; }
        .mindmap-grid .node-teknoputra { grid-column: 1; grid-row: 3; justify-self:start; }
        .mindmap-grid .node-uio { grid-column: 3; grid-row: 3; justify-self:end; }
        .mindmap-grid .node-cwal { grid-column: 2; grid-row: 4; justify-self:center; }
        .mindmap-node {
            background:#fff;
            border-radius:12px;
            padding:18px;
            box-shadow:0 6px 18px rgba(18,24,26,0.06);
            cursor:pointer;
            transition:transform .18s ease, box-shadow .18s ease;
            display:flex;
            gap:14px;
            align-items:flex-start;
            border: none;
            text-align:left;
            width:260px; /* slightly wider for spacing */
            max-width:100%;
            position:relative;
            z-index:3; /* keep nodes above hub */
        }
        .mindmap-node:focus, .mindmap-node:hover { transform:translateY(-6px); box-shadow:0 14px 30px rgba(18,24,26,0.08); outline:none; }
        .mindmap-node .icon { min-width:56px; height:56px; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#fff; background: linear-gradient(135deg, var(--accent), var(--accent-600)); font-size:1.5rem; box-shadow:0 6px 12px rgba(111,66,193,0.12); }
        .mindmap-node h5 { margin:0 0 6px; font-weight:600; font-size:1.05rem; }
        .mindmap-node p { margin:0; color:#555; font-size:0.94rem; line-height:1.3rem; }

        /* central hub */
        .mindmap-hub {
            position:absolute;
            width:88px; height:88px;
            border-radius:50%;
            background:linear-gradient(135deg, #fff, #f7f7fb);
            border: 4px solid rgba(111,66,193,0.12);
            left:50%;
            top:45%; /* nudged up to avoid overlapping middle-top node */
            transform:translate(-50%,-50%);
            display:flex;
            align-items:center;
            justify-content:center;
            z-index:1; /* sit *under* nodes */
            font-size:0.95rem;
            color:var(--accent-600);
            font-weight:700;
            box-shadow:0 8px 20px rgba(111,66,193,0.08);
            cursor: pointer;
            transition: transform .12s ease, box-shadow .12s ease;
        }
        .mindmap-hub:hover, .mindmap-hub:focus { transform: translateY(-6px); box-shadow:0 14px 30px rgba(111,66,193,0.08); outline:none; }

        /* animated purple connectors */
        .mindmap-svg .mm-line { stroke: var(--accent); stroke-width:2.5; stroke-linecap: round; fill:none; stroke-dasharray: 8 10; animation: mm-move 2s linear infinite; opacity:0.98; }
        .mindmap-svg marker path { fill: var(--accent); }
        @keyframes mm-move { to { stroke-dashoffset: -18; } }
        @media (max-width:767px){
            .mindmap-grid { grid-template-columns: 1fr; gap:14px; transform:none; }
            .mindmap-node{ width:100%; }
            .mindmap-hub { display:none; }
            .mindmap-svg { display:none; }
        }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/partials/header.php'; ?>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <div class="overlay">
            <p class="lead mb-0">INTERNATIONAL INDUSTRIAL & INSTITUTIONAL PARTNERSHIP (IIIP) DIVISION</p>
        </div>
    </div>
</section>

<!-- About content -->
<section class="section-card">
    <div class="container">
        <div class="row align-items-center mb-4">
            <div class="col-12">
                <h2>About IIIP</h2>
                <p>The International, Industrial & Institutional Partnership (IIIP) Division at UniKL RCMP drives strategic collaboration across education, industry, and global engagement. IIIP delivers professional development through Advancement & Continuing Education, strengthens industry collaboration via Industrial Linkages, fosters global partnerships through the UniKL International Office, supports innovation and entrepreneurship through Teknoputra, and champions women’s leadership via CWAL.</p>
                <p>Together, IIIP enhances UniKL’s impact, relevance, and excellence at both national and international levels.</p>
            </div>
        </div>
    </div>
</section>

<!-- IIIP Interactive Infographic -->
<section class="section-card infographic bg-white">
    <div class="container">
        <div class="row mb-3">
            <div class="col-12 text-center">
                <h3>IIIP Divisions</h3>
                <p class="infographic-note">Hover or tap any card to read more about each department.</p>
            </div>
        </div>
        <div class="infographic-grid" role="list">
            <div class="mindmap" aria-hidden="false">
                <svg class="mindmap-svg" aria-hidden="true"></svg>
                <div class="mindmap-hub" role="button" tabindex="0" data-title="International, Industrial & Institutional Partnership (IIIP)" data-desc="IIIP coordinates Advancement & Continuing Education (ACE), Industrial Linkages, University International Office, Teknoputra entrepreneurship and CWAL women's leadership programs." aria-haspopup="dialog">IIIP</div> 
                <div class="mindmap-grid" role="list">
                        <button class="mindmap-node node-ace" role="listitem" data-dept="ACE" data-title="Advancement & Continuing Education (ACE)" data-desc="Advancement and Continuing Education (ACE) at Universiti Kuala Lumpur Royal College of Medicine Perak (UniKL RCMP) is dedicated to lifelong learning and professional development. ACE extends excellence to individuals and organizations, offering industry-relevant courses led by renowned academic and industry experts.">
                            <div class="icon"><i class="bi bi-book"></i></div>
                            <div><h5>ACE</h5><p>Professional development & lifelong learning for individuals and organisations.</p></div>
                        </button>

                        <button class="mindmap-node node-il" role="listitem" data-dept="IL" data-title="Industrial Linkages (ILD)" data-desc="The Industrial Linkages (IL) engages with industry to address mutual needs. IL is the gateway for industry and university collaboration: internships, recruitment, expertise sharing, and programs that develop talents suitable for current industry needs.">
                            <div class="icon"><i class="bi bi-gear"></i></div>
                            <div><h5>IL</h5><p>Bridging industry and university through internships, training and partnership.</p></div>
                        </button>

                        <button class="mindmap-node node-teknoputra" role="listitem" data-dept="TEKNOPUTRA" data-title="Teknoputra" data-desc="Teknoputra is a department that focuses on entrepreneurship development for students and alumni. It manages activities to develop new entrepreneurs with the right mindset and attributes.">
                            <div class="icon"><i class="bi bi-lightbulb"></i></div>
                            <div><h5>TEKNOPUTRA</h5><p>Entrepreneurship development and support for students and alumni.</p></div>
                        </button>

                        <button class="mindmap-node node-uio" role="listitem" data-dept="UIO" data-title="University International Office (UIO)" data-desc="University International Office (UIO) manages and coordinates international partnerships & collaborations, exchange & mobility programmes, customised edutourism programmes, and international visits at UniKL RCMP. We are the primary liaison for UniKL's global partners.">
                            <div class="icon"><i class="bi bi-globe2"></i></div>
                            <div><h5>UIO</h5><p>Central hub for UniKL's international partnerships and mobility programmes.</p></div>
                        </button>

                        <button class="mindmap-node node-cwal" role="listitem" data-dept="CWAL" data-title="Centre For Women Advancement and Leadership (CWAL)" data-desc="CWAL empowers Malaysian women to attain prosperity and a higher quality of life in line with the Shared Prosperity Vision 2030.">
                            <div class="icon"><i class="bi bi-people"></i></div>
                            <div><h5>CWAL</h5><p>Empowering women towards leadership and shared prosperity.</p></div>
                        </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Department details modal -->
    <div class="modal fade" id="deptModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Department</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">Description</div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

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

      const deptModalEl = document.getElementById('deptModal');
      if(deptModalEl && typeof bootstrap !== 'undefined') {
        const deptModal = new bootstrap.Modal(deptModalEl);
        document.querySelectorAll('.infographic-card, .mindmap-node, .mindmap-hub').forEach(card=>{
            card.addEventListener('click', ()=>{
                const title = card.dataset.title || card.dataset.dept || 'IIIP';
                const desc = card.dataset.desc || '';
                const modalTitle = deptModalEl.querySelector('.modal-title');
                const modalBody = deptModalEl.querySelector('.modal-body');

                // Populate modal content safely
                modalTitle.textContent = title;
                if (desc && desc.trim() !== '') {
                    // Use textContent to avoid injecting HTML
                    modalBody.innerHTML = '';
                    const p = document.createElement('p');
                    p.textContent = desc;
                    modalBody.appendChild(p);
                } else {
                    modalBody.innerHTML = '<p class="text-muted">No description available.</p>';
                }

                deptModal.show();
            });
            card.addEventListener('keydown', (e)=>{
                if(e.key === 'Enter' || e.key === ' ') { e.preventDefault(); card.click(); }
            });
        });
      }

      /* Draw SVG connectors from center hub to each node (curved arrows) */
      function drawMindmapLines(){
        const svg = document.querySelector('.mindmap-svg');
        const container = document.querySelector('.mindmap');
        if(!svg || !container) return;
        const rect = container.getBoundingClientRect();
        svg.setAttribute('width', rect.width);
        svg.setAttribute('height', rect.height);
        svg.innerHTML = '';

        // add defs and arrow marker
        const defs = document.createElementNS('http://www.w3.org/2000/svg','defs');
        const marker = document.createElementNS('http://www.w3.org/2000/svg','marker');
        marker.setAttribute('id','mm-arrow');
        marker.setAttribute('markerWidth','8');
        marker.setAttribute('markerHeight','8');
        marker.setAttribute('refX','8');
        marker.setAttribute('refY','4');
        marker.setAttribute('orient','auto');
        const markerPath = document.createElementNS('http://www.w3.org/2000/svg','path');
        markerPath.setAttribute('d','M0,0 L8,4 L0,8 z');
        markerPath.setAttribute('fill','var(--accent)');
        marker.appendChild(markerPath);
        defs.appendChild(marker);
        svg.appendChild(defs);

        const hub = document.querySelector('.mindmap-hub');
        if(!hub) return;
        const hubRect = hub.getBoundingClientRect();
        const hubCenter = { x: hubRect.left + hubRect.width/2 - rect.left, y: hubRect.top + hubRect.height/2 - rect.top };

        document.querySelectorAll('.mindmap-node').forEach(node=>{
            const nodeRect = node.getBoundingClientRect();
            const nodeCenter = { x: nodeRect.left + nodeRect.width/2 - rect.left, y: nodeRect.top + nodeRect.height/2 - rect.top };

            // build a smooth cubic Bezier curve from hub -> node
            const dx = nodeCenter.x - hubCenter.x;
            const dy = nodeCenter.y - hubCenter.y;
            // control points pushed out a bit to create nice curves
            const c1 = { x: hubCenter.x + dx * 0.28, y: hubCenter.y + dy * 0.12 };
            const c2 = { x: hubCenter.x + dx * 0.72, y: hubCenter.y + dy * 0.88 };

            const path = document.createElementNS('http://www.w3.org/2000/svg','path');
            const d = `M ${hubCenter.x} ${hubCenter.y} C ${c1.x} ${c1.y}, ${c2.x} ${c2.y}, ${nodeCenter.x} ${nodeCenter.y}`;
            path.setAttribute('d', d);
            path.setAttribute('class', 'mm-line');
            path.setAttribute('marker-end', 'url(#mm-arrow)');
            svg.appendChild(path);
        });
      }

      // Draw on load and resize
      window.addEventListener('load', drawMindmapLines);
      window.addEventListener('resize', ()=>{
        clearTimeout(window._mindmapResize);
        window._mindmapResize = setTimeout(drawMindmapLines, 120);
      });
    })();
</script>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>