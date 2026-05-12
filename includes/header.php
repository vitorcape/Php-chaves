<?php
// ============================================================
// includes/header.php — Layout principal (sidebar + topbar)
// ============================================================
// Variáveis esperadas:
//   $pageTitle  (string) — título da aba e do topbar
//   $activeMenu (string) — 'dashboard'|'users'|'posts'|'admin'
// ============================================================

$user = currentUser();

function navLink(string $href, string $icon, string $label, string $active, string $key, string $extra = ''): string
{
    $cls = ($active === $key) ? ' active' : '';
    return '<a href="' . APP_BASE . $href . '" class="nav-link' . $cls . ($extra ? ' ' . $extra : '') . '">'
         . '<span class="nav-icon">' . $icon . '</span>'
         . '<span class="nav-label">' . $label . '</span>'
         . '</a>';
}

function navGroup(string $icon, string $label, array $links, string $active, string $key): string
{
    $isOpen = ($active === $key) ? ' open' : '';
    $html   = '<div class="nav-group' . $isOpen . '">';
    $html  .= '<button class="nav-group-btn"><span class="nav-icon">' . $icon . '</span>'
            . '<span class="nav-label">' . $label . '</span><span class="nav-arrow">›</span></button>';
    $html  .= '<div class="nav-group-items">';
    foreach ($links as [$href, $lbl]) {
        $html .= '<a href="' . APP_BASE . $href . '" class="nav-sub-link">' . $lbl . '</a>';
    }
    $html .= '</div></div>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'App') ?> — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_BASE ?>/style.css">
    <!-- Aplica o tema antes do render para evitar flash -->
    <script>(function(){var t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <span class="logo-mark">✦</span>
        <span class="logo-text"><?= APP_NAME ?></span>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-section-label">Geral</span>
        <?= navLink('/index.php', '⬡', 'Início', $activeMenu, 'home') ?>
        <?= navLink('/dashboard.php', '⬡', 'Dashboard', $activeMenu, 'dashboard') ?>

        <span class="nav-section-label">Gestão</span>
        <?= navLink('/weather.php', '🌤', 'Previsão do Tempo', $activeMenu, 'weather') ?>
        <?= navGroup('◈', 'Usuários', [
            ['/users/index.php',  'Listar usuários'],
            ['/users/create.php', 'Novo usuário'],
        ], $activeMenu, 'users') ?>

        <?= navGroup('◉', 'Publicações', [
            ['/posts/index.php',  'Listar publicações'],
            ['/posts/create.php', 'Nova publicação'],
        ], $activeMenu, 'posts') ?>

        <?php if (hasRole('admin')): ?>
            <span class="nav-section-label">Administração</span>
            <?= navLink('/admin.php', '◆', 'Painel Admin', $activeMenu, 'admin', 'nav-admin') ?>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <span class="user-avatar"><?= mb_strtoupper(mb_substr($user['name'], 0, 1)) ?></span>
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                <span class="user-role role-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
            </div>
        </div>
        <a href="<?= APP_BASE ?>/logout.php" class="logout-btn" title="Sair">⏻</a>
    </div>
</aside>

<!-- OVERLAY MOBILE -->
<div class="sidebar-overlay" id="overlay" onclick="toggleSidebar()"></div>

<!-- MAIN WRAPPER -->
<div class="main-wrapper">

    <!-- TOPBAR -->
    <header class="topbar">
        <button class="menu-btn" onclick="toggleSidebar()">☰</button>
        <h1 class="topbar-title"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
        <div class="topbar-right">
            <span class="topbar-user"><?= htmlspecialchars($user['name']) ?></span>
            <!-- Theme toggle -->
            <button class="theme-toggle" id="themeToggle" title="Alternar tema" onclick="toggleTheme()">
                <span class="icon-moon">🌙</span>
                <span class="icon-sun">☀️</span>
            </button>
        </div>
    </header>

    <!-- CONTENT -->
    <main class="content">