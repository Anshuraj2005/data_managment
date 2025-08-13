<?php
session_start();
include '../db.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$category_error = '';
$success_message = '';

// Create `categories` table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL
)");

// Handle adding category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $cat_name = trim($_POST['category_name']);
    if ($cat_name !== '') {
        $stmt = $conn->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $cat_name);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $success_message = "✅ Category added successfully!";
            } else {
                $category_error = "⚠️ Category already exists.";
            }
        } else {
            $category_error = "❌ Error adding category.";
        }
        $stmt->close();
    } else {
        $category_error = "❌ Category name cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add Category - Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white min-h-screen p-8 shadow-lg flex flex-col">
      <h2 class="text-3xl font-extrabold mb-10 tracking-wide">User Panel</h2>
      <nav class="space-y-6 flex-grow">
        <a href="dashboard.php" class="block hover:bg-blue-700 p-3 rounded">🏠 Dashboard</a>
        <a href="add_category.php" class="block hover:bg-blue-700 p-3 rounded bg-blue-800">➕ Add Category</a>
        <a href="show_category.php" class="block hover:bg-blue-700 p-3 rounded">📂 Show Categories</a>
        <a href="add_file.php" class="block hover:bg-blue-700 p-3 rounded">📁 Add File</a>
        <a href="view_files.php" class="block hover:bg-blue-700 p-3 rounded">👁️ View Files</a>
      </nav>
      <a href="logout.php" class="mt-auto block bg-red-700 text-red-100 text-center p-3 rounded hover:bg-red-800 font-semibold">
        🔓 Logout
      </a>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-10">
      <div class="max-w-xl mx-auto bg-white shadow p-6 rounded">
        <h1 class="text-2xl font-bold mb-4">Add New Category</h1>

        <?php if ($category_error): ?>
          <div class="text-red-600 mb-4"><?= htmlspecialchars($category_error) ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
          <div class="text-green-600 mb-4"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
          <div>
            <label class="block mb-1 font-semibold">Category Name</label>
            <input type="text" name="category_name" required class="w-full border px-3 py-2 rounded" />
          </div>
          <button type="submit" name="add_category"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Category</button>
        </form>
      </div>
    </main>
  </div>
</body>
</html>
