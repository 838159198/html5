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

	<div class="weui-panel__ft" style="border-bottom:1px solid #E5E5E5;">
    		<a href="?a=content_list&status=1" class="weui-cell weui-cell_access weui-cell_link">
    			<div class="weui-cell__bd">待学习</div>
    			<span class="weui-cell__ft"></span>
    		</a>
    	</div>

            <div class="weui-panel__bd" style="border-bottom:0px solid #E5E5E5;">
           <?php 
           $temp=$this->waitLearning;
           if(!empty($temp)){
           foreach($temp as $k=>$v){?> 	
                <a href="?a=contentDetail&cid=<?php echo $v['cid'] ?>" class="weui-media-box weui-media-box_appmsg">
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
                <?php } 
                }?>
                
            </div>


            <div class="weui-panel__ft" style="border-bottom:1px solid #E5E5E5;">
    		<a href="?a=content_list&status=2" class="weui-cell weui-cell_access weui-cell_link">
    			<div class="weui-cell__bd">学习中</div>
    			<span class="weui-cell__ft"></span>
    		</a>
    	</div>

            <div class="weui-panel__bd" style="border-bottom:0px solid #E5E5E5;">
                <?php 
           $temp=$this->learning;
           if(!empty($temp)){
           foreach($temp as $k=>$v){?> 	
                <a href="?a=contentDetail&cid=<?php echo $v['cid'] ?>" class="weui-media-box weui-media-box_appmsg">
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
                <?php } 
                }?>
            </div>



            <div class="weui-panel__ft" style="border-bottom:1px solid #E5E5E5;">
    		<a href="?a=content_list&status=3" class="weui-cell weui-cell_access weui-cell_link">
    			<div class="weui-cell__bd">已学完</div>
    			<span class="weui-cell__ft"></span>
    		</a>
    	</div>

            <div class="weui-panel__bd" style="border-bottom:0px solid #E5E5E5;">
                <?php 
           $temp=$this->learned;
           if(!empty($temp)){
           foreach($temp as $k=>$v){?> 	
                <a href="?a=contentDetail&cid=<?php echo $v['cid'] ?>" class="weui-media-box weui-media-box_appmsg">
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
                <?php } 
                }?>
            </div>


</body>

</html>