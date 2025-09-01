<?php
session_start();
include 'db.php';

$message = '';
$redirect = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT password FROM dms_admin WHERE username = ?");
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
  <title>Admin Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: linear-gradient(-45deg, #4f46e5, #3b82f6, #06b6d4, #9333ea);
      background-size: 400% 400%;
      animation: gradientAnimation 15s ease infinite;
      font-family: 'Inter', sans-serif;
    }
    @keyframes gradientAnimation {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    .btn-hover:hover {
      transform: translateY(-3px) scale(1.02);
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
  </style>
  <?php if ($redirect): ?>
    <meta http-equiv="refresh" content="2;url=Admin/admin_dashboard.php">
  <?php endif; ?>
</head>
<body class="flex items-center justify-center min-h-screen">

  <section class="flex flex-col md:flex-row bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden max-w-4xl w-full">
    
    <!-- Left Image -->
    <div class="hidden md:block md:w-1/2 relative">
      <img src="assets/admin_login_img.webp" alt="Login Image" class="object-cover h-full w-full" />
      <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
    </div>
    
    <!-- Right Form -->
    <div class="w-full md:w-1/2 p-10">
      <h1 class="text-4xl font-extrabold text-gray-800 text-center mb-4">Admin Login</h1>
      <p class="text-center text-gray-600 mb-6">Access your dashboard securely</p>

      <?php if ($message): ?>
        <div class="mb-4 text-center text-lg font-semibold <?= $redirect ? 'text-green-600' : 'text-red-600' ?>">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <form action="" method="POST" class="space-y-5">
        <!-- Username -->
        <div>
          <label class="block mb-1 font-medium text-gray-700">Username</label>
          <input 
            type="text" 
            name="username" 
            placeholder="Enter your username" 
            required
            value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        <!-- Password -->
        <div>
          <label class="block mb-1 font-medium text-gray-700">Password</label>
          <div class="relative">
            <input 
              type="password" 
              name="password" 
              placeholder="Enter your password" 
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
              id="password"
            />
            <button type="button" class="absolute right-3 top-3 text-gray-500 hover:text-gray-700" onclick="togglePassword()">
              👁
            </button>
          </div>
        </div>

        <!-- Submit Button -->
        <button 
          type="submit" 
          class="btn-hover w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold text-lg shadow-md hover:bg-indigo-700 transition"
        >
          Login
        </button>
      </form>
    </div>
  </section>

  <script>
    function togglePassword() {
      const pwd = document.getElementById('password');
      pwd.type = pwd.type === 'password' ? 'text' : 'password';
    }
  </script>

</body>
</html>
