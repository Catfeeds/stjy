<?php
namespace Admin\Action;
use Think\Action;
use Admin\Model;
class TableCountAction extends CommonAction{
	/**
     * 当前模块参数
     */
    public function _infoModule()
    {
        $this->class = M('class')->order('id desc')->select();
        $data = array('info' => array('name' => '校管家表格计算',
            'description' => ' 查看数据总表',
        ),
            'menu' => array(
                array('name' => '市场业绩表',
                    'url' => url('TableCount/scyjb_xq'),
                    'icon' => 'list',
                ),
                array('name' => '市场占有率',
                    'url' => url('TableCount/sczyl_xq'),
                    'icon' => 'list',
                ),
                array('name' => '新增明细',
                    'url' => url('TableCount/xzmx_xq'),
                    'icon' => 'list',
                ),
                array('name' => '减少明细',
                    'url' => url('TableCount/jsmx_xq'),
                    'icon' => 'list',
                ),
                array('name' => '经营数据表',
                    'url' => url('TableCount/jysjb_xq'),
                    'icon' => 'list',
                ),
                array('name' => '退费',
                    'url' => url('TableCount/tuifei_xq'),
                    'icon' => 'list',
                ),
            ),
           // 'add' => array(
           //     array('name' => '添加文章',
           //         'url' => url('Article/article'),
           //     ),
           // )
        );
        return $data;
    }

	//市场业绩总表
	public function index(){
        // 获取当前用户的角色
        $username = $_SESSION['username'];
        $uid = M('admin')->where('username ="'.$username.'"')->getField('id');
        $rid = M('role_user')->where('user_id ='.$uid)->getField('role_id');

        $data = M('sjzb'); // 实例化对象
        if($rid == 2){
            $count = $data->count();// 查询满足要求的总记录数
        }else if($rid == 4){
            $count = $data->where('status_cw is not null')->count();
        }else{
            $count = $data->where('status_fxfzr is not null')->count();
        }

        $Page = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        if($rid == 2){
            $list = $data->join('stjy_school ON stjy_sjzb.sid=stjy_school.id')->field('stjy_sjzb.*,stjy_school.name')->select();
        }else if($rid == 4){
            $list = $data->join('stjy_school ON stjy_sjzb.sid=stjy_school.id')->field('stjy_sjzb.*,stjy_school.name')->where('stjy_sjzb.status_cw is not null')->select();
        }else{
            $list = $data->join('stjy_school ON stjy_sjzb.sid=stjy_school.id')->field('stjy_sjzb.*,stjy_school.name')->where('stjy_sjzb.status_fxfzr is not null')->select();
        }

        // 获取表明与序号对应的一维数组
        $arr = $this->getTabelnames();

        $this->assign('list',$list);// 赋值数据集
        $this->assign('fpage',$show);// 赋值分页输出
        $this->assign('rid',$rid);// 赋值角色id
        $this->assign('arr',$arr);
        $this->adminDisplay();
	}

	//市场业绩表
	public function scyjb(){
        $data = M('qishu'); // 实例化对象
        $count = $data->count();// 查询满足要求的总记录数
        $Page = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $data->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('list',$list);// 赋值数据集
        $this->assign('fpage',$show);// 赋值分页输出
        $this->adminDisplay();
	}

	//市场业绩表详情
	public function scyjb_xq(){
	    $data = new \Admin\Action\CountScyjAction();
	    $list = $data->getScyjbData("201709","1");//获得统计数据
        dump($list);
        $this->assign("list",$list);
        $this->adminDisplay();
	}

	//市场占有率详情
	public function sczyl_xq(){
        $this->adminDisplay();
	}

	//新增明细详情
	public function xzmx_xq(){
        $this->adminDisplay();
	}

	//减少明细详情
	public function jsmx_xq(){
        $this->adminDisplay();
	}

	//经营数据表
	public function jysjb_xq(){
        $this->adminDisplay();
	}

	//经营数据表
	public function tuifei_xq(){
        $this->adminDisplay();
	}

}
?>
