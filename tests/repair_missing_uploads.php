<?php
require_once __DIR__ . '/../config/database.php';

function normalizeUploadPath(string $value): string
{
    $value = str_replace('\\', '/', trim($value));
    $value = preg_replace('#/+#', '/', $value);
    return $value;
}

function fileExistsForUpload(string $storedPath): bool
{
    $normalized = normalizeUploadPath($storedPath);
    if ($normalized === '' || str_contains($normalized, '..')) {
        return false;
    }

    $fullPath = dirname(__DIR__) . '/' . ltrim($normalized, '/');
    return is_file($fullPath);
}

$tables = [
    'site_banner_slides' => [
        'id',
        'image',
    ],
    'site_side_banners' => [
        'placement',
        'image',
    ],
    'product_review_images' => [
        'id',
        'image',
    ],
];

$removed = 0;
foreach ($tables as $table => $columns) {
    $select = $conn->query('SELECT ' . implode(', ', $columns) . ' FROM ' . $table);
    $rows = $select->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $image = (string)($row['image'] ?? '');
        if ($image === '') {
            continue;
        }
        if (fileExistsForUpload($image)) {
            continue;
        }

        $identifier = $row['id'] ?? $row['placement'] ?? 'unknown';
        $whereColumn = array_key_exists('id', $row) ? 'id' : 'placement';
        $delete = $conn->prepare('DELETE FROM ' . $table . ' WHERE ' . $whereColumn . ' = :identifier');
        $delete->execute([':identifier' => $identifier]);
        echo "Removed broken image from $table ($whereColumn=$identifier): $image\n";
        $removed++;
    }
}

echo "Finished: removed $removed broken image records.\n";
