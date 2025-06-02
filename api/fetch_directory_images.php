<?php
// ディレクトリのパスを指定する
$dir = "../img/" ;
// 画像のパスを入れる配列
$img_array = array();
if( is_dir( $dir ) && $handle = opendir( $dir ) ) {
    $path = "";
    while( ($file = readdir($handle)) !== false ) {
        $path = "./img/" . $file;
        $path_info = pathinfo( $path );

        //画像ファイルかチェックする
        if (img_check($path_info['extension'])) {
            $img_array[] = $path;
        }
    }
}

//json形式で出力
header('Content-type: application/json');
$response = array();
$response['response_data'] = $img_array;
echo json_encode($response);

//画像チェック
function img_check($extension){
    $validExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'bmp', 'tiff', 'tif', 'svg', 'ico', 'heic', 'avif'
    ];
    return in_array(strtolower($extension), $validExtensions, true);
}