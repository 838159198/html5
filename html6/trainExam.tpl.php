<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0" />
<title>考试须知</title>
<link type="text/css" href="<?php echo ROOT_URL . "/view/style/html5/weui.css" ?>" rel="stylesheet" />
<link type="text/css" href="<?php echo ROOT_URL . "/view/style/html5/style.css" ?>" rel="stylesheet" />
<script type="text/javascript" src="<?php echo ROOT_URL . "/view/js/jquery.js" ?>"></script>
</head>
<body ontouchstart>
<div class="container">
    <div class="exam-name"><?php echo $this->exam["name"] ?></div>
    <div class="exam-info" >
        <div class="full" style="width:20%;">
            <label><?php echo $this->exam_info['exam_point'] ?></label>
            <span>考试总分</span>
        </div>
        <div class="jige" style="width:20%;">
            <label><?php echo $this->exam_info['exam_passing_grade'] ?></label>
            <span>及格分数</span>
        </div>
        <div class="time" style="width:20%;">
            <label><?php echo $this->exam_info['exam_total_tm'] == 0 ?  '不限制' : $this->exam_info['exam_total_tm'].'分' ?></label>
            <span>答卷时长</span>
        </div>
        <div class="tishu" style="width:20%;">
            <label><?php echo isset($this->exam_info['papr_qsn_count']) ? $this->exam_info['papr_qsn_count'] : '不详';?></label>
            <span>试题总数</span>
        </div>
        <div class="cishu" style="width:20%;">
            <label><?php echo $this->exam_info['exam_times']==0?'无限制': $this->exam_info['exam_times'] ?></label>
            <span>可考次数</span>
        </div>
    </div>
    <div class="wall">考试须知</div>
    <div class="txt-content">
        <?php echo $this->exam_info['exam_notice']?>
        <?php echo $this->exam["button"] ?>


        <?php
            if($this->displayStatus['status'] && !empty($this->exam_info['allowJson']))
            {
            ?>
                
                    <a href='' target='_blank' class='weui-btn weui-btn_primary'>开始考试</a>
                
            <?php
            }elseif(!empty($this->exam_info['courseid']) || !empty($this->exam_info['planid'])){
            ?>
                <a href='<?php echo ROOT_URL."/html6.php?a=trainExamList&examId=".$this->exam_info["exam_id"]."&courseId=".$this->exam_info["courseid"]?>' target='_blank' class='weui-btn weui-btn_primary'>开始考试</a>
            <?php
            } else {
            ?>
                <a href='' target='_blank' class='weui-btn weui-btn_primary'>禁止考试</a>
            <?php
            }
            ?>
    </div>
    <div id="toast" style="display: none;">
        <div class="weui-mask_transparent"></div>
        <div class="weui-toast">
            <i class="weui-icon-success-no-circle weui-icon_toast"></i>
            <p class="weui-toast__content">报名成功</p>
        </div>
    </div>
</div>
<script type="text/javascript">
//报名
function enroll(examid, userid){
    $.ajax({
        type: "POST",
        async: true,
        url: "<?php echo ROOT_URL . "/html5.php?a=examEnroll"?>",
        data: {"examid": examid, "userid": userid},
        dataType: "json",
        success: function(data){
            if(data == 1){
                $(".weui-btn").text("等待审核");
                var $toast = $('#toast');
                if ($toast.css('display') != 'none') return;
                $toast.fadeIn(100);
                setTimeout(function () {
                    $toast.fadeOut(100);
                }, 2000);
            }
        }
    });
}
</script>
</body>
</html>