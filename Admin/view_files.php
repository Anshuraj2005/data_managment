<?php
session_start();
include '../db.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Handle file deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file_id'])) {
    $file_id = intval($_POST['delete_file_id']);
    
    // Get file path to delete from server
    $stmt = $conn->prepare("SELECT filepath FROM dms_files WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $stmt->bind_result($filepath);
    if ($stmt->fetch()) {
        $fullPath = "../" . $filepath;
        if (file_exists($fullPath)) {
            unlink($fullPath); // Delete file from server
        }
    }
    $stmt->close();

    // Delete from DB
    $stmt = $conn->prepare("DELETE FROM dms_files WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $stmt->close();

    header("Location: view_files.php?deleted=1");
    exit();
}

// Get categories
$categories = [];
$res = $conn->query("SELECT * FROM dms_categories ORDER BY name");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $categories[] = $row;
    }
    $res->free();
}

// Apply filter
$selected_category = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
$filtered_categories = $categories;

if ($selected_category) {
    $filtered_categories = array_filter($categories, function ($cat) use ($selected_category) {
        return $cat['id'] == $selected_category;
    });
}

// Get files per category
$files_by_cat = [];
foreach ($filtered_categories as $cat) {
    $cat_id = $cat['id'];
    $stmt = $conn->prepare("SELECT * FROM dms_files WHERE category_id = ?");
    $stmt->bind_param("i", $cat_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $files_by_cat[$cat_id] = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// File type icons function
function getFileIcon($ext) {
    $ext = strtolower($ext);
    $icons = [
        'pdf'   => 'https://cdn-icons-png.flaticon.com/512/337/337946.png',
        'doc'   => 'https://cdn-icons-png.flaticon.com/512/732/732226.png',
        'docx'  => 'https://cdn-icons-png.flaticon.com/512/732/732226.png',
        'xls'   => 'https://cdn-icons-png.flaticon.com/512/732/732220.png',
        'xlsx'  => 'https://cdn-icons-png.flaticon.com/512/732/732220.png',
        'ppt'   => 'https://cdn-icons-png.flaticon.com/512/732/732224.png',
        'pptx'  => 'https://cdn-icons-png.flaticon.com/512/732/732224.png',
        'jpg'   => 'https://cdn-icons-png.flaticon.com/512/337/337940.png',
        'jpeg'  => 'https://cdn-icons-png.flaticon.com/512/337/337940.png',
        'png'   => 'https://cdn-icons-png.flaticon.com/512/337/337940.png',
        'gif'   => 'https://cdn-icons-png.flaticon.com/512/337/337940.png',
        'zip'   => 'https://cdn-icons-png.flaticon.com/512/888/888879.png',
        'rar'   => 'https://cdn-icons-png.flaticon.com/512/888/888879.png',
        'txt'   => 'https://cdn-icons-png.flaticon.com/512/2991/2991106.png',
        'html'  => 'https://cdn-icons-png.flaticon.com/512/136/136528.png',
        'css'   => 'https://cdn-icons-png.flaticon.com/512/136/136527.png',
        'js'    => 'https://cdn-icons-png.flaticon.com/512/136/136530.png',
        'php'   => 'https://cdn-icons-png.flaticon.com/512/919/919830.png',
        'mp3'   => 'https://cdn-icons-png.flaticon.com/512/3097/3097412.png',
        'wav'   => 'https://cdn-icons-png.flaticon.com/512/3097/3097412.png',
        'mp4'   => 'https://cdn-icons-png.flaticon.com/512/3097/3097411.png',
        'avi'   => 'https://cdn-icons-png.flaticon.com/512/3097/3097411.png',
        'default' => 'https://cdn-icons-png.flaticon.com/512/109/109612.png'
    ];

    return $icons[$ext] ?? $icons['default'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>View Files</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      margin: 0;
      padding: 0;
      display: flex;
    }
    aside {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      width: 16rem;
    }
    main {
      margin-left: 16rem;
      height: 100vh;
      overflow-y: auto;
      padding: 2.5rem;
      flex: 1;
    }
  </style>
  <script>
    function confirmDelete() {
      return confirm('Are you sure you want to delete this file?');
    }
  </script>
</head>
<body class="bg-gray-100">

  <!-- Sidebar -->
  <aside class="bg-gray-900 text-white p-8 flex flex-col">
    <h2 class="text-3xl font-extrabold mb-10">Admin Panel</h2>
    <nav class="space-y-6">
      <a href="admin_dashboard.php" class="block hover:bg-blue-700 p-3 rounded">🏠 Dashboard</a>
      <a href="add_category.php" class="block hover:bg-blue-700 p-3 rounded">➕ Add Category</a>
      <a href="show_category.php" class="block hover:bg-blue-700 p-3 rounded">📂 Show Categories</a>
      <a href="add_file.php" class="block hover:bg-blue-700 p-3 rounded">📁 Add File</a>
      <a href="view_files.php" class="block hover:bg-blue-700 p-3 rounded bg-blue-800">👁️ View Files</a>
    </nav>
    <a href="logout.php" class="mt-auto block bg-red-700 text-center p-3 rounded hover:bg-red-800">🔓 Logout</a>
  </aside>

  <!-- Main Content -->
<main class="p-8 bg-gray-50 min-h-screen">
  <!-- Page Title -->
  <h1 class="text-4xl font-extrabold mb-8 text-gray-800 border-b-4 border-blue-600 pb-3 flex items-center gap-2">
    📁 Uploaded Files
  </h1>

  <!-- Filter Section -->
  <div class="mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <h2 class="text-xl font-semibold text-gray-700 flex items-center gap-2">
      🔍 Filter Files by Category
    </h2>
    <form method="GET" class="flex items-center gap-4 w-full sm:w-auto">
      <select name="category_id"
        class="border border-gray-300 rounded-lg px-4 py-3 w-full sm:w-64 bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= isset($_GET['category_id']) && $_GET['category_id'] == $cat['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold shadow-lg transition transform hover:scale-105">
        Search
      </button>
    </form>
  </div>

  <!-- Categories and Files -->
  <?php if (empty($filtered_categories)): ?>
    <p class="text-gray-500 italic">No categories available.</p>
  <?php else: ?>
    <?php foreach ($filtered_categories as $cat): ?>
      <div class="mb-12">
        <!-- Category Title -->
        <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b-2 border-gray-200 pb-3 flex items-center gap-2">
          📂 <?= htmlspecialchars($cat['name']) ?>
        </h2>

        <?php if (empty($files_by_cat[$cat['id']])): ?>
          <p class="text-gray-500 italic">No files uploaded in this category.</p>
        <?php else: ?>
          <!-- File Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            <?php foreach ($files_by_cat[$cat['id']] as $file): ?>
              <?php $ext = pathinfo($file['filename'], PATHINFO_EXTENSION); ?>
              <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition transform hover:-translate-y-1 p-6 flex flex-col items-center text-center border border-gray-100">
                <!-- File Icon -->
                <img src="<?= getFileIcon($ext) ?>" alt="File Icon" class="w-16 h-16 mb-4 object-contain">
                
                <!-- File Name -->
                <h3 class="font-semibold text-gray-800 text-sm truncate w-full mb-2" title="<?= htmlspecialchars($file['filename']) ?>">
                  <?= htmlspecialchars($file['filename']) ?>
                </h3>
                
                <!-- Upload Time -->
                <p class="text-xs text-gray-500 mb-4">Uploaded: <?= htmlspecialchars($file['uploaded_at']) ?></p>
                
                <!-- Action Buttons -->
                <div class="flex flex-col gap-2 w-full">
                  <!-- Download Button -->
                  <a href="../<?= htmlspecialchars($file['filepath']) ?>" download
                    class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 rounded-lg shadow hover:shadow-md transition">
                    ⬇ Download
                  </a>
                  
                  <!-- Delete Button -->
                  <form method="POST" onsubmit="return confirmDelete();">
                    <input type="hidden" name="delete_file_id" value="<?= $file['id'] ?>">
                    <button type="submit"
                     class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 w-full rounded-lg shadow hover:shadow-md transition">
                      ❌ Delete
                    </button>

                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</main>

</body>
</html>
