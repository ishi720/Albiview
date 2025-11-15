<?php
header('Content-Type: application/json; charset=utf-8');

// アップロードディレクトリのパス
$base_dir = '../uploads/';

// レスポンス用の配列
$response = [
    'success' => false,
    'message' => ''
];

// リクエストメソッドチェック
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = '無効なリクエストメソッドです';
    http_response_code(405);
    echo json_encode($response);
    exit;
}

// JSONデータを取得
$input = json_decode(file_get_contents('php://input'), true);

// フォルダ名の取得
$folder_name = null;
if (isset($_POST['folder_name'])) {
    $folder_name = $_POST['folder_name'];
} elseif (isset($input['folder_name'])) {
    $folder_name = $input['folder_name'];
}

// フォルダ名が指定されているかチェック
if (empty($folder_name)) {
    $response['message'] = 'フォルダ名が指定されていません';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

// フォルダ名のバリデーション
$folder_name = trim($folder_name);

// 不正な文字をチェック
if (preg_match('/[\/\\\\:*?"<>|]/', $folder_name)) {
    $response['message'] = 'フォルダ名に使用できない文字が含まれています';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

// ディレクトリトラバーサル攻撃を防ぐ
if ($folder_name === '.' || $folder_name === '..' || strpos($folder_name, '..') !== false) {
    $response['message'] = '不正なフォルダ名です';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

// フォルダパスの構築
$folder_path = $base_dir . $folder_name;

// 既に存在するかチェック
if (file_exists($folder_path)) {
    $response['message'] = '同名のフォルダまたはファイルが既に存在します';
    http_response_code(409);
    echo json_encode($response);
    exit;
}

// フォルダを作成
if (mkdir($folder_path, 0755)) {
    // .gitkeepファイルを作成（空フォルダをGitで管理するため）
    file_put_contents($folder_path . '/.gitkeep', '');
    
    $response['success'] = true;
    $response['message'] = 'フォルダを作成しました: ' . $folder_name;
    $response['folder_name'] = $folder_name;
    http_response_code(201);
} else {
    $response['message'] = 'フォルダの作成に失敗しました';
    http_response_code(500);
}

echo json_encode($response);
