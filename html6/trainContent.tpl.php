<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0" />
<title>课程内容</title>
<link type="text/css" href="<?php echo ROOT_URL . "/view/style/html5/weui.css" ?>" rel="stylesheet" />
<link type="text/css" href="<?php echo ROOT_URL . "/view/style/html5/style.css" ?>" rel="stylesheet" />
<script type="text/javascript" src="<?php echo ROOT_URL . "/view/js/jquery.js" ?>"></script>
</head>
<body ontouchstart>
<div class="weui-panel__hd" style="border-bottom:1px solid #E5E5E5;"><a style="color: #999999; cursor:pointer;"onclick="window.location.href=document.referrer" >返回</a><a style="cursor:pointer;" onclick="examlist()" class="chengji">课后考试</a></div>
<div class="weui-panel__bd" id="train">
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
                <a target="_blank"  href="<?php echo ROOT_URL."/html6.php?a=trainVideo&cid={$v['cid']}{$temp[0]}&wid={$v['wid']}&pass={$pass}"?>" class="weui-media-box weui-media-box_appmsg">
                    <div class="weui-media-box__hd">
                        <img class="weui-media-box__thumb" src="<?php echo ROOT_URL."/";?>view/images/minfo_head.jpg" alt="">
                    </div>
                    <div class="weui-media-box__bd">
                       <h4 class='weui-media-box__title'>课件名称：<?php echo $v['w_name']?></h4>
                    <ul class='weui-media-box__info'>
                    <li class='weui-media-box__info__meta'>类型：<?php echo $v['desc_cn'] ?></li>
                    <li class='weui-media-box__info__meta'>时长：<?php echo $v['w_length'] ?></li>
                    <li class='weui-media-box__info__meta'>学分：<?php echo $v['deduction_point'] ?></li>
                   <li class='weui-media-box__info__meta'>费用：<?php echo $v['deduction_credit']?>元</li></ul>
                    </div>
                    
              </a>
       <?php }     
	?>    

</div>
    <!-- 考试列表 -->
 <div class="weui-panel__bd" id="exam" style="visibility:hidden;">   

    <?php 
         $temp = $this->examResult;
         if(is_string($temp)){?>
        <div class="weui-media-box__hd" id="exam">
        <?php echo $temp;?>
        </div>
        <?php }else{  
        
         foreach ($temp as $key => $v) {?>
             <a href="<?php echo ROOT_URL."/html6.php?a=trainExam&courseId=".$v['cid']."&exam_id=".$v['exam_id']?>" class="weui-media-box weui-media-box_appmsg" >
                    <div class="weui-media-box__hd" >
                        <img class="weui-media-box__thumb" src="<?php echo ROOT_URL."/";?>view/images/minfo_head.jpg" alt="">
                    </div>
                    <div class="weui-media-box__bd">
                       <h4 class='weui-media-box__title'>考试名称：<?php echo $v['exam_name']?></h4>
                    <ul class='weui-media-box__info'>
                    <li class='weui-media-box__info__meta'>分类：<?php echo $v['desc_cn'] ?></li>
                    <li class='weui-media-box__info__meta'>限时：<?php echo $v['exam_total_tm']==0?'不限时':$v['exam_total_tm'].'分钟' ?></li></ul><ul class='weui-media-box__info'>
                    <li class='weui-media-box__info__meta'>总分：<?php echo $v['points'] ?></li>
                   <li class='weui-media-box__info__meta'>及格：<?php echo $v['pass']?>元</li></ul>
                   
                    </div>
                    
                    <?php echo $v['passed']==1?'已通过':''?>
                    
              </a>

         <?php }
                    }?>

    


               
            </div>
            <div class="weui-panel__ft">
                <a href="javascript:void(0);" class="weui-cell weui-cell_access weui-cell_link">
                    <div class="weui-cell__bd">查看更多</div>
                    <span class="weui-cell__ft"></span>
                </a>    
            </div>
</div>


</body>
<script type="text/javascript">
if(<?php echo count($this->coursewareList); ?> > 10){
	$('.weui-cell').show();
}
else{
	$('.weui-cell').hide();
}
//点击考试，显示考试列表
function examlist(){
    $('#exam').attr("style","visibility:visible");
    $('#train').attr('style','display:none');

}



</script>