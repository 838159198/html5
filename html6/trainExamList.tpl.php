<?php
$this->head(
    array(
        'head' => array(
            '<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">'
        ),
        'title'=>L::getText('考试', array('file'=>__FILE__, 'line'=>__LINE__))
        ,'css'=>array(
            '/html5/weui.css',
            '/html5/example.css'
        )
        ,'js'=>array(
            '/html5/vue.min.js',
            '/html5/countdown.js',
            '/html5/myVue.js',
            '/html5/zepto.min.js',
            '/html5/weui.min.js',
            '/html6/exam.js'
        )
    )
);
?>
<body>
<style type="text/css">
*{   
    -webkit-touch-callout:none;  /*系统默认菜单被禁用*/   
    -webkit-user-select:none; /*webkit浏览器*/   
    -khtml-user-select:none; /*早期浏览器*/   
    -moz-user-select:none;/*火狐*/   
    -ms-user-select:none; /*IE10*/   
    user-select:none;   
}
input,textarea {      
     -webkit-user-select:auto !important; /*webkit浏览器*/     
}
</style>
<div id="question">
    <div class="weui-tab question">
        <div class="weui-navbar">
            <div class="my-navbar__item">{{questionType}}</div>
            <div class="my-navbar__item">{{active+1}}/{{all}}</div>
            <div class="my-navbar__item" v-on:click="sheet">答题卡</div>
        </div>
        <div class="my-content">
            <div v-for="item in items">
                <div v-if="(item.type == 'single' || item.type == 'true-false')">
                    <single-answer v-bind:todo="item" v-on:chang="changUp"></single-answer>
                </div>
                <div v-else-if="item.type == 'muti'">
                    <muti-choice v-bind:todo="item" v-on:chang="changUp"></muti-choice>
                </div>
                <div v-else>
                    <fill-blanks v-bind:todo="item" v-on:chang="changUp"></fill-blanks>
                </div>
            </div>
            <div style="text-align: center;">
                <a href="javascript:" class="weui-btn weui-btn_plain-primary" v-bind:class="prevClass" v-on:click="prev">上一题</a>
                <a href="javascript:" class="weui-btn weui-btn_plain-primary" v-bind:class="nextClass" v-on:click="next">下一题</a>
            </div>
        </div>
        <div class="weui-tabbar">
            <div class="my-w-time">
                <div class="my-time">
                    
                </div>
            </div>
            <div class="my-w-submit">
                <div class="my-submit">
                    提交试卷
                </div>
            </div>
        </div>
    </div>
    <div class="weui-tab answerSheet" id="answerSheet">
        <div class="weui-navbar">
            <div class="my-navbar__item" v-on:click="sheet">返回考试</div>
            <div class="my-navbar__item">答题卡</div>
            <div class="my-navbar__item my-time"></div>
        </div>
        <div class="my-content" style="background-color: #F1F1F1;height: 100%;">
            <div class="btn-div">
                <a class="weui-btn weui-btn_mini sheet-btn" href="javascript:;" v-for="(item, index) in items"  v-bind:class="getClass(index)" v-on:click="jumpQuestion(index)">{{index+1}}</a>
            </div>
        </div>
        <div class="weui-tabbar">
            <div class="my-w-submit" style="width: 100%;">
                <div class="my-submit">
                    提交试卷
                </div>
            </div>
        </div>
    </div>
    <!-- 弹出层 -->
    <div id="dialogs">
        <!--BEGIN dialog1-->
        <div class="js_dialog" id="iosDialog1" style="display: none;z-index: 10;">
            <div class="weui-mask"></div>
            <div class="weui-dialog">
                <div class="weui-dialog__hd"><strong class="weui-dialog__title">确认交卷?</strong></div>
                <div class="weui-dialog__bd">点击确定后试卷将提交并退出考试</div>
                <div class="weui-dialog__ft">
                    <a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_default">取消</a>
                    <a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_primary" onclick="submitExam()">确定</a>
                </div>
            </div>
        </div>
        <!--END dialog1-->
        <!--BEGIN dialog2-->
        <div class="js_dialog" id="iosDialog2" style="display: none;z-index: 11;">
            <div class="weui-mask"></div>
            <div class="weui-dialog">
                <div class="weui-dialog__bd" id="point"></div>
                <div class="weui-dialog__ft">
                    <a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_primary" id="afterSubmit">确定</a>
                </div>
            </div>
        </div>
        <!--END dialog2-->
    </div>
</div>
    <!-- 弹出层结束 -->
    <script type="text/javascript">
        examData = <?php echo $this->examData;?>;

        examChangStatus = [];
    </script>
</body>