<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// アップロードディレクトリのパス
$base_dir = "../uploads/";

$folders = getFolderList($base_dir);

// JSON形式で出力
echo json_encode([
    'success' => true,
    'total' => count($folders),
    'folders' => $folders
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

/**
 * 指定されたディレクトリからフォルダ一覧を取得
 *
 * @param string $dir ディレクトリパス
 * @return array フォルダ名の配列
 */
function getFolderList(string $dir): array
{
    if (!is_dir($dir)) {
        return [];
    }

    $items = scandir($dir);
    if ($items === false) {
        return [];
    }

    $folders = [];
    foreach ($items as $item) {
        // '.'と'..'をスキップ
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . $item;
        
        // ディレクトリのみを抽出
        if (is_dir($path)) {
            $image_count = countImagesInFolder($path);
            $folders[] = [
                'name' => $item,
                'path' => $item,
                'image_count' => $image_count,
                'created_at' => date('Y-m-d H:i:s', filectime($path))
            ];
        }
    }

    // 名前順でソート
    usort($folders, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    return $folders;
}

/**
 * フォルダ内の画像ファイル数をカウント
 *
 * @param string $folder_path フォルダパス
 * @return int 画像ファイル数
 */
function countImagesInFolder(string $folder_path): int
{
    $files = scandir($folder_path);
    if ($files === false) {
        return 0;
    }

    $count = 0;
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        if (is_file($folder_path . '/' . $file)) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if (isImageExtension($extension)) {
                $count++;
            }
        }
    }

    return $count;
}

/**
 * 指定された拡張子が画像ファイル形式かどうかを判定
 *
 * @param string $extension ファイルの拡張子
 * @return bool 画像ファイル形式の場合 true
 */
function isImageExtension(string $extension): bool
{
    $validExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'bmp', 'tiff', 'tif', 'svg', 'ico', 'heic', 'avif'
    ];
    return in_array(strtolower($extension), $validExtensions, true);
}
