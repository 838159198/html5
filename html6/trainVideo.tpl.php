<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0" />
<title>课件内容</title>
<!-- 预加载整个页面 它会在后台静悄悄的加载指定的文档，并把它们存储在缓存里-->
<link rel="prefetch" href="<?php echo ROOT_URL . '/data/courseware'.$this->coursewareData["w_video"] ?>" />
<link type="text/css" href="<?php echo ROOT_URL . "/view/style/html5/weui.css" ?>" rel="stylesheet" />
<link type="text/css" href="<?php echo ROOT_URL . "/view/style/html5/style.css" ?>" rel="stylesheet" />
<link type="text/css" href="<?php echo ROOT_URL . "/view/style/html5/examle.css" ?>"/>
<script type="text/javascript" src="<?php echo ROOT_URL . "/view/js/jquery.js" ?>"></script>
</head>
<body ontouchstart >
<script src="<?php echo ROOT_URL."/view/js/html6/hlm.min.js"?>"></script>
<video width="100%" height="100%" playsinline x5-video-player-type="h5"  x5-video-player-fullscreen="true" id="video" controls="controls" preload="load" >
</video>
<input name="courseId" type="hidden" value="<?php echo $this->coursewareData['wid']; ?>"> 
</body>
<script>
var myVideo = document.getElementById('video');
var w_length=<?php echo empty($this->coursewareData['w_length'])?0:$this->coursewareData['w_length'] ?>;//获取总的时长
if(<?php echo $this->coursewareData['uc_length']; ?> >0 && <?php echo $this->coursewareData['uc_length']; ?> < <?php echo empty($this->coursewareData['w_length'])?0:$this->coursewareData['w_length'] ?> ) {//获取时长，从该时长播放
    myVideo.currentTime=<?php echo $this->coursewareData['uc_length']; ?>*60;//秒
   
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
})



</script>
<script type="text/javascript">
//保存学习进度
function saveProgress(){
    var pdf=<?php echo $this->coursewareData['now_length'] ?>;
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
            data    : {'uc_length':currentTime,'w_length':duration_fen,'courseId':<?php echo $_GET['cid'];?>,'coursewareId':<?php echo $_GET['wid'];?>,'pdf_page':pdf},
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