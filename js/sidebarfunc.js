const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');
const btn = document.getElementById('btnSidebar');

btn.addEventListener('click', () => {

    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');

});

overlay.addEventListener('click', () => {

    sidebar.classList.remove('active');
    overlay.classList.remove('active');

});