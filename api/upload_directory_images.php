<?php
header('Content-Type: application/json; charset=utf-8');

// アップロード先ディレクトリ
$upload_dir = '../uploads/';

// ディレクトリが存在しない場合は作成
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// レスポンス用の配列
$response = [
    'success' => false,
    'message' => '',
    'uploaded_count' => 0,
    'errors' => []
];

// ファイルがアップロードされているか確認
if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
    $response['message'] = '画像ファイルが選択されていません';
    echo json_encode($response);
    exit;
}

// 許可する画像形式
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

// 各ファイルを処理
$uploaded_count = 0;
$files = $_FILES['images'];
$file_count = count($files['name']);

for ($i = 0; $i < $file_count; $i++) {
    // エラーチェック
    if ($files['error'][$i] !== UPLOAD_ERR_OK) {
        $response['errors'][] = $files['name'][$i] . ': アップロードエラー';
        continue;
    }

    // ファイル情報取得
    $original_name = basename($files['name'][$i]);
    $tmp_name = $files['tmp_name'][$i];
    $file_size = $files['size'][$i];
    $mime_type = mime_content_type($tmp_name);

    // 拡張子チェック
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        $response['errors'][] = $original_name . ': 許可されていないファイル形式';
        continue;
    }

    // MIMEタイプチェック
    if (!in_array($mime_type, $allowed_mime_types)) {
        $response['errors'][] = $original_name . ': 無効な画像ファイル';
        continue;
    }

    // ファイルサイズチェック（10MB以下）
    if ($file_size > 10 * 1024 * 1024) {
        $response['errors'][] = $original_name . ': ファイルサイズが大きすぎます（10MB以下）';
        continue;
    }

    // ユニークなファイル名を生成
    $new_filename = date('YmdHis') . '_' . uniqid() . '.' . $extension;
    $destination = $upload_dir . $new_filename;

    // ファイルを移動
    if (move_uploaded_file($tmp_name, $destination)) {
        $uploaded_count++;
    } else {
        $response['errors'][] = $original_name . ': 保存に失敗しました';
    }
}

// レスポンス作成
if ($uploaded_count > 0) {
    $response['success'] = true;
    $response['uploaded_count'] = $uploaded_count;
    $response['message'] = $uploaded_count . '件の画像をアップロードしました';
} else {
    $response['message'] = '画像のアップロードに失敗しました';
}

echo json_encode($response);
