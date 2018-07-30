layui.use(['form','layer'],function(){
    var form = layui.form
        layer = parent.layer === undefined ? layui.layer : top.layer,
        $ = layui.jquery;

    form.on("submit(addUser)",function(data){
        //弹出loading
        var index = top.layer.msg('数据提交中，请稍候',{icon: 16,time:false,shade:0.8});
        // 实际使用时的提交信息
         $.post(JSON_API + 'addUser',{
             id : $(".id").val(),
             username : $(".username").val(),  //登录名
             nickname : $(".nickname").val(),
             realName : $(".realName").val(),
             userEmail : $(".userEmail").val(),  //邮箱
             userPhone : $(".userPhone").val(),
             status : data.field.status,    //用户状态
             myself : $(".myself").val()    //用户简介
         },function(res){
             top.layer.close(index);
             if (res.success === '1') {
                 top.layer.msg("用户添加成功！");
                 setTimeout(function(){
                     top.layer.close(index);
                     top.layer.msg("用户添加成功！");
                     layer.closeAll("iframe");
                     //刷新父页面
                     parent.location.reload();
                 },500);
             } else {
                 top.layer.msg(res.error_msg);
             }
             return;
         }, 'json')
        return false;
    });

    //格式化时间
    function filterTime(val){
        if(val < 10){
            return "0" + val;
        }else{
            return val;
        }
    }
    //定时发布
    var time = new Date();
    var submitTime = time.getFullYear()+'-'+filterTime(time.getMonth()+1)+'-'+filterTime(time.getDate())+' '+filterTime(time.getHours())+':'+filterTime(time.getMinutes())+':'+filterTime(time.getSeconds());

})