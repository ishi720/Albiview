<?php

declare(strict_types=1);

// ディレクトリのパスを指定する
$dir = "../img/" ;
// 画像のパスを入れる配列
$img_array = [];
if (is_dir($dir) && $handle = opendir($dir)) {
    while( ($file = readdir($handle)) !== false ) {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        //画像ファイルかチェックする
        if (img_check($extension)) {
            $img_array[] = "./img/" . $file;
        }
    }
    closedir($handle);
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