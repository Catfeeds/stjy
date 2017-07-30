<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>网站后台管理系统</title>

    <!-- import css -->
    <link rel="stylesheet" type="text/css" href="/Public/Admin/css/pingtu.css" />
    <link rel="stylesheet" type="text/css" href="/Public/Admin/css/admin.css" />
	
    <!-- import js -->
    <script type="text/javascript" src="/Public/Admin/js/jquery.min.js"></script>
    <script type="text/javascript" src="/Public/Admin/js/pingtu.js"></script>
    <!-- 因IE8不支持CSS的媒体查询，因此需引入respond.js才能使其实现媒体查询功能。  -->
    <script type="text/javascript" src="/Public/Admin/js/respond.js"></script>

</head>
<body>
    
    <div class="one-head clearfix">
        <div class="one-logo">
            <a href="http://www.ddmall.com.cn" target="_blank">
                <img src="/Public/Admin/images/logo.png" alt="ddmall后台管理系统" />
            </a>
            <button class="button icon-navicon admin-nav-btn" data-target=".admin-nav"></button>
            <button class="button icon-navicon icon-ellipsis-v admin-menu-btn" data-target=".admin-menu"></button>
        </div>
        <div class="one-nav">
            <ul id="nav" class="nav  nav-navicon nav-inline admin-nav">
            </ul>
        </div>
        <div class="one-nav">
            <ul class="nav nav-navicon nav-inline admin-nav" id="nav">
            </ul>
            <ul class="nav nav-navicon nav-menu nav-inline admin-nav nav-tool">
               <!--<li> <a href="http://www.ddmall.com.cn/" target="_blank" class="icon-home"></a></li>-->
                <li> <a href="<?php echo U('/AdminUser/edit');?>" target="one-iframe" class="icon-user"></a></li>
                <li> <a href="<?php echo U('/Login/logout');?>" class="one-logout bg-red icon-power-off"></a></li>
            </ul>
        </div>
    </div>
    <div class="one-sidebar">
        <ul class="nav  nav-navicon admin-menu">
            <div id="nav-head" class="nav-head"> <?php echo ($infoModule["info"]["name"]); ?></div>
            <div id="menu">
            </div>
        </ul>
    </div>
    
    <div class="one-admin">
            <iframe id="one-iframe" name="one-iframe" class="one-iframe" src="" frameborder="0"></iframe>
    </div>

    <script type="text/javascript">
        //生成主菜单
        var data = <?php echo ($menuList); ?>;
        var topNav = '';
        for(var i in data){
            if(data[i]['menu'] != ''){
                topNav += '<li><a href="javascript:;" data="'+i+'" url="" class="icon-'+data[i].icon+'"> '+data[i].name+'</a></li>';
            }
        }
        $('#nav').html(topNav);
        
        //绑定导航连接
        $('#nav').on('click','a',function(){
            $('#nav-head').text($(this).text());
            var n = $(this).attr('data');
            var menu = data[n]['menu'];
            var menuHtml =  '';
            if(menu != ''){
                for(var i in menu){
                    menuHtml += '<li><a href="javascript:;" url="'+menu[i].url+'" class="icon-'+menu[i].icon+'"> '+menu[i].name+'</a></li>';
                }
            }
            $('#menu').html(menuHtml);
            //设置样式
            $('#nav li').removeClass('active');
            $(this).parent('li').addClass('active');
            //打开菜单
            $('#menu a:first').click();
        });
        //绑定菜单连接
        $('#menu').on('click','a',function(){
            var url = $(this).attr('url');
            $('.one-iframe').attr('src',url);
            //设置样式
            $('#menu li').removeClass('active');
            $(this).parent('li').addClass('active');
        });
        $('#nav a:first').click();

    </script>

</body>
</html>