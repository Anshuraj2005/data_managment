<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Page</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Background animation */
    @keyframes gradientAnimation {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    body {
      background: linear-gradient(-45deg, #6366f1, #3b82f6, #06b6d4, #8b5cf6);
      background-size: 400% 400%;
      animation: gradientAnimation 15s ease infinite;
      font-family: 'Inter', sans-serif;
    }
    /* Button hover */
    .btn-hover {
      transition: all 0.3s ease;
    }
    .btn-hover:hover {
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
    }
  </style>
</head>
<body class="flex items-center justify-center min-h-screen">

  <div class="bg-white/80 backdrop-blur-2xl shadow-2xl rounded-3xl p-10 w-full max-w-md text-center relative">
    
    <!-- Top Decorative Circle -->
    <div class="absolute top-[-40px] right-[-40px] w-28 h-28 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full blur-2xl opacity-30"></div>

    <!-- Logo or Illustration -->
    <div class="flex justify-center mb-6">
      <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
      </div>
    </div>

    <!-- Heading -->
    <h1 class="text-4xl font-extrabold text-gray-800 mb-2">Welcome</h1>
    <p class="text-gray-600 mb-8">Please select your login type</p>

    <!-- Buttons -->
    <div class="space-y-5">
      <!-- User Login -->
      <a href="user_login.php" class="block">
        <button class="btn-hover w-full flex items-center justify-center gap-3 px-6 py-4 bg-indigo-600 text-white text-lg font-semibold rounded-xl shadow-lg hover:bg-indigo-700">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A4 4 0 0112 15a4 4 0 016.879 2.804M15 11a3 3 0 10-6 0 3 3 0 006 0z" />
          </svg>
          User Login
        </button>
      </a>
      
      <!-- Admin Login -->
      <a href="admin_login.php" class="block">
        <button class="btn-hover w-full flex items-center justify-center gap-3 px-6 py-4 bg-emerald-600 text-white text-lg font-semibold rounded-xl shadow-lg hover:bg-emerald-700">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v4a1 1 0 001 1h3m10 0h3a1 1 0 001-1V7a1 1 0 00-1-1h-3m-4 0V4a1 1 0 00-1-1h-2a1 1 0 00-1 1v2" />
          </svg>
          Admin Login
        </button>
      </a>
    </div>

    <!-- Extra -->
    <!--
    <p class="mt-6 text-gray-600">New here? 
      <a href="user_register.php" class="text-purple-600 font-semibold hover:underline">Register</a>
    </p>
    -->
  </div>

</body>
</html>
