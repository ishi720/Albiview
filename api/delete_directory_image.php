<?php
header('Content-Type: application/json; charset=utf-8');

// ベース画像ディレクトリのパス
$base_image_dir = '../uploads/';

// レスポンス用の配列
$response = [
    'success' => false,
    'message' => ''
];

// リクエストメソッドチェック
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    $response['message'] = '無効なリクエストメソッドです';
    http_response_code(405);
    echo json_encode($response);
    exit;
}

// JSONデータを取得
$input = json_decode(file_get_contents('php://input'), true);

// ファイル名の取得（URLパラメータまたはJSONボディから）
$filename = null;
$folder = '';

if (isset($_POST['filename'])) {
    $filename = $_POST['filename'];
    $folder = isset($_POST['folder']) ? $_POST['folder'] : '';
} elseif (isset($_GET['filename'])) {
    $filename = $_GET['filename'];
    $folder = isset($_GET['folder']) ? $_GET['folder'] : '';
} elseif (isset($input['filename'])) {
    $filename = $input['filename'];
    $folder = isset($input['folder']) ? $input['folder'] : '';
}

// ファイル名が指定されているかチェック
if (empty($filename)) {
    $response['message'] = 'ファイル名が指定されていません';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

// セキュリティ: ファイル名からディレクトリトラバーサル攻撃を防ぐ
$filename = basename($filename);

// フォルダが指定されている場合
if ($folder) {
    $folder = basename($folder);
    $image_dir = $base_image_dir . $folder . '/';
} else {
    $image_dir = $base_image_dir;
}

// ファイルパスの構築
$filepath = $image_dir . $filename;

// ファイルの存在確認
if (!file_exists($filepath)) {
    $response['message'] = '指定されたファイルが見つかりません';
    http_response_code(404);
    echo json_encode($response);
    exit;
}

// ファイルが画像ディレクトリ内にあることを確認（セキュリティチェック）
$real_base_dir = realpath($base_image_dir);
$real_filepath = realpath($filepath);

if ($real_filepath === false || strpos($real_filepath, $real_base_dir) !== 0) {
    $response['message'] = '不正なファイルパスです';
    http_response_code(403);
    echo json_encode($response);
    exit;
}

// ファイルが通常のファイルであることを確認
if (!is_file($filepath)) {
    $response['message'] = '指定されたパスはファイルではありません';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

// ファイルを削除
if (unlink($filepath)) {
    $response['success'] = true;
    $response['message'] = 'ファイルを削除しました: ' . $filename;
    http_response_code(200);
} else {
    $response['message'] = 'ファイルの削除に失敗しました';
    http_response_code(500);
}

echo json_encode($response);