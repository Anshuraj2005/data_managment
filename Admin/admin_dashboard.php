<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

include '../db.php';

// Fetch category names and file counts
$categories = [];
$file_counts = [];
$category_ids = [];

$res = $conn->query("SELECT c.id, c.name, COUNT(f.id) AS file_count 
                     FROM dms_categories c 
                     LEFT JOIN dms_files f ON f.category_id = c.id 
                     GROUP BY c.id, c.name 
                     ORDER BY c.name ASC");

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $category_ids[] = $row['id'];
        $categories[] = $row['name'];
        $file_counts[] = $row['file_count'];
    }
}

// Fetch total number of files
$totalFiles = 0;
$resFiles = $conn->query("SELECT COUNT(*) as total FROM dms_files");
if ($resFiles) {
    $row = $resFiles->fetch_assoc();
    $totalFiles = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .chart-container {
      position: relative;
      height: 350px;
      width: 100%;
      max-width: 800px;
      margin: auto;
    }
  </style>
</head>
<body class="bg-gray-100">

  <!-- Sidebar -->
  <aside class="fixed top-0 left-0 w-72 h-screen bg-gray-900 text-white p-8 shadow-lg flex flex-col">
    <h2 class="text-3xl font-extrabold mb-10 tracking-wide">Admin Panel</h2>
    <nav class="space-y-6 flex-grow">
      <a href="admin_dashboard.php" class="block hover:bg-blue-700 p-3 rounded transition duration-200">🏠 Dashboard</a>
      <a href="add_category.php" class="block hover:bg-blue-700 p-3 rounded transition duration-200">➕ Add Category</a>
      <a href="show_category.php" class="block hover:bg-blue-700 p-3 rounded transition duration-200">📂 Show Categories</a>
      <a href="add_file.php" class="block hover:bg-blue-700 p-3 rounded transition duration-200">📁 Add File</a>
      <a href="view_files.php" class="block hover:bg-blue-700 p-3 rounded transition duration-200">👁️ View Files</a>
    </nav>
    <a href="logout.php" class="mt-auto block bg-red-700 text-red-100 text-center p-3 rounded hover:bg-red-800 transition duration-200 font-semibold">
      🔓 Logout
    </a>
  </aside>

  <!-- Main Content -->
<main class="ml-72 p-10 bg-gray-50 min-h-screen">
  <!-- Header -->
  <h1 class="text-4xl font-extrabold text-gray-800 mb-10 flex items-center">
    👋 Welcome, <span class="text-blue-600 ml-2"><?= htmlspecialchars($_SESSION['username']) ?></span>
  </h1>

  <!-- Date & Time and Total Files -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-12">
    
    <!-- Current Date & Time Box -->
    <div
      class="bg-gradient-to-br from-blue-500 to-indigo-600 text-white rounded-3xl shadow-xl p-5 text-center transform hover:scale-105 transition duration-500">
      <h2 class="text-lg font-medium tracking-wide mb-3 uppercase">Current Date & Time</h2>
      <p id="dateTime" class="text-3xl font-extrabold"></p>
    </div>

    <!-- Total Files Box -->
    <div
      class="bg-gradient-to-br from-green-500 to-emerald-600 text-white rounded-3xl shadow-xl p-5 text-center transform hover:scale-105 transition duration-500">
      <h2 class="text-lg font-medium tracking-wide mb-3 uppercase">Total Files</h2>
      <p class="text-6xl font-extrabold"><?= $totalFiles ?></p>
    </div>
    
  </div>

  <!-- Charts Section -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
    
    <!-- Pie Chart -->
    <div
      class="bg-white shadow-lg rounded-3xl p-8 hover:shadow-2xl transition duration-500 border border-gray-100">
      <h2 class="text-2xl font-bold text-gray-700 mb-6 text-center">📊 Files by Category</h2>
      <div class="chart-container relative" style="height: 350px;">
        <canvas id="pieChart"></canvas>
      </div>
    </div>

    <!-- Bar Chart -->
    <div
      class="bg-white shadow-lg rounded-3xl p-8 hover:shadow-2xl transition duration-500 border border-gray-100">
      <h2 class="text-2xl font-bold text-gray-700 mb-6 text-center">📈 File Types (Click a category)</h2>
      <div class="chart-container relative" style="height: 350px;">
        <canvas id="barChart"></canvas>
      </div>
    </div>
    
  </div>
</main>



  <script>
    // ✅ Update Date & Time
    function updateDateTime() {
      const now = new Date();
      const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
      document.getElementById('dateTime').textContent = now.toLocaleDateString('en-US', options);
    }
    setInterval(updateDateTime, 1000);
    updateDateTime();

    // ✅ Charts Data
    const categories = <?= json_encode($categories) ?>;
    const fileCounts = <?= json_encode($file_counts) ?>;
    const categoryIds = <?= json_encode($category_ids) ?>;

    // ✅ Pie Chart
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    const pieChart = new Chart(pieCtx, {
      type: 'pie',
      data: {
        labels: categories,
        datasets: [{
          data: fileCounts,
          backgroundColor: ['#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#3B82F6', '#6366F1', '#8B5CF6'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        plugins: {
          tooltip: {
            callbacks: {
              label: function(context) {
                return `${context.label}: ${context.parsed} dms_files`;
              }
            }
          }
        },
        onClick: function(evt, elements) {
          if (elements.length > 0) {
            const index = elements[0].index;
            const categoryId = categoryIds[index];
            loadBarChart(categoryId, categories[index]);
          }
        }
      }
    });

    // ✅ Bar Chart
    const barCtx = document.getElementById('barChart').getContext('2d');
    let barChart = new Chart(barCtx, {
      type: 'bar',
      data: {
        labels: [],
        datasets: [{
          label: 'Files',
          data: [],
          backgroundColor: '#06B6D4',
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });

    // ✅ Load file types for a category
    function loadBarChart(categoryId, categoryName) {
      fetch('fetch_file_types.php?category_id=' + categoryId)
        .then(response => response.json())
        .then(data => {
          const labels = data.map(item => item.file_type);
          const counts = data.map(item => item.count);
          barChart.data.labels = labels;
          barChart.data.datasets[0].data = counts;
          barChart.update();
        });
    }
  </script>
</body>
</html>
