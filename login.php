<?php
require_once 'includes/db.php';

$err = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $mysqli->prepare("SELECT user_id, password_hash FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param('ss', $login, $login);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows === 1){
        $u = $res->fetch_assoc();
        if(password_verify($password, $u['password_hash'])){
            $_SESSION['user_id'] = $u['user_id'];

            $up = $mysqli->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $up->bind_param('i', $u['user_id']);
            $up->execute();

            header('Location: index.php');
            exit;
        }
    }
    $err = "Invalid username/email or password";
}

require 'includes/header.php';
?>

<div class="login-container">

  <h2>Welcome Back 👋</h2>
  <p class="sub-text">Login to continue</p>

  <?php if($err): ?>
      <div class="error-box"><?= esc($err) ?></div>
  <?php endif; ?>

  <form method="post" class="login-form">

      <label>Username or Email
        <input name="login" required placeholder="Enter username or email">
      </label>

      <label>Password
        <input name="password" type="password" required placeholder="Enter your password">
      </label>

      <button type="submit" class="btn-login">Login</button>

  </form>

  <p class="register-link">
     New user? <a href="register.php">Create an account</a>
  </p>

</div>

<?php require 'includes/footer.php'; ?>


<style>
/* Page Background */
body {
  background: #f5f7fa;
  font-family: "Poppins", sans-serif;
  margin: 0;
  padding: 10px 10px;
}

/* Login Card */
.login-container {
  max-width: 420px;
  margin: auto;
  background: white;
  padding: 35px;
  border-radius: 16px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.12);
  animation: fadeIn 0.5s ease;
  margin-top: 40px;
}

/* Title */
.login-container h2 {
  text-align: center;
  margin-bottom: 5px;
  font-size: 28px;
  color: #0d6efd;
}

.sub-text {
  text-align: center;
  color: #666;
  font-size: 15px;
  margin-bottom: 20px;
}

/* Error Box */
.error-box {
  background: #ffe5e5;
  color: #c62828;
  padding: 12px 15px;
  border-radius: 8px;
  border-left: 5px solid #ef5350;
  margin-bottom: 20px;
  font-weight: 500;
}

/* Label & Inputs */
.login-form label {
  display: block;
  margin-bottom: 18px;
  font-weight: 600;
  color: #444;
}

.login-form input {
  width: 100%;
  padding: 12px;
  border-radius: 10px;
  border: 1px solid #bbb;
  margin-top: 6px;
  font-size: 15px;
  background: #fafafa;
  transition: 0.3s;
}

.login-form input:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 6px rgba(13,110,253,0.3);
  outline: none;
}

/* Button */
.btn-login {
  width: 100%;
  padding: 14px;
  background: #0d6efd;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 18px;
  font-weight: 600;
  cursor: pointer;
  margin-top: 10px;
  transition: 0.25s ease;
}

.btn-login:hover {
  background: #084ac1;
}

/* Register link */
.register-link {
  text-align: center;
  margin-top: 15px;
  font-size: 15px;
}

.register-link a {
  color: #0d6efd;
  font-weight: 600;
  text-decoration: none;
}

.register-link a:hover {
  text-decoration: underline;
}

/* Animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: translateY(0); }
}
</style>
