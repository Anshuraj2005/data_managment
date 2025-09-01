<?php
session_start();
include '../db.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
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

// Get files per category
$files_by_cat = [];
foreach ($categories as $cat) {
    $cat_id = $cat['id'];
    $stmt = $conn->prepare("SELECT * FROM dms_files WHERE category_id = ?");
    $stmt->bind_param("i", $cat_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $files_by_cat[$cat_id] = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Uploaded Files</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="flex min-h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white fixed top-0 left-0 h-screen p-8 shadow-lg flex flex-col">
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
    <main class="flex-1 ml-64 p-10 overflow-y-auto">
      <h1 class="text-2xl font-bold mb-6">Uploaded Files</h1>

      <!-- Filter Bar -->
      <div class="bg-white p-6 rounded-lg shadow mb-8 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-700">Filter Files by Category</h2>
        <div class="flex items-center space-x-3">
          <select id="categoryFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="all">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <button id="applyFilter" class="flex items-center bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.6 3.6a7.5 7.5 0 0013.05 13.05z" />
            </svg>
            Search
          </button>
        </div>
      </div>

      <?php if (empty($categories)): ?>
        <p>No categories available.</p>
      <?php else: ?>
        <div id="fileContainer">
          <?php foreach ($categories as $cat): ?>
            <div class="mb-10 category-block" data-category="<?= $cat['id'] ?>">
              <h2 class="text-xl font-semibold mb-4 border-b pb-2"><?= htmlspecialchars($cat['name']) ?></h2>

              <?php if (empty($files_by_cat[$cat['id']])): ?>
                <p class="text-gray-500 italic">No files uploaded.</p>
              <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                  <?php foreach ($files_by_cat[$cat['id']] as $file): ?>
                    <?php
                      $ext = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
                      $iconUrl = 'https://cdn-icons-png.flaticon.com/512/833/833524.png'; // default icon

                      if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                          $iconUrl = 'https://cdn-icons-png.flaticon.com/512/337/337940.png';
                      } elseif ($ext === 'pdf') {
                          $iconUrl = 'https://cdn-icons-png.flaticon.com/512/337/337946.png';
                      } elseif (in_array($ext, ['doc','docx'])) {
                          $iconUrl = 'https://cdn-icons-png.flaticon.com/512/281/281760.png';
                      } elseif (in_array($ext, ['xls','xlsx'])) {
                          $iconUrl = 'https://cdn-icons-png.flaticon.com/512/732/732220.png';
                      } elseif (in_array($ext, ['mp3','wav','ogg'])) {
                          $iconUrl = 'https://cdn-icons-png.flaticon.com/512/727/727245.png';
                      } elseif (in_array($ext, ['mp4','mkv','mov'])) {
                          $iconUrl = 'https://cdn-icons-png.flaticon.com/512/1165/1165808.png';
                      }
                    ?>
                    <div class="bg-white border rounded-xl shadow hover:shadow-2xl hover:scale-105 transition-transform duration-300 p-4 flex flex-col justify-between">
                      <div class="flex items-center space-x-3 mb-4">
                        <img src="<?= $iconUrl ?>" alt="icon" class="w-12 h-12 object-contain">
                        <h3 class="font-semibold text-gray-800 text-lg truncate" title="<?= htmlspecialchars($file['filename']) ?>">
                          <?= htmlspecialchars($file['filename']) ?>
                        </h3>
                      </div>
                      <p class="text-sm text-gray-500 mb-4">Uploaded: <?= htmlspecialchars($file['uploaded_at']) ?></p>
                      <div>
                        <a href="../<?= htmlspecialchars($file['filepath']) ?>" download class="w-full block bg-green-600 text-white py-2 rounded hover:bg-green-700 text-center text-sm">Download</a>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </main>
  </div>

<script>
  document.getElementById('applyFilter').addEventListener('click', function () {
    const selectedCategory = document.getElementById('categoryFilter').value;
    const blocks = document.querySelectorAll('.category-block');

    blocks.forEach(block => {
      if (selectedCategory === 'all') {
        block.style.display = 'block';
      } else {
        block.style.display = (block.getAttribute('data-category') === selectedCategory) ? 'block' : 'none';
      }
    });
  });
</script>

</body>
</html>
