<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0"/>
<title>培训系统</title>
<link type="text/css" href="<?php echo ROOT_URL . "/view/style/html5/weui.css" ?>" rel="stylesheet" />
<link type="text/css" href="<?php echo ROOT_URL . "/view/style/html5/style.css" ?>" rel="stylesheet" />

<script type="text/javascript" src="<?php echo ROOT_URL . "/view/js/jquery.js" ?>"></script>
</head>

<body ontouchstart>
<div class="container">
    <div class="weui-panel">
    	<div class="weui-panel__hd">全部课程
    		<a href="<?php echo ROOT_URL .'/html6.php?a=contentList' ?>" class="chengji">我的课程</a>
    	</div>
    	<div class="weui-panel__bd" id="examList"></div>
    	<div class="weui-panel__ft" id="more">
    		<a href="javascript:;" class="weui-cell weui-cell_access weui-cell_link">
    			<div class="weui-cell__bd" id="much">查看更多</div>
    			<span class="weui-cell__ft"></span>
    		</a>
    	</div>
    </div>
<!-- 成功 -->
    <div id="toast1" style="display:none;">
        <div class="weui-mask_transparent"></div>
        <div class="weui-toast" style="background:none;">
            <i class="weui-icon-success weui-icon_msg"></i>
            <p class="weui-toast__content" style="color:grey;">报名成功</p>
        </div>
    </div>
</div>
<!-- 成功结束 -->
<!-- 成功 -->
    <div id="toast5" style="display:none;">
        <div class="weui-mask_transparent"></div>
        <div class="weui-toast" style="background:none;">
            <i class="weui-icon-success weui-icon_msg"></i>
            <p class="weui-toast__content" style="color:grey;">加入成功</p>
        </div>
    </div>
</div>
<!-- 成功结束 -->
<!--BEGIN dialog1-->
        <div class="js_dialog" id="iosDialog1" style="display: none;z-index: 10;">
            <div class="weui-mask"></div>
            <div class="weui-dialog">
                <div class="weui-dialog__hd"><strong class="weui-dialog__title">亲,确定报名嘛</strong></div>
                <div class="weui-dialog__bd">点击确定报名成功后,该课程会加入到我的课程</div>
                <div class="weui-dialog__ft">
                    <a id="cancel1" href="javascript:;" class="weui-dialog__btn weui-dialog__btn_default">取消</a>
                    <a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_primary" id="ding1">确定</a>
                </div>
            </div>
        </div>
<!--END dialog1-->
<!--BEGIN dialog2-->
        <div class="js_dialog" id="iosDialog2" style="display: none;z-index: 10;">
            <div class="weui-mask"></div>
            <div class="weui-dialog">
                <div class="weui-dialog__hd"><strong class="weui-dialog__title">亲,确定参加该课程学习嘛</strong></div>
                <div class="weui-dialog__bd">点击确定后该课程会加入到我的课程</div>
                <div class="weui-dialog__ft">
                    <a id="cancel2" href="javascript:;" class="weui-dialog__btn weui-dialog__btn_default">取消</a>
                    <a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_primary" id="ding2">确定</a>
                </div>
            </div>
        </div>
<!--END dialog2-->
<!-- 等待 -->
 <div id="toast2" style="display:none ;">
        <div class="weui-mask_transparent"></div>
        <div class="weui-toast" style="background:none;">
            <i class="weui-icon-waiting weui-icon_msg"></i>
            <p class="weui-toast__content" style="color:grey">等待审核</p>
        </div>
</div>
<!-- 等待结束 -->
<!-- 警告 -->  
<div id="toast3" style="display:none ;">
        <div class="weui-mask_transparent"></div>
        <div class="weui-toast" style="background:none;">
             <i class="weui-icon-warn weui-icon_msg-primary"></i>
            <p class="weui-toast__content" style="color:grey">人数已满</p>
        </div>
</div>
<!-- 警告结束 -->
<!-- 错误 -->
<div id="toast4" style="display:none ;">
        <div class="weui-mask_transparent"></div>
        <div class="weui-toast" style="background:none;">
            <i class="weui-icon-warn weui-icon_msg"></i>
            <p class="weui-toast__content" style="color:grey">请联系管理员</p>
        </div>
</div>
<!-- 错误结束 -->
<!-- 错误 -->
<div id="toast6" style="display:none ;">
        <div class="weui-mask_transparent"></div>
        <div class="weui-toast" style="background:none;">
            <i class="weui-icon-warn weui-icon_msg"></i>
            <p class="weui-toast__content" style="color:grey">加入失败</p>
        </div>
</div>
<!-- 错误结束 -->
</body>
</html>
<script type="text/javascript">

$(window).load(function(){
    getExamList();


});
$("#more").click(function(){
        $('#list').siblings('div:gt(8)').show();
        $('#more').hide();
    });
//全部课程列表
function getExamList(){
    //var page = $("#page").val();
    $.ajax({
        type: "GET",
        async: true,
        url: "<?php echo ROOT_URL . "/html6.php?a=ajaxAllTrainList"?>",
        dataType:"json",
        success: function(data){
            console.log(data.length);
            if(data.length){
                var htmlLi = "";
                for(var i = 0; i < data.length; i++){
                    htmlLi += "<div id='list' class='weui-media-box__bd listbox'>";
                    htmlLi += "<a href='javascript:void;' onclick='enrolll("+data[i].cid+","+data[i].dialog+")' class='weui-media-box weui-media-box_appmsg'>";
                    htmlLi +="<div class='weui-media-box__hd'>";
                     htmlLi+= ""+data[i]._img+"";
                    htmlLi+="</div>";
                    htmlLi += "<div class='weui-media-box__bd'>";
                    htmlLi += "<h4 class='weui-media-box__title'>"+ data[i].c_name +"</h4>";
                    htmlLi += "<ul class='weui-media-box__info'>";
                    htmlLi += "<li class='weui-media-box__info__meta'>类型："+ data[i].c_elective +"</li>";
                    htmlLi += "<li class='weui-media-box__info__meta'>分类："+ data[i].classfiy +"</li>";
                    htmlLi += "<li class='weui-media-box__info__meta'>讲师："+ data[i].e_name +"</li>";
                    htmlLi += "<li class='weui-media-box__info__meta'>正在学习："+ data[i].user_learning_num +"人</li></ul>";
                    htmlLi += "<ul class='weui-media-box__info'><li class='weui-media-box__info__meta'>学习时间："+ data[i].starttime +" ~ "+ data[i].endtime +"</li></ul></div></a><div class='weui-btn weui-btn_primary'>"+data[i]._enroll+"</div></div>";
                }
                $("#examList").append(htmlLi);
               
               
                if(data.length > 10){
                    $('#list').siblings('div:gt(8)').hide();
                       
                    $("#more").show();
                }else{
                    $("#more").show();
                   
                }



            }
        }
    });
}
//弹窗出现
function enrolll(a,b){
    if(b==1){
        $('#iosDialog2').fadeIn(200);
    }
    else{
        $('#iosDialog1').fadeIn(200); 
    } 
    //确定按钮点击事件
    $('#ding1').click(
    function(){ 

       $('#iosDialog1').fadeOut(200,function(){
        
        $.ajax({
        type: "POST",
        async: true,
        url: "<?php echo ROOT_URL . "/html6.php?a=enroll"?>",
        data: {"cid": a},
        dataType: "json",
        success: function(data){
            if(data == 'common'){
                $('#toast2').fadeIn(200);
                setTimeout(function(){
                $('#toast2').fadeOut(200);
                location.reload();
                },2000);
                
            }
            else if(data="ok"){
                $('#toast1').fadeIn(200);
                setTimeout(function(){
                $('#toast1').fadeOut(200);
                location.reload();
                },2000);    
            }
            else if(data="over"){
                $('#toast3').fadeIn(200);
                setTimeout(function(){
                $('#toast3').fadeOut(200);
                location.reload();
                },2000);   
            }
            else{
                $('#toast4').fadeIn(200);
                setTimeout(function(){
                $('#toast4').fadeOut(200);
                location.reload();
                },2000);  
            }
        }
        });
        
    });
       

    })
    $('#ding2').click(
    function(){ 

       $('#iosDialog2').fadeOut(200);

       $.ajax({
        type:'POST',
        async:true,
        url :'?a=join',
        data:{'cid':a},
        dataType:'json',
        success:function(data){
            if(data=='success'){
                $('#toast5').fadeIn(200);
                setTimeout(function(){
                $('#toast5').fadeOut(200);
                location.reload();
                },2000);  
            }else{
                $('#toast6').fadeIn(200);
                setTimeout(function(){
                $('#toast6').fadeOut(200);
                location.reload();
                },2000);  
            }
        }
       })




     

    })


//点击取消，弹窗消失
$('#cancel1').click(
    function(){ 

       $('#iosDialog1').fadeOut(200);
      

    })
$('#cancel2').click(
    function(){ 
      
       $('#iosDialog2').fadeOut(200);

    })

   
}
//提交
function submitExam(a){
    
    if(a==1){
        $('#iosDialog1').fadeOut(200);
    }else{
        $('#iosDialog2').fadeOut(200);
    }
    $('#toast').fadeIn(200,function(){
        setTimeout(function (){$('#toast').fadeOut(200);},2000);
    });

}
</script>