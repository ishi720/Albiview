"use strict"

let showMode = "directory";
let pint_user = "";
let currentPage = 1;
let perPage = 50;
let currentFolder = ""; // 現在選択中のフォルダ

// 画像一覧を読み込む関数
function loadImages() {
    if (showMode == "directory") {
        $.ajax({
            url: './api/fetch_directory_images.php',
            type: 'GET',
            data: {
                page: currentPage,
                per_page: perPage,
                folder: currentFolder
            },
            dataType: 'json',
            contentType: 'application/json',
        }).done(function(response){
            $("#main").html(
                $("#main_tmpl").render(response)
            );
            setTimeout( function(){
                $('#content').isotope();
            }, 500);
        }).fail(function(){
            console.error('画像取得失敗');
        });
    } else {
        $.ajax({
            url: './api/fetch_pinterest_images.php?pint_user='+ pint_user,
            type: 'GET',
            dataType: 'json',
            contentType: 'application/json'
        }).done(function(response){
            $("#main").html(
                $("#pint_tmpl").render(response)
            );
            setTimeout( function(){
                $('#content').isotope();
            }, 500);
        }).fail(function(){
            console.error('画像取得失敗');
        });
    }
}

// フォルダ一覧を読み込む関数
function loadFolders() {
    $.ajax({
        url: './api/fetch_folders.php',
        type: 'GET',
        dataType: 'json'
    }).done(function(response){
        if (response.success) {
            $("#folder-list").html(
                $("#folder_list_tmpl").render(response)
            );
            // フォルダ選択のドロップダウンも更新
            updateFolderSelect(response.folders);
        }
    }).fail(function(){
        console.error('フォルダ一覧取得失敗');
    });
}

// フォルダ選択ドロップダウンを更新
function updateFolderSelect(folders) {
    const $select = $('#target-folder');
    $select.find('option:not(:first)').remove();
    folders.forEach(function(folder) {
        $select.append($('<option>', {
            value: folder.path,
            text: folder.name
        }));
    });
}

// 現在のフォルダ表示を更新
function updateCurrentFolderDisplay() {
    const displayName = currentFolder ? currentFolder : 'すべての画像';
    $('#current-folder-name').text(displayName);
}

$(function() {
    // Lightboxのオプション
    lightbox.option({
        'disableScrolling': true,
        'wrapAround': true
    });

    // 初期画像読み込み
    loadImages();

    // フォルダ管理ボタンのクリックイベント
    $('#folder-manage-btn').on('click', function() {
        $('#folder-list-container').slideToggle();
        if ($('#folder-list-container').is(':visible')) {
            loadFolders();
        }
    });

    // フォルダ作成ボタン
    $('#create-folder-btn').on('click', function() {
        $('#folder-create-modal').fadeIn();
    });

    // モーダルを閉じる
    $('.close, .cancel-btn').on('click', function() {
        $('#folder-create-modal').fadeOut();
        $('#folder-create-form')[0].reset();
        $('#folder-create-status').html('');
    });

    // モーダル外クリックで閉じる
    $(window).on('click', function(e) {
        if ($(e.target).is('#folder-create-modal')) {
            $('#folder-create-modal').fadeOut();
        }
    });

    // フォルダ作成フォームの送信
    $('#folder-create-form').on('submit', function(e) {
        e.preventDefault();

        const folderName = $('#folder-name').val().trim();
        if (!folderName) {
            $('#folder-create-status').html('<p style="color:red;">フォルダ名を入力してください</p>');
            return;
        }

        $('#folder-create-status').html('<p>作成中...</p>');

        $.ajax({
            url: './api/create_folder.php',
            type: 'POST',
            data: JSON.stringify({ folder_name: folderName }),
            contentType: 'application/json',
            dataType: 'json'
        }).done(function(response) {
            if (response.success) {
                $('#folder-create-status').html('<p style="color:green;">' + response.message + '</p>');
                $('#folder-create-form')[0].reset();

                // フォルダ一覧を再読み込み
                setTimeout(function() {
                    loadFolders();
                    $('#folder-create-modal').fadeOut();
                    $('#folder-create-status').html('');
                }, 1500);
            } else {
                $('#folder-create-status').html('<p style="color:red;">エラー: ' + response.message + '</p>');
            }
        }).fail(function(xhr) {
            console.error('フォルダ作成失敗', xhr);
            let errorMsg = 'フォルダの作成に失敗しました';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $('#folder-create-status').html('<p style="color:red;">' + errorMsg + '</p>');
        });
    });

    // フォルダを開くボタン
    $(document).on('click', '.view_folder_btn', function() {
        const folderPath = $(this).data('folder');
        currentFolder = folderPath;
        updateCurrentFolderDisplay();
        loadImages();
        $('#folder-list-container').slideUp();
    });

    // フォルダ削除ボタン
    $(document).on('click', '.delete_folder_btn', function() {
        const btn = $(this);
        const folderPath = btn.data('folder');

        if (!confirm('フォルダ「' + folderPath + '」を削除しますか？\n※フォルダ内の画像も全て削除されます')) {
            return;
        }

        btn.prop('disabled', true).text('削除中...');

        $.ajax({
            url: './api/delete_folder.php',
            type: 'POST',
            data: JSON.stringify({ folder_name: folderPath }),
            contentType: 'application/json',
            dataType: 'json'
        }).done(function(response) {
            if (response.success) {
                // 現在開いているフォルダが削除された場合はリセット
                if (currentFolder === folderPath) {
                    currentFolder = '';
                    updateCurrentFolderDisplay();
                    loadImages();
                }
                // フォルダ一覧を再読み込み
                loadFolders();
            } else {
                alert('削除に失敗しました: ' + response.message);
                btn.prop('disabled', false).text('削除');
            }
        }).fail(function(xhr) {
            console.error('フォルダ削除失敗', xhr);
            alert('削除に失敗しました');
            btn.prop('disabled', false).text('削除');
        });
    });

    // アップロードボタンのクリックイベント
    $('#upload-btn').on('click', function() {
        $('#upload-form').slideToggle();
        if ($('#upload-form').is(':visible')) {
            loadFolders(); // フォルダ選択肢を更新
        }
    });

    // キャンセルボタン
    $('#cancel-btn').on('click', function() {
        $('#upload-form').slideUp();
        $('#image-upload-form')[0].reset();
        $('#upload-status').html('');
    });

    // 削除ボタンのクリックイベント（動的に追加される要素用）
    $(document).on('click', '.delete_btn', function() {
        const btn = $(this);
        const filepath = btn.data('filename');
        const filename = filepath.split('/').pop(); // パスからファイル名を取得

        if (!confirm('「' + filename + '」を削除しますか？')) {
            return;
        }

        // ボタンを無効化
        btn.prop('disabled', true).text('削除中...');

        $.ajax({
            url: './api/delete_directory_image.php',
            type: 'POST',
            data: JSON.stringify({ filename: filename, folder: currentFolder }),
            contentType: 'application/json',
            dataType: 'json'
        }).done(function(response) {
            if (response.success) {
                // 画像コンテナをフェードアウトして削除
                btn.closest('.photo_container').fadeOut(300, function() {
                    $(this).remove();
                    // Isotopeレイアウトを更新
                    $('#content').isotope('layout');
                });
            } else {
                alert('削除に失敗しました: ' + response.message);
                btn.prop('disabled', false).text('削除');
            }
        }).fail(function(xhr) {
            console.error('削除失敗', xhr);
            alert('削除に失敗しました');
            btn.prop('disabled', false).text('削除');
        });
    });

    // 画像アップロードフォームの送信
    $('#image-upload-form').on('submit', function(e) {
        e.preventDefault();

        const files = $('#image-files')[0].files;
        if (files.length === 0) {
            $('#upload-status').html('<p style="color:red;">画像ファイルを選択してください</p>');
            return;
        }

        const formData = new FormData();
        const targetFolder = $('#target-folder').val();

        for (let i = 0; i < files.length; i++) {
            formData.append('images[]', files[i]);
        }
        if (targetFolder) {
            formData.append('folder', targetFolder);
        }

        $('#upload-status').html('<p>アップロード中...</p>');

        $.ajax({
            url: './api/upload_directory_images.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function(response) {
            if (response.success) {
                $('#upload-status').html('<p style="color:green;">アップロード完了: ' + response.uploaded_count + '件</p>');
                $('#image-upload-form')[0].reset();

                // 画像一覧を再読み込み
                setTimeout(function() {
                    loadImages();
                    $('#upload-form').slideUp();
                    $('#upload-status').html('');
                }, 1500);
            } else {
                $('#upload-status').html('<p style="color:red;">エラー: ' + response.message + '</p>');
            }
        }).fail(function(xhr) {
            console.error('アップロード失敗', xhr);
            $('#upload-status').html('<p style="color:red;">アップロードに失敗しました</p>');
        });
    });
});