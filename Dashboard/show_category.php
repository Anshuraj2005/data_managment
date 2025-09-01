<?php
session_start();
include '../db.php'; // Include DB (1 level up)

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch all categories
$categories = [];
$res = $conn->query("SELECT * FROM dms_categories ORDER BY name");
while ($row = $res->fetch_assoc()) {
    $categories[] = $row;
}

// Check if a category is selected via GET param
$selected_cat_id = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;
$selected_cat = null;
$files = [];

if ($selected_cat_id > 0) {
    // Fetch selected category details
    $stmt = $conn->prepare("SELECT * FROM dms_categories WHERE id = ?");
    $stmt->bind_param("i", $selected_cat_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $selected_cat = $result->fetch_assoc();
    $stmt->close();

    // Fetch files in this category
    if ($selected_cat) {
        $stmt = $conn->prepare("SELECT * FROM dms_files WHERE category_id = ?");
        $stmt->bind_param("i", $selected_cat_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $files = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Show Categories & Files</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex bg-gray-100 font-sans text-gray-900">

  <!-- Sidebar -->
  <aside class="w-64 bg-gray-900 text-white min-h-screen p-8 shadow-lg flex flex-col">
    <h2 class="text-3xl font-extrabold mb-10 tracking-wide">User Panel</h2>
    <nav class="space-y-6 flex-grow">
      <a href="dashboard.php" class="block hover:bg-blue-700 p-3 rounded transition duration-200">🏠 Dashboard</a>
      <!-- <a href="add_category.php" class="block hover:bg-blue-700 p-3 rounded transition duration-200">➕ Add Category</a> -->
      <a href="show_category.php" class="block hover:bg-blue-700 p-3 rounded transition duration-200">📂 Show Categories</a>
      <!-- <a href="add_file.php" class="block hover:bg-blue-700 p-3 rounded transition duration-200">📁 Add File</a> -->
      <a href="view_files.php" class="block hover:bg-blue-700 p-3 rounded transition duration-200">👁️ View Files</a>
    </nav>
    <a href="logout.php" class="mt-auto block bg-red-700 text-red-100 text-center p-3 rounded hover:bg-red-800 transition duration-200 font-semibold">
      🔓 Logout
    </a>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-12 max-w-5xl mx-auto">
    <h1 class="text-4xl font-bold mb-10 border-b-4 border-blue-600 pb-3">Categories</h1>

    <!-- List of Categories -->
    <?php if (count($categories) === 0): ?>
      <p class="text-lg text-gray-600 italic">No categories found.</p>
    <?php else: ?>
      <ul class="mb-12 space-y-3">
        <?php foreach ($categories as $cat): ?>
          <li>
            <a href="?cat_id=<?= $cat['id'] ?>"
               class="flex items-center text-xl font-semibold hover:text-blue-700 hover:underline transition duration-200 <?= ($selected_cat_id === $cat['id']) ? 'text-blue-800 underline' : 'text-gray-800' ?>">
              <!-- Folder icon SVG -->
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h4l3 3h11v9H3V7z" />
              </svg>
              <?= htmlspecialchars($cat['name']) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <!-- Show Files of Selected Category -->
    <?php if ($selected_cat): ?>
      <h2 class="text-3xl font-bold mb-6 border-b-2 border-gray-300 pb-2">
        Files in "<span class="text-blue-700"><?= htmlspecialchars($selected_cat['name']) ?></span>"
      </h2>
      <?php if (empty($files)): ?>
        <p class="text-gray-500 italic text-lg">No files uploaded in this category.</p>
      <?php else: ?>
        <ul class="list-disc list-inside space-y-3 max-w-3xl text-lg">
          <?php foreach ($files as $file): 
            $file_path = '/' . $file['filepath']; 
          ?>
            <li class="flex items-center justify-between">
              <a href="<?= htmlspecialchars($file_path) ?>" target="_blank" class="text-blue-600 hover:underline flex-grow">
                <?= htmlspecialchars($file['filename']) ?>
              </a>
              <a href="<?= htmlspecialchars($file_path) ?>" download
                 class="ml-4 text-sm text-green-700 hover:text-green-900 font-medium">
                [Download]
              </a>
              <small class="ml-6 text-gray-400 whitespace-nowrap">(Uploaded: <?= $file['uploaded_at'] ?>)</small>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

    <?php elseif ($selected_cat_id > 0): ?>
      <p class="text-red-600 text-lg font-semibold">Category not found.</p>
    <?php endif; ?>

  </main>
</body>
</html>
