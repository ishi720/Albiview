<?php
// ディレクトリのパスを指定する
$dir = "../img/" ;
// 画像のパスを入れる配列
$img_array = array();
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

//画像チェック
function img_check($extension) {
    $validExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'bmp', 'tiff', 'tif', 'svg', 'ico', 'heic', 'avif'
    ];
    return in_array(strtolower($extension), $validExtensions, true);
}