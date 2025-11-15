<?php

declare(strict_types=1);

// ベースディレクトリのパス
$base_dir = "../uploads/";

// フォルダパラメータ取得
$folder = isset($_GET['folder']) ? $_GET['folder'] : '';

// セキュリティ: ディレクトリトラバーサル防止
if ($folder) {
    $folder = basename($folder);
    $dir = $base_dir . $folder . '/';
} else {
    $dir = $base_dir;
}

// ページ関連のパラメータ取得（デフォルト: page=1, per_page=20）
$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$perPage = max(1, isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20);

$allImages = getImageList($dir, $folder);
$total = count($allImages);
$totalPages = (int)ceil($total / $perPage);

// ページネーション処理
$offset = ($page - 1) * $perPage;
$pagedImages = array_slice($allImages, $offset, $perPage);

// json形式で出力
header('Content-Type: application/json');
echo json_encode([
    'meta' => [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'current_folder' => $folder
    ],
    'response_data' => $pagedImages
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

/**
 * 指定されたディレクトリから画像ファイル一覧を取得
 *
 * @param string $dir ディレクトリパス
 * @param string $folder フォルダ名（空の場合はルート）
 * @return array 画像ファイルの相対パス配列
 */
function getImageList(string $dir, string $folder): array
{
    if (!is_dir($dir)) {
        return [];
    }

    $files = scandir($dir);
    if ($files === false) {
        return [];
    }

    $images = [];
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        if (is_file($dir . $file)) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if (isImageExtension($extension)) {
                if ($folder) {
                    $images[] = "./uploads/" . $folder . "/" . $file;
                } else {
                    $images[] = "./uploads/" . $file;
                }
            }
        }
    }

    return $images;
}

/**
 * 指定された拡張子が画像ファイル形式かどうかを判定
 *
 * @param string $extension ファイルの拡張子（例: "jpg", "png"）
 * @return bool 画像ファイル形式の場合 true、それ以外は false
 */
function isImageExtension(string $extension): bool
{
    $validExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'bmp', 'tiff', 'tif', 'svg', 'ico', 'heic', 'avif'
    ];
    return in_array(strtolower($extension), $validExtensions, true);
}