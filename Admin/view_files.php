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

    // Optional: Before deleting from DB, delete the physical file from the server
    $stmt = $conn->prepare("SELECT filepath FROM files WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $file = $res->fetch_assoc()) {
        $file_path = '../' . $file['filepath'];
        if (file_exists($file_path)) {
            unlink($file_path); // Delete physical file
        }
    }
    $stmt->close();

    // Delete from DB
    $stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to avoid form resubmission
    header("Location: view_files.php");
    exit();
}

// Get categories with files
$categories = [];
$res = $conn->query("SELECT * FROM categories ORDER BY name");
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
    $stmt = $conn->prepare("SELECT * FROM files WHERE category_id = ?");
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
  <meta charset="UTF-8" />
  <title>View Uploaded Files</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white min-h-screen p-8 shadow-lg flex flex-col">
      <h2 class="text-3xl font-extrabold mb-10 tracking-wide">User Panel</h2>
      <nav class="space-y-6 flex-grow">
        <a href="dashboard.php" class="block hover:bg-blue-700 p-3 rounded transition duration-200">🏠 Dashboard</a>
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
    <main class="flex-1 p-10">
      <h1 class="text-2xl font-bold mb-6">Uploaded Files</h1>

      <?php if (empty($categories)): ?>
        <p>No categories available.</p>
      <?php else: ?>
        <?php foreach ($categories as $cat): ?>
          <div class="mb-6 bg-white p-4 rounded shadow">
            <h2 class="text-xl font-semibold mb-2"><?= htmlspecialchars($cat['name']) ?></h2>

            <?php if (empty($files_by_cat[$cat['id']])): ?>
              <p class="text-gray-500 italic">No files uploaded.</p>
            <?php else: ?>
              <ul class="list-disc list-inside space-y-1">
                <?php foreach ($files_by_cat[$cat['id']] as $file): ?>
                  <li class="flex items-center justify-between">
                    <a href="../<?= htmlspecialchars($file['filepath']) ?>" target="_blank" class="text-blue-600 hover:underline">
                      <?= htmlspecialchars($file['filename']) ?>
                    </a>
                    <small class="text-gray-500 ml-2">(uploaded at <?= htmlspecialchars($file['uploaded_at']) ?>)</small>

                    <!-- Delete Button -->
                    <form method="POST" onsubmit="return confirm('Delete this file?');" class="ml-4">
                      <input type="hidden" name="delete_file_id" value="<?= $file['id'] ?>">
                      <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-semibold">
                        Delete
                      </button>
                    </form>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
