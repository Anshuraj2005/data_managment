<?php
include '../db.php';

$category_id = intval($_GET['category_id']);
$data = [];

// Fetch all files for this category
$stmt = $conn->prepare("SELECT filename FROM dms_files WHERE category_id=?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

// Count file types
$type_counts = [
    'Word' => 0,
    'Excel' => 0,
    'PDF' => 0,
    'Other' => 0
];

while ($row = $result->fetch_assoc()) {
    $ext = strtolower(pathinfo($row['filename'], PATHINFO_EXTENSION));
    if (in_array($ext, ['doc', 'docx'])) {
        $type_counts['Word']++;
    } elseif (in_array($ext, ['xls', 'xlsx'])) {
        $type_counts['Excel']++;
    } elseif ($ext === 'pdf') {
        $type_counts['PDF']++;
    } else {
        $type_counts['Other']++;
    }
}

// Prepare JSON response
foreach ($type_counts as $type => $count) {
    if ($count > 0) {
        $data[] = ['file_type' => $type, 'count' => $count];
    }
}

header('Content-Type: application/json');
echo json_encode($data);
?>
