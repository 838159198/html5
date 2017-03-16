<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0" />
<title>我的成绩</title>
<link type="text/css" href="<?php echo ROOT_URL . "/view/style/html5/weui.css" ?>" rel="stylesheet" />
<link type="text/css" href="<?php echo ROOT_URL . "/view/style/html5/style.css" ?>" rel="stylesheet" />
<script type="text/javascript" src="<?php echo ROOT_URL . "/view/js/jquery.js" ?>"></script>
</head>
<body ontouchstart>
<div class="container">
    <div class="weui-panel weui-panel_access">
        <div class="weui-panel__hd">成绩列表</div>
        <div class="weui-panel__bd" id="scoreList">
            <!-- <div class="weui-media-box weui-media-box_appmsg">
                <div class="weui-media-box__hd border">100分</div>
                <div class="weui-media-box__bd">
                    <h4 class="weui-media-box__title">标题一</h4>
                    <ul class="weui-media-box__info">
                        <li class='weui-media-box__info__meta'>满分：100</li>
                        <li class='weui-media-box__info__meta'>及格：60</li>
                        <li class='weui-media-box__info__meta'>次数：2</li>
                    </ul>
                    <ul class="weui-media-box__info">
                        <li class='weui-media-box__info__meta'>考试时间：100 ~ 200</li>
                        <li class='weui-media-box__info__meta'>答题用时：60</li>
                    </ul>
                </div>
            </div>
            <div class="weui-media-box weui-media-box_appmsg">
                <div class="weui-media-box__hd border">100分</div>
                <div class="weui-media-box__bd">
                    <h4 class="weui-media-box__title">标题一</h4>
                    <ul class="weui-media-box__info">
                        <li class='weui-media-box__info__meta'>满分：100</li>
                        <li class='weui-media-box__info__meta'>及格：60</li>
                        <li class='weui-media-box__info__meta'>次数：2</li>
                    </ul>
                    <ul class="weui-media-box__info">
                        <li class='weui-media-box__info__meta'>考试时间：100 ~ 200</li>
                        <li class='weui-media-box__info__meta'>答题用时：60</li>
                    </ul>
                </div>
            </div>-->
        </div> 
        <div class="weui-panel__ft">
            <a href="javascript:;" class="weui-cell weui-cell_access weui-cell_link">
                <div class="weui-cell__bd">查看更多</div>
                <div class="weui-cell__ft"></div>
            </a>
        </div>
    </div>
</div>
<script type="text/javascript">
var page = 0;
$(window).load(function(){
    getExamScoreList();
    $(".weui-cell").click(function(){
        getExamScoreList();
    });
});

function getExamScoreList(){
    //var page = $("#page").val();
    $.ajax({
        type: "GET",
        async: true,
        url: "<?php echo ROOT_URL . "/html6.php?a=ajaxTrainScoreList"?>",
        data: {"page": page},
        dataType:"json",
        success: function(data){
            if(data.length){
                var htmlLi = "";
                for(var i = 0; i < data.length; i++){
                    htmlLi += "<div class='weui-media-box weui-media-box_appmsg'>";
                    htmlLi += "<div class='weui-media-box__hd border'>"+ data[i].scores +"分</div>";
                    htmlLi += "<div class='weui-media-box__bd'>";
                    htmlLi += "<h4 class='weui-media-box__title'>"+ data[i].name +"</h4>";
                    htmlLi += "<ul class='weui-media-box__info'>";
                    htmlLi += "<li class='weui-media-box__info__meta'>满分："+ data[i].points +"</li>";
                    htmlLi += "<li class='weui-media-box__info__meta'>及格："+ data[i].pass +"</li>";
                    htmlLi += "<li class='weui-media-box__info__meta'>次数："+ data[i].times +"</li></ul>";
                    htmlLi += "<ul class='weui-media-box__info'>";
                    htmlLi += "<li class='weui-media-box__info__meta'>考试时间："+ data[i].startTime +" ~ "+ data[i].endTime +"</li>";
                    htmlLi += "<li class='weui-media-box__info__meta'>答题用时："+ data[i].duration +"</li></ul></div></div>";
                    // htmlLi += "<div class='weui-media-box weui-media-box_text'>";
                    // htmlLi += "<h4 class='weui-media-box__title'>"+ data[i].name;
                    // htmlLi += "<a href='<?php echo ROOT_URL ?>/html5.php?a=examInfo&eid="+ data[i].id +"' class='weui-btn weui-btn_primary exam1'>参加考试</a></h4>";
                    // htmlLi += "<ul class='weui-media-box__info'>";
                    // htmlLi += "<li class='weui-media-box__info__meta'>满分："+ data[i].total +"</li>";
                    // htmlLi += "<li class='weui-media-box__info__meta'>及格："+ data[i].pass +"</li>";
                    // htmlLi += "<li class='weui-media-box__info__meta'>限时："+ data[i].duration +"分钟</li></ul>";
                    // htmlLi += "<ul class='weui-media-box__info'><li class='weui-media-box__info__meta'>考试时间："+ data[i].startTime +" ~ "+ data[i].endTime +"</li></ul></div>";
                }
                $("#scoreList").append(htmlLi);
                page++;
                //$("#page").val(parseInt(page) + 1);
                if(data.length < 10){
                    $(".weui-cell").hide();
                }else{
                   $(".weui-cell").show(); 
                }
            }
        }
    });
}
</script>
</body>
</html>