"use strict"

let showMode = "directory";
let pint_user = "";

$(function() {
    if (showMode == "directory") {
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
});
