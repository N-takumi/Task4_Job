function mypage()
{

  function init(){

    //ユーザー情報の取得
    $.ajax({
        url  : "http://localhost/restapi/getMypage",
        type : "GET",
        cache       : false,
        contentType : false,
        processData : false,
        dataType    : "html"
    })
    .done(function(response){
        var data = response;
        console.log(data);
        $("#userinfo").html(data);
    })
    .fail(function(jqXHR, textStatus, errorThrown){
        alert("取得エラー:"+errorThrown);
    });

  }
  init();
}


function searchDate(){
  var date_info = $('#date').val();
  console.log(date_info);
    //検索結果の取得
    $.ajax({
        url  : "http://localhost/restapi/getMypage/search/"+(date_info),
        type : "GET",
        cache       : false,
        contentType : false,
        processData : false,
        dataType    : "html"
    })
     .done(function(response){
      var data = response;
      console.log(data);
      $("#searchResult").html(data);
    })
    .fail(function(jqXHR, textStatus, errorThrown){
        alert("取得エラー:"+errorThrown);
    });
}


$(function(){
  mypage();
});
