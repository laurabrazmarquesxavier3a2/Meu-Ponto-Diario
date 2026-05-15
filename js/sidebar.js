const sidebar = document.getElementById("sidebar");
const btn = document.getElementById("btnSidebar");
const overlay = document.getElementById("sidebarOverlay");

btn.addEventListener("click", () => {
    sidebar.classList.toggle("show");
    overlay.classList.toggle("show");
});

overlay.addEventListener("click", () => {
    sidebar.classList.remove("show");
    overlay.classList.remove("show");
});