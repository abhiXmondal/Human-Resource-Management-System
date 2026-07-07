// Employee Dashboard behavior — accessible and interactive

(function(){
  // Utility: safe query
  const $ = (sel, ctx=document)=> ctx.querySelector(sel);
  const $$ = (sel, ctx=document)=> Array.from(ctx.querySelectorAll(sel));

  // Sidebar navigation
  const menuLinks = $$('.nav-menu a');
  const sections = $$('.section');

  function activateSection(id){
    sections.forEach(s=>{
      if(s.id === id){ s.hidden = false; s.focus(); s.setAttribute('aria-hidden','false'); }
      else { s.hidden = true; s.setAttribute('aria-hidden','true'); }
    });
    menuLinks.forEach(a=> a.parentElement.classList.toggle('active', a.dataset.target===id));
  }

  menuLinks.forEach(link=>{
    link.addEventListener('click', (e)=>{
      e.preventDefault();
      const target = link.dataset.target;
      activateSection(target);
      // move focus to section for keyboard users
    });

    link.addEventListener('keydown', (e)=>{
      if(e.key === 'Enter' || e.key === ' '){ e.preventDefault(); link.click(); }
    });
  });

  // Show dashboard by default
  activateSection('dashboard');

  // Date display
  const dateEl = $('#date');
  if(dateEl){
    const today = new Date();
    dateEl.textContent = today.toLocaleDateString(undefined, { weekday:'long', year:'numeric', month:'long', day:'numeric' });
  }

  // Fetch user profile (if session exists)
  async function fetchProfile(){
    try{
      const res = await fetch('api/get_user_profile.php', { credentials: 'same-origin' });
      if(!res.ok) return;
      const data = await res.json();
      if(data.success && data.profile){
        const name = data.profile.full_name || data.profile.employee_id || 'Employee';
        const userNameEl = $('#userName');
        if(userNameEl) userNameEl.textContent = name;
        const welcome = $('#welcome');
        if(welcome) welcome.textContent = `Welcome back, ${name.split(' ')[0] || name}`;
        // payroll placeholders
        if(data.profile.salary){ $('#grossSalary') && ($('#grossSalary').textContent = data.profile.salary); }
      }
    }catch(err){ console.warn('Profile fetch failed', err); }
  }
  fetchProfile();

  // Attendance: fetch recent and populate tables
  async function fetchAttendance(month){
    try{
      const url = new URL('api/get_attendance.php', window.location.origin);
      if(month) url.searchParams.set('month', month);
      const res = await fetch(url, { credentials: 'same-origin' });
      if(!res.ok) return;
      const data = await res.json();
      const body = $('#attendanceBody');
      const full = $('#attendanceFull');
      if(body) body.innerHTML = '';
      if(full) full.innerHTML = '';
      if(data && Array.isArray(data.attendance)){
        if(data.attendance.length===0){
          if(body) body.innerHTML = '<tr><td colspan="4" class="muted">No records found</td></tr>';
          if(full) full.innerHTML = '<tr><td colspan="4" class="muted">No records found</td></tr>';
        } else {
          data.attendance.forEach(row=>{
            const r = document.createElement('tr');
            r.innerHTML = `<td>${row.attendance_date}</td><td>${row.check_in || '-'}</td><td>${row.check_out || '-'}</td><td>${row.status || '-'}</td>`;
            if(body) body.appendChild(r.cloneNode(true));
            if(full) full.appendChild(r);
          });
        }
      }
      // update stats
      $('#monthlyAttendance') && ( $('#monthlyAttendance').textContent = data.count ? `${Math.round((data.count/30)*100)}%` : '—' );
    }catch(err){ console.warn('Attendance fetch failed', err); }
  }
  // initial fetch
  fetchAttendance();

  // Month filter
  const monthSelect = $('#monthSelect');
  const filterBtn = $('#filterAttendance');
  if(filterBtn && monthSelect){
    // set current value
    const now = new Date();
    monthSelect.value = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}`;
    filterBtn.addEventListener('click', ()=>{
      const val = monthSelect.value;
      fetchAttendance(val);
      announce('Filtered attendance for ' + val);
    });
  }

  // Check-in / Check-out behavior (local simulation)
  const checkInBtn = $('#checkInBtn');
  const checkOutBtn = $('#checkOutBtn');
  const lastAction = $('#lastAction');

  function loadLastAction(){
    const v = localStorage.getItem('hrms_last_action');
    if(v && lastAction) lastAction.textContent = v;
  }
  loadLastAction();

  function setLastAction(text){
    localStorage.setItem('hrms_last_action', text);
    if(lastAction) lastAction.textContent = text;
    toast(text);
  }

  if(checkInBtn){
    checkInBtn.addEventListener('click', ()=>{
      const now = new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
      setLastAction('Checked in at ' + now);
      announce('Check in successful');
    });
  }
  if(checkOutBtn){
    checkOutBtn.addEventListener('click', ()=>{
      const now = new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
      setLastAction('Checked out at ' + now);
      announce('Check out successful');
    });
  }

  // Leave form submit
  const leaveForm = $('#leaveForm');
  if(leaveForm){
    leaveForm.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const fd = new FormData(leaveForm);
      try{
        const res = await fetch('api/leave_request.php', { method:'POST', body: fd, credentials: 'same-origin' });
        const data = await res.json();
        if(data.success){
          announce('Leave request submitted');
          // refresh leave status
          fetchLeaves();
          leaveForm.reset();
        } else {
          announce('Failed to submit leave: ' + (data.errors ? data.errors.join(', ') : data.message || 'error'));
        }
      }catch(err){ console.error(err); announce('Network error while submitting leave'); }
    });
  }

  // Fetch leave status
  async function fetchLeaves(){
    try{
      const res = await fetch('api/leave_request.php', { credentials: 'same-origin' });
      if(!res.ok) return;
      const data = await res.json();
      const body = $('#leaveStatusBody');
      if(body) body.innerHTML = '';
      if(data && Array.isArray(data.leaves)){
        if(data.leaves.length===0){
          if(body) body.innerHTML = '<tr><td colspan="3" class="muted">No leave requests</td></tr>';
        } else {
          data.leaves.forEach(l=>{
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${l.leave_type}</td><td>${l.start_date} - ${l.end_date}</td><td>${l.status||'Pending'}</td>`;
            body && body.appendChild(tr);
          });
        }
      }
    }catch(err){ console.warn('Leaves fetch failed', err); }
  }
  fetchLeaves();

  // Activity list (small demo)
  const activityList = $('#activityList');
  if(activityList){
    activityList.innerHTML = '';
    const items = [
      { icon:'fa-circle-check', text:'Leave request approved', time:'2 hours ago' },
      { icon:'fa-file-invoice-dollar', text:'June payslip available', time:'Yesterday' }
    ];
    items.forEach(it=>{
      const li = document.createElement('li');
      li.innerHTML = `<i class="fa-solid ${it.icon} text-success" aria-hidden="true"></i><div><p>${it.text}</p><span class="muted">${it.time}</span></div>`;
      activityList.appendChild(li);
    });
  }

  // Logout
  const logoutBtn = $('#logoutBtn');
  if(logoutBtn){
    logoutBtn.addEventListener('click', (e)=>{
      // call auth/logout.php to destroy session
      fetch('auth/logout.php', { credentials: 'same-origin' }).then(()=>{
        window.location.href = 'login.html';
      }).catch(()=>{ window.location.href = 'login.html'; });
    });
  }

  // Toast/announce utilities
  const announcer = $('#announcer');
  const toastEl = $('#toast');
  function announce(msg){
    if(announcer) announcer.textContent = msg;
    toast(msg);
  }
  function toast(msg){
    if(!toastEl) return;
    toastEl.hidden = false; toastEl.textContent = msg;
    setTimeout(()=>{ toastEl.hidden = true; }, 3500);
  }

  // small accessibility helper: focus first focusable in section when activated
  // already set in activateSection by focusing section element

})();
