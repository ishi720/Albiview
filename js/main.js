"use strict"

$(function() {
    $.ajax({
        url: './api/fetch_directory_images.php',
        type: 'GET',
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
});