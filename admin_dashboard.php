<?php
require_once __DIR__ . '/config.php';

// enforce admin user (user_id == 1)
if (empty($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: login.php?redirect=admin_dashboard');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>AMUNRA — Admin Console</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<!-- Chart.js for charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* modern minimal admin theme */
:root{
  --bg:#f6f7fb;
  --card:#ffffff;
  --muted:#6b7280;
  --primary:#0b5a2b; /* deep emerald */
  --accent:#c19a53;  /* gold accent for brand */
  --radius:14px;
  --glass: rgba(255,255,255,0.6);
  --shadow: 0 10px 30px rgba(2,6,23,0.06);
}

*{box-sizing:border-box}
html,body{height:100%;margin:0;font-family:"Inter",system-ui,Segoe UI,Roboto,Arial;color:#0f1724;background:var(--bg);-webkit-font-smoothing:antialiased}
.header{
  height:72px;display:flex;align-items:center;justify-content:space-between;padding:0 28px;background:linear-gradient(90deg,#071122,#0b1320);color:#fff;
  box-shadow: 0 4px 18px rgba(2,6,23,0.12);
}
.brand{display:flex;align-items:center;gap:12px;font-weight:700}
.brand .logo{width:38px;height:38px;border-radius:8px;background:linear-gradient(135deg,var(--accent),#b8862f);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800}
.header .actions{display:flex;gap:12px;align-items:center}
.container{max-width:1320px;margin:20px auto;padding:0 20px}
.layout{display:grid;grid-template-columns:120px 1fr;gap:20px;align-items:start}

/* slim sidebar */
.sidebar{background:transparent;padding:8px 4px;border-radius:12px;display:flex;flex-direction:column;gap:8px;align-items:center}
.nav-btn{width:100%;height:72px;border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--muted);background:transparent;border: none;cursor:pointer;transition:all .18s ease;padding:8px 6px}
.nav-btn i{font-size:20px}
.nav-btn .nav-label{display:block;margin-top:6px;font-size:12px;color:var(--muted);font-weight:600}
.nav-btn.active, .nav-btn:hover{background:rgba(11,90,43,0.08);color:var(--primary)}
.nav-btn.active .nav-label, .nav-btn:hover .nav-label{color:var(--primary)}

/* Edit / Delete button styles */
.btn-edit {
  background: #e6fbef;
  color: #0b6a2f;
  border: 1px solid rgba(11,106,47,0.12);
  padding: 6px 10px;
  border-radius: 8px;
  cursor: pointer;
  font-weight:700;
}
.btn-edit:hover { background:#d6f6df; transform: translateY(-1px); }

.btn-delete {
  background: #fff0f0;
  color: #b91c1c;
  border: 1px solid rgba(185,28,28,0.08);
  padding: 6px 10px;
  border-radius: 8px;
  cursor: pointer;
  font-weight:700;
}
.btn-delete:hover { background:#ffe6e6; transform: translateY(-1px); }

/* Make table header a single gold color */
.table th{background: var(--accent); color:#fff; padding:12px; text-align:left; font-weight:700}

/* main content */
.main{display:flex;flex-direction:column;gap:18px}
.grid-top{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.kpi{background:var(--card);padding:16px;border-radius:12px;box-shadow:var(--shadow);display:flex;flex-direction:column;gap:6px}
.kpi h6{margin:0;color:var(--muted);font-size:12px}
.kpi .value{font-weight:700;font-size:1.25rem;color:#07281a}

/* multi-column sections */
.panel{background:var(--card);border-radius:12px;padding:16px;box-shadow:var(--shadow)}
.panel h3{margin:0 0 12px 0;color:#07281a}
.row{display:flex;gap:14px;align-items:flex-start}

/* charts and lists */
.chart-wrap{flex:1;min-height:220px}
.list{flex:1;max-height:420px;overflow:auto;padding-right:6px}
.item{display:flex;align-items:center;gap:12px;padding:10px;border-radius:10px;border:1px solid rgba(10,20,10,0.02);margin-bottom:10px;background:linear-gradient(180deg,#fff,#fbfbfb)}
.item .meta{font-size:13px;color:var(--muted)}
.table{width:100%;border-collapse:collapse}
.table th{background: var(--accent); color:#fff; padding:12px; text-align:left; font-weight:700;}
.table td{padding:12px;border-bottom:1px solid rgba(10,20,10,0.03);font-size:13px;color:#111}
.controls{display:flex;gap:8px;align-items:center;flex-wrap:wrap}

/* compact modals */
.modal{position:fixed;inset:0;background:rgba(3,7,18,0.5);display:none;align-items:center;justify-content:center;z-index:9999;padding:18px}
.modal .dialog{width:860px;max-width:98%;background:var(--card);padding:18px;border-radius:12px;box-shadow:0 18px 48px rgba(2,6,23,0.18)}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.field label{display:block;font-size:13px;color:var(--muted);margin-bottom:6px}
.input, textarea, select{width:100%;padding:10px;border-radius:8px;border:1px solid rgba(10,20,10,0.04)}
.btn-ghost{background:transparent;border:1px solid rgba(10,20,10,0.06);padding:8px 12px;border-radius:8px;cursor:pointer}
.btn-primary{background:linear-gradient(90deg,var(--primary),#0f6a37);color:#fff;padding:8px 12px;border-radius:8px;border:0;cursor:pointer}

/* Overview separators and small pie tweaks */
[data-panel="overview"] .grid-top {
  border-bottom: 1px solid rgba(15,23,36,0.06);
  padding-bottom: 14px;
  margin-bottom: 14px;
}

[data-panel="overview"] .row {
  border-radius: 10px;
  padding: 10px 12px;
}

/* add subtle divider between the two main analytic rows */
[data-panel="overview"] .row + .row {
  border-top: 1px solid rgba(15,23,36,0.04);
  padding-top: 18px;
  margin-top: 18px;
}

/* Top rooms list separators */
#top-rooms div {
  border-bottom: 1px dashed rgba(15,23,36,0.04);
  padding: 8px 0;
  display:flex;
  justify-content:space-between;
  align-items:center;
}

/* Activity log minor styling */
#activity-log div {
  padding: 10px 6px;
  border-bottom: 1px solid rgba(15,23,36,0.03);
}

/* Small pie container - restrict width to make pie smaller */
.small-pie {
  width: 180px;
  max-width: 100%;
  margin: 10px auto 0;
  display:flex;
  align-items:center;
  justify-content:center;
  height: 140px;
}

/* ensure canvas fills container height */
.small-pie canvas {
  width: 100% !important;
  height: 100% !important;
}

/* adjust Top Rooms font sizes for compact list */
#top-rooms div > div:first-child { font-size: 0.95rem; color:#0b1320; }
#top-rooms div > div:last-child { font-size: 0.95rem; color:var(--accent); font-weight:700; }

/* responsive adjustments */
@media (max-width:1100px){
  .grid-top{grid-template-columns:repeat(2,1fr)}
  .layout{grid-template-columns:64px 1fr}
}
@media (max-width:720px){
  .grid-top{grid-template-columns:1fr}
  .sidebar{flex-direction:row;justify-content:flex-start}
  .nav-btn{width:48px;height:48px}
}
</style>
</head>
<body>
<header class="header">
  <div class="brand"><div class="logo">A</div><div style="line-height:1"><div style="font-size:14px">AMUNRA</div><div style="font-size:11px;color:#cfe6d6">Admin Console</div></div></div>
  <div class="actions">
    <div style="display:flex;gap:10px;align-items:center">
      <button class="btn-ghost" style="color:#000; margin-top: 5px; background-color: white;" onclick="exportCSV()">Export CSV</button>
      <div style="color:#fff;font-weight:600;margin-left:6px">Admin</div>
      <a href="logout.php" class="btn-ghost" style="color:#fff;margin-left:8px">Sign out</a>
    </div>
  </div>
</header>

<div class="container">
  <div class="layout">
    <nav class="sidebar" aria-label="Main navigation">
      <button class="nav-btn active" title="Overview" data-tab="overview">
        <i class="fas fa-chart-pie"></i>
        <span class="nav-label">Overview</span>
      </button>
      <button class="nav-btn" title="Rooms" data-tab="rooms">
        <i class="fas fa-door-open"></i>
        <span class="nav-label">Rooms</span>
      </button>
      <button class="nav-btn" title="Menu" data-tab="menu">
        <i class="fas fa-utensils"></i>
        <span class="nav-label">Menu</span>
      </button>
      <button class="nav-btn" title="Bookings" data-tab="bookings">
        <i class="fas fa-calendar-check"></i>
        <span class="nav-label">Bookings</span>
      </button>
      <button class="nav-btn" title="Orders" data-tab="orders">
        <i class="fas fa-receipt"></i>
        <span class="nav-label">Orders</span>
      </button>
      <button class="nav-btn" title="Users" data-tab="users">
        <i class="fas fa-users"></i>
        <span class="nav-label">Users</span>
      </button>
      <button class="nav-btn" title="Reviews" data-tab="reviews">
        <i class="fas fa-star"></i>
        <span class="nav-label">Reviews</span>
      </button>
    </nav>

    <main class="main">
      <!-- Overview -->
      <section class="panel" data-panel="overview">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
          <h3>Overview</h3>
          <div class="controls"><input id="search-global" class="input" placeholder="Search bookings, users..." style="width:260px" oninput="globalSearch(this.value)"></div>
        </div>

        <!-- KPIs -->
        <div class="grid-top" style="margin-bottom:14px">
          <div class="kpi"><h6>Total Bookings</h6><div class="value" id="kpi-bookings">—</div><div class="small" id="kpi-bookings-sub"></div></div>
          <div class="kpi"><h6>Revenue (12mo est.)</h6><div class="value" id="kpi-revenue">—</div><div class="small" id="kpi-revenue-sub"></div></div>
          <div class="kpi"><h6>Occupancy</h6><div class="value" id="kpi-occupancy">—</div><div class="small" id="kpi-occupancy-sub"></div></div>
          <div class="kpi"><h6>Pending Orders</h6><div class="value" id="kpi-orders">—</div><div class="small" id="kpi-orders-sub"></div></div>
        </div>

        <!-- Detailed analytics row -->
        <div class="row" style="gap:18px;flex-wrap:wrap">
          <div class="panel chart-wrap" style="flex:1;min-width:360px">
            <h3>Bookings Trend (last 30 days)</h3>
            <canvas id="bookingsTrendChart" height="160"></canvas>
          </div>

          <div class="panel" style="width:420px;min-width:320px">
            <h3>Orders Status</h3>

            <!-- small pie wrapper -->
            <div class="small-pie">
              <canvas id="ordersPieChart"></canvas>
            </div>

            <div style="margin-top:12px">
              <h4 style="margin:6px 0 8px 0">Top Rooms by Revenue</h4>
              <div id="top-rooms" style="max-height:180px;overflow:auto"></div>
            </div>
          </div>
        </div>

        <div class="row" style="gap:18px; margin-top:14px; flex-wrap:wrap">
          <div class="panel chart-wrap" style="flex:1;min-width:360px">
            <h3>Revenue by Month (last 12 months)</h3>
            <canvas id="revenueMonthlyChart" height="160"></canvas>
          </div>
          <div class="panel" style="width:420px;min-width:320px">
            <h3>Activity Log</h3>
            <div id="activity-log" style="max-height:420px;overflow:auto;margin-top:10px"></div>
          </div>
        </div>
      </section>

      <!-- Rooms -->
      <section class="panel" data-panel="rooms" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
          <h3>Room Inventory</h3>
          <div class="controls"><button class="btn-primary" onclick="openRoomModal()">+ New Room</button><button class="btn-ghost" onclick="loadRooms()">Refresh</button></div>
        </div>
        <div id="rooms-table-wrap"></div>
      </section>

      <!-- Menu -->
      <section class="panel" data-panel="menu" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
          <h3>Food & Beverage</h3>
          <div class="controls"><button class="btn-primary" onclick="openMenuModal()">+ New Item</button><button class="btn-ghost" onclick="loadMenu()">Refresh</button></div>
        </div>
        <div id="menu-table-wrap"></div>
      </section>

      <!-- Bookings -->
      <section class="panel" data-panel="bookings" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
          <h3>Bookings</h3>
          <div class="controls">
            <select id="filter-status" onchange="applyBookingFilter()"><option value="">All</option><option>Active</option><option>Completed</option></select>
            <button class="btn-ghost" onclick="loadBookings()">Refresh</button>
          </div>
        </div>
        <table class="table" id="bookings-table"><thead><tr><th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Guests</th><th>Actions</th></tr></thead><tbody></tbody></table>
      </section>

      <!-- Orders -->
      <section class="panel" data-panel="orders" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
          <h3>Orders</h3>
          <div class="controls"><button class="btn-ghost" onclick="loadOrders()">Refresh</button></div>
        </div>
        <table class="table" id="orders-table"><thead><tr><th>#</th><th>Guest</th><th>Room</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead><tbody></tbody></table>
      </section>

      <!-- Users -->
      <section class="panel" data-panel="users" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
          <h3>Users</h3>
          <div class="controls"><button class="btn-ghost" onclick="loadUsers()">Refresh</button></div>
        </div>
        <table class="table" id="users-table"><thead><tr><th>Username</th><th>Full Name</th><th>Email</th><th>Contact Number</th><th>NIC</th><th>Joined</th></tr></thead><tbody></tbody></table>
      </section>

      <!-- Reviews -->
      <section class="panel" data-panel="reviews" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
          <h3>Reviews</h3>
          <div class="controls"><button class="btn-ghost" onclick="loadReviews()">Refresh</button></div>
        </div>
        <div id="reviews-container"></div>
      </section>

    </main>
  </div>
</div>

<!-- Room Modal -->
<div id="modal-room" class="modal"><div class="dialog">
  <div style="display:flex;justify-content:space-between;align-items:center"><h3 id="room-title">Room</h3><button onclick="closeRoomModal()" class="btn-ghost">Close</button></div>
  <div class="form-grid" style="margin-top:12px">
    <div class="field"><label>Name</label><input id="frm_r_name" class="input"></div>
    <div class="field"><label>Category</label><input id="frm_r_category" class="input"></div>
    <div class="field"><label>Price</label><input id="frm_r_price" class="input" type="number" step="0.01"></div>
    <div class="field"><label>Total rooms</label><input id="frm_r_total" class="input" type="number"></div>
    <div class="field full"><label>Image URL</label><input id="frm_r_image" class="input"></div>
    <div class="field full"><label>Description</label><textarea id="frm_r_desc" rows="4" class="input"></textarea></div>
  </div>
  <div style="text-align:right;margin-top:12px"><button class="btn-primary" onclick="saveRoom()">Save</button></div>
</div></div>

<!-- Menu Modal -->
<div id="modal-menu" class="modal"><div class="dialog">
  <div style="display:flex;justify-content:space-between;align-items:center"><h3 id="menu-title">Menu Item</h3><button onclick="closeMenuModal()" class="btn-ghost">Close</button></div>
  <div class="form-grid" style="margin-top:12px">
    <div class="field"><label>Name</label><input id="frm_m_name" class="input"></div>
    <div class="field"><label>Category</label><input id="frm_m_category" class="input"></div>
    <div class="field"><label>Price</label><input id="frm_m_price" class="input" type="number" step="0.01"></div>
    <div class="field full"><label>Image URL</label><input id="frm_m_image" class="input"></div>
    <div class="field full"><label>Description</label><textarea id="frm_m_desc" rows="3" class="input"></textarea></div>
    <div class="field"><label><input id="frm_m_available" type="checkbox"> Available</label></div>
  </div>
  <div style="text-align:right;margin-top:12px"><button class="btn-primary" onclick="saveMenu()">Save</button></div>
</div></div>

<script>
// basic tab handling
document.querySelectorAll('.nav-btn').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    document.querySelectorAll('.nav-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    const tab = btn.getAttribute('data-tab');
    document.querySelectorAll('[data-panel]').forEach(p=>p.style.display='none');
    document.querySelector(`[data-panel="${tab}"]`).style.display='block';
    // lazy load
    if(tab==='rooms') loadRooms();
    if(tab==='menu') loadMenu();
    if(tab==='bookings') loadBookings();
    if(tab==='orders') loadOrders();
    if(tab==='users') loadUsers();
    if(tab==='reviews') loadReviews();
  });
});

// API helpers (admin_api.php already present)
const API = 'admin_api.php';
async function apiGet(action, qs=''){ const res = await fetch(API+'?action='+action+(qs?('&'+qs):''), {credentials:'same-origin'}); return res.json(); }
async function apiPost(action, data={}){ data.action = action; const fd = new FormData(); for(const k in data) fd.append(k,data[k]); const res = await fetch(API, {method:'POST', body: fd, credentials:'same-origin'}); return res.json(); }

/* Enhanced Overview analytics */
let bookingsTrendChart = null, revenueMonthlyChart = null, ordersPieChart = null;

async function loadStats(){
  // keep previous KPI loads for backwards compatibility
  await loadOverviewDetails();
}

async function loadOverviewDetails(){
  const [bkRes, roomsRes, ordersRes, usersRes, reviewsRes] = await Promise.all([
    apiGet('list_bookings'),
    apiGet('list_rooms'),
    apiGet('list_orders'),
    apiGet('list_users'),
    apiGet('list_reviews')
  ]);

  const bookings = bkRes.bookings || [];
  const rooms = roomsRes.rooms || [];
  const orders = ordersRes.orders || [];
  const users = usersRes.users || [];
  const reviews = reviewsRes.reviews || [];

  // KPI values
  document.getElementById('kpi-bookings').textContent = bookings.length;
  document.getElementById('kpi-orders').textContent = (orders.filter(o=>o.status==='pending')||[]).length;
  const totalRevenue = orders.reduce((s,o)=>s + (parseFloat(o.total_amount)||0), 0)
                    + bookings.reduce((s,b)=>s + (parseFloat(b.price)||0), 0); // rough
  document.getElementById('kpi-revenue').textContent = 'LKR ' + Math.round(totalRevenue);
  document.getElementById('kpi-bookings-sub').textContent = `${users.length} users • ${reviews.length} reviews`;

  // Occupancy: simple occupancy = (reserved nights / (total_rooms * days range)) for last 30 days
  const today = new Date();
  const last30 = new Date(); last30.setDate(today.getDate() - 30);
  let reservedNights = 0;
  bookings.forEach(b=>{
    const ci = new Date(b.checkin), co = new Date(b.checkout);
    // overlap between booking and last30..today
    const start = ci < last30 ? last30 : ci;
    const end = co > today ? today : co;
    if (end > start) {
      reservedNights += Math.round((end - start) / (1000*60*60*24));
    }
  });
  const totalRoomNights = rooms.reduce((s,r)=>s + (r.total_rooms || 0), 0) * 30 || 1;
  const occupancyRate = Math.round((reservedNights / totalRoomNights) * 100);
  document.getElementById('kpi-occupancy').textContent = (isFinite(occupancyRate) ? occupancyRate : 0) + '%';
  document.getElementById('kpi-occupancy-sub').textContent = `${reservedNights} room-nights / ${totalRoomNights}`;

  // Bookings trend last 30 days (daily counts)
  const dayMap = {};
  for (let d = new Date(last30); d <= today; d.setDate(d.getDate()+1)) {
    const key = d.toISOString().slice(0,10);
    dayMap[key] = 0;
  }
  bookings.forEach(b=>{
    const ci = new Date(b.checkin), co = new Date(b.checkout);
    for (let d = new Date(ci); d < co; d.setDate(d.getDate()+1)) {
      const key = d.toISOString().slice(0,10);
      if (key in dayMap) dayMap[key] += 1;
    }
  });
  const dayLabels = Object.keys(dayMap);
  const dayValues = dayLabels.map(k=>dayMap[k]);
  const ctxB = document.getElementById('bookingsTrendChart').getContext('2d');
  if (bookingsTrendChart) bookingsTrendChart.destroy();
  bookingsTrendChart = new Chart(ctxB, { type: 'line', data: { labels: dayLabels, datasets: [{ label: 'Active bookings per day', data: dayValues, borderColor: '#0b5a2b', backgroundColor: 'rgba(11,90,43,0.08)', fill: true }] }, options: { responsive:true, plugins:{legend:{display:false}} } });

  // Revenue by month (last 12 months)
  const monthMap = {};
  for (let i=11;i>=0;i--) {
    const d = new Date(); d.setMonth(d.getMonth()-i);
    const key = d.toISOString().slice(0,7);
    monthMap[key] = 0;
  }
  // use orders' created_at or bookings price; prefer orders
  orders.forEach(o=>{
    const key = (new Date(o.created_at)).toISOString().slice(0,7);
    if (key in monthMap) monthMap[key] += parseFloat(o.total_amount)||0;
  });
  bookings.forEach(b=>{
    const key = (new Date(b.created_at||b.checkin)).toISOString().slice(0,7);
    if (key in monthMap) monthMap[key] += parseFloat(b.price)||0;
  });
  const monthLabels = Object.keys(monthMap);
  const monthValues = monthLabels.map(k=>monthMap[k]);
  const ctxR = document.getElementById('revenueMonthlyChart').getContext('2d');
  if (revenueMonthlyChart) revenueMonthlyChart.destroy();
  revenueMonthlyChart = new Chart(ctxR, { type:'bar', data:{ labels: monthLabels, datasets:[{label:'Revenue', data:monthValues, backgroundColor:'#c19a53'}] }, options:{ responsive:true, plugins:{legend:{display:false}} } });

  // Orders status pie
  const statusCounts = orders.reduce((acc,o)=>{ acc[o.status] = (acc[o.status]||0)+1; return acc; }, {});
  const pieLabels = Object.keys(statusCounts);
  const pieValues = pieLabels.map(l=>statusCounts[l]);
  const ctxP = document.getElementById('ordersPieChart').getContext('2d');
  if (ordersPieChart) ordersPieChart.destroy();
  ordersPieChart = new Chart(ctxP, {
    type: 'doughnut',
    data: {
      labels: pieLabels,
      datasets: [{
        data: pieValues,
        backgroundColor: ['#f59e0b','#10b981','#2563eb','#ef4444']
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false, // important: allow chart to resize to .small-pie container
      plugins: {
        legend: { position: 'bottom' }
      },
      layout: { padding: 6 }
    }
  });

  // Top rooms by revenue (aggregate from bookings/orders)
  const roomRevenue = {};
  bookings.forEach(b=> { roomRevenue[b.room_name] = (roomRevenue[b.room_name]||0) + (parseFloat(b.price)||0); });
  // include orders mapped to reservation.room_name when available
  orders.forEach(o=> { roomRevenue[o.room_name] = (roomRevenue[o.room_name]||0) + (parseFloat(o.total_amount)||0); });
  const topRooms = Object.keys(roomRevenue).map(name=>({name, revenue:roomRevenue[name]})).sort((a,b)=>b.revenue-a.revenue).slice(0,6);
  const topRoomsEl = document.getElementById('top-rooms'); topRoomsEl.innerHTML = '';
  topRooms.forEach(tr=> {
    const div = document.createElement('div'); div.style.display='flex'; div.style.justifyContent='space-between'; div.style.padding='6px 0'; div.innerHTML = `<div style="font-weight:600">${escapeHtml(tr.name)}</div><div style="color:#8b7355;font-weight:700">LKR ${(tr.revenue||0).toFixed(2)}</div>`;
    topRoomsEl.appendChild(div);
  });

  // Activity log: combine bookings (new), orders (new), reviews, users (signup)
  const activities = [];
  bookings.slice().reverse().slice(0,50).forEach(b=> activities.push({when: b.created_at || b.checkin, type:'booking', text:`Booking by ${b.username} — ${b.room_name} (${b.checkin}→${b.checkout})`}));
  orders.slice().reverse().slice(0,50).forEach(o=> activities.push({when: o.created_at, type:'order', text:`Order #${o.id} by ${o.username} — LKR ${parseFloat(o.total_amount).toFixed(2)}`}));
  reviews.slice().reverse().slice(0,50).forEach(r=> activities.push({when: r.created_at, type:'review', text:`Review by ${r.username} — ${r.title}`}));
  users.slice().reverse().slice(0,50).forEach(u=> activities.push({when: u.created_at, type:'signup', text:`User signup: ${u.username}`}));
  activities.sort((a,b)=> new Date(b.when) - new Date(a.when));
  const activityLogEl = document.getElementById('activity-log'); activityLogEl.innerHTML = '';
  activities.slice(0,80).forEach(a=>{
    const item = document.createElement('div');
    item.style.padding='10px'; item.style.borderBottom='1px solid rgba(0,0,0,0.04)';
    item.innerHTML = `<div style="font-size:13px;color:#333">${escapeHtml(a.text)}</div><div style="font-size:12px;color:#888">${new Date(a.when).toLocaleString()}</div>`;
    activityLogEl.appendChild(item);
  });

  // Update activity feed (short) on the right side as well (if present)
  const activity = document.getElementById('activity-list');
  if (activity) {
    activity.innerHTML = '';
    activities.slice(0,10).forEach(a=>{
      const div = document.createElement('div'); div.className='item'; div.innerHTML = `<div style="flex:1"><strong>${escapeHtml(a.text.split(' — ')[0])}</strong><div class="meta">${escapeHtml(a.text.split(' — ')[1]||'')}</div></div>`; activity.appendChild(div);
    });
  }
}

// ROOMS
async function loadRooms(){
  const res = await apiGet('list_rooms'); const wrap = document.getElementById('rooms-table-wrap'); wrap.innerHTML = '';
  if(!res.rooms || !res.rooms.length){ wrap.innerHTML = '<div class="small">No rooms yet</div>'; return; }
  // build simple table with quick edit
  let html = '<table class="table"><thead><tr><th>Room</th><th>Category</th><th>Price</th><th>Available</th><th>Actions</th></tr></thead><tbody>';
  res.rooms.forEach(r=>{
    html += `<tr><td>${escapeHtml(r.name)}</td><td>${escapeHtml(r.category)}</td><td>LKR ${parseFloat(r.price).toFixed(2)}</td><td>${r.available_rooms}/${r.total_rooms}</td>
      <td><button class="btn-edit" onclick="editRoom(${r.id})">Edit</button> <button class="btn-delete" onclick="deleteRoom(${r.id})">Delete</button></td></tr>`;
  });
  html += '</tbody></table>';
  wrap.innerHTML = html;
}

let editingRoomId = 0;
function openRoomModal(){ editingRoomId=0; document.getElementById('room-title').textContent='Create Room'; ['frm_r_name','frm_r_category','frm_r_price','frm_r_total','frm_r_image','frm_r_desc'].forEach(id=>document.getElementById(id).value=''); document.getElementById('modal-room').style.display='flex'; }
function closeRoomModal(){ document.getElementById('modal-room').style.display='none'; }
async function saveRoom(){
  const payload = { id: editingRoomId, name: document.getElementById('frm_r_name').value.trim(), category: document.getElementById('frm_r_category').value.trim(), price: document.getElementById('frm_r_price').value, total_rooms: document.getElementById('frm_r_total').value, image_url: document.getElementById('frm_r_image').value.trim(), description: document.getElementById('frm_r_desc').value.trim() };
  if(!payload.name) return alert('Room name required');
  const res = await apiPost('save_room', payload);
  if(res.success){ closeRoomModal(); loadRooms(); toast('Room saved'); } else alert(res.message||'Error');
}
async function editRoom(id){
  const res = await apiGet('list_rooms'); const room = (res.rooms||[]).find(r=>r.id==id);
  if(!room) return alert('Not found');
  editingRoomId = room.id; document.getElementById('room-title').textContent='Edit Room'; document.getElementById('frm_r_name').value=room.name; document.getElementById('frm_r_category').value=room.category; document.getElementById('frm_r_price').value=room.price; document.getElementById('frm_r_total').value=room.total_rooms; document.getElementById('frm_r_image').value=room.image_url; document.getElementById('frm_r_desc').value=room.description; document.getElementById('modal-room').style.display='flex';
}
async function deleteRoom(id){ if(!confirm('Delete room?')) return; const res = await apiPost('delete_room',{id}); if(res.success) loadRooms(); else alert(res.message||'Error'); }

// MENU
let editingMenuId = 0;
function openMenuModal(){ editingMenuId=0; document.getElementById('menu-title').textContent='Create Item'; ['frm_m_name','frm_m_category','frm_m_price','frm_m_image','frm_m_desc'].forEach(id=>document.getElementById(id).value=''); document.getElementById('frm_m_available').checked=true; document.getElementById('modal-menu').style.display='flex'; }
function closeMenuModal(){ document.getElementById('modal-menu').style.display='none'; }
async function loadMenu(){
  const res = await apiGet('list_menu'); const wrap = document.getElementById('menu-table-wrap'); wrap.innerHTML = '';
  if(!res.menu || !res.menu.length){ wrap.innerHTML = '<div class="small">No menu items</div>'; return; }
  let html = '<table class="table"><thead><tr><th>Item</th><th>Category</th><th>Price</th><th>Available</th><th>Actions</th></tr></thead><tbody>';
  res.menu.forEach(m=>{
    html += `<tr><td>${escapeHtml(m.name)}</td><td>${escapeHtml(m.category)}</td><td>LKR ${parseFloat(m.price).toFixed(2)}</td><td>${m.available? 'Yes':'No'}</td><td><button class="btn-edit" onclick="editMenu(${m.id})">Edit</button> <button class="btn-delete" onclick="deleteMenu(${m.id})">Delete</button></td></tr>`;
  });
  html += '</tbody></table>'; wrap.innerHTML = html;
}
async function saveMenu(){
  const payload = { id: editingMenuId, name: document.getElementById('frm_m_name').value.trim(), category: document.getElementById('frm_m_category').value.trim(), price: document.getElementById('frm_m_price').value, image_url: document.getElementById('frm_m_image').value.trim(), description: document.getElementById('frm_m_desc').value.trim(), available: document.getElementById('frm_m_available').checked?1:0 };
  if(!payload.name) return alert('Name required');
  const res = await apiPost('save_menu', payload);
  if(res.success){ closeMenuModal(); loadMenu(); toast('Menu saved'); } else alert(res.message||'Error');
}
async function editMenu(id){
  const res = await apiGet('list_menu'); const it = (res.menu||[]).find(x=>x.id==id);
  if(!it) return alert('Not found'); editingMenuId = it.id; document.getElementById('menu-title').textContent='Edit Item'; document.getElementById('frm_m_name').value=it.name; document.getElementById('frm_m_category').value=it.category; document.getElementById('frm_m_price').value=it.price; document.getElementById('frm_m_image').value=it.image_url; document.getElementById('frm_m_desc').value=it.description; document.getElementById('frm_m_available').checked = it.available==1; document.getElementById('modal-menu').style.display='flex';
}
async function deleteMenu(id){ if(!confirm('Delete item?')) return; const res = await apiPost('delete_menu',{id}); if(res.success) loadMenu(); else alert(res.message||'Error'); }

// BOOKINGS
let bookingsCache = [];
async function loadBookings(){
  const res = await apiGet('list_bookings'); bookingsCache = res.bookings||[];
  const tbody = document.querySelector('#bookings-table tbody'); tbody.innerHTML='';
  bookingsCache.forEach(b=>{ const tr = document.createElement('tr'); tr.innerHTML = `<td>${escapeHtml(b.username)}</td><td>${escapeHtml(b.room_name)}</td><td>${b.checkin}</td><td>${b.checkout}</td><td>${b.guests}</td><td><button class="btn-ghost" onclick="cancelBooking(${b.id})">Cancel</button></td>`; tbody.appendChild(tr); });
  // activity feed
  const activity = document.getElementById('activity-list'); activity.innerHTML=''; bookingsCache.slice(0,10).forEach(b=>{ const div = document.createElement('div'); div.className='item'; div.innerHTML = `<div style="flex:1"><strong>${escapeHtml(b.username)}</strong><div class="meta">Booked ${escapeHtml(b.room_name)} • ${b.checkin} → ${b.checkout}</div></div>`; activity.appendChild(div); });
}
async function cancelBooking(id){ if(!confirm('Cancel reservation?')) return; const res = await apiPost('cancel_booking',{reservation_id:id}); if(res.success){ loadBookings(); loadRooms(); toast('Reservation cancelled'); } else alert(res.message||'Error'); }

// ORDERS
async function loadOrders(){ const res = await apiGet('list_orders'); const tbody = document.querySelector('#orders-table tbody'); tbody.innerHTML=''; (res.orders||[]).forEach(o=>{ const tr = document.createElement('tr'); tr.innerHTML = `<td>${o.id}</td><td>${escapeHtml(o.username)}</td><td>${escapeHtml(o.room_name)}</td><td>LKR ${parseFloat(o.total_amount).toFixed(2)}</td><td>${o.status}</td><td><select onchange="changeOrderStatus(${o.id}, this.value)"><option ${o.status=='pending'?'selected':''}>pending</option><option ${o.status=='confirmed'?'selected':''}>confirmed</option><option ${o.status=='completed'?'selected':''}>completed</option><option ${o.status=='cancelled'?'selected':''}>cancelled</option></select></td>`; tbody.appendChild(tr); }); }
async function changeOrderStatus(orderId,status){ const res = await apiPost('update_order_status',{order_id:orderId,status}); if(res.success) loadOrders(); else alert(res.message||'Error'); }

// USERS & REVIEWS
async function loadUsers(){ const res = await apiGet('list_users'); const tbody = document.querySelector('#users-table tbody'); tbody.innerHTML=''; (res.users||[]).forEach(u=>{ const tr=document.createElement('tr'); tr.innerHTML=`<td>${escapeHtml(u.username)}</td><td>${escapeHtml(u.fullname||'N/A')}</td><td>${escapeHtml(u.email)}</td><td>${escapeHtml(u.contact_number||'N/A')}</td><td>${escapeHtml(u.nic||'N/A')}</td><td>${u.created_at}</td>`; tbody.appendChild(tr); }); }
async function loadReviews(){ const res = await apiGet('list_reviews'); const wrap = document.getElementById('reviews-container'); wrap.innerHTML=''; (res.reviews||[]).forEach(rv=>{ const div=document.createElement('div'); div.className='item'; div.innerHTML=`<div style="flex:1"><strong>${escapeHtml(rv.username)}</strong><div class="meta">${escapeHtml(rv.title)} • ${rv.rating}★</div><div style="margin-top:6px">${escapeHtml(rv.comment)}</div></div><div><button class="btn-delete" onclick="deleteReview(${rv.id})">Delete</button></div>`; wrap.appendChild(div); }); }
async function deleteReview(id){ if(!confirm('Delete review?')) return; const res = await apiPost('delete_review',{id}); if(res.success) loadReviews(); else alert(res.message||'Error'); }

// utilities
function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
function toast(msg){ const el=document.createElement('div'); el.style.position='fixed'; el.style.right='18px'; el.style.bottom='18px'; el.style.background='#072b1a'; el.style.color='#fff'; el.style.padding='10px 14px'; el.style.borderRadius='8px'; el.style.boxShadow='0 8px 24px rgba(2,6,23,0.24)'; el.style.zIndex=99999; el.textContent=msg; document.body.appendChild(el); setTimeout(()=>el.remove(),3000); }

// export CSV (bookings)
async function exportCSV(){
  const res = await apiGet('list_bookings');
  const rows = res.bookings || [];
  if(!rows.length) return alert('No bookings to export');
  const headers = ['id','user','room','price','checkin','checkout','guests','created_at'];
  const csv = [headers.join(',')].concat(rows.map(r=>[r.id, `"${r.username}"`, `"${r.room_name}"`, r.price, r.checkin, r.checkout, r.guests, r.created_at].join(','))).join('\n');
  const blob = new Blob([csv], {type:'text/csv'}); const url=URL.createObjectURL(blob);
  const a=document.createElement('a'); a.href=url; a.download='bookings_export.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
}

// search and filter helpers
function globalSearch(q){ q = q.trim().toLowerCase(); if(!q){ loadBookings(); return; } const filtered = bookingsCache.filter(b=> (b.username||'').toLowerCase().includes(q) || (b.room_name||'').toLowerCase().includes(q)); const tbody = document.querySelector('#bookings-table tbody'); tbody.innerHTML=''; filtered.forEach(b=>{ const tr=document.createElement('tr'); tr.innerHTML=`<td>${escapeHtml(b.username)}</td><td>${escapeHtml(b.room_name)}</td><td>${b.checkin}</td><td>${b.checkout}</td><td>${b.guests}</td><td><button class="btn-ghost" onclick="cancelBooking(${b.id})">Cancel</button></td>`; tbody.appendChild(tr); }); }
function applyBookingFilter(){ /* placeholder for advanced filters */ }

// initial loads
loadStats(); loadRooms(); loadMenu(); loadBookings(); loadOrders(); loadUsers(); loadReviews();

</script>
</body>
</html>
