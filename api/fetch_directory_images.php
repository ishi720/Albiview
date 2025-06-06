<?php

declare(strict_types=1);

// ディレクトリのパスを指定する
$dir = "../img/" ;
// 画像のパスを入れる配列
$img_array = [];

// 画像ファイルのリストを作成
if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if (is_file($dir . $file)) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            //拡張子のチェック
            if (img_check($extension)) {
                $img_array[] = "./img/" . $file;
            }
        }
    }
}
//json形式で出力
header('Content-type: application/json');
echo json_encode([
    'response_data' => $img_array
]);

/**
 * 指定された拡張子が画像ファイル形式かどうかを判定
 *
 * @param string $extension ファイルの拡張子（例: "jpg", "png"）
 * @return bool 画像ファイル形式の場合 true、それ以外は false
 */
function img_check(string $extension): bool {
    $validExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'bmp', 'tiff', 'tif', 'svg', 'ico', 'heic', 'avif'
    ];
    return in_array(strtolower($extension), $validExtensions, true);
}