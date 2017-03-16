<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0" />
<title>课程详情</title>
<!-- 预加载整个页面 它会在后台静悄悄的加载指定的文档，并把它们存储在缓存里-->
<link rel="prefetch" href="<?php echo ROOT_URL . '/data/courseware'.$this->coursewareData["w_video"] ?>" />
<link type="text/css" href="<?php echo ROOT_URL . "/view/style/html5/weui.css" ?>" rel="stylesheet" />
<link type="text/css" href="<?php echo ROOT_URL . "/view/style/html5/style.css" ?>" rel="stylesheet" />
<script type="text/javascript" src="<?php echo ROOT_URL . "/view/js/jquery.js" ?>"></script>
<script src="<?php echo ROOT_URL."/view/js/html6/hlm.min.js"?>"></script>
<style>
#goods:after{
	content: " ";
    position: absolute;
  
    bottom: 0;
    right: 0;
    height: 1px;
    border-bottom: 0px solid #E5E5E5;
    color: #E5E5E5;
   
    transform-origin: 0 100%;
   
    transform: scaleY(0.5);
    left: 15px;

}

</style>
</head>
<body ontouchstart onunload="saveProgress()">
<div class="container">
    <div class="weui-panel">
    	<div class="weui-panel__hd"><?php echo !empty($this->courseName)?$this->courseName:'该课程没有命名' ?>
    		<a href="<?php echo ROOT_URL .'/html5.php?a=examScoreList' ?>" class="chengji"></a>
    	</div>
    	<div id="goods" class="weui-panel__hd" style="overflow:hidden;">
    	<!-- 课程简介 -->
    	简介：<?php echo !empty($this->courseDetail['c_des'])?$this->courseDetail['c_des']:'(无)'; ?>
    	</div>
		<div class="weui-panel__bd" style="width:100%;height:300px;overflow:hidden;">
		<video  width="100%" height="100%" controls="controls"   playsinline x5-video-player-type="h5"  x5-video-player-fullscreen="true" id="video"  preload="load" >
		</video>
		<img width="100%" height="100%" style="display:none" class="show" src="<?php echo ROOT_URL.'/view/images/timg.gif'?>"> 
		<input name="courseId" type="hidden" value="<?php echo $this->coursewareData['wid']; ?>"><!-- 课件id-->
		</div>
		<div class="weui-panel__hd">
			<a href="javascript:void;" id="train_list" style="color:#999999;">课件目录</a>
			<a href="javascript:void;" id="exam_list" class="chengji">考试目录</a>
    		
    	</div>

    	<!-- 课件目录 -->
		<div class="weui-panel__bd" id="trainList" style="padding-left:;display:block">
		<?php  
		foreach($this->coursewareList as &$v) {
			//扣除积分提示处理
                                if($v['deduction_credit'] > $v['credit']) {
                                    $temp[2] = ' style="color:red;"';
                                    $temp[3] = '_';
                                } else {
                                    $temp[2] = '';
                                    $temp[3] = $v['deduction_credit'];
                                }
                                if($this->hasPassCourse){
                                  $pass = 1;
                                }else{
                                  $pass = 0;
                                }
                                if($v['w_type'] == 'offline'){
                                  $tempAllowLearn = false;
                                }
                                else{
                                  $tempAllowLearn = !!$v['seenAble'];
                                }





		 ?>
				<a  href="<?php echo ROOT_URL."/html6.php?a=contentDetail&cid={$v['cid']}{$temp[0]}&wid={$v['wid']}&pass={$pass}"?>" class="weui-media-box weui-media-box_appmsg">
    		   	<div class="weui-media-box__bd">
                     
                    <ul class='weui-media-box__info'>
                    <li class='weui-media-box__info__meta'>课件名称：<?php echo $v['w_name']?></li>
                    <li class='weui-media-box__info__meta'>类型：<?php echo $v['desc_cn'] ?></li>
                    <li class='weui-media-box__info__meta'>时长：<?php echo $v['w_length'] ?></li>
                    <li class='weui-media-box__info__meta'>学分：<?php echo $v['deduction_point'] ?></li>
                   <li class='weui-media-box__info__meta'>费用：<?php echo $v['deduction_credit']?>元</li></ul>
                    </div>
                    
                </a>
               <?php }
                     ?>





    	</div>
<!-- 考试 -->
		<div class="weui-panel__bd" id="examList" style="padding-left:3%;display:none">
		 <?php 
         $temp = $this->examResult;
         if(is_string($temp)){?>
        <div class="weui-media-box__bd" id="exam" style="padding-left:1.5%;color:#999999;">
        <?php echo $temp;?>
        </div>
        <?php }else{  
        
         foreach ($temp as $key => $v) {?>
				<a href="<?php echo ROOT_URL."/html6.php?a=trainExam&courseId=".$v['cid']."&exam_id=".$v['exam_id']?>" class="weui-media-box weui-media-box_appmsg" >
    		   		<div class="weui-media-box__bd">
                    <ul class='weui-media-box__info'>
                    <li class='weui-media-box__info__meta'>考试名称：<?php echo $v['exam_name']?></li>
                    <li class='weui-media-box__info__meta'>分类：<?php echo $v['desc_cn'] ?></li>
                    <li class='weui-media-box__info__meta'>限时：<?php echo $v['exam_total_tm']==0?'不限时':$v['exam_total_tm'].'分钟' ?></li></ul><ul class='weui-media-box__info'>
                    <li class='weui-media-box__info__meta'>总分：<?php echo $v['points'] ?></li>
                   <li class='weui-media-box__info__meta'>及格：<?php echo $v['pass']?></li></ul>
                   
                    </div>
                    <?php echo $v['passed']==1?'<span style="color: #00FF00">已通过</span>':''?>
                </a>
                <?php 
            	} 
            	}    
	            ?>  






    	</div>






</div>
	</body>
	<script>
	$('#exam_list').click(function(){
		$('#examList').attr('style','display:block');

		$('#trainList').attr('style','display:none');
	})

	$('#train_list').click(function(){
		$('#examList').attr('style','display:none');
		$('#trainList').attr('style','display:block');
	})

	</script>
	<script>
var myVideo = document.getElementById('video');
var w_length=<?php echo empty($this->coursewareData['w_length'])?0:$this->coursewareData['w_length'] ?>;//获取总的时长
if(<?php echo !empty($this->coursewareData['uc_length'])?$this->coursewareData['uc_length']:0 ?> >0 && <?php echo !empty($this->coursewareData['uc_length'])?$this->coursewareData['uc_length']:0; ?> < <?php echo empty($this->coursewareData['w_length'])?0:$this->coursewareData['w_length'] ?> ) {//获取时长，从该时长播放
    myVideo.currentTime=<?php echo !empty($this->coursewareData['uc_length'])?$this->coursewareData['uc_length']:0; ?>*60;//秒
   
}else{
    myVideo.currentTime=0; 
}
myVideo.play();


if(Hls.isSupported()) {//m3ub文件播放
    var video = document.getElementById('video');
    var hls = new Hls();
    hls.loadSource('<?php echo ROOT_URL . '/data/courseware'.$this->coursewareData["w_video"] ?>');
    hls.attachMedia(video);
    hls.on(Hls.Events.MANIFEST_PARSED,function() {
      video.play();
    });
}


// video.addEventListener('timeupdate', function (e) {
// console.log(video.currentTime) // 当前播放的进度（保留）
// })
// 播放结束时保存进度
video.addEventListener('ended', function (e) {
saveProgress();
clearInterval(bb);//播放完清除setinterval

})
//每个一秒钟监测一次播放状态，4说明可以顺利播放视频，缓存是出现缓存动画。
var bb=setInterval(function(){

// console.log(video.readyState);
if(video.readyState==4){
	$('video').attr({width:"100%",height:"100%"});
	$('.show').attr('style','display:none');
	
}
else{

	$('video').attr({width:"1px",height:"1px"});
	$('video').removeAttr('controls');
	$('.show').attr('style','display:block;height:100%;width:100%');

}
}
,1000)

//点击video切换进度条的显隐
$('video').click(function(){
	var con=$('video').attr('controls');
	// console.log(con);
	if(con=='controls'){
		$('video').removeAttr('controls');
	}
	else{
		$('video').attr({controls:"controls"});
	}

})

</script>
<script type="text/javascript">
//保存学习进度
function saveProgress(){
    var pdf=<?php echo !empty($this->coursewareData['now_length'])?$this->coursewareData['now_length']:0 ?>;
	var courseId=$('input[name=courseId]').val();//获取课件id
	var myVideo = document.getElementById('video');
	//获取视频总长度 
    var duration = myVideo.duration;
    var duration_fen=(duration/60).toFixed(2);
       // alert(duration_fen);
      //得到当前的进度
    var currentTime = myVideo.currentTime; 
    var currentTime_fen=(currentTime/60).toFixed(2);
      // alert(currentTime);
    $.ajax({
    		type    : 'POST',
            url     : '<?php echo ROOT_URL;?>/courseware.php?a=saveProgress',
            data    : {'uc_length':currentTime,'w_length':duration_fen,'courseId':<?php echo $_GET['cid'];?>,'coursewareId':<?php echo !empty($_GET['wid'])?$_GET['wid']:0;?>,'pdf_page':pdf},
            // async   : Boolean(async),    //异步?
          
            dataType: 'json',
            success : function(data){
                //不需要显示返回提示
            }    

   	})
   	

}


//刷新页面和退出页面保存进度
$(window).bind('beforeunload', function(e){
       saveProgress();
       
     
});





</script>
</html>