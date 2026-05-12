</main><!-- /content -->
</div><!-- /main-wrapper -->

<script>
// ── Sidebar mobile ─────────────────────────────────────────
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('visible');
}

// ── Nav group accordion ────────────────────────────────────
document.querySelectorAll('.nav-group-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        btn.closest('.nav-group').classList.toggle('open');
    });
});

// ── Theme toggle ───────────────────────────────────────────
function toggleTheme() {
    var html    = document.documentElement;
    var current = html.getAttribute('data-theme') || 'light';
    var next    = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
}
</script>
</body>
</html>