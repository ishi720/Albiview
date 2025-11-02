<?php
header('Content-Type: application/json; charset=utf-8');

// 画像ディレクトリのパス
$image_dir = '../uploads/';

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
if (isset($_POST['filename'])) {
    $filename = $_POST['filename'];
} elseif (isset($_GET['filename'])) {
    $filename = $_GET['filename'];
} elseif (isset($input['filename'])) {
    $filename = $input['filename'];
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
$real_image_dir = realpath($image_dir);
$real_filepath = realpath($filepath);

if ($real_filepath === false || strpos($real_filepath, $real_image_dir) !== 0) {
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
