<?php
require 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['user_id'])) {
    $redir = $_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/index.php';
    header("Location: $redir"); exit;
}

$err = '';
if ($_POST) {
    $email = trim($_POST['email']);
    $pwd   = $_POST['password'];
    $stmt  = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pwd, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['name']    = $user['name'];
        $redir = $user['role'] === 'admin' ? 'admin/dashboard.php' : 'user/index.php';
        header("Location: $redir"); exit;
    } else {
        $err = 'Invalid email or password. Please try again.';
    }
}
?>

<?php $page_title = 'Login'; $base_path=''; include 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card p-4 shadow-sm">
                <h3 class="text-center mb-4">Member Login</h3>

                <?php if($err): ?>
                    <div class="alert alert-danger alert-dismissible fade show"><?= $err ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-lg mt-3">Login</button>
                </form>

                <p class="text-center mt-3 text-muted">
                    Don't have an account? <a href="register.php">Register here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>