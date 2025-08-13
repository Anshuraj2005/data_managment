<?php
session_start();
include 'db.php';

$message = '';
$redirect = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($db_password);
        $stmt->fetch();

        if ($password === $db_password) {
            $_SESSION['username'] = $username;
            $message = "✅ Login successful! Redirecting to Admin Dashboard...";
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
  <title>User Login Page</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <?php if ($redirect): ?>
    <meta http-equiv="refresh" content="2;url=Admin/admin_dashboard.php">
  <?php endif; ?>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
  <section class="flex bg-white rounded-lg shadow-lg max-w-4xl w-full">
    
    <div class="hidden md:block w-1/2">
      <img 
        src="assets/admin_login_img.webp" 
        alt="Login Image" 
        class="object-cover h-full w-full rounded-l-lg"
      />
    </div>
    
    <div class="w-full md:w-1/2 p-8">
      <form action="" method="POST" class="space-y-6">
        <h1 class="text-3xl font-semibold text-center text-gray-700 mb-6">Admin Login Page</h1>

        <?php if ($message): ?>
          <p class="text-center <?= $redirect ? 'text-green-600' : 'text-red-600' ?>">
            <?= htmlspecialchars($message) ?>
          </p>
        <?php endif; ?>
        
        <input 
          type="text" 
          name="username" 
          placeholder="Enter your name" 
          required
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
        />
        
        <input 
          type="password" 
          name="password" 
          placeholder="Enter your password" 
          required
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        
        <button 
          type="submit" 
          class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition"
        >
          Login
        </button>
      </form>
    </div>
    
  </section>
</body>
</html>
