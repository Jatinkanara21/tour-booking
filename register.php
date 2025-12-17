<?php
require 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['user_id'])) { header('Location: user/index.php'); exit; }

$msg = $err = '';
if ($_POST) {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pwd   = $_POST['password'];
    $cpwd  = $_POST['cpassword'];

    if (strlen($pwd) < 8) { $err = 'Password must be at least 8 characters long.'; }
    elseif ($pwd !== $cpwd) { $err = 'Passwords do not match.'; }
    else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) { $err = 'An account with this email already exists.'; }
        else {
            $hash = password_hash($pwd, PASSWORD_ARGON2ID);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            if ($stmt->execute([$name, $email, $hash])) {
                $msg = 'Registration successful! You can now <a href="login.php">log in</a>.';
            } else {
                $err = 'Registration failed. Please try again later.';
            }
        }
    }
}
?>

<?php $page_title = 'Register'; $base_path=''; include 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4 shadow-sm">
                <h3 class="text-center mb-4">Create Your Account</h3>

                <?php if($msg): ?>
                    <div class="alert alert-success alert-dismissible fade show"><?= $msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
                <?php if($err): ?>
                    <div class="alert alert-danger alert-dismissible fade show"><?= $err ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <?php if(!$msg): // Hide form on success ?>
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <small class="form-text text-muted">Must be at least 8 characters long.</small>
                    </div>
                    <div class="mb-3">
                        <label for="cpassword" class="form-label">Confirm Password</label>
                        <input type="password" id="cpassword" name="cpassword" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-lg mt-3">Register</button>
                </form>
                <?php endif; ?>

                <p class="text-center mt-3 text-muted">
                    Already have an account? <a href="login.php">Login here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>