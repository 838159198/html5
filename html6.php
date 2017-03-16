<?php
require dirname(__FILE__). '/controllerShare.php';
class html6 extends L{
    protected $courseModel = null;
    protected $planModel = null;
    protected $examModel = null;
    public function __construct()
    {
        $this->db = of_db::inst();
        $this->courseModel = new model_CourseModel();
        $this->planModel = new model_PlanModel();
        $this->examModel = new model_ExamModel();
    }
    /**
     * html6培训入口
     * @author zhanglibin
     * @date 2017.2.27
     */

    public function index(){
    	if( !isset($_SESSION['user']['userId'])){
            L::header(ROOT_URL . '/html6.php?a=contentList');
            exit;
        }
        L::header(ROOT_URL.'/html6.php?a=allContent');
    }
  
    public function trainExam (){
        $this->view->displayStatus = array('status' => true, 'cause' => array());
        if (isset($_SESSION['user']['login'])){  // cookie自动登入

            $exam_id = $this->get('exam_id', '');  // 考试ID
            $courseid = $this->get('courseId', '');  // 课程ID
            $planid = $this->get('planId', '');  // 计划ID
            $isExercise = $this->get('isExercise',0);//是否是练习
            if (!$this->examModel->isPaid($_SESSION['user']['userId'], $exam_id)) {
                $this->header(ROOT_URL . '/exam.php');
            }
            $temp = $this->sql("select * from t_exams_mate where t_exams_mate.infoId='{$exam_id}'");
            /* 获取考生信息 start */
            $sql = "SELECT
                `t_user`.`username`,       /*登录名*/
                `t_user`.`real_name`,      /*真实姓名*/
                `t_user`.`examcard`,       /*准考证*/
                `t_user`.`idcard`,         /*证件号码*/
                `t_user`.`icon`,           /*头像*/
                `t_user`.`real_photo`,     /*证件照*/
                `t_group`.`group_name`     /*分组名称*/
                FROM
                `t_user`
                LEFT JOIN `t_user_group` ON `t_user_group`.`user_id` = `t_user`.`user_id`
                LEFT JOIN `t_group` ON `t_group`.`group_id` = `t_user_group`.`group_id`
                WHERE
                `t_user`.user_id = '{$_SESSION['user']['userId']}'";
            $user = of_db::sql($sql);
            $this->view->userinfo = $user[0];
            /* 获取考生信息 end */
            if (isset($temp[0]['examWay']) && $temp[0]['examWay'] == 1) {
                $examobj=new exam();
                //检测并提交考试过期快照
                $snapshot = $examobj->submitExamExpiresSnapshot($exam_id, $_SESSION['user']['userId']);
              
                if ($snapshot === true) {
                    $this->view->tip = L::getText("上次未交卷考试成功提交");
                } elseif ($snapshot === false) {
                    $this->view->tip = L::getText("上次未交卷考试提交失败");
                } elseif ($snapshot === null) {

                //  $this->view->tip = L::getText("没有需要提交的快照！");
                }

                $valid_res = $examobj->validIsAllowUserExam($exam_id);
                $this->view->exam_info = $valid_res['exam'];
                
                $this->view->agent_is_ie = strpos($_SERVER["HTTP_USER_AGENT"], "MSIE");
 
                if (isset($valid_res['exam']['exam_disable_minute'])) {

                    $expires = $valid_res['exam']['exam_disable_minute'] == 0 ? $valid_res['exam']['exam_end_tm'] : date('Y-m-d H:i:s', strtotime('+' . $valid_res['exam']['exam_disable_minute'] . '  minute', strtotime($valid_res['exam']['exam_begin_tm'])));

                    $allowJson = $examobj->allowExam(array(
                        'examId' => $exam_id,
                        'expires' => $expires,
                    ));
                    $this->view->exam_info['allowJson'] = $allowJson;
                }

                // 如果是课程和计划的考试允许通过
                if($courseid != '' || $planid != ''){
                    $valid_res['res'] = 'success';
                }

                if ($valid_res['res'] == 'success') //已通过验证，这里跳转的考试页面
                {
                    /* 获取考生当前考试已考次数 start */
                    $sql = "SELECT
                                COUNT(`t_result_mate`.`times`) take_exam_times
                            FROM
                                `t_result_mate`
                            WHERE
                                `t_result_mate`.`userId` = '{$_SESSION['user']['userId']}'
                                AND `t_result_mate`.`examId` = '{$exam_id}'
                                AND `t_result_mate`.`courseId` = '{$courseid}'
                                AND `t_result_mate`.`planId` = '{$planid}'";
                    $temp = of_db::sql($sql);
                    $this->view->exam_info['take_exam_times'] = $temp[0]['take_exam_times'];
                    $this->view->exam_info['have_exam_times'] = $valid_res['exam']['exam_times'] - $temp[0]['take_exam_times'];
                    /* 获取考生当前考试已考次数 end */
                } else {
                    //未通过验证
                    $this->view->displayStatus['status'] = false;
                    $this->view->displayStatus['cause'] = $valid_res['info'];
                }
                // 增加计算试卷题目数量
                $temp = &exam_core_exams::snapshot(array(
                    'examId' => $exam_id,
                    'userId' => $_SESSION['user']['userId'],
                    'expires' => '1970-01-01 00:00:01', //不为 1970-01-01 00:00:00 的快照
                    'data' => false,
                ));
                if(!empty($planid)){
                    $this->view->exam_info['planid'] = $planid;
                    $this->view->exam_info['allowJson'] = '';
                }
                if(!empty($courseid)){
                    $this->view->exam_info['courseid'] = $courseid;
                    $this->view->exam_info['allowJson'] = '';
                }
                $this->view->exam_info['papr_qsn_count'] = count($temp['info']['extra']['questions']);
                if(isset($_GET['auto']) && $snapshot === null && $courseid == '' && $planid == ''){
                  $this->header(ROOT_URL . '/exam.php?a=startExam&examId=' . $_GET['exam_id'] . '&uniqid=' . $allowJson);
                  exit;
                }
                elseif(isset($_GET['auto']) && $snapshot === null && ($courseid != '' || $planid != '')){                  
                  echo '<script>
                       window.onload=function(){
                       var form=document.createElement("form");
                       document.body.appendChild(form);
                       form.action="'.ROOT_URL . '/exam.php?a=startExam&examId=' . $_GET['exam_id'] . '&courseId=' .$courseid .'&planId=' .$planid.'";
                       form.method="post";
                       var input =document.createElement("input");
                       form.appendChild(input);
                       input.type="hidden";
                       input.name="isExercise";
                       input.value="'.$isExercise.'";
                       form.submit();
                       };
                       </script>';
                  exit;
                }
                
            } else {
                $this->view->exam_info = $temp[0];
                
            }
        } else {
            $this->header(ROOT_URL . '/exam.php');
        }
        $this->display();
    }
    public function trainExamList(){

        if (isset($_GET['courseId']) || isset($_GET['planId'])) //是培训考试
        { 
            $_GET += array(
                'courseId' => false,
                'planId' => false,
            );
            if ($_GET['courseId']) //判断计划权限
            {
                $temp = new course;
                $isTrain = &$temp->getUserLearnType($_GET['courseId'], $_GET['planId']);

            } else {
                //判断课程权限
                $temp = new plan;
                $isTrain = &$temp->getUserLearnType($_GET['planId']);
            }

            //新增处理，课件看完百分比后可以参加考试
            // $courseModel = new model_CourseModel();
            $isExercise = $this->post('isExercise',0);
            if($_GET['planId'] != '' || $isExercise==1){
                $isTrain['takeExam'] = true;
            }elseif($_GET['courseId'] != ''){
                $isTrain['takeExam'] = $this->courseModel->finishedCourseProportion($_GET['courseId'],$_SESSION['user']['userId']);
            }

            // 课程，学习计划考试时间以考试为依据
            // $sql = sprintf(
            //             "SELECT startTime, endTime FROM t_exams_mate
            //             WHERE infoId='%d' AND startTime <= NOW() AND endTime > NOW()",
            //             $this->get('examId')
            //         );

            // $isTrain['takeExam'] = $isTrain['takeExam'] && $this->sql($sql) ? true : false;
            
            $examobj=new exam();
            if ($isTrain['takeExam']) //允许培训考试
            {
                $examobj->submitExamExpiresSnapshot($_GET['examId'], $_SESSION['user']['userId']); //检查过期数据
                if ($examobj->allowExam(array(
                    'examId' => &$_GET['examId'],
                    'expires' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] + 86400),
                    'snapshot' => &$examData,
                ))) {
                    if (empty($examData['info']['showTime'])) //初次进入考试
                    {
                        $sql = "SELECT
                            `t_result_mate`.id
                        FROM
                            `t_result_mate`, `t_result_info`
                        WHERE
                            `t_result_mate`.examId = '{$_GET['examId']}'
                        AND `t_result_mate`.userId = '{$_SESSION['user']['userId']}'
                        AND `t_result_mate`.times = '0'
                        AND `t_result_mate`.courseId = '{$_GET['courseId']}'
                        AND `t_result_mate`.planId = '{$_GET['planId']}'
                        AND `t_result_mate`.infoId = `t_result_info`.id
                        AND `t_result_info`.pass <= `t_result_info`.scores";
                        $temp = of_db::sql($sql);

                        if (isset($temp[0])) {
                            $examError = 105;
                        } else {
                            $sql = " SELECT t_exams_mate.`maxTimes`, COUNT(`t_result_mate`.`times`) AS `times`
                                    FROM
                                                `t_result_mate`
                                  LEFT JOIN
                                    `t_exams_mate`
                                 ON   `t_result_mate`.examId= `t_exams_mate`.infoId
                                        WHERE `t_result_mate`.examId= '{$_GET['examId']}'
                                        AND `t_result_mate`.userId = '{$_SESSION['user']['userId']}'
                                        AND `t_result_mate`.courseId = '{$_GET['courseId']}'
                                        AND `t_result_mate`.planId = '{$_GET['planId']}'";
                            $examMaxNum = of_db::sql($sql);
                            if (($examMaxNum[0]['times'] < $examMaxNum[0]['maxTimes']) || $examMaxNum[0]['maxTimes'] == 0) {
                                //判断是否达到最大考试次数限制
                                $sql = "INSERT INTO `t_result_mate` (
                                 `infoId`, `examId`, `userId`,
                                    `examPaperId`, `courseId`, `planId`,
                                    `showTime`, `times`, `ipAddress`, `judge`
                                ) SELECT
                                    '0', '{$_GET['examId']}', '{$_SESSION['user']['userId']}',
                                    '{$examData['info']['examPaperId']}', '{$_GET['courseId']}', '{$_GET['planId']}',
                                    NOW(), IFNULL(MAX(`t_result_mate`.times), 0) + 1 times, '{$_SERVER['REMOTE_ADDR']}', '0'
                                FROM
                                    (SELECT TRUE) `temp`
                                        LEFT JOIN
                                                `t_result_mate`
                                            ON
                                                examId = '{$_GET['examId']}'
                                            AND userId = '{$_SESSION['user']['userId']}'
                                            AND courseId = '{$_GET['courseId']}'
                                            AND planId = '{$_GET['planId']}' ";

                                $examData['expand']['resultMateId'] = of_db::sql($sql); //修改考试时间
                            } else {
                                $examError = 106;
                            }
                        }
                    }
                } else {
                    $examError = 103;
                }
            } else {
                $examError = 102;
            }
        } else if (isset($_GET['uniqid']) && $examobj->allowExam($_GET['examId']) === $_GET['uniqid']) {
            //权限验证通过
            if ($examData = &exam_core_exams::snapshot(array( //读取快照数据
                'examId' => &$_GET['examId'],
                'userId' => &$_SESSION['user']['userId'],
                'data' => false,
            ))) {
                if (empty($examData['info']['showTime'])) //初次进入考试
                {
//                    $sql = "UPDATE
//                        `t_user`
//                    SET
//                        `credit`=`credit`-'{$examData['expand']['examMate']['credit']}'
//                    WHERE
//                        `user_id`='{$_SESSION['user']['userId']}'
//                    AND `credit`>='{$examData['expand']['examMate']['credit']}'";
//
//                    if ($examData['expand']['examMate']['credit'] === '0' || of_db::sql($sql)) //扣除考试积分成功
//                    {
                        $sql = "INSERT INTO `t_result_mate` (
                            `infoId`, `examId`, `userId`, `examPaperId`,
                            `courseId`, `planId`, `showTime`,
                            `times`, `ipAddress`, `judge`
                        ) SELECT
                            '0', '{$_GET['examId']}', '{$_SESSION['user']['userId']}', '{$examData['info']['examPaperId']}',
                            '', '', NOW(),
                            IFNULL(MAX(`t_result_mate`.times), 0) + 1 times, '{$_SERVER['REMOTE_ADDR']}', '0'
                        FROM
                            (SELECT TRUE) `data`
                                LEFT JOIN `t_result_mate` ON
                                    `t_result_mate`.examId = '{$_GET['examId']}'
                                AND `t_result_mate`.userId = '{$_SESSION['user']['userId']}'
                        LIMIT 1";
                        $examData['expand']['resultMateId'] = of_db::sql($sql); //插入开始考试标记
//                    } else {
//                        $examError = '考试积分不足 : ' . $examData['expand']['examMate']['credit'];
//                    }
                }
            } else {
                $examError = 104;
            }
        } else {
            $examError = 101;
        }
        if (isset($examError)) //存在错误
        {
          $temp = array();
          switch ($examError) {
            case '101':
              $temp['title'] = L::getText('无权参加考试');
              $temp['reason'][] = L::getText('您不在考试范围内');
              $temp['reason'][] = L::getText('本次考试已过期');
              break;

            case '102':
              $temp['title'] = L::getText('未完成课程/学习计划');
              $temp['reason'][] = L::getText('您未完成本考试的课程/学习计划');
              $temp['reason'][] = L::getText('讲师评定未通过');
              break;

            case '103':

            case '104':
              $temp['title'] = L::getText('未能生成试卷');
              $temp['reason'][] = L::getText('本次考试缓存数据丢失');
              $temp['reason'][] = L::getText('本次考试已过期');
              break;

            case '105':
              $temp['title'] = L::getText('已经通过本考试');
              $temp['reason'][] = L::getText('您已经通过本考试');
              $temp['reason'][] = L::getText('本次考试设置为及格后不能再考');
              break;

            case '106':
              $temp['title'] = L::getText('本考试设置最大的考试次数为').'&nbsp;'.$examMaxNum[0]['maxTimes'].'&nbsp;'.L::getText('次');
              $temp['reason'][] = L::getText('本考试设置了考试次数');
              $temp['reason'][] = L::getText('您已经到达最大考试次数限制');
              break;

            default:
              $temp['title'] = L::getText('未能生成试卷');
              $temp['reason'][] = L::getText('本次考试缓存数据丢失');
              $temp['reason'][] = L::getText('本次考试已过期');
              break;
          }
          $this->view->error = $temp;
          
          // of_view::display('/tpl/exam/startExamError.tpl.php');
        } else {
            // print_r($examData);exit;
            // self::showExam($examData, true, $_GET['examId']); //开始考试
            $examId = $_GET['examId'];
            $isExam = true;

        


    
   
    
      $nowDate = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']); //当前时间
      if (empty($examData['info']['showTime'])) {
          $temp = ($examData['expand']['examMate']['duration'] === '0' ? //本次考试结束时间
              strtotime($examData['expand']['examMate']['endTime']) - strtotime($examData['expand']['examMate']['startTime']) :
              $examData['expand']['examMate']['duration'] * 60) + $_SERVER['REQUEST_TIME'];

          $examData['info']['showTime'] = $nowDate; //进入考试时间
          $isExam && exam_core_exams::snapshot(array( //修改快照数据(标记已经开始考试)
              'examId' => &$examData['info']['examId'],
              'userId' => &$examData['info']['userId'],
              'expires' => date('Y-m-d H:i:s', $temp), //过期时间
              'data' => &$examData,
          ));
        }

        $examData['expand']['examMate']['nowTime'] = &$nowDate; //当前时间
        $answerData = $examData['info']['extra'];
        unset($examData['info']['extra']); //删除额外数据
        $isAntiCheat = 0;
        $resTime = '1970-01-01 01:01:01';
        if (!empty($examId)) {
            $sql = "SELECT
            t_exams_mate.resultPublishTime,
            t_exams_mate.isAntiCheat,
            t_exams_mate.disablesubmit,
            t_exams_mate.examModeAnswer,
            t_exams_mate.noAll
            FROM
            t_exams_mate
            WHERE
            t_exams_mate.infoId = {$examId}";
            $examObj = of_db::sql($sql);
            $resTime = isset($examObj[0]['resultPublishTime']) ? $examObj[0]['resultPublishTime'] : '1970-01-01 01:01:01';
            $isAntiCheat = isset($examObj[0]['isAntiCheat']) ? $examObj[0]['isAntiCheat'] : '0';
            $examData['expand']['examMate']['disablesubmit'] = isset($examObj[0]['disablesubmit']) ? $examObj[0]['disablesubmit'] : '0';
            $examData['expand']['examMate']['examModeAnswer'] = isset($examObj[0]['examModeAnswer']) ? $examObj[0]['examModeAnswer'] : '0';
            $noAll = isset($examObj[0]['noAll']) ? $examObj[0]['noAll'] : '0';
        }
        //
        //
        //
        
        // 参考手机APP部分对数据进行处理
            $data = $examData;
            if (isset($data['info']['extra']['load'])) {
                unset($data['info']['extra']['load']);
            }

            $data['data'] = is_array($data['data']) ? $data['data'] : array();
            // file_put_contents('./log.txt', serialize($data).PHP_EOL,FILE_APPEND);
            if(isset($data['info']['extra']['questions'])){
                foreach ($data['info']['extra']['questions'] as &$que) {
                    foreach ($que['disable']['analytical'] as &$an) {
                        if (isset($an['params']['value'])) {
                            $an['params']['value'] = str_replace(' alt=""', "", $an['params']['value']);
                            if (preg_match_all('/<img src="([^"]+)"/', $an['params']['value'], $temp) && preg_match_all('/<img.*?>/', $an['params']['value'], $imgs)) {
                                for ($i = 0; $i < count($imgs[0]); $i++) {
                                    $an['params']['value'] = str_replace($imgs[0][$i], '{\{' .common_utilityMethod::getBaseUrl(true). $temp[1][$i] . '}\}', $an['params']['value']);
                                }
                            }
                            $an['params']['value'] = str_replace(array("\r\n", "\r", "^\s+\r?\n", "\t", "\n", "&nbsp;"), "", strip_tags($an['params']['value']));
                        }
                    }
                }
            }

            foreach ($data['data'] as $key => &$da) {
                $points = isset($da['points']) ? $da['points'] : null;
                if (isset($da['node']['data']['synopsis'])) {
                    $da['node']['data']['synopsis'] = is_array($da['node']['data']['synopsis']) ? $da['node']['data']['synopsis'] : array();
                    foreach ($da['node']['data']['synopsis'] as &$synopsis) {
                        if (isset($synopsis['params']['value'])) {
                            $synopsis['params']['value'] = str_replace(' alt=""', "", $synopsis['params']['value']);
                            if (preg_match_all('/<img src="([^"]+)"/', $synopsis['params']['value'], $temp) && preg_match_all('/<img.*?>/', $synopsis['params']['value'], $imgs)) {
                                for ($i = 0; $i < count($imgs[0]); $i++) {
                                    $synopsis['params']['value'] = str_replace($imgs[0][$i], '{\{' .common_utilityMethod::getBaseUrl(true). $temp[1][$i] . '}\}', $synopsis['params']['value']);
                                }
                            }
                            $synopsis['params']['value'] = str_replace(array(
                                "\r\n",
                                "\r",
                                "^\s+\r?\n",
                                "\t",
                                "\n",
                                "&nbsp;",
                            ), "", strip_tags($synopsis['params']['value']));
                        }
                    }
                }
                if (isset($da['node']['data']['stem'])) {
                    $da['node']['data']['stem'] = is_array($da['node']['data']['stem']) ? $da['node']['data']['stem'] : array();
                    foreach ($da['node']['data']['stem'] as &$stem) {
                        if (isset($stem['params']['value'])) {
                            $stem['params']['value'] = str_replace(' alt=""', "", $stem['params']['value']);
                            if (preg_match_all('/<img src="([^"]+)"/', $stem['params']['value'], $temp) && preg_match_all('/<img.*?>/', $stem['params']['value'], $imgs)) {
                                for ($i = 0; $i < count($imgs[0]); $i++) {
                                    $stem['params']['value'] = str_replace($imgs[0][$i], '{\{' .common_utilityMethod::getBaseUrl(true). $temp[1][$i] . '}\}', $stem['params']['value']);
                                }
                            }
                            if (preg_match_all('/<input.*?linkageblock="options:(\d+?)".*?>/i', $stem['params']['value'], $matches)) {
                                foreach ($matches[1] as $k => $text) {
                                    $stem['params']['value'] = str_replace($matches[0][$k], '(' . $text . ')', $stem['params']['value']);
                                }
                            }
                            $stem['params']['value'] = str_replace(array(
                                "\r\n",
                                "\r",
                                "^\s+\r?\n",
                                "\t",
                                "\n",
                                "&nbsp;",
                            ), "", strip_tags($stem['params']['value']));
                        }
                    }
                }
            }

            unset($data['info']['showTime']);
            //处理数组,减小数据量 date(2016-7-21) author(zhangzhuo)
            unset($data['info']['extra']);
            $result[0]['isQuestionsRandom'] = $data['expand']['examMate']['isQuestionsRandom'];
            $temp = '试题';    //记录大题名称
            $tempKey = 0;      //记录大题顺序(乱序时使用)
            $tempArray = array();
            foreach ($data['data'] as $key => &$value) {
                if(isset($value['points']) && !empty($value['points'])){
                    $tempArray[$tempKey][] = $key;
                    $value['partName'] = $temp;
                    $value['partNum'] = $tempKey;
                    $value['questionId'] = $value['node']['info']['questionId'];
                    $value['type'] = $value['node']['info']['type'];
                    $value['questionTitle'] = $value['node']['data']['stem'][0]['params']['value'];
                    foreach ($value['node']['data']['option'] as $k => $v) {
                        $value['option'][$k]['questionDataId'] = $v['questionDataId'];
                        if(isset($v['params']['type'])){
                            $value['option'][$k]['type'] = $v['params']['type'];
                        }
                        else{
                            $value['option'][$k]['type'] = 'richtext';
                        }
                        if(isset($value['node']['data']['synopsis'])){
                            $value['option'][$k]['optionTitle'] = $value['node']['data']['synopsis'][$k]['params']['value'];
                        }
                        else{
                            $value['option'][$k]['optionTitle'] = "";
                        }
                        // 用户答案
                        $value['option'][$k]['userData'] = isset($v['params']['value']) ? $v['params']['value'] : '';
                    }
                    unset($value['node']);
                    if($result[0]['isQuestionsRandom'] == 1 && ($value['type'] == 'singleAnswer' || $value['type'] == 'multipleChoice')){       //试题和选项乱序(选项乱序)
                        shuffle($value['option']);
                    }
                }
                else{
                    $temp = $value['node'];
                    $tempKey++;
                    unset($data['data'][$key]);
                }
            }
            if($result[0]['isQuestionsRandom'] == 1){           //试题和选项乱序(试题乱序)
                $afterSort = array();
                $newData = array();
                foreach ($tempArray as $l => $val) {
                    $keys = array_keys($val);
                    shuffle($keys);
                    foreach ($keys as $n) {
                        $afterSort[] = $val[$n];
                    }
                }
                foreach ($afterSort as $val) {
                    $newData[] = $data['data'][$val];
                }
                $data['data'] = $newData;
            }
            else{
                $data['data'] = array_merge($data['data']);
            }
            // 继续精简数据结构、减小数据大小
            $temp = array();
            $temp['info'] = $data['info'];
            $temp['expand'] = $data['expand'];
            // 试题类型对应
            $typeArray = array(
                'binaryChoice'      => 'true-false',        //判断
                'fillBlankAnswer'   => 'fill',              //填空
                'multipleChoice'    => 'muti',              //多选
                'shortAnswer'       => 'short-answer',      //简答
                'singleAnswer'      => 'single'             //单选
            );
            unset($value);
            unset($v);
            $temp['all'] = 0;
            $temp['questionType'] = '';
            // 初始化显示哪道题
            $temp['active'] = 0;
            $temp['prevClass'] = ($temp['active'] == 0) ? 'weui-btn_plain-disabled' : '';
            $temp['nextClass'] = ($temp['active'] == count($data['data'])) ? 'weui-btn_plain-disabled' : '';
            foreach ($data['data'] as $key => $value) {
                $temp['items'][$key]['type'] = $typeArray[$value['type']];
                $temp['items'][$key]['title'] = $value['questionTitle'];
                $temp['items'][$key]['questionId'] = $value['questionId'];
                $temp['items'][$key]['class'] = ($key != $temp['active']) ? 'question-un-active' : '';
                foreach ($value['option'] as $k => $v) {
                    $temp['items'][$key]['option'][$k] = array(
                        'optionId' => $v['questionDataId'],
                        'value'    => chr(65+$k) . '.  ' . $v['optionTitle'],
                        'check'    => html_entity_decode(strip_tags($v['userData']))
                    );
                }
            }

            $temp = json_encode($temp);

            $temp = str_replace('{\\\\{', '<img src=\"', $temp);

            $temp = str_replace('}\\\\}', '\">', $temp);

            $this->view->examData = $temp;


        //考试参数
        // of_view::display('/tpl/html6/trainExamList.tpl.php');
    }
    $this->display();
}
    /**
     * 描述 : 保存考试快照
     * 作者 : Edgar.lee
     */
    public function snapshotExam()
    {
        if (exam_core_exams::snapshot(array( //修改快照数据(标记已经开始考试)
            'examId' => &$_POST['info']['examId'],
            'userId' => &$_POST['info']['userId'],
            'expires' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] + $_POST['expand']['duration']), //考试过期时间
            'data' => array(
                'data' => &$_POST['data'],
                'expand' => array(
                    'markList' => &$_POST['expand']['markList'],
                ),
            ),
        ))) {
            echo 1;
        } else {
            $sql = "SELECT
                `infoId`
            FROM
                `t_result_mate`
            WHERE
                `id` = '{$_POST['expand']['resultMateId']}'";

            if (is_array($temp = of_db::sql($sql))) //查询成功
            {
                if (isset($temp[0])) {
                    if ($temp[0]['infoId'] !== '0') {
                        echo 'err002'; //考试已被收卷
                    } else {
                        echo 'err003'; //快照读取失败
                    }
                } else {
                    echo 'err001'; //考试被删除
                }
            } else {
                echo 'err004'; //数据库连接失败
            }
        }
    }
    /**
     * 描述 : 保存考试答案
     * 作者 : Edgar.lee
     */
    public static function submitExam(&$examData = null)
    {
        isset($_POST['data']) || $_POST['data'] = array();
        $snapshot = exam_core_exams::snapshot(
            array( //修改快照数据(标记已经开始考试)
                'examId' => &$_POST['examId'],
                'userId' => &$_POST['userId'],
                'expires' => &$_POST['snapshotExpires'], //考试过期时间
                'data' => array(
                    'data' => &$_POST['data'],
                    'expand' => array(
                        'markList' => '',
                    ),
                ),
            )
        );
        if ($snapshot){
            $result = exam::submitExam($snapshot);
        }
        else{
            $result['state'] = 'error';
        }
        echo json_encode($result);
    }
    

    public function trainScoreList(){

        $this->display();
    }
    public function ajaxTrainScoreList(){
         $page = $this->get("page") * 10;
        $userid = $_SESSION["user"]["userId"];

        $sql = "SELECT
                    `t_result_info`.`name`,  -- 考试名称
                    ROUND(`t_result_info`.`scores`) AS `scores`,  -- 得分
                    `t_result_info`.`points`,  -- 总分
                    `t_result_info`.`pass`,  -- 及格分
                    `t_result_mate`.`times`,  -- 考试次数
                    DATE_FORMAT(`t_result_info`.`startTime`, '%Y.%c.%d %H:%i') AS `startTime`,  -- 开始时间
                    DATE_FORMAT(`t_result_info`.`endTime`, '%Y.%c.%d %H:%i') AS `endTime`,  -- 结束时间
                    ROUND((`t_result_info`.`endTime` - `t_result_info`.`startTime`) / 60, 2) AS `duration`
                FROM
                    `t_result_mate`
                    LEFT JOIN `t_result_info` ON `t_result_info`.`id` = `t_result_mate`.`infoId`
                WHERE
                    `t_result_mate`.`userId` = '{$userid}'
                    AND `t_result_mate`.`courseId` <>''
                    ORDER BY `t_result_mate`.`id` DESC
                LIMIT {$page}, 10";//只显示课程考试的成绩
            
        $res = of_db::sql($sql);

        echo json_encode($res);
        

    }


   
	


	
    public function trainVideo($array=array()){
     
        if(!isset($_SESSION['user']['userId']) || empty($_SESSION['user']['userId'])){
            $this->header(ROOT_URL.'/html6.php?a=trainVideo');exit;
        }
   // return $array['cid'];
    //读取课程列表pass
        if(isset($array['cid']) && isset($array['wid']))
        {
            $array['cid'] = (int)$array['cid'];
            // $location = ROOT_URL . "/course.php?a=courseDetail&cid={$_GET['cid']}" . (($_GET['pid'] = $this->get('pid', '')) ? "&pid={$_GET['pid']}" : '');    //跳转路径
            if(isset($_SESSION['user']['login']))
            {
                $courseObj = new course;
                $courseStatusData = $courseObj->getUserLearnType($array['cid'], ($array['pid'] ? $array['pid'] : false));
                if($courseStatusData['learnStatus'] || TRUE)
                {
                    //验证当前课件是否属于课程
                    $sql = "SELECT
                        `t_courseware`.w_credit    /*课件积分*/
                    FROM
                        `t_course_warelist`,
                        `t_courseware`
                    WHERE
                        `t_courseware`.wid = `t_course_warelist`.wid
                    AND `t_course_warelist`.wid = '{$array['wid']}'
                    AND `t_course_warelist`.cid = '{$array['cid']}'";

                    $temp = $this->db->sql($sql);
                    if(count($temp))
                    {
                        $w_credit = $temp[0]['w_credit'];    //课件积分

                        //更新课件记录并扣除积分
                        $sql = "SELECT
                            COUNT(*) c
                        FROM
                            `t_user_learning_history`
                        WHERE
                            `t_user_learning_history`.level = '3'
                        AND `t_user_learning_history`.user_id = '{$_SESSION['user']['userId']}'
                        AND `t_user_learning_history`.wid = '{$array['wid']}'";

                        $temp = $this->db->sql($sql);
                        if($temp[0]['c'] === '0')    //没有当前课件的学习记录
                        {
                            //扣除课件积分
                            $sql = "UPDATE 
                                `t_user`
                            SET 
                                `t_user`.credit = `t_user`.credit - '{$w_credit}'
                            WHERE 
                                `t_user`.user_id = '{$_SESSION['user']['userId']}'
                            AND `t_user`.credit > '{$w_credit}'";

                            if($w_credit !== '0' && $this->db->sql($sql) === 0)    //积分不足
                            {
                                L::header(ROOT_URL."/html6.php?a=trainVideo");
                                return ;
                            }
                        }

                        $tempWhere = !empty($array['pid']) ? "`t_elective_course_user`.pid = '{$array['pid']}'" : "`t_elective_course_user`.cid = '{$array['cid']}'";
                        //读取课件列表
                        $sql = "SELECT
                            IF(
                                ISNULL(`t_user_learning_history`.user_id),
                                `t_courseware`.w_credit,
                                0
                            ) deduction_credit,    /*扣除积分*/
                            `t_courseware`.wid,    /*课件ID*/
                            `t_courseware`.w_name,    /*课件名字*/
                            `t_courseware`.w_type,
                            `t_elective_course_user`.id eleId,
                            `t_course_warelist`.elective_num,
                            `t_course`.c_elective
                        FROM
                            `t_course_warelist`
                                LEFT JOIN `t_user_learning_history` ON
                                    `t_user_learning_history`.level = '3'
                                AND `t_user_learning_history`.user_id = '{$_SESSION['user']['userId']}'
                                AND `t_user_learning_history`.wid = `t_course_warelist`.wid
                                LEFT JOIN `t_courseware` ON
                                    `t_courseware`.wid = `t_course_warelist`.wid
                                LEFT JOIN `t_elective_course_user` ON {$tempWhere}
                                AND `t_elective_course_user`.user_id = '{$_SESSION['user']['userId']}'
                                LEFT JOIN `t_course` ON `t_course`.cid = '{$array['cid']}'
                        WHERE
                            `t_course_warelist`.cid = '{$array['cid']}'
                        AND `t_courseware`.wid IS NOT NULL
                        GROUP BY
                            `t_courseware`.wid
                        ORDER BY
                            `t_course_warelist`.w_sequence";
                        $temp = $this->db->sql($sql);
                        $saveAble = ($temp[0]['c_elective'] == 1 && empty($temp[0]['eleId'])) ? 0 : 1;
                        $this->view->saveAble = $saveAble;
                        $this->view->coursewareList = $temp;
                        $courseware=new courseware();//实例化
                        if($saveAble){
                            //更新课件学习记录
                            $condition = array(
                                'user_id' => $_SESSION['user']['userId'],
                                'cid' => $array['cid'],
                                'wid' => $array['wid'],
                                'level' => 3
                            );
                            //插入学分表
                            $courseware->changePoint($condition);
                        }
                        
                        if(!$array['pid'] && $saveAble){
                            if (($id = $courseware->getLearningHistoryIdByCondition($condition))) {//把getLearningHistoryIdByCondition由私有属性改为公有属性
                                $courseware->updateLearningHistory($id);//把updateLearningHistory由私有属性改为公有属性
                            } else {
                                $courseware->addLearningHistory($condition);//把addLearningHistory的私有属性改为公有的属性
                            }
                        }

                        //更新课程学习记录
                        if($array['pid'])    //计划中的课件
                        {
                            //更新计划中课程的学习记录
                            $condition['pid'] = $array['pid'];
                            $condition['level'] = 2;
                            if($saveAble){
                                if (($id = $courseware->getLearningHistoryIdByCondition($condition))) {
                                    $courseware->updateLearningHistory($id);
                                } else {
                                    $courseware->addLearningHistory($condition);
                                }

                                //更新计划的学习记录
                                
                                $condition = array(
                                    'user_id' => $_SESSION['user']['userId'],
                                    'pid' => $array['pid'],
                                    'level' => 1
                                );
                                if (($id = $courseware->getLearningHistoryIdByCondition($condition))) {
                                    $courseware->updateLearningHistory($id);
                                } else {
                                    $courseware->addLearningHistory($condition);
                                }
                            }

                            //读取课件辅助参数
                            $sql = "SELECT
                                `t_study_plan_course`.c_isModifyProgress    /*是否允许改变播放进度,0=禁止,1=允许*/
                            FROM
                                `t_study_plan_course`
                            WHERE
                                `t_study_plan_course`.pid = '{$array['pid']}'
                            AND `t_study_plan_course`.cid = '{$array['cid']}'";

                            $temp = $this->db->sql($sql);
                            $this->view->auxiliaryConfig = $temp[0];
                        } else {    //课程中的课件
                            //更新课程的学习记录
                            if($saveAble){
                                $condition = array(
                                    'user_id' => $_SESSION['user']['userId'],
                                    'cid' => $array['cid'],
                                    'level' => 1
                                );
                                if (($id = $courseware->getLearningHistoryIdByCondition($condition))) {
                                    $courseware->updateLearningHistory($id);
                                } else {
                                    $courseware->addLearningHistory($condition);
                                }
                            }
                            //读取课件辅助参数
                            $sql = "SELECT
                                `t_course`.c_isModifyProgress    /*是否允许改变播放进度,0=禁止,1=允许*/
                            FROM
                                `t_course`
                            WHERE
                                `t_course`.cid = '{$array['cid']}'";

                            $temp = $this->db->sql($sql);
                            $this->view->auxiliaryConfig = $temp[0];
                        }

                        

                        //读取当前课件基础信息
                        $sql = "SELECT
                            `t_courseware`.wid,    /*课件ID*/
                            `t_courseware`.w_name,    /*课件名字*/
                            (              /*兼容旧版*/
                                CASE `t_courseware`.w_type
                                    WHEN '3' THEN 'flv'
                                    WHEN '4' THEN 'swf'
                                    WHEN '6' THEN 'mp3'
                                    WHEN '' THEN 'none'
                                    ELSE w_type
                                END
                            ) w_type,      /*课件类型*/
                            `t_courseware`.w_video,    /*课件路径*/
                            SUBSTR(`t_courseware`.w_des_h, 1, INSTR(`t_courseware`.w_des_h, ':')),
                            IF(
                                SUBSTR(`t_courseware`.w_des_h, 1, INSTR(`t_courseware`.w_des_h, ':')) = 'img:',
                                'img',
                                'text'
                            ) lecture_type,    /*讲义类型*/
                            `t_courseware`.w_des,    /*讲义*/
                            `t_courseware`.w_length,    /*课件总长度*/
                            `t_courseware`.create_tm,    /*课件创建时间*/
                            IFNULL(`t_user`.credit, 0) surplus_credit,    /*剩余积分*/
                            `t_user_courseware_note`.content,    /*笔记内容*/
                            IFNULL(
                                IF(
                                    `t_user_courseware`.uc_length > `t_courseware`.w_length,    /*`t_user_courseware`.uc_length 可能为null,必须用>写法*/
                                    `t_courseware`.w_length,
                                    `t_user_courseware`.uc_length
                                ),
                                0
                            ) uc_length,    /*当前课件播放的最大长度*/
                            IFNULL(t_user_courseware.now_length,0) now_length,
                            `t_course_warelist`.elective_num
                        FROM
                            `t_courseware`
                                LEFT JOIN `t_user` ON
                                    `t_user`.user_id = '{$_SESSION['user']['userId']}'
                                LEFT JOIN `t_user_courseware_note` ON
                                    `t_user_courseware_note`.user_id = `t_user`.user_id
                                AND `t_user_courseware_note`.wid = `t_courseware`.wid
                                LEFT JOIN `t_user_courseware` ON
                                    `t_user_courseware`.user_id = `t_user`.user_id
                                AND `t_user_courseware`.wid = `t_courseware`.wid
                                LEFT JOIN `t_course_warelist` ON `t_course_warelist`.wid = `t_courseware`.wid
                                AND `t_course_warelist`.cid = '{$array['cid']}'
                        WHERE
                            `t_courseware`.wid = '{$array['wid']}'";

                        $temp = $this->db->sql($sql);
                        if($saveAble){
                            $temp[0]['elective_num'] = -1;
                            if($temp[0]['w_type'] == 'none' || $temp[0]['w_type'] == 'swf'){
                                $temp[0]['uc_length'] = $temp[0]['w_length'];
                            }
                        }
                        if(!in_array($temp[0]['w_type'], array('flv', 'mp3', 'youku', 'img', 'swf', 'mp4', 'pdf', 'none', 'video', 'letvcdn')))
                        {
                            $temp[0]['w_type'] = 'none';
                        }
                        if(strip_tags($temp[0]['w_des']) == ''){
                            $temp[0]['w_des'] = '<p><font size="5">'.L::getText('本课件内容为空，您现在已经获得当前课件的学分并已完成课件的学习').'</font></p>';
                        }
                          // echo $temp[0]['uc_length'];exit;
                        // $this->view->coursewareData = $temp[0];
                        $arr=array();
                        $arr=$temp[0];
                        //加密视频地址
                        if($temp[0]['w_type'] === 'flv' || $temp[0]['w_type'] === 'mp4' || $temp[0]['w_type'] === 'swf' || $temp[0]['w_type'] === 'mp3' || $temp[0]['w_type'] === 'pdf' || $temp[0]['w_type'] === 'video')
                        {
                            if(preg_match('@^\w+://.+$@', $temp[0]['w_video']))    //远程路径
                            {
                                $temp =$temp[0]['w_video'];
                            } else {    //服务器媒体
                                if($temp[0]['w_type'] === 'pdf'){
                                    $temp = '/courseware/' . trim($temp[0]['w_video'], '/'); 
                                    $temp = common_officeToSwf_officeToSwf::showSwf($temp);
                                }else{
                                    $temp = '/data/courseware/' . trim($temp[0]['w_video'], '/');               //格式化的视频地址加密源
                                    $temp = $temp;
                                }
                            }
                            L::cookie('coursewareMediaU', $temp, null, '');
                        } elseif($temp[0]['w_type'] === 'youku') {
                            $temp = $courseware->getYoukuMediaId($temp[0]['w_video']);//把getYoukuMediaId私有属性改为公有属性
                            L::cookie('coursewareMediaU', $temp, null, '');
                        }

                        //问答列表
                        $temp = array(
                            'pageSize' => 5,
                            'associate' => array(
                                'course_id' => $array['cid'], 
                                'plan_id' => $array['pid'], 
                                'courseware_id' => $array['wid']
                            )
                        );
                        $this->view->questionPageTable = $this->_common->pageTable('question::getQuestionPageTable', $temp);

                        //读取教师列表
                        $sql = "SELECT
                            GROUP_CONCAT(DISTINCT `t_teacher`.e_name SEPARATOR ',') e_name_list
                        FROM
                            `t_course_teacher`P@
                                LEFT JOIN `t_teacher` ON
                                    `t_teacher`.e_id = `t_course_teacher`.e_id
                        WHERE
                            `t_course_teacher`.cid = '{$array['cid']}'";

                        $temp = $this->db->sql($sql);
                        $this->view->teacherNameList = $temp[0]['e_name_list'];

                        $courseModel = new model_CourseModel();
                        $passed = $courseModel->finishedCourseware($this->get('cid'), $_SESSION['user']['userId'], 0);
                        $this->view->takeExam =  !!$passed['isPassed'];

                        // $this->display();
                        return $arr;
                    } else {    //无效所属关系
                        L::header(ROOT_URL.'/html6.php?a=trainVideo');
                    }
                } else {    //不允许学习
                    L::header(ROOT_URL."/html6.php?a=trainVideo");
                }
            } else {    //未登入
                L::header(ROOT_URL."/html6.php?a=trainVideo");
            }
        } else {    //请求地址有误
            L::header(ROOT_URL."/html6.php?a=trainVideo");
        }    



    }
    
    /**
     * 全部的课程(需要报名和选修的课程)
     * 
     */
    function allContent(){
        if(!empty($_SESSION['user']['userId'])){
           $this->display(); 
        }else{
            L::header(ROOT_URL.'/html6.php?a=contentList');
        }
        
    }
    /**
     * 全部的课程列表
     * 
     */
    function ajaxAllTrainList(){
       
        $attrObj = new common_DefinedAttr();
        $courseModel = new model_CourseModel();
        $key = $attrObj->getAttrKeyByCategoryAndLimit("课程分类", "course");
        $userid = $_SESSION['user']['userId'];

        $where = "";
        
        
       
        $where .= " AND `t_course`.`c_end_time` > now()
                    AND IF(`t_course`.`c_elective` = 1, `t_elective_course_user`.`cid` IS NULL AND `t_elective_course_user`.`user_id` IS NULL, TRUE)
                    AND IF(`t_course`.`c_elective` = 2, `t_user_course`.`opinion` != 070301 OR `t_user_course`.`opinion` IS NULL, TRUE) ";
     

        $sql = "SELECT
                    `t_course`.`cid`,  -- 课程ID
                    `t_course`.`c_name`,  -- 课程名称
                    `t_course`.`frontCoverImg`,  -- 课程封面
                    `t_course`.`c_elective`,
                    `t_course`.`registration`,
                    `t_course`.`c_person_amount`,
                    DATE_FORMAT(`t_course`.`c_start_time`, '%Y-%m-%d %H:%i') AS `starttime`,
                    DATE_FORMAT(`t_course`.`c_end_time`, '%Y-%m-%d %H:%i') AS `endtime`,
                    ifnull(GROUP_CONCAT(`t_teacher`.`e_name`),'') AS `e_name`
                FROM
                    `t_course`
                    LEFT JOIN `t_course_teacher` ON `t_course_teacher`.`cid` = `t_course`.`cid`
                    LEFT JOIN `t_teacher` ON `t_teacher`.`e_id` = `t_course_teacher`.`e_id`
                    LEFT JOIN `t_data_stratified_data` ON
                        `t_data_stratified_data`.tablename = 't_course'
                    AND `t_data_stratified_data`.id = `t_course`.cid
                    LEFT JOIN `t_elective_course_user` ON `t_elective_course_user`.`cid` = `t_course`.`cid` AND `t_elective_course_user`.`user_id` = '{$userid}'
                    LEFT JOIN `t_user_course` ON `t_user_course`.`cid` = `t_course`.`cid` AND `t_user_course`.`user_id` = '{$userid}' AND `t_user_course`.`app_status` = 070403
                WHERE
                    `t_course`.`c_status` = 1
                    {$where}
                GROUP BY `t_course`.`cid`
                ORDER BY `t_course`.`update_tm` DESC
                ";
                 // echo $sql;exit;
        $data = $this->db->sql($sql);

        foreach($data as $key => &$val){
            // 正学习人数
            $val['user_learning_num'] = $courseModel->getLearningNum($val['cid']);
            // 课程类型
            if($val['c_elective'] == 0 || $val['c_elective'] ==  3){
                unset($data[$key]);
            }elseif($val['c_elective'] == 1){
                $val['c_elective'] = "选修课";
                $val['_enroll']="加入我的课程";
                $val['dialog']=1;//区分两个弹窗
            }elseif($val['c_elective'] == 2){
                if($courseModel->isCourseHasUser($val['cid'], $_SESSION['user']['userId'])){
                    $status = $courseModel->isEnroll($val['cid'], $_SESSION['user']['userId']);
                    $val['_enroll'] = '';
                    if($status == 1 && empty($params['search'])){
                        unset($data[$key]);
                        continue;
                    }elseif($status == 2){
                        $val['_enroll'] .= "申请被拒绝";
                                             
                    }elseif($status == 3){
                        $val['_enroll'] .= "审核中...";
                    }else{
                        if($val['c_person_amount']!=0){
                            if($val['user_learning_num']>=$val['c_person_amount']){
                                $val['_enroll'].="人数已满";
                            }else{
                                $val['_enroll'].= "我要报名";
                            }
                        }else{   


                        $val['_enroll'] .= "我要报名";
                        }

                    }
                    //代报名此版本不做
                    // if($val['registration'] == 1){
                    //     $val['_enroll'] .= "<li class='fr mr15' id='course_btn_area_msgbox_replaced_". $val['cid'] ."'>
                    //                           <span class='ddf-box_btn_area_msg'>
                    //                             <a href='javascript:;' onclick='denroll(\"". $val['cid'] ."\");' class='ddf-box_btn_area_msglink'>". L::getText("代报名") ."</a>
                    //                           </span>
                    //                         </li>";
                    // }
                }
                $val['dialog']=2;
                $val['c_elective'] = "需要报名";
            }

            $val['_img'] = '<img style="height:100%;" class="weui-media-box__thumb" src="' .OF_URL . '/addin/oFileManager/fileExtension.php?fileUrl=' .of::config('_of.writableDir') . $val['frontCoverImg'] .'" onerror="this.src=\''.ROOT_URL . of::config('_of.view.tplPath').'/images/cover_course.jpg\'" title="'. $val['c_name'] .'" alt="'. $val['c_name'] .'" />';

            // 自定义属性分类
            $attrObj = new common_DefinedAttr();
            $attrName = $attrObj->getAttrValue('t_course', $val['cid'], '课程分类');
            $val['classfiy'] = str_replace(" ", "，", $attrName);

            
            // if($val['c_person_amount']!=0){
            //     if($val['user_learning_num']>=$val['c_person_amount']){
            //         $val['_enroll'].="人数已满";
            //     }
            // }   
            // 已学过
            // $val['pass_course_user_num'] = $courseModel->getPassNum($val['cid']);
        


        }

        $data = array_merge($data);
        echo json_encode($data);
    }

    /**
     * 
     * 需要报名de课程->课程报名
     */
    public function enroll() {
        if(isset($_SESSION['user']['userId']) && $_SESSION['user']['status'] && $cid = $this->post('cid', false))
        {
            $app_content = $this->post('app_content', '');
            $sql = "REPLACE INTO `t_user_course`
                (`user_id`, `cid`, `app_status`, `app_content`, `opinion`, `app_tm`)
            VALUES
                ('{$_SESSION['user']['userId']}', '{$cid}', '070403', '{$app_content}', '070303', now())";

            $this->db->sql($sql);
            $verifyClass = new admin_verify_verifyCtl();
            $result = $verifyClass->testApply(array(array('obj_id'=>$cid,'user_id'=>$_SESSION['user']['userId'])));
            echo json_encode($result);
        }
    }

    /**
     * 选修课->参加学习
     */
    public function join(){
        $courseId = $_POST['cid'];
        if(is_numeric($courseId)){
            $sql = "SELECT * FROM `t_elective_course_user` WHERE `cid`='{$courseId}' AND `user_id`='{$_SESSION['user']['userId']}'";
            $result = $this->sql($sql);
            if(empty($result)){
                $sql = "INSERT INTO `t_elective_course_user` (`cid`,`user_id`) VALUES ('{$courseId}','{$_SESSION['user']['userId']}')";
                $data=$this->sql($sql);
                if($data){
                     $res = 'success';
                }else{
                     $res = 'false';
                }
            }
            else{
                $res="false";
            }
            
           
        }
        else{
            $res = 'false';
        }
        echo json_encode($res);
        
    }

    /**
     * 我的课程列表
     * 包括待学习，正在学，学习中
     */
    function contentList(){
    //待学习课程列表
        $courseModel = new model_CourseModel();
        $userid = $_SESSION['user']['userId'];
        $Cids = $courseModel->userCourseAll($userid);
        // 去掉已经学习过的
        $joinCids = $courseModel->userJoinCourse($userid);
        foreach($joinCids as $val){
            if(in_array($val['cid'], $Cids)){
                unset($Cids[array_search($val['cid'], $Cids)]);
            }
        }
        $incids = implode(", ", $Cids) != '' ? implode(", ", $Cids) : "''";

        $sql = "SELECT
                    `t_course`.`cid`,
                    `t_course`.`c_name`,
                    `t_course`.`frontCoverImg`,
                    `t_course`.`c_elective`,
                    `t_course`.`registration`,
                    DATE_FORMAT(`t_course`.`c_start_time`, '%Y-%m-%d %H:%i') AS `starttime`,
                    DATE_FORMAT(`t_course`.`c_end_time`, '%Y-%m-%d %H:%i') AS `endtime`,
                    ifnull(GROUP_CONCAT(`t_teacher`.`e_name`),'') AS `e_name`
                FROM
                    `t_course`
                    LEFT JOIN `t_course_teacher` ON `t_course_teacher`.`cid` = `t_course`.`cid`
                    LEFT JOIN `t_teacher` ON `t_teacher`.`e_id` = `t_course_teacher`.`e_id`
                    LEFT JOIN `t_elective_course_user` ON `t_elective_course_user`.`cid` = `t_course`.`cid` AND `t_elective_course_user`.`user_id` = '{$userid}'
                    LEFT JOIN `t_user_course` ON `t_user_course`.`cid` = `t_course`.`cid` AND `t_user_course`.`user_id` = '{$userid}' AND `t_user_course`.`app_status` = 070403
                WHERE
                    `t_course`.`c_status` = 1
                    AND `t_course`.`c_end_time` > now()
                    AND `t_course`.`cid` IN ({$incids})
                    AND IF(`t_course`.`c_elective` = 1, `t_elective_course_user`.`cid`  != '', TRUE)
                    AND IF(`t_course`.`c_elective` = 2, `t_user_course`.`opinion` = 070301, TRUE)
                GROUP BY `t_course`.`cid`
                ORDER BY `t_course`.`c_end_time` ASC
                LIMIT 2";
               
            $data=$this->sql($sql);
            foreach($data as $k=>$val){
                $attrObj = new common_DefinedAttr();
                $attrName = $attrObj->getAttrValue('t_course', $val['cid'], '课程分类');
                $data[$k]['classfiy'] = str_replace(" ", "，", $attrName);//获取课程分类
                if($val['c_elective'] == 0 || $val['c_elective'] == 3){
                    $data[$k]['c_elective'] ="必修课";
                }elseif($val['c_elective'] == 1){
                    $data[$k]['c_elective'] ="选修课";
                }elseif($val['c_elective'] == 2){
                    $data[$k]['c_elective'] ="需要报名";
                }

                // 正学习人数
             
                $data[$k]['user_learning_num'] = $courseModel->getLearningNum($val['cid']);

                $data[$k]['_img'] = '<img style="height:100%;" class="weui-media-box__thumb" src="' .OF_URL . '/addin/oFileManager/fileExtension.php?fileUrl=' .of::config('_of.writableDir') . $val['frontCoverImg'] .'" onerror="this.src=\''.ROOT_URL . of::config('_of.view.tplPath').'/images/cover_course.jpg\'" title="'. $val['c_name'] .'" alt="'. $val['c_name'] .'" />';
           
            }
        
        $this->view->waitLearning=$data;
       
        //学习中
       
        $train_obj=new model_courseModel();
        $temp=$train_obj->getUserUnPassCourse($_SESSION['user']['userId']);//获取全部有效课程
        $temp = implode(',',$temp);
        // echo $temp;exit;
        $params=array('pass' => false, 'tableid' => 'getCoursePageTableLearning', 'empty' => false, 'out' => $temp);
        $pass = $params['pass'];

        $courseModel = new model_CourseModel();
            $userid = $_SESSION['user']['userId'];

            $courseids = $courseModel->userJoinCourse($userid);
            $Cids = '';

            foreach($courseids as $k => $v){
                $status = $courseModel->isPassed($v['cid'], $userid);
                if($status['isPassed'] == $pass){
                    $Cids .= $v['cid'] . ",";
                }
            }
            // echo $Cids;exit;
            $Cids = rtrim($Cids, ",") != '' ? rtrim($Cids, ",") : '\'\'';

            if(isset($params['out']) && !empty($params['out'])){
                $where = " AND `t_course`.`cid` NOT IN ({$params['out']}) AND `t_course`.`c_end_time` > now() ";
                // echo $where;exit;
            }
            else{
                $where = '';
            }
            $sql = "SELECT
                    `t_course`.`cid`,  
                   `t_course`.`c_name`,  
                   `t_course`.`frontCoverImg`,  
                   `t_course`.`c_elective`,
                   `t_course`.`registration`,
                   DATE_FORMAT(`t_course`.`c_start_time`, '%Y-%m-%d %H:%i') AS `starttime`,
                   DATE_FORMAT(`t_course`.`c_end_time`, '%Y-%m-%d %H:%i') AS `endtime`,
                   ifnull(GROUP_CONCAT(`t_teacher`.`e_name`),'') AS `e_name`
                FROM
                    `t_course`
                    LEFT JOIN `t_course_teacher` ON `t_course_teacher`.`cid` = `t_course`.`cid`
                    LEFT JOIN `t_teacher` ON `t_teacher`.`e_id` = `t_course_teacher`.`e_id`
                WHERE
                    `t_course`.`c_status` = 1
                    AND `t_course`.`cid` IN ({$Cids})
                    {$where}
                GROUP BY `t_course`.`cid`
                ORDER BY `t_course`.`c_end_time` ASC 
                LIMIT 2 ";
            $data=$this->sql($sql);
            foreach($data as $k=>$val){
                // 自定义属性分类
                $attrObj = new common_DefinedAttr();
                $attrName = $attrObj->getAttrValue('t_course', $val['cid'], '课程分类');
                $data[$k]['classfiy'] = str_replace(" ", "，", $attrName);
                // 课程类型
                if($val['c_elective'] == 0 || $val['c_elective'] == 3){
                    $data[$k]['c_elective'] = "必修课";
                }elseif($val['c_elective'] == 1){
                    $data[$k]['c_elective'] = "选修课";
                }elseif($val['c_elective'] == 2){
                    $data[$k]['c_elective'] = "需要报名";

                }    
                // 正学习人数
                $data[$k]['user_learning_num'] = $courseModel->getLearningNum($val['cid']);

                $data[$k]['_img'] = '<img style="height:100%;" class="weui-media-box__thumb" src="' .OF_URL . '/addin/oFileManager/fileExtension.php?fileUrl=' .of::config('_of.writableDir') . $val['frontCoverImg'] .'" onerror="this.src=\''.ROOT_URL . of::config('_of.view.tplPath').'/images/cover_course.jpg\'" title="'. $val['c_name'] .'" alt="'. $val['c_name'] .'" />';
            }
          $this->view->learning=$data;  
          // 已学完
        $params=array('pass' => true, 'tableid' => 'getCoursePageTableLearned', 'empty' => false);
        $pass=$params['pass'];
        $courseModel = new model_CourseModel();
            $userid = $_SESSION['user']['userId'];

            $courseids = $courseModel->userJoinCourse($userid);
            $Cids = '';

            foreach($courseids as $k => $v){
                $status = $courseModel->isPassed($v['cid'], $userid);
                if($status['isPassed'] == $pass){
                    $Cids .= $v['cid'] . ",";

                }
            }

            $Cids = rtrim($Cids, ",") != '' ? rtrim($Cids, ",") : '\'\'';

            if(isset($params['out']) && !empty($params['out'])){
                $where = " AND `t_course`.`cid` NOT IN ({$params['out']}) AND `t_course`.`c_end_time` > now() ";
            }
            else{
                $where = '';
            }
            $sql = "SELECT
                    `t_course`.`cid`,  
                   `t_course`.`c_name`,  
                   `t_course`.`frontCoverImg`,  
                   `t_course`.`c_elective`,
                   `t_course`.`registration`,
                   DATE_FORMAT(`t_course`.`c_start_time`, '%Y-%m-%d %H:%i') AS `starttime`,
                   DATE_FORMAT(`t_course`.`c_end_time`, '%Y-%m-%d %H:%i') AS `endtime`,
                   ifnull(GROUP_CONCAT(`t_teacher`.`e_name`),'') AS `e_name`
                FROM
                    `t_course`
                    LEFT JOIN `t_course_teacher` ON `t_course_teacher`.`cid` = `t_course`.`cid`
                    LEFT JOIN `t_teacher` ON `t_teacher`.`e_id` = `t_course_teacher`.`e_id`
                WHERE
                    `t_course`.`c_status` = 1
                    AND `t_course`.`cid` IN ({$Cids})
                    {$where}
                GROUP BY `t_course`.`cid`
                ORDER BY `t_course`.`c_end_time` ASC 
                LIMIT 2";
            $data=$this->sql($sql);
            foreach($data as $k=>$val){
                // 自定义属性分类
                $attrObj = new common_DefinedAttr();
                $attrName = $attrObj->getAttrValue('t_course', $val['cid'], '课程分类');
                $data[$k]['classfiy'] = str_replace(" ", "，", $attrName);
                // 课程类型
                if($val['c_elective'] == 0 || $val['c_elective'] == 3){
                    $data[$k]['c_elective'] = "必修课";
                }elseif($val['c_elective'] == 1){
                    $data[$k]['c_elective'] = "选修课";
                }elseif($val['c_elective'] == 2){
                    $data[$k]['c_elective'] = "需要报名";

                }    
                // 正学习人数
                $data[$k]['user_learning_num'] = $courseModel->getLearningNum($val['cid']);

                $data[$k]['_img'] = '<img style="height:100%;" class="weui-media-box__thumb" src="' .OF_URL . '/addin/oFileManager/fileExtension.php?fileUrl=' .of::config('_of.writableDir') . $val['frontCoverImg'] .'" onerror="this.src=\''.ROOT_URL . of::config('_of.view.tplPath').'/images/cover_course.jpg\'" title="'. $val['c_name'] .'" alt="'. $val['c_name'] .'" />';
            }
        $this->view->learned=$data;    


         $this->display();

    }
    /**
     * 我的课程-待学习
     * 我的课程-正在学
     * 我的课程-学习中
     * 
     */
    function content_list(){
        $status=$this->get('status','');

        if(empty($status)){
            $this->display(ROOT_URL.'/index.php');
        }
        if($status==1){
              
            //待学习课程列表
        $courseModel = new model_CourseModel();
        $userid = $_SESSION['user']['userId'];
        $Cids = $courseModel->userCourseAll($userid);
        // 去掉已经学习过的
        $joinCids = $courseModel->userJoinCourse($userid);
        foreach($joinCids as $val){
            if(in_array($val['cid'], $Cids)){
                unset($Cids[array_search($val['cid'], $Cids)]);
            }
        }
        $incids = implode(", ", $Cids) != '' ? implode(", ", $Cids) : "''";

        $sql = "SELECT
                    `t_course`.`cid`,
                    `t_course`.`c_name`,
                    `t_course`.`frontCoverImg`,
                    `t_course`.`c_elective`,
                    `t_course`.`registration`,
                    DATE_FORMAT(`t_course`.`c_start_time`, '%Y-%m-%d %H:%i') AS `starttime`,
                    DATE_FORMAT(`t_course`.`c_end_time`, '%Y-%m-%d %H:%i') AS `endtime`,
                    ifnull(GROUP_CONCAT(`t_teacher`.`e_name`),'') AS `e_name`
                FROM
                    `t_course`
                    LEFT JOIN `t_course_teacher` ON `t_course_teacher`.`cid` = `t_course`.`cid`
                    LEFT JOIN `t_teacher` ON `t_teacher`.`e_id` = `t_course_teacher`.`e_id`
                    LEFT JOIN `t_elective_course_user` ON `t_elective_course_user`.`cid` = `t_course`.`cid` AND `t_elective_course_user`.`user_id` = '{$userid}'
                    LEFT JOIN `t_user_course` ON `t_user_course`.`cid` = `t_course`.`cid` AND `t_user_course`.`user_id` = '{$userid}' AND `t_user_course`.`app_status` = 070403
                WHERE
                    `t_course`.`c_status` = 1
                    AND `t_course`.`c_end_time` > now()
                    AND `t_course`.`cid` IN ({$incids})
                    AND IF(`t_course`.`c_elective` = 1, `t_elective_course_user`.`cid`  != '', TRUE)
                    AND IF(`t_course`.`c_elective` = 2, `t_user_course`.`opinion` = 070301, TRUE)
                GROUP BY `t_course`.`cid`
                ORDER BY `t_course`.`c_end_time` ASC
                ";
               
            $data=$this->sql($sql);
            foreach($data as $k=>$val){
                $attrObj = new common_DefinedAttr();
                $attrName = $attrObj->getAttrValue('t_course', $val['cid'], '课程分类');
                $data[$k]['classfiy'] = str_replace(" ", "，", $attrName);//获取课程分类
                if($val['c_elective'] == 0 || $val['c_elective'] == 3){
                    $data[$k]['c_elective'] ="必修课";
                }elseif($val['c_elective'] == 1){
                    $data[$k]['c_elective'] ="选修课";
                }elseif($val['c_elective'] == 2){
                    $data[$k]['c_elective'] ="需要报名";
                }

                // 正学习人数
             
                $data[$k]['user_learning_num'] = $courseModel->getLearningNum($val['cid']);

                $data[$k]['_img'] = '<img style="height:100%;" class="weui-media-box__thumb" src="' .OF_URL . '/addin/oFileManager/fileExtension.php?fileUrl=' .of::config('_of.writableDir') . $val['frontCoverImg'] .'" onerror="this.src=\''.ROOT_URL . of::config('_of.view.tplPath').'/images/cover_course.jpg\'" title="'. $val['c_name'] .'" alt="'. $val['c_name'] .'" />';
           
            }
        $this->view->title="待学习";
        $this->view->content_list=$data;  
        }
        else if($status==2){
            $train_obj=new model_courseModel();
        $temp=$train_obj->getUserUnPassCourse($_SESSION['user']['userId']);//获取全部有效课程
        $temp = implode(',',$temp);
        // echo $temp;exit;
        $params=array('pass' => false, 'tableid' => 'getCoursePageTableLearning', 'empty' => false, 'out' => $temp);
        $pass = $params['pass'];

        $courseModel = new model_CourseModel();
            $userid = $_SESSION['user']['userId'];

            $courseids = $courseModel->userJoinCourse($userid);
            $Cids = '';

            foreach($courseids as $k => $v){
                $status = $courseModel->isPassed($v['cid'], $userid);
                if($status['isPassed'] == $pass){
                    $Cids .= $v['cid'] . ",";
                }
            }
            // echo $Cids;exit;
            $Cids = rtrim($Cids, ",") != '' ? rtrim($Cids, ",") : '\'\'';

            if(isset($params['out']) && !empty($params['out'])){
                $where = " AND `t_course`.`cid` NOT IN ({$params['out']}) AND `t_course`.`c_end_time` > now() ";
                // echo $where;exit;
            }
            else{
                $where = '';
            }
            $sql = "SELECT
                    `t_course`.`cid`,  
                   `t_course`.`c_name`,  
                   `t_course`.`frontCoverImg`,  
                   `t_course`.`c_elective`,
                   `t_course`.`registration`,
                   DATE_FORMAT(`t_course`.`c_start_time`, '%Y-%m-%d %H:%i') AS `starttime`,
                   DATE_FORMAT(`t_course`.`c_end_time`, '%Y-%m-%d %H:%i') AS `endtime`,
                   ifnull(GROUP_CONCAT(`t_teacher`.`e_name`),'') AS `e_name`
                FROM
                    `t_course`
                    LEFT JOIN `t_course_teacher` ON `t_course_teacher`.`cid` = `t_course`.`cid`
                    LEFT JOIN `t_teacher` ON `t_teacher`.`e_id` = `t_course_teacher`.`e_id`
                WHERE
                    `t_course`.`c_status` = 1
                    AND `t_course`.`cid` IN ({$Cids})
                    {$where}
                GROUP BY `t_course`.`cid`
                ORDER BY `t_course`.`c_end_time` ASC 
                 ";
            $data=$this->sql($sql);
            foreach($data as $k=>$val){
                // 自定义属性分类
                $attrObj = new common_DefinedAttr();
                $attrName = $attrObj->getAttrValue('t_course', $val['cid'], '课程分类');
                $data[$k]['classfiy'] = str_replace(" ", "，", $attrName);
                // 课程类型
                if($val['c_elective'] == 0 || $val['c_elective'] == 3){
                    $data[$k]['c_elective'] = "必修课";
                }elseif($val['c_elective'] == 1){
                    $data[$k]['c_elective'] = "选修课";
                }elseif($val['c_elective'] == 2){
                    $data[$k]['c_elective'] = "需要报名";

                }    
                // 正学习人数
                $data[$k]['user_learning_num'] = $courseModel->getLearningNum($val['cid']);

                $data[$k]['_img'] = '<img style="height:100%;" class="weui-media-box__thumb" src="' .OF_URL . '/addin/oFileManager/fileExtension.php?fileUrl=' .of::config('_of.writableDir') . $val['frontCoverImg'] .'" onerror="this.src=\''.ROOT_URL . of::config('_of.view.tplPath').'/images/cover_course.jpg\'" title="'. $val['c_name'] .'" alt="'. $val['c_name'] .'" />';
            }
            $this->view->title="学习中";
            $this->view->content_list=$data;  

        }
        else if($status==3){
            $params=array('pass' => true, 'tableid' => 'getCoursePageTableLearned', 'empty' => false);
        $pass=$params['pass'];
        $courseModel = new model_CourseModel();
            $userid = $_SESSION['user']['userId'];

            $courseids = $courseModel->userJoinCourse($userid);
            $Cids = '';

            foreach($courseids as $k => $v){
                $status = $courseModel->isPassed($v['cid'], $userid);
                if($status['isPassed'] == $pass){
                    $Cids .= $v['cid'] . ",";

                }
            }

            $Cids = rtrim($Cids, ",") != '' ? rtrim($Cids, ",") : '\'\'';

            if(isset($params['out']) && !empty($params['out'])){
                $where = " AND `t_course`.`cid` NOT IN ({$params['out']}) AND `t_course`.`c_end_time` > now() ";
            }
            else{
                $where = '';
            }
            $sql = "SELECT
                    `t_course`.`cid`,  
                   `t_course`.`c_name`,  
                   `t_course`.`frontCoverImg`,  
                   `t_course`.`c_elective`,
                   `t_course`.`registration`,
                   DATE_FORMAT(`t_course`.`c_start_time`, '%Y-%m-%d %H:%i') AS `starttime`,
                   DATE_FORMAT(`t_course`.`c_end_time`, '%Y-%m-%d %H:%i') AS `endtime`,
                   ifnull(GROUP_CONCAT(`t_teacher`.`e_name`),'') AS `e_name`
                FROM
                    `t_course`
                    LEFT JOIN `t_course_teacher` ON `t_course_teacher`.`cid` = `t_course`.`cid`
                    LEFT JOIN `t_teacher` ON `t_teacher`.`e_id` = `t_course_teacher`.`e_id`
                WHERE
                    `t_course`.`c_status` = 1
                    AND `t_course`.`cid` IN ({$Cids})
                    {$where}
                GROUP BY `t_course`.`cid`
                ORDER BY `t_course`.`c_end_time` ASC 
                ";
            $data=$this->sql($sql);
            foreach($data as $k=>$val){
                // 自定义属性分类
                $attrObj = new common_DefinedAttr();
                $attrName = $attrObj->getAttrValue('t_course', $val['cid'], '课程分类');
                $data[$k]['classfiy'] = str_replace(" ", "，", $attrName);
                // 课程类型
                if($val['c_elective'] == 0 || $val['c_elective'] == 3){
                    $data[$k]['c_elective'] = "必修课";
                }elseif($val['c_elective'] == 1){
                    $data[$k]['c_elective'] = "选修课";
                }elseif($val['c_elective'] == 2){
                    $data[$k]['c_elective'] = "需要报名";

                }    
                // 正学习人数
                $data[$k]['user_learning_num'] = $courseModel->getLearningNum($val['cid']);

                $data[$k]['_img'] = '<img style="height:100%;" class="weui-media-box__thumb" src="' .OF_URL . '/addin/oFileManager/fileExtension.php?fileUrl=' .of::config('_of.writableDir') . $val['frontCoverImg'] .'" onerror="this.src=\''.ROOT_URL . of::config('_of.view.tplPath').'/images/cover_course.jpg\'" title="'. $val['c_name'] .'" alt="'. $val['c_name'] .'" />';
            }
        $this->view->title="已学完";
        $this->view->content_list=$data;     


        }
        $this->display();
    }
    function contentDetail(){

        $array=array();
        $result=array();
        $array['wid']=$this->get('wid','');
        $array['cid']=$this->get('cid','');
        $array['pass']=$this->get('pass','');

        if(!empty($array['wid']) && !empty($array['cid'])){
            $result=$this->trainVideo($array);
            $this->view->coursewareData=$result;
        }
 
        //  if (!isset($_SESSION['user']['userId'])) {
        //     $this->header(ROOT_URL . '/html6.php?a=trainList');
        //     exit;
        // }
      
        if($cid = $this->get('cid', false))
        {

            if (!$this->courseModel->isPaid($_SESSION['user']['userId'], $cid)) {//该课程需要付费的，此版本不做
                

                $this->header(ROOT_URL . '/html6.php?a=contentList');
            }

            $definedObj = new common_DefinedAttr();
            //读取课程基础信息
                $sql = "(SELECT
                    `t_course`.cid,    /*课程ID*/
                    `t_course`.c_name,    /*课程名称*/
                    `t_course`.c_train_type,
                    `t_course`.c_des,    /*课程描述*/
                    `t_course`.frontCoverImg,    /*课程封面*/
                    `t_course`.`cid` AS desc_cn,    /*分类名称*/
                    t_course.c_proportion,
                    t_course.c_see_single,
                    t_course.c_start_time,
                    t_course.c_end_time,
                    t_course.c_allow_ip,
                    IF(`t_course`.c_approve = 1, 2, `t_course`.c_elective) c_elective,    /*进修选项,0=必须,1=选修,3=报名*/
                    `t_course`.c_pass_condition,    /*通过条件,包含1;=讲师评定,2;=通过考试,3;=达到学时*/
                    CASE
                        WHEN `t_course`.c_pass_condition = '1;' THEN '讲师评定'
                        WHEN `t_course`.c_pass_condition = '2;' THEN '通过考试'
                        WHEN `t_course`.c_pass_condition = '3;' THEN '达到学时'
                    END c_pass_condition_cn
                FROM
                    `t_course`
                WHERE
                    `t_course`.c_status = '1'
                AND `t_course`.cid = '{$cid}') `data`";

               

              
                
                //生成已经学习过的 课程-课件学习记录
                $course=new course();
                $course->creatLearnHistory($cid);//私有属性改为公有属性
           
 
            //读取评价等级
            $sql = "(SELECT
                `data`.*,
                ROUND(IFNULL(AVG(`t_course_appraise`.score), 0), 0) avg_score    /*平均得星数*/
            FROM
                {$sql}
                    LEFT JOIN `t_course_appraise` ON
                        `t_course_appraise`.cid = `data`.cid
            GROUP BY
                `data`.cid) `data`";

            //读取总学时、总学分
            $sql = "(SELECT
                `data`.*,
                ROUND(SUM(`t_courseware`.w_length), 1) courseware_total_length,    /*课件播放总时长*/
                SUM(`t_courseware`.w_point) w_point    /*总课件学分*/
            FROM
                {$sql}
                    LEFT JOIN `t_course_warelist` ON
                        `t_course_warelist`.cid = `data`.cid
                    LEFT JOIN `t_courseware` ON
                        `t_courseware`.wid = `t_course_warelist`.wid
            GROUP BY
                `data`.cid) `data`";

            //读取教师列表
            $sql = "SELECT
                `data`.*,
                GROUP_CONCAT(
                    DISTINCT
                        `t_teacher`.e_name
                    SEPARATOR
                        ','
                ) teacher_name_list    /*教师姓名列表*/
            FROM
                {$sql}
                    LEFT JOIN `t_course_teacher` ON
                        `t_course_teacher`.cid = `data`.cid
                    LEFT JOIN `t_teacher` ON
                        `t_teacher`.e_id = `t_course_teacher`.e_id
            GROUP BY
                `data`.cid";
            $temp = $this->db->sql($sql);
            // echo $temp[0]['c_name'];exit;
            $this->view->courseName=$temp[0]['c_name'];
            if(count($temp))
            {
                // 获取相关课程   属性改为公有属性
                $this->view->relateCourse = $course->getCourseByCondition(
                    array('cid'=>array('<>'=>$temp[0]['cid']))
                );

                //对于学习计划中的课程
                if($pid){
                    // 获取正在学习人数
                    $this->view->LearningNum = count($this->planModel->getPlanCourseLearningUser($pid,$temp[0]['cid']));
                    $p_elective = $temp[0]['p_elective'];
                    $seenAble = '';
                    //判断是否在学习范围内
                    $planUsers = $this->planModel->getPlanUsers($pid);
                    $inCourseRange = array_key_exists($_SESSION['user']['userId'], $planUsers);
                    //判断是否在学习时间范围内
                    $inCourseTime = !!( time() >= strtotime($temp[0]['opening_begin_tm']) && (time() < strtotime($temp[0]['opening_end_tm'])));
                    //判断IP限制
                    $inCourseIP = false;
                    if(empty($temp[0]['p_allow_ip']))
                    {
                        $inCourseIP = true;
                    } else {
                        $ipTemp = array(explode(',', $temp[0]['p_allow_ip']), bindec(decbin(ip2long($_SERVER["REMOTE_ADDR"]))));
                        foreach($ipTemp[0] as &$v)
                        {
                            $ipTemp[2] = explode('-', $v);
                            if( $ipTemp[1] === 2130706433 || (bindec(decbin(ip2long($ipTemp[2][0]))) <= $ipTemp[1] && bindec(decbin(ip2long($ipTemp[2][1]))) >= $ipTemp[1]) )
                            {
                                $inCourseIP = true;
                                break;
                            }
                        }
                    }
                    //判断前置课程是否学完
                    $inOtherCourse = true;
                    $sql = "SELECT
                                t_study_plan_course_condition.c_cid
                            FROM
                                t_study_plan_course_condition
                            WHERE
                                t_study_plan_course_condition.pid = '{$pid}'
                            AND t_study_plan_course_condition.cid = '{$cid}'";
                    $result = $this->sql($sql);
                    if(empty($inOtherCourse)){
                        $inOtherCourse = true;
                    }
                    else{
                        foreach ($result as $key => $value) {
                            $tempRes = $this->planModel->isPassed($pid,$value['c_cid'],$_SESSION['user']['userId']);
                            $inOtherCourse = $inOtherCourse && $tempRes['isPassed'];
                        }
                    }
                    if(!$inCourseRange){
                        $seenAble = L::getText('您不在学习范围内');
                    }
                    elseif(!$inCourseTime){
                        $seenAble = L::getText('现在不在指定学习时间范围内');
                    }
                    elseif(!$inCourseIP){
                        $seenAble = L::getText('您不在指定的IP范围内');
                    }
                    elseif(!$inOtherCourse){
                        $seenAble = L::getText('本课程的前置课程未通过');
                    }
                    //对于需要报名判断
                    if($temp[0]['p_elective'] == '2'){
                        $tempSql = "SELECT
                                        opinion
                                    FROM
                                        t_user_plan_application
                                    WHERE
                                        pid = '{$pid}'
                                    AND user_id = '{$_SESSION['user']['userId']}' LIMIT 1";
                        $result = $this->db->sql($tempSql);
                        if(empty($result)){
                            $seenAble = L::getText('学习本课程需要先报名,审核通过后方可学习');
                        }
                        elseif($result[0]['opinion'] == '070302'){
                            $seenAble = L::getText('您的报名未通过审核');
                        }
                        elseif($result[0]['opinion'] == '070303'){
                            $seenAble = L::getText('您的报名正在审核中,通过后便可学习');
                        }
                    }
                    //用户观看课程时长
                    $tempSql = "SELECT
                                    ROUND(
                                        SUM(
                                            t_user_courseware.uc_length
                                        ),
                                        1
                                    ) uc_length
                                FROM
                                    t_course_warelist
                                LEFT JOIN t_user_courseware ON t_user_courseware.wid = t_course_warelist.wid
                                AND t_user_courseware.user_id = '{$_SESSION['user']['userId']}'
                                WHERE
                                    t_course_warelist.cid = '{$temp[0]['cid']}'";
                    $result = $this->db->sql($tempSql);
                    $ucLength = $result[0]['uc_length'];
                    //用户是否通过课程
                    $result = $this->planModel->isPassed($pid,$cid,$_SESSION['user']['userId']);
                    $hasPassCourse = $result['isPassed'];
                    //课程分类
                    $temp[0]['desc_cn'] =$definedObj->getAttrValue('t_course', $temp[0]['cid'], '课程分类');
                    //课程进度百分比
                    $temp[0]['learnProgress'] = round($ucLength*100/$temp[0]['courseware_total_length'],1);
                    //课程简介
                    if(!empty($temp[0]['c_des'])){
                        $temp[0]['c_des'] = str_replace("\n", "<br />", $temp[0]['c_des']);
                    }
                    //课程类型
                    $c_elective = $temp[0]['p_elective'];
                    switch ($temp[0]['p_elective']) {
                        case '0':
                            $temp[0]['c_elective'] = L::getText('必修课');
                            break;

                        case '1':
                            $temp[0]['c_elective'] = L::getText('选修课');
                            break;

                        default:
                            $temp[0]['c_elective'] = L::getText('需要报名');
                            break;
                    }
                    //通过条件
                    switch ($temp[0]['c_pass_condition']) {
                        case '1;':
                            $passCondition = L::getText('您的教师决定本课程通过与否');
                            break;

                        case '2;':
                            $passCondition = L::getText('课程所有课件的平均进度达到').$temp[0]['c_proportion'].L::getText('%时->参加并通过全部考试');
                            break;

                        default:
                            if($temp[0]['c_see_single'] == 1){
                                $passCondition = L::getText('每个课件进度均达到').$temp[0]['c_proportion'].L::getText('%时便可通过课程');
                            }
                            else{
                                $passCondition = L::getText('课程所有课件的平均进度达到').$temp[0]['c_proportion'].L::getText('%时便可通过课程');
                            }
                            break;
                    }
                    //考试练习
                    $examAndExercise = L::getText('本课程设置了考试和练习');
                    $courseNoticeData = array();
                    if(!empty($seenAble)){
                        $courseNoticeData[L::getText('禁止学习: ')] = $seenAble;
                    }
                    $courseNoticeData[L::getText('通过条件: ')] = $passCondition;
                    $courseDetail = $temp[0];
                    $this->view->seenAble = $seenAble;
                    $this->view->courseDetail = $temp[0];
                    $this->view->hasPassCourse = !!$hasPassCourse;
                    $this->view->pid = $pid;
                }
                //对于独立课程
                else{
                    // 获取正在学习人数
                    $this->view->LearningNum = $this->courseModel->getLearningNum($temp[0]['cid']);
                    $seenAble = '';
                    //判断是否在学习范围内
                    $inCourseRange = $this->courseModel->isCourseHasUser($temp[0]['cid'],$_SESSION['user']['userId']);
                    //是否在学习时间范围内
                    $inCourseTime = !!( time() >= strtotime($temp[0]['c_start_time']) && (time() < strtotime($temp[0]['c_end_time'])));
                    //判断IP限制
                    $inCourseIP = false;
                    if(empty($temp[0]['c_allow_ip']))
                    {
                        $inCourseIP = true;
                    } else {
                        $ipTemp = array(explode(',', $temp[0]['c_allow_ip']), bindec(decbin(ip2long($_SERVER["REMOTE_ADDR"]))));
                        foreach($ipTemp[0] as &$v)
                        {
                            $ipTemp[2] = explode('-', $v);
                            if( $ipTemp[1] === 2130706433 || (bindec(decbin(ip2long($ipTemp[2][0]))) <= $ipTemp[1] && bindec(decbin(ip2long($ipTemp[2][1]))) >= $ipTemp[1]) )
                            {
                                $inCourseIP = true;
                                break;
                            }
                        }
                    }
                    if(!$inCourseRange){
                        $seenAble = L::getText('您不在学习范围内');
                    }
                    elseif(!$inCourseTime){
                        $seenAble = L::getText('现在不在指定学习时间范围内');
                    }
                    elseif(!$inCourseIP){
                        $seenAble = L::getText('您不在指定的IP范围内');
                    }
                    //对于需要报名判断
                    if($temp[0]['c_elective'] == '2'){
                        $tempSql = "SELECT
                                        opinion
                                    FROM
                                        t_user_course
                                    WHERE
                                        cid = '{$temp[0]['cid']}'
                                    AND user_id = '{$_SESSION['user']['userId']}' LIMIT 1";
                        $result = $this->db->sql($tempSql);
                        if(empty($result)){
                            $seenAble = L::getText('学习本课程需要先报名,审核通过后方可学习');
                        }
                        elseif($result[0]['opinion'] == '070302'){
                            $seenAble = L::getText('您的报名未通过审核');
                        }
                        elseif($result[0]['opinion'] == '070303'){
                            $seenAble = L::getText('您的报名正在审核中,通过后便可学习');
                        }
                    }

                    //用户观看课程时长
                    $tempSql = "SELECT
                                    ROUND(
                                        SUM(
                                            t_user_courseware.uc_length
                                        ),
                                        1
                                    ) uc_length
                                FROM
                                    t_course_warelist
                                LEFT JOIN t_user_courseware ON t_user_courseware.wid = t_course_warelist.wid
                                AND t_user_courseware.user_id = '{$_SESSION['user']['userId']}'
                                WHERE
                                    t_course_warelist.cid = '{$temp[0]['cid']}'";
                    $result = $this->db->sql($tempSql);
                    $ucLength = $result[0]['uc_length'];
                    //用户是否通过课程
                    $result = $this->courseModel->isPassed($temp[0]['cid'],$_SESSION['user']['userId']);
                    $hasPassCourse = $result['isPassed'];
                    //课程分类
                    $temp[0]['desc_cn'] =$definedObj->getAttrValue('t_course', $temp[0]['cid'], '课程分类');
                    //课程进度百分比
                    $temp[0]['learnProgress'] = round($ucLength*100/$temp[0]['courseware_total_length'],1);
                    //课程简介
                    if(!empty($temp[0]['c_des'])){
                        $temp[0]['c_des'] = str_replace("\n", "<br />", $temp[0]['c_des']);
                    }
                    //课程类型
                    $c_elective = $temp[0]['c_elective'];
                    switch ($temp[0]['c_elective']) {
                        case '0':
                            $temp[0]['c_elective'] = L::getText('必修课');
                            break;

                        case '1':
                            $temp[0]['c_elective'] = L::getText('选修课');
                            break;

                        default:
                            $temp[0]['c_elective'] = L::getText('需要报名');
                            break;
                    }
                    //通过条件
                    switch ($temp[0]['c_pass_condition']) {
                        case '1;':
                            $passCondition = L::getText('您的教师决定本课程通过与否');
                            break;

                        case '2;':
                            if($temp[0]['c_see_single'] == 1){
                                $passCondition = L::getText('每个课件进度均达到').$temp[0]['c_proportion'].L::getText('%时->参加并通过全部考试');
                            }
                            else{
                                $passCondition = L::getText('课程所有课件的平均进度达到').$temp[0]['c_proportion'].L::getText('%时->参加并通过全部考试');
                            }
                            break;

                        default:
                            if($temp[0]['c_see_single'] == 1){
                                $passCondition = L::getText('每个课件进度均达到').$temp[0]['c_proportion'].L::getText('%时便可通过课程');
                            }
                            else{
                                $passCondition = L::getText('课程所有课件的平均进度达到').$temp[0]['c_proportion'].L::getText('%时便可通过课程');
                            }
                            break;
                    }
                    //考试练习
                    $courseNoticeData = array();
                    if(!empty($seenAble)){
                        $courseNoticeData[L::getText('禁止学习: ')] = $seenAble;
                    }
                    $courseNoticeData[L::getText('通过条件: ')] = $passCondition;
                    $courseDetail = $temp[0];
                    $this->view->seenAble = $seenAble;
                    $this->view->courseDetail = $temp[0];
                    $this->view->hasPassCourse = !!$hasPassCourse;
                }

                //读取课件列表
                $sql = "SELECT
                    `t_course_warelist`.cid,    /*课程ID*/
                    `t_courseware`.wid,    /*课件ID*/
                    `t_courseware`.w_name,    /*课件名字*/
                    `t_courseware`.w_credit,    /*所需积分*/
                    `t_courseware`.w_length,    /*课件时度*/
                    `t_courseware`.w_type,
                    IF(ROUND(IFNULL(MAX(`t_user_courseware`.uc_length) * 100 / IFNULL(`t_user_courseware`.w_length, `t_courseware`.w_length), 0), 0)>100,100,ROUND(IFNULL(MAX(`t_user_courseware`.uc_length) * 100 / IFNULL(`t_user_courseware`.w_length, `t_courseware`.w_length), 0), 0)) learn_progress,    /*学习进度*/
                    `t_courseware`.`wid` AS `desc_cn`,    /*分类名称*/
                    IF(
                        ISNULL(`t_user_learning_history`.user_id),
                        `t_courseware`.w_credit,
                        0
                    ) deduction_credit,    /*扣除积分*/
                    `t_courseware`.w_point AS deduction_point,
                    IFNULL(`t_user`.credit, 0) credit,    /*用户剩余积分*/
                    `t_course_warelist`.elective_num
                FROM
                    `t_course_warelist`
                        LEFT JOIN `t_courseware` ON
                            `t_courseware`.wid = `t_course_warelist`.wid
                        LEFT JOIN `t_user_courseware` ON
                            `t_user_courseware`.wid = `t_courseware`.wid
                        AND `t_user_courseware`.user_id = '{$_SESSION['user']['userId']}'
                        LEFT JOIN `t_user_learning_history` ON
                            `t_user_learning_history`.level = '3'
                        AND `t_user_learning_history`.user_id = '{$_SESSION['user']['userId']}'
                        AND `t_user_learning_history`.wid = `t_course_warelist`.wid
                        LEFT JOIN `t_user` ON
                            `t_user`.user_id = '{$_SESSION['user']['userId']}'
                        LEFT JOIN `t_point_history` ON `t_point_history`.course_id = '{$cid}'
                        AND `t_point_history`.courseware_id = `t_course_warelist`.wid
                        AND `t_point_history`.user_id = '{$_SESSION['user']['userId']}'
                WHERE
                    `t_course_warelist`.cid = '{$cid}'
                AND `t_courseware`.wid IS NOT NULL
                GROUP BY
                    `t_courseware`.wid
                ORDER BY
                    `t_course_warelist`.w_sequence";

                $coursewareResult = $course->getCategory($this->db->sql($sql), "课件分类");

                //考试列表
                $tempWhere = $pid ? "`t_result_mate`.planId ='{$pid}'" : "(ISNULL(`t_result_mate`.planId) OR `t_result_mate`.planId = '')";
                $sql = "SELECT
                            `t_course_exam`.cid,
                            /*课程ID*/
                            `t_exams_info`.id exam_id,
                            /*考试ID*/
                            `t_exams_info`.`name` exam_name,
                            /*考试名称*/
                            `t_exams_mate`.cover_img,
                            /*考试封面图片*/
                            `t_exams_mate`.`maxTimes`,
                            /*最大考试次数*/
                            `t_exams_mate`.credit,
                            /*考试金额*/
                            `t_exams_mate`.duration exam_total_tm,
                            /*答卷时长*/
                            IF(`t_exams_mate`.`unityPoint` != 0, `t_exams_mate`.`unityPoint`, `t_papers_meta`.`total_point`) AS points,
                            /*试卷总分*/
                            `t_papers_info`.pass,
                            /*及格分数*/
                            `t_exams_info`.`id` AS desc_cn,
                            /*分类名称*/
                            COUNT(`t_result_info`.examId) pass_num,
                            /*考试及次数,0=没及格或没参加过考试,xx=及格次数*/
                            COUNT(`t_result_mate`.`times`) AS `times`,
                            /*考试次数*/
                            `t_exams_mate`.unPass
                        FROM
                            `t_course_exam`
                        LEFT JOIN `t_exams_info` ON `t_exams_info`.id = `t_course_exam`.exam_id
                        INNER JOIN `t_exams_mate` ON `t_exams_mate`.infoId = `t_exams_info`.id
                        AND `t_exams_mate`.type = '1'
                        LEFT JOIN `t_result_mate` ON t_result_mate.examId = t_exams_info.id
                        AND t_result_mate.infoId <> 0
                        AND {$tempWhere}
                        AND `t_result_mate`.courseId = '{$cid}'
                        AND `t_result_mate`.userId = '{$_SESSION['user']['userId']}'
                        LEFT JOIN `t_result_info` ON `t_result_mate`.infoId = `t_result_info`.id
                        AND `t_result_info`.scores >= `t_result_info`.pass
                        LEFT JOIN `t_exams_data`  ON `t_exams_data`.`infoId`   = `t_exams_info`.`id`
                        LEFT JOIN `t_papers_info` ON `t_papers_info`.`id`      = `t_exams_data`.`paperId`
                        LEFT JOIN `t_papers_meta` ON `t_papers_meta`.`info_id` = `t_exams_data`.`paperId`
                        WHERE
                            `t_course_exam`.cid = '{$cid}'
                        AND `t_exams_info`.id IS NOT NULL
                        AND `t_exams_mate`.infoId IS NOT NULL
                        GROUP BY
                            `t_exams_info`.id";
                $examResult = $course->getCategory($this->db->sql($sql), "考试分类");
                $seen = $this->courseModel->finishedCourseProportion($cid, $_SESSION['user']['userId']);
                if(empty($examResult)){
                    $examResult = L::getText('本课程没有设立考试');
                }
                else{
                    if($seen){
                        foreach ($examResult as $key => &$v) {
                            if(($v['times'] >= $v['maxTimes'] && $v['maxTimes'] != 0) || ($v['pass_num'] > 0 && $v['unPass'] > 0)){
                                $v['attend'] = 0;
                            }
                            else{
                                $v['attend'] = 1;
                            }
                            $v['passed'] = ($v['pass_num'] > 0) ? 1: 0;
                        }
                        unset($v);
                    }
                    else{
                        if($courseDetail['c_see_single'] == 1){
                            $examResult = L::getText('参加考试 : 您的每个课件学习进度达到').$courseDetail['c_proportion'].L::getText('%时，才可以参加考试');
                        }
                        else{
                            $examResult = L::getText('参加考试 : 您的学习进度达到').$courseDetail['c_proportion'].L::getText('%时，才可以参加考试');
                        }
                    }
                }
                $this->view->examResult = $examResult;
                //练习列表
                $tempWhere = $pid ? "`t_result_mate`.planId ='{$pid}'" : "(ISNULL(`t_result_mate`.planId) OR `t_result_mate`.planId = '')";
                $sql = "SELECT
                            `t_course_exam`.cid,
                            /*课程ID*/
                            `t_exams_info`.id exam_id,
                            /*考试ID*/
                            `t_exams_info`.`name` exam_name,
                            /*考试名称*/
                            `t_exams_mate`.cover_img,
                            /*考试封面图片*/
                            `t_exams_mate`.`maxTimes`,
                            /*最大考试次数*/
                            `t_exams_mate`.credit,
                            /*考试金额*/
                            `t_exams_mate`.duration exam_total_tm,
                            /*答卷时长*/
                            IF(`t_exams_mate`.`unityPoint` != 0, `t_exams_mate`.`unityPoint`, `t_papers_meta`.`total_point`) AS points,
                            /*试卷总分*/
                            `t_papers_info`.pass,
                            /*及格分数*/
                            `t_exams_info`.`id` AS desc_cn,
                            /*分类名称*/
                            COUNT(`t_result_info`.examId) pass_num,
                            /*考试及次数,0=没及格或没参加过考试,xx=及格次数*/
                            COUNT(`t_result_mate`.`times`) AS `times`,
                            /*考试次数*/
                            `t_exams_mate`.unPass
                        FROM
                            `t_course_exam`
                        LEFT JOIN `t_exams_info` ON `t_exams_info`.id = `t_course_exam`.exam_id
                        INNER JOIN `t_exams_mate` ON `t_exams_mate`.infoId = `t_exams_info`.id
                        AND `t_exams_mate`.type = '0'
                        LEFT JOIN `t_result_mate` ON t_result_mate.examId = t_exams_info.id
                        AND t_result_mate.infoId <> 0
                        AND {$tempWhere}
                        AND `t_result_mate`.courseId = '{$cid}'
                        AND `t_result_mate`.userId = '{$_SESSION['user']['userId']}'
                        LEFT JOIN `t_result_info` ON `t_result_mate`.infoId = `t_result_info`.id
                        AND `t_result_info`.scores >= `t_result_info`.pass
                        LEFT JOIN `t_exams_data`  ON `t_exams_data`.`infoId`   = `t_exams_info`.`id`
                        LEFT JOIN `t_papers_info` ON `t_papers_info`.`id`      = `t_exams_data`.`paperId`
                        LEFT JOIN `t_papers_meta` ON `t_papers_meta`.`info_id` = `t_exams_data`.`paperId`
                        WHERE
                            `t_course_exam`.cid = '{$cid}'
                        AND `t_exams_info`.id IS NOT NULL
                        AND `t_exams_mate`.infoId IS NOT NULL
                        GROUP BY
                            `t_exams_info`.id";
                $excResult = $course->getCategory($this->db->sql($sql), "练习分类");
                // $seen = $this->courseModel->finishedCourseProportion($cid, $_SESSION['user']['userId']);
                $joinStatus = 0;
                if(empty($excResult)){
                    $excResult = L::getText('本课程没有设立练习');
                }
                else{
                    // if($seen){
                        foreach ($excResult as $key => &$v) {
                            if(($v['times'] >= $v['maxTimes'] && $v['maxTimes'] != 0) || ($v['pass_num'] > 0 && $v['unPass'] > 0)){
                                $v['attend'] = 0;
                            }
                            else{
                                $v['attend'] = 1;
                            }
                            $v['passed'] = ($v['pass_num'] > 0) ? 1: 0;
                        }
                        unset($v);
                        //取消课件学习达到一定进度才能参加练习的限制
                    // }
                    // else{
                    //     if($courseDetail['c_see_single'] == 1){
                    //         $excResult = L::getText('参加练习 : 您的每个课件学习进度达到').$courseDetail['c_proportion'].L::getText('%时，才可以参加练习');
                    //     }
                    //     else{
                    //         $excResult = L::getText('参加练习 : 您的学习进度达到').$courseDetail['c_proportion'].L::getText('%时，才可以参加练习');
                    //     }
                    // }
                }
                // print_r($excResult);exit;
                $this->view->exceResult = $excResult;
                if($pid){
                    //对课件可看处理
                    if(empty($seenAble)){
                        $allEleCourse = $this->planModel->getHavingElePlanByUserId($_SESSION['user']['userId']);
                        if($p_elective == 1 && !in_array($pid, $allEleCourse)){
                            $joinStatus = 1;
                            foreach ($coursewareResult as $key => &$value) {
                                $value['seenAble'] = $value['elective_num'];
                            }
                            unset($value);
                        }
                        else{
                            if(in_array($cid, $allEleCourse)){
                                $joinStatus = 2;
                            }
                            foreach ($coursewareResult as $key => &$value) {
                                $value['seenAble'] = 1;
                            }
                            unset($value);
                        }
                    }
                    else{
                        foreach ($coursewareResult as $key => &$value) {
                            $value['seenAble'] = 0;
                        }
                        unset($value);
                    }
                }
                else{
                    //对于课件可看处理
                    if(empty($seenAble)){
                        $allEleCourse = $this->courseModel->getHavingEleCourseByUserId($_SESSION['user']['userId']);
                        if($c_elective == 1 && !in_array($cid, $allEleCourse)){
                            $joinStatus = 1;
                            foreach ($coursewareResult as $key => &$value) {
                                $value['seenAble'] = $value['elective_num'];
                            }
                            unset($value);
                        }
                        else{
                            if(in_array($cid, $allEleCourse)){
                                $joinStatus = 2;
                            }
                            foreach ($coursewareResult as $key => &$value) {
                                $value['seenAble'] = 1;
                            }
                            unset($value);
                        }
                    }
                    else{
                        foreach ($coursewareResult as $key => &$value) {
                            $value['seenAble'] = 0;
                        }
                        unset($value);
                    }
                    $temp = $this->courseModel->isPassed($cid,$_SESSION['user']['userId']);
                    if($temp['isPassed']){
                        $this->view->delAble = true;
                    }
                    else{
                        $this->view->delAble = false;
                    }
                }
                $this->view->joinStatus = $joinStatus;
                $this->view->coursewareList = $coursewareResult;//课件列表

                // 读取附件列表
                $sql = "SELECT
                    `t_course_att`.c_att_old_name,    /*附件路径*/
                    `t_course_att`.c_att_name,    /*附件名称*/
                    `t_course_att`.create_tm    /*上传日期*/
                FROM
                    `t_course_att`
                WHERE
                    `t_course_att`.cid = '{$cid}'";

                $this->view->courseAtt = $this->db->sql($sql);    //附件列表
                $temp = new question;    //问答列表
                $this->view->questionPageTable = $temp->getQuestionPageTable(array('associate'=>array('course_id' => $cid, 'plan_id' => $pid ? $pid : '')));

                //判断是否有考试和练习
                $examAndExercise = L::getText('本课程');
                $examAndExercise .= ($examResult == L::getText('本课程没有设立考试')) ? L::getText('没有考试') : L::getText('设置了考试');
                $examAndExercise .= '、';
                $examAndExercise .= ($excResult == L::getText('本课程没有设立练习')) ? L::getText('没有练习') : L::getText('设置了练习');
                $courseNoticeData[L::getText('考试练习: ')] = $examAndExercise;
                $this->view->courseNoticeData = $courseNoticeData;
            } else {    //无效课程
                L::header(ROOT_URL . '/html6.php?a=trainList');
            }

            $this->display();
        } else {
            L::header(ROOT_URL . '/html6.php?a=trainList');
        }
        
    }


}
 ?>   