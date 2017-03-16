<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
	<title>我的课程</title>
	<link type="text/css" href="<?php echo ROOT_URL . "/view/style/html5/weui.css" ?>" rel="stylesheet" />
	<link type="text/css" href="<?php echo ROOT_URL . "/view/style/html5/style.css" ?>" rel="stylesheet" />
	<script type="text/javascript" src="<?php echo ROOT_URL . "/view/js/jquery.js" ?>"></script>
</head>
<body ontouchstart>
<div class="weui-panel__ft" >
	<div class="weui-panel weui-panel_access">
            <div class="weui-panel__hd"><a style="color: #999999; cursor:pointer;"onclick="window.location.href=document.referrer" >返回</a> 我的课程-<?php echo $this->title;?><a href="?a=trainScoreList" class="chengji">我的成绩</a></div>
            <div class="weui-panel__bd">
               <?php 
           $temp=$this->content_list;
           if(!empty($temp)){
           foreach($temp as $k=>$v){
            ?> 	
                <a id="list" href="?a=contentDetail&cid=<?php echo $v['cid'] ?>" class="weui-media-box weui-media-box_appmsg">
                    <div class="weui-media-box__hd">
                      <?php echo $v['_img']?>
                    </div>
                    <div class="weui-media-box__bd">
                        <h4 class="weui-media-box__title"><?php echo $v['c_name']?></h4>
                    <ul class='weui-media-box__info'>
                    <li class='weui-media-box__info__meta'>类型：<?php echo $v['c_elective'] ?></li>
                    <li class='weui-media-box__info__meta'>分类：<?php echo $v['classfiy'] ?></li>
                    <li class='weui-media-box__info__meta'>讲师：<?php echo $v['e_name'] ?></li>
                    <li class='weui-media-box__info__meta'>正在学习：<?php echo $v['user_learning_num']."人" ?></li>
                    </ul>
                    <ul class='weui-media-box__info'>
                    <li class='weui-media-box__info__meta'>学习时间：<?php echo $v['starttime']." ~ ".$v['endtime'] ?></li>
                    </ul>
                    </div>
                </a>
                <?php  } 
                }?>
            </div>
            <div class="weui-panel__ft">
                <a href="javascript:void(0);" class="weui-cell weui-cell_access weui-cell_link">
                    <div class="weui-cell__bd">查看更多</div>
                    <span class="weui-cell__ft"></span>
                </a>    
            </div>
        </div>

</div>
</body>
<script>

	$(document).ready(function(){
		if(<?php echo count($temp)?> >10){
			$('a:gt(11)').hide();
			$('.weui-cell').show();

		}
		else{
			$('.weui-cell').hide();
		}
	})

	$('.weui-cell').click(function(){
		$('a:gt(11)').show();
        $('.weui-cell').hide();


	})
</script>
</html>