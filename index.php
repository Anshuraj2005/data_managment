<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Page</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 via-white to-purple-100 min-h-screen flex items-center justify-center">

  <div class="text-center space-y-6">
    <h1 class="text-3xl font-bold text-gray-800">Welcome</h1>
    
    <div class="space-x-4">
      <a href="user_login.php">
        <button class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-300">
          User Login
        </button>
      </a>
      
      <a href="admin_login.php">
        <button class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 transition duration-300">
          Admin Login
        </button>
      </a>

      <a href="user_register.php">
        <button class="px-6 py-2 bg-purple-600 text-white font-semibold rounded-lg shadow-md hover:bg-purple-700 transition duration-300">
          User Registration
        </button>
      </a>
    </div>
  </div>

</body>
</html>
