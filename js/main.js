"use strict"

let showMode = "directory";
let pint_user = "";
let currentPage = 1;
let perPage = 50;

// 画像一覧を読み込む関数
function loadImages() {
    if (showMode == "directory") {
        $.ajax({
            url: './api/fetch_directory_images.php',
            type: 'GET',
            data: {
                page: currentPage,
                per_page: perPage
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

$(function() {
    // Lightboxのオプション
    lightbox.option({
        'disableScrolling': true,
        'wrapAround': true
    });

    // 初期画像読み込み
    loadImages();

    // アップロードボタンのクリックイベント
    $('#upload-btn').on('click', function() {
        $('#upload-form').slideToggle();
    });

    // キャンセルボタン
    $('#cancel-btn').on('click', function() {
        $('#upload-form').slideUp();
        $('#image-upload-form')[0].reset();
        $('#upload-status').html('');
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
        for (let i = 0; i < files.length; i++) {
            formData.append('images[]', files[i]);
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