<?php
session_start();
include '../db.php';

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');

// Get total categories
$totalCategories = 0;
$res = $conn->query("SELECT COUNT(*) as total FROM dms_categories");
if ($res) {
    $row = $res->fetch_assoc();
    $totalCategories = $row['total'];
    $res->free();
}

// Get total files
$totalFiles = 0;
$res = $conn->query("SELECT COUNT(*) as total FROM dms_files");
if ($res) {
    $row = $res->fetch_assoc();
    $totalFiles = $row['total'];
    $res->free();
}

// Get categories with file count
$categories = [];
$sql = "SELECT c.name, COUNT(f.id) AS file_count
        FROM dms_categories c
        LEFT JOIN dms_files f ON c.id = f.category_id
        GROUP BY c.id, c.name
        ORDER BY c.name ASC";
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $categories[] = $row;
    }
    $res->free();
}

// Prepare data for chart
$catNames = array_column($categories, 'name');
$fileCounts = array_column($categories, 'file_count');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 min-h-screen flex">

  <!-- Sidebar -->
  <aside class="fixed left-0 top-0 w-64 h-full bg-gray-900 text-white p-8 shadow-lg flex flex-col">
    <h2 class="text-3xl font-extrabold mb-10 tracking-wide">User Panel</h2>
    <nav class="space-y-6 flex-grow">
      <a href="dashboard.php" class="block hover:bg-blue-700 p-3 rounded transition duration-200">🏠 Dashboard</a>
      <a href="show_category.php" class="block hover:bg-blue-700 p-3 rounded transition duration-200">📂 Show Categories</a>
      <a href="view_files.php" class="block hover:bg-blue-700 p-3 rounded transition duration-200">👁️ View Files</a>
    </nav>
    <a href="logout.php" class="mt-auto block bg-red-700 text-red-100 text-center p-3 rounded hover:bg-red-800 transition duration-200 font-semibold">
      🔓 Logout
    </a>
  </aside>

  <!-- Main Content -->
  <main class="ml-64 flex-1 p-10 overflow-y-auto h-screen">
    <header>
      <h1 class="text-3xl font-semibold text-gray-700 mb-8">Welcome, <?= $username ?> 👋</h1>
    </header>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
      
      <!-- Total Categories -->
      <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-6 rounded-2xl shadow-lg flex flex-col items-center justify-center hover:scale-105 transform transition">
        <h2 class="text-lg font-medium">Total Categories</h2>
        <p class="text-5xl font-extrabold mt-2"><?= $totalCategories ?></p>
      </div>
      
      <!-- Total Files -->
      <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white p-6 rounded-2xl shadow-lg flex flex-col items-center justify-center hover:scale-105 transform transition">
        <h2 class="text-lg font-medium">Total Files</h2>
        <p class="text-5xl font-extrabold mt-2"><?= $totalFiles ?></p>
      </div>

    </div>

    <!-- Chart Section -->
    <div class="bg-white p-6 rounded-2xl shadow-lg mt-8 w-full">
      <h2 class="text-2xl font-bold mb-6 text-gray-700">Files per Category</h2>
      <div class="w-full overflow-x-auto">
        <canvas id="categoryChart" style="min-width: 900px; height: 400px;"></canvas>
      </div>
    </div>
  </main>

  <!-- Chart Script -->
  <script>
    const ctx = document.getElementById('categoryChart').getContext('2d');

    // Dynamic gradients for bars
    const gradients = [];
    const colors = [
      ['#3b82f6', '#06b6d4'], // Blue
      ['#10b981', '#34d399'], // Green
      ['#8b5cf6', '#6366f1'], // Purple
      ['#f59e0b', '#f97316'], // Orange
      ['#ef4444', '#dc2626'], // Red
      ['#ec4899', '#db2777']  // Pink
    ];

    const chartHeight = 400;
    for (let i = 0; i < <?= count($catNames) ?>; i++) {
      const gradient = ctx.createLinearGradient(0, 0, 0, chartHeight);
      const colorPair = colors[i % colors.length];
      gradient.addColorStop(0, colorPair[0]);
      gradient.addColorStop(1, colorPair[1]);
      gradients.push(gradient);
    }

    const categoryChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($catNames) ?>,
        datasets: [{
          data: <?= json_encode($fileCounts) ?>,
          backgroundColor: gradients,
          borderRadius: 14,
          barThickness: 70,      // Increased bar width
          maxBarThickness: 90    // Increased max bar width
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 1200,
          easing: 'easeOutQuart'
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#111827',
            titleColor: '#fff',
            bodyColor: '#fff',
            padding: 12,
            cornerRadius: 8
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: {
              color: '#374151',
              font: { size: 16, weight: '600' }
            }
          },
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1,
              color: '#374151',
              font: { size: 14, weight: '600' }
            },
            grid: { color: '#e5e7eb', drawBorder: false }
          }
        }
      }
    });
  </script>
</body>
</html>
