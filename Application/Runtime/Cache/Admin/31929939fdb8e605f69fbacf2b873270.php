<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=0.5,user-scalable=no">
    <!-- 上述3个meta标签*必须*放在最前面，任何其他内容都*必须*跟随其后！ -->
    <title><?php echo ($config["site_title"]); ?> - 关于我们</title>
	<meta name="keywords" content="<?php echo ($config["site_keywords"]); ?>">
	<meta name="description" content="<?php echo ($config["site_description"]); ?>">
    <!-- Bootstrap -->
    <link href="/Public/Home/css/bootstrap.min.css" rel="stylesheet">
    <link href="/Public/Home/css/style.css" rel="stylesheet">
    <link href="/Public/Home/swiper/swiper-3.3.0.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Public/Home/css/common.css"/><!-- 基本样式 -->
    <link rel="stylesheet" href="/Public/Home/css/animate.min.css"/> <!-- 动画效果 -->

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>html,body{position:relative;height:100%;}body{background:#eee;font-family:Helvetica Neue,Helvetica,Arial,sans-serif;font-size:14px;color:#000;margin:0;padding:0;}.swiper-container{width:100%;height:100%;}.swiper-slide{text-align:center;font-size:18px;background:#fff;display:-webkit-box;display:-ms-flexbox;display:-webkit-flex;display:flex;-webkit-box-pack:center;-ms-flex-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-align:center;-ms-flex-align:center;-webkit-align-items:center;align-items:center;}.swiper-pagination-bullet{ width:20px;height:20px;text-align:center;line-height:20px;font-size:12px;color:#fff;opacity:1;background:#ff5a00}.swiper-pagination-bullet-active{color:#fff;background:#ac2829;}</style>
  </head>
  <body>
    <div class="w750">
    	<div class="head"></div>
        <div class="menu">
        	<ul>
            	<li><a href="/">首页</a></li>
                <li class="menu_line">|</li>
                <li><a href="/List/index/id/8">跟团旅游线</a></li>
                <li class="menu_line">|</li>
                <li <?php if($sid == 6): ?>class="active"<?php endif; ?>><a href="/Page/index/id/6">包车纯玩游西藏</a></li>
                <li class="menu_line">|</li>
                <li <?php if($sid == 1): ?>class="active"<?php endif; ?>><a href="/Page/index/id/1">关于我们</a></li>
            </ul>
        </div>
        <div class="clearfix"></div>
		<h3 class="text-center page-header"><?php echo ($page["title"]); ?></h3>
		<p class="text-center" style="color:#666;">作者：<?php echo ($page["editor"]); ?>&nbsp;&nbsp;&nbsp;&nbsp;时间：<?php echo (date("Y-m-d H:i:s",$page["time"])); ?>&nbsp;&nbsp;&nbsp;&nbsp;点击量：<?php echo ($page["click"]); ?></p>
        <div class="body_wrap">
            <?php echo ($page["content"]); ?>
        </div>
        
        
    </div>

    <div id="HBox">
            <ul class="list">
                <li>
                    <strong>姓 名  <font color="#ff0000">*</font></strong>
                    <div class="fl"><input type="text" name="nickname" value="" class="ipt nickname" /></div>
                </li>
                <li>
                    <strong>手 机 <font color="#ff0000">*</font></strong>
                    <div class="fl"><input type="text" name="phone" value="" class="ipt phone" /></div>
                </li>
                <li>
                    <strong>Q Q <font color="#ff0000">*</font></strong>
                    <div class="fl"><input type="text" name="qq" value="" class="ipt qq" /></div>
                </li>
                <li><input type="button" value="确认提交" class="submitBtn" /></li>
            </ul>
    </div><!-- HBox end --> 
	
    <script src="/Public/Home/js/jquery-1.12.0.min.js"></script>
    <script src="/Public/Home/js/jquery.hDialog.min.js"></script>
    <script src="/Public/Home/js/bootstrap.min.js"></script>
    <script src="/Public/Home/swiper/swiper-3.3.0.min.js"></script>
    <script src="/Public/Home/swiper/swiper-3.3.0.jquery.min.js"></script>
    <script>
		var swiper = new Swiper('.swiper-container', {
			pagination: '.swiper-pagination',
			paginationClickable: true,
			paginationBulletRender: function (index, className) {
				return '<span class="' + className + '">' + (index + 1) + '</span>';
			}
		});
    </script>
    <script>
        $(function(){
            var $el = $('.dialog');
            $el.hDialog();
            $('.demo6').hDialog({modalHide: false});
            //提交并验证表单
            $('.submitBtn').click(function() {
                var EmailReg = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/; //邮件正则
                var PhoneReg = /^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/ ; //手机正则
                var $nickname = $('.nickname');
                var $qq = $('.qq'); 
                var $phone = $('.phone');
                if($nickname.val() == ''){
                    $.tooltip('姓名还没填呢...'); $nickname.focus();
                }else if($phone.val() == ''){
                    $.tooltip('手机还没填呢...'); $phone.focus();
                }else if(!PhoneReg.test($phone.val())){
                    $.tooltip('手机格式错咯...'); $phone.focus();
                }else if($qq.val() == ''){
                    $.tooltip('QQ还没填呢...'); $qq.focus();
                }else{
                    $.ajax({
                        url : 'Common/Order',
                        type : 'POST',
                        dataType : 'json',
                        data : {nickname:$nickname.val(), phone:$phone.val(), qq:$qq.val() },
                        success : function (data) {
                            if (data.status == 1) {
                                $.tooltip('提交成功，2秒后自动关闭',2000,true);
                                setTimeout(function(){ 
                                    $el.hDialog('close',{box:'#HBox'}); 
                                },2000);
                            } else {
                                $.tooltip('提交失败，请联系客服，5秒后自动关闭',5000,true);
                                setTimeout(function(){ 
                                    //$el.hDialog('close',{box:'#HBox'},'http://smwell.sinaapp.com/');  //也可以加跳转链接哦~
                                    $el.hDialog('close',{box:'#HBox'}); 
                                },5000);
                            }
                        }  
                    });

                    
                }
            });
            
        });
    </script>
  </body>
</html>