$(function(){
    var $iosDialog1 = $('#iosDialog1'),
        $iosDialog2 = $('#iosDialog2');

    $('#dialogs').on('click', '.weui-dialog__btn_default', function(){
        $(this).parents('.js_dialog').fadeOut(200);
    });

    $('.my-submit').on('click', function(){
        $iosDialog1.fadeIn(200);
    });
    $('#showIOSDialog2').on('click', function(){
        $iosDialog2.fadeIn(200);
    });
});
$('.sheetIcon').click(function(){
    $('#answerSheet').toggle();
})
function snapshotExam(){
    if(examChangStatus.length != 0){
        var data = getSnapshot();
        $.post('?a=snapshotExam', data, function(data){
            if(data == 1){
                examChangStatus = [];
            }
        });
    }
}
function submitExam(){
    var examData = getSnapshot();
    var notice = '';
    $.post('?a=submitExam', examData, function(data){
        $('#afterSubmit').unbind();
        data = L.json(data);
        $('#iosDialog1').fadeOut(200);
        if(data.state == 'error'){
            notice = '交卷失败,请稍后再次提交';
            $('#afterSubmit').on('click',function(){
                $('#iosDialog2').fadeOut(200);
            });
        }
        else{
            notice = '<div style="padding-bottom: 2vh;line-height: 24px;height: 24px;">客观题成绩：<span style="font-size: 22px;font-weight:blod;">'+data.scores+'</span></div><div>主观成绩需人工评分</div>';
            $('#afterSubmit').on('click',function(){
                window.location.href = ROOT_URL+'/html5.php?a=examList';
            });
        }
        $('#point').html(notice);
        $('#iosDialog2').fadeIn(200);
    });
}
function getSnapshot(){
    var temp = {};
    var qus;
    for (var i = examChangStatus.length - 1; i >= 0; i--) {
        temp[app.items[examChangStatus[i]].questionId] = {};
        for (var k = app.items[examChangStatus[i]].option.length - 1; k >= 0; k--) {
            switch(app.items[examChangStatus[i]].type){
                case 'single':
                case 'true-false':
                    qus = {
                        'type' : 'radio',
                        'valueAttr' : '',
                        'value' : app.items[examChangStatus[i]].option[k].check
                    };
                    break;

                case 'muti':
                    qus = {
                        'type' : 'checkbox',
                        'valueAttr' : '',
                        'value' : app.items[examChangStatus[i]].option[k].check
                    };
                    break;

                case 'fill':
                    qus = {
                        'type' : 'input',
                        'value' : app.items[examChangStatus[i]].option[k].check
                    };
                    break;
                case 'short-answer':
                    qus = {
                        'value' : app.items[examChangStatus[i]].option[k].check
                    };
                    break;

                default :
                    qus = {};
            }
            temp[app.items[examChangStatus[i]].questionId][app.items[examChangStatus[i]].option[k]['optionId']] = {
                'params' : L.json(qus)
            }
        }
    }
    var data = {};
    data['data'] = temp;
    data['examId'] = app.info.examId;
    data['userId'] = app.info.userId;
    data['snapshotExpires'] = app.info.snapshotExpires;
    data['resultMateId'] = app.expand.resultMateId;
    return data;
}