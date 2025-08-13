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
            $stmt = $conn->prepare("INSERT INTO files (category_id, filename, filepath) VALUES (?, ?, ?)");
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
$res = $conn->query("SELECT * FROM categories ORDER BY name");
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
    <main class="flex-1 p-10">
      <div class="max-w-xl mx-auto bg-white shadow p-6 rounded">
        <h1 class="text-2xl font-bold mb-4">Upload File to Category</h1>

        <?php if ($file_error): ?>
          <div class="text-red-600 mb-4"><?= htmlspecialchars($file_error) ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
          <div class="text-green-600 mb-4"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
          <div>
            <label class="block mb-1 font-semibold">Select Category</label>
            <select name="category_id" required class="w-full border px-3 py-2 rounded">
              <option value="">-- Choose Category --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block mb-1 font-semibold">Select File</label>
            <input type="file" name="file" required class="w-full border px-3 py-2 rounded" />
          </div>

          <button type="submit" name="upload_file"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Upload</button>
        </form>
      </div>
    </main>
  </div>
</body>
</html>
