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
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
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
} elseif (isset($_GET['folder_name'])) {
    $folder_name = $_GET['folder_name'];
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

// セキュリティ: ディレクトリトラバーサル攻撃を防ぐ
$folder_name = basename($folder_name);

// フォルダパスの構築
$folder_path = $base_dir . $folder_name;

// フォルダの存在確認
if (!file_exists($folder_path)) {
    $response['message'] = '指定されたフォルダが見つかりません';
    http_response_code(404);
    echo json_encode($response);
    exit;
}

// ディレクトリであることを確認
if (!is_dir($folder_path)) {
    $response['message'] = '指定されたパスはフォルダではありません';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

// フォルダがuploadsディレクトリ内にあることを確認（セキュリティチェック）
$real_base_dir = realpath($base_dir);
$real_folder_path = realpath($folder_path);

if ($real_folder_path === false || strpos($real_folder_path, $real_base_dir) !== 0) {
    $response['message'] = '不正なフォルダパスです';
    http_response_code(403);
    echo json_encode($response);
    exit;
}

// フォルダとその中身を削除
if (deleteDirectory($folder_path)) {
    $response['success'] = true;
    $response['message'] = 'フォルダを削除しました: ' . $folder_name;
    http_response_code(200);
} else {
    $response['message'] = 'フォルダの削除に失敗しました';
    http_response_code(500);
}

echo json_encode($response);

/**
 * ディレクトリとその中身を再帰的に削除
 *
 * @param string $dir 削除するディレクトリのパス
 * @return bool 成功した場合 true、失敗した場合 false
 */
function deleteDirectory(string $dir): bool
{
    if (!is_dir($dir)) {
        return false;
    }

    $items = scandir($dir);
    if ($items === false) {
        return false;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . '/' . $item;

        if (is_dir($path)) {
            // サブディレクトリを再帰的に削除
            if (!deleteDirectory($path)) {
                return false;
            }
        } else {
            // ファイルを削除
            if (!unlink($path)) {
                return false;
            }
        }
    }

    // ディレクトリ自体を削除
    return rmdir($dir);
}
