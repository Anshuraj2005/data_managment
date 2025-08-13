<?php
session_start();
include '../db.php';

// Ensure user logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$upload_dir = '../uploads/';

// Create uploads folder if not exists
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Create categories table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL
)");

// Create files table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
)");

// Handle category deletion
if (isset($_POST['delete_category']) && isset($_POST['delete_category_id'])) {
    $del_cat_id = intval($_POST['delete_category_id']);

    // Delete the category; this deletes all linked files automatically
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $del_cat_id);
    $stmt->execute();
    $stmt->close();
}

$category_error = $file_error = '';

// Add category manually
if (isset($_POST['add_category'])) {
    $cat_name = trim($_POST['category_name']);
    if ($cat_name !== '') {
        $stmt = $conn->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $cat_name);
        if (!$stmt->execute()) {
            $category_error = "Error adding category.";
        }
        $stmt->close();
    } else {
        $category_error = "Category name cannot be empty.";
    }
}

// Upload file with selected category
if (isset($_POST['upload_file'])) {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file']['tmp_name'];
        $original_name = basename($_FILES['file']['name']);

        // Get selected category id from form (validate it)
        $selected_cat_id = intval($_POST['category_id']);
        // Make sure category exists
        $stmt = $conn->prepare("SELECT id, name FROM categories WHERE id = ?");
        $stmt->bind_param("i", $selected_cat_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $file_error = "Selected category does not exist.";
            $stmt->close();
            goto render_page;
        }
        $stmt->bind_result($cat_id, $cat_name);
        $stmt->fetch();
        $stmt->close();

        // Determine upload folder based on category name
        $cat_folder = $upload_dir . $cat_name . '/';
        if (!is_dir($cat_folder)) {
            mkdir($cat_folder, 0755, true);
        }

        // Sanitize filename
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $safe_name = preg_replace("/[^A-Za-z0-9.\-_]/", "_", $original_name);
        $unique_name = pathinfo($safe_name, PATHINFO_FILENAME) . '_' . time() . '.' . $ext;

        $target_path = $cat_folder . $unique_name;
        $relative_path = 'uploads/' . $cat_name . '/' . $unique_name;

        if (move_uploaded_file($file_tmp, $target_path)) {
            $stmt = $conn->prepare("INSERT INTO files (category_id, filename, filepath) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $cat_id, $unique_name, $relative_path);
            if (!$stmt->execute()) {
                $file_error = "Database error saving file info.";
            }
            $stmt->close();
        } else {
            $file_error = "Failed to move uploaded file.";
        }
    } else {
        $file_error = "No file selected or upload error.";
    }
}

render_page:

// Fetch categories and files
$categories = [];
$result = $conn->query("SELECT * FROM categories ORDER BY name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    $result->free();
}

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
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard - Manage Categories & Files</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen bg-gray-100">
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
  <main class="flex-1 p-8">
    <!-- Add Category -->
    <section class="mb-8 bg-white p-6 rounded shadow">
      <h3 class="text-2xl font-semibold mb-4">Add Category</h3>
      <?php if ($category_error): ?>
        <p class="text-red-600 mb-4"><?= htmlspecialchars($category_error) ?></p>
      <?php endif; ?>
      <form method="POST" class="flex gap-2">
        <input type="text" name="category_name" placeholder="New category name" required
          class="border border-gray-300 rounded px-3 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <button type="submit" name="add_category"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Add</button>
      </form>
    </section>

    <!-- Upload File -->
    <section class="mb-8 bg-white p-6 rounded shadow">
      <h3 class="text-2xl font-semibold mb-4">Upload File (Select Category)</h3>
      <?php if ($file_error): ?>
        <p class="text-red-600 mb-4"><?= htmlspecialchars($file_error) ?></p>
      <?php endif; ?>
      <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-4 max-w-md">
        <label class="block">
          Select Category:
          <select name="category_id" required
            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars(ucfirst($cat['name'])) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label class="block">
          Select File:
          <input type="file" name="file" required
            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </label>

        <button type="submit" name="upload_file" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
          Upload File
        </button>
      </form>
    </section>

    <!-- Show Categories & Files -->
    <section class="bg-white p-6 rounded shadow">
      <h3 class="text-2xl font-semibold mb-4">Categories & Files</h3>
      <?php if (count($categories) === 0): ?>
        <p>No categories found.</p>
      <?php else: ?>
        <ul class="space-y-4">
          <?php foreach ($categories as $cat): ?>
            <li>
              <div class="flex items-center justify-between mb-1">
                <h4 class="font-bold text-lg"><?= htmlspecialchars(ucfirst($cat['name'])) ?></h4>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this category and all its files?');">
                  <input type="hidden" name="delete_category_id" value="<?= $cat['id'] ?>" />
                  <button type="submit" name="delete_category" 
                    class="text-red-600 hover:text-red-800 text-sm px-2 py-1 border border-red-600 rounded">
                    Delete
                  </button>
                </form>
              </div>
              <?php if (empty($files_by_cat[$cat['id']])): ?>
                <p class="text-gray-500 italic">No files uploaded.</p>
              <?php else: ?>
                <ul class="list-disc list-inside space-y-1">
                  <?php foreach ($files_by_cat[$cat['id']] as $file): 
                    $file_url = '../' . $file['filepath'];
                  ?>
                    <li>
                      <a href="<?= htmlspecialchars($file_url) ?>" target="_blank" class="text-blue-600 hover:underline">
                        <?= htmlspecialchars($file['filename']) ?>
                      </a>
                      <small class="text-gray-400 text-sm">(uploaded at <?= $file['uploaded_at'] ?>)</small>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
