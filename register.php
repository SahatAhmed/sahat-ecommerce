<?php
require_once 'includes/db.php';

$errors = [];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if(strlen($username) < 6) $errors[] = "Username must be at least 6 characters";
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email";
    if(strlen($password) < 8 || !preg_match('/[A-Z]/',$password) || !preg_match('/[^a-zA-Z0-9]/',$password))
        $errors[] = "Password must be 8+ chars with one uppercase and one special char";
    if($password !== $confirm) $errors[] = "Passwords do not match";

    if(empty($errors)){
        $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param('ss',$username,$email);
        $stmt->execute();
        if($stmt->get_result()->num_rows > 0){
            $errors[] = "Username or email already exists";
        } else {
            $hash = password_hash($password,PASSWORD_DEFAULT);
            $ins = $mysqli->prepare("INSERT INTO users (first_name,last_name,username,email,mobile_number,password_hash) VALUES (?,?,?,?,?,?)");
            $ins->bind_param('ssssss',$first,$last,$username,$email,$mobile,$hash);
            if($ins->execute()){
                header('Location: login.php?registered=1'); exit;
            } else {
                $errors[] = "Registration failed. Try again.";
            }
        }
    }
}
require 'includes/header.php';
?>

<div class="register-container">

  <h2>Create Your Account</h2>

  <?php if($errors): ?>
    <div class="error-box">
      <?= implode('<br>', array_map('esc', $errors)) ?>
    </div>
  <?php endif; ?>

  <form method="post" class="register-form">

      <div class="row">
        <label>First Name
          <input name="first_name" required value="<?= esc($_POST['first_name'] ?? '') ?>">
        </label>

        <label>Last Name
          <input name="last_name" required value="<?= esc($_POST['last_name'] ?? '') ?>">
        </label>
      </div>

      <label>Username
        <input name="username" minlength="6" required value="<?= esc($_POST['username'] ?? '') ?>">
      </label>

      <label>Email
        <input name="email" type="email" required value="<?= esc($_POST['email'] ?? '') ?>">
      </label>

      <label>Mobile Number
        <input name="mobile_number" value="<?= esc($_POST['mobile_number'] ?? '') ?>">
      </label>

      <label>Password
        <input name="password" type="password" required>
      </label>

      <label>Confirm Password
        <input name="confirm_password" type="password" required>
      </label>

      <button type="submit" class="btn-submit">Register</button>

  </form>

</div>

<?php require 'includes/footer.php'; ?>

<style>
/* Background */
body {
  background: white;
  font-family: "Poppins", sans-serif;
  margin: 0;
  padding: 10px 10px;
}

/* Centered container */
.register-container {
  max-width: 480px;
  margin: auto;
  background: rgba(255,255,255,0.95);
  padding: 35px;
  border-radius: 18px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
  backdrop-filter: blur(6px);
  margin-top: 25px;
}

/* Title */
.register-container h2 {
  text-align: center;
  margin-bottom: 20px;
  font-size: 26px;
  color: #0d6efd;
}

/* Error Box */
.error-box {
  background: #ffdddd;
  border-left: 5px solid #e53935;
  padding: 12px 15px;
  margin-bottom: 20px;
  border-radius: 8px;
  color: #b71c1c;
  font-weight: 500;
}

/* Inputs */
.register-form label {
  display: block;
  margin-bottom: 15px;
  font-weight: 600;
  color: #444;
}

.register-form input {
  width: 100%;
  padding: 12px;
  border-radius: 10px;
  border: 1px solid #bdbdbd;
  margin-top: 6px;
  font-size: 15px;
  transition: 0.3s;
  background: #fafafa;
}

.register-form input:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 6px rgba(13,110,253,0.3);
  outline: none;
}

/* Two-column row */
.row {
  display: flex;
  gap: 50px;
}
.row label {
  flex: 1;
}

/* Button */
.btn-submit {
  width: 100%;
  padding: 14px;
  background: #0d6efd;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 18px;
  font-weight: 600;
  margin-top: 10px;
  cursor: pointer;
  transition: 0.3s;
}

.btn-submit:hover {
  background: #084ac1;
}
</style>
