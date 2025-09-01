<?php
session_start();
include '../db.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$upload_dir = '../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$file_error = '';
$success_message = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_file'])) {
    $cat_id = intval($_POST['category_id']);

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file']['tmp_name'];
        $original_name = basename($_FILES['file']['name']);

        // Sanitize
        $original_name = preg_replace("/[^A-Za-z0-9.\-_]/", "_", $original_name);

        $ext = pathinfo($original_name, PATHINFO_EXTENSION);
        $base = pathinfo($original_name, PATHINFO_FILENAME);
        $unique_name = $base . '_' . time() . '.' . $ext;

        $cat_folder = $upload_dir . $cat_id . '/';
        if (!is_dir($cat_folder)) {
            mkdir($cat_folder, 0755, true);
        }

        $target_path = $cat_folder . $unique_name;
        $relative_path = 'uploads/' . $cat_id . '/' . $unique_name;

        if (move_uploaded_file($file_tmp, $target_path)) {
            $stmt = $conn->prepare("INSERT INTO dms_files (category_id, filename, filepath) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $cat_id, $unique_name, $relative_path);
            if ($stmt->execute()) {
                $success_message = "✅ File uploaded successfully.";
            } else {
                $file_error = "❌ Database error saving file.";
            }
            $stmt->close();
        } else {
            $file_error = "❌ Failed to move uploaded file.";
        }
    } else {
        $file_error = "❌ No file selected or upload error.";
    }
}

// Fetch categories
$categories = [];
$res = $conn->query("SELECT * FROM dms_categories ORDER BY name");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $categories[] = $row;
    }
    $res->free();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Upload File - Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="flex min-h-screen">

    <!-- Sidebar -->
  <aside class="w-64 bg-gray-900 text-white min-h-screen p-8 shadow-lg flex flex-col">
    <h2 class="text-3xl font-extrabold mb-10 tracking-wide">User Panel</h2>
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
<main class="flex-1 p-10 bg-gray-50 min-h-screen flex justify-center items-center">
  <div class="max-w-xl w-full bg-white shadow-xl rounded-2xl p-8 border border-gray-100">
    <h1 class="text-3xl font-extrabold mb-6 text-gray-800 text-center">
      📤 Upload File to Category
    </h1>

    <!-- Error Message -->
    <?php if ($file_error): ?>
      <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
        <?= htmlspecialchars($file_error) ?>
      </div>
    <?php endif; ?>

    <!-- Success Message -->
    <?php if ($success_message): ?>
      <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
        <?= htmlspecialchars($success_message) ?>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
      <!-- Category Selection -->
      <div>
        <label class="block mb-2 font-semibold text-gray-700 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h4l3 3h11v9H3V7z" />
          </svg>
          Select Category
        </label>
        <select name="category_id" required
          class="w-full border border-gray-300 px-4 py-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
          <option value="">-- Choose Category --</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- File Input -->
      <div>
        <label class="block mb-2 font-semibold text-gray-700 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Select File
        </label>
        <input type="file" name="file" required
          class="w-full border border-gray-300 px-4 py-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-400 transition bg-gray-50" />
      </div>

      <!-- Submit Button -->
      <button type="submit" name="upload_file"
        class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold shadow-md hover:bg-blue-700 hover:shadow-lg transform hover:scale-105 transition duration-300">
        ✅ Upload File
      </button>
    </form>
  </div>
</main>

  </div>
</body>
</html>
