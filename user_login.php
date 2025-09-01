<?php
session_start();
include 'db.php'; // Connects to 'managment' database

$message = '';
$redirect = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT password FROM dms_login WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($db_password);
        $stmt->fetch();

        if ($password === $db_password) {
            $_SESSION['username'] = $username;
            $message = "✅ Login successful! Redirecting...";
            $redirect = true;
        } else {
            $message = "❌ Incorrect password.";
        }
    } else {
        $message = "❌ User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>User Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <?php if ($redirect): ?>
    <meta http-equiv="refresh" content="2;url=Dashboard/dashboard.php">
  <?php endif; ?>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-r from-sky-400 to-blue-600">
  <div class="bg-white rounded-3xl shadow-2xl flex flex-col md:flex-row overflow-hidden w-full max-w-5xl">
    
    <!-- Left Image Section -->
    <div class="md:w-1/2 hidden md:block">
      <img src="assets/user_login_img.jpg" alt="Login" class="w-full h-full object-cover">
    </div>

    <!-- Right Login Form -->
    <div class="md:w-1/2 w-full p-10 flex flex-col justify-center">
      <h2 class="text-4xl font-bold text-gray-800 mb-2 text-center">User Login</h2>
      <p class="text-gray-500 text-center mb-6">Access your account securely</p>

      <?php if ($message): ?>
        <p class="text-center mb-4 <?= $redirect ? 'text-green-600' : 'text-red-600' ?>">
          <?= htmlspecialchars($message) ?>
        </p>
      <?php endif; ?>

      <form action="" method="POST" class="space-y-5">
        <div>
          <label class="block text-gray-700 mb-2">Username</label>
          <input 
            type="text" 
            name="username" 
            placeholder="Enter your username" 
            required
            value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label class="block text-gray-700 mb-2">Password</label>
          <input 
            type="password" 
            name="password" 
            placeholder="Enter your password" 
            required
            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <button 
          type="submit" 
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition duration-300"
        >
          Login
        </button>
      </form>
    </div>
  </div>
</body>
</html>
