<?php
session_start();
include 'db.php'; // Make sure this connects to your 'managment' database

$message = '';
$redirect = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check if user already exists
    $stmt = $conn->prepare("SELECT S_no FROM login WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = "❌ Username already exists.";
    } else {
        // Insert new user (plain text password as requested)
        $stmt = $conn->prepare("INSERT INTO login (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            $message = "✅ Registration successful! Redirecting to login...";
            $redirect = true;
        } else {
            $message = "❌ Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>User Registration</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <?php if ($redirect): ?>
    <meta http-equiv="refresh" content="2;url=user_login.php">
  <?php endif; ?>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
  <section class="flex bg-white rounded-lg shadow-lg max-w-4xl w-full">
    <div class="hidden md:block w-1/2">
      <img 
        src="assets/Registration_img.png" 
        alt="Register Image" 
        class="object-cover h-full w-full rounded-l-lg"
      />
    </div>
    <div class="w-full md:w-1/2 p-8">
      <form action="" method="POST" class="space-y-6">
        <h1 class="text-3xl font-semibold text-center text-gray-700 mb-6">User Registration</h1>

        <?php if ($message): ?>
          <p class="text-center <?= $redirect ? 'text-green-600' : 'text-red-600' ?>">
            <?= htmlspecialchars($message) ?>
          </p>
        <?php endif; ?>
        
        <input 
          type="text" 
          name="username" 
          placeholder="Enter username" 
          required
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-purple-500"
          value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
        />
        
        <input 
          type="password" 
          name="password" 
          placeholder="Create password" 
          required
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-purple-500"
        />
        
        <button 
          type="submit" 
          class="w-full bg-purple-600 text-white py-2 rounded hover:bg-purple-700 transition"
        >
          Register
        </button>
      </form>
    </div>
  </section>
</body>
</html>
