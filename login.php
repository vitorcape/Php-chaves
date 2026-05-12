<?php
// ============================================================
// login.php — Página de login
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

// Se já está logado, vai pro dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . APP_BASE . '/index.php');
    exit;
}

$erros = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['password'] ?? '';

    if (empty($email))  $erros[] = 'Informe o e-mail.';
    if (empty($senha))  $erros[] = 'Informe a senha.';

    if (empty($erros)) {
        $pdo  = getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND active = 1 LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['password'])) {
            loginUser($user);
            $redirect = $_GET['redirect'] ?? (APP_BASE . '/index.php');
            header('Location: ' . $redirect);
            exit;
        } else {
            $erros[] = 'E-mail ou senha incorretos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_BASE ?>/style.css">
    <script>(function(){var t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>

    <style>
        .auth-body { flex-direction: column; }
        .form-group { margin-bottom: .1rem; }
        .form-grid  { gap: 1rem; }
        .btn-block  { width: 100%; justify-content: center; padding: .75rem; font-size: .95rem; margin-top: .5rem; }
    </style>
</head>
<body class="auth-body">
    <button class="theme-toggle auth-theme-btn" onclick="toggleTheme()" title="Alternar tema"><span class="icon-moon">🌙</span><span class="icon-sun">☀️</span></button>
    <button class="theme-toggle auth-theme-btn" onclick="toggleTheme()" title="Alternar tema"><span class="icon-moon">&#127769;</span><span class="icon-sun">&#9728;&#65039;</span></button>
    <div class="auth-card">
        <div class="auth-logo">
            <span class="logo-mark">✦</span>
            <span class="logo-text"><?= APP_NAME ?></span>
        </div>

        <h2 class="auth-title">Bem-vindo de volta</h2>
        <p class="auth-sub">Entre com sua conta para continuar</p>

        <?php if ($erros): ?>
            <div class="alert alert-danger">
                <span>⚠</span>
                <div><?= implode('<br>', array_map('htmlspecialchars', $erros)) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" class="form-grid" novalidate>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($email) ?>"
                       placeholder="seu@email.com" autocomplete="email" required>
            </div>
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password"
                       placeholder="••••••••" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>

        <p class="auth-footer">
            Não tem conta? <a href="<?= APP_BASE ?>/register.php">Criar conta</a>
        </p>
    </div>
<script>
function toggleTheme(){var h=document.documentElement;var c=h.getAttribute("data-theme")||"light";var n=c==="dark"?"light":"dark";h.setAttribute("data-theme",n);localStorage.setItem("theme",n);}
</script>
</body>
</html>