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
                array('name' => '业绩总表',
                    'url' => url('TableCount/index'),
                    'icon' => 'list',
                ),
                array('name' => '市场业绩表',
                    'url' => url('TableCount/yejilist/tid/8'),
                    'icon' => 'list',
                ),
                array('name' => '市场占有率',
                    'url' => url('TableCount/yejilist/tid/9'),
                    'icon' => 'list',
                ),
                array('name' => '新增明细',
                    'url' => url('TableCount/yejilist/tid/10'),
                    'icon' => 'list',
                ),
                array('name' => '减少明细',
                    'url' => url('TableCount/yejilist/tid/11'),
                    'icon' => 'list',
                ),
                array('name' => '经营数据表',
                    'url' => url('TableCount/yejilist/tid/12'),
                    'icon' => 'list',
                ),
                array('name' => '退费',
                    'url' => url('TableCount/yejilist/tid/13'),
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
        }else if($_SESSION['superadmin'] == true){
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
        }else if($_SESSION['superadmin'] == true){
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

    public function yejilist(){
        $data = M('sjzb')->field('qishu,sid')->where('status_xz = 2')->select();
        $count = M('sjzb')->where('status_xz = 2')->count();// 查询满足要求的总记录数
        $Page = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = M('sjzb')->join('LEFT JOIN stjy_school ON stjy_sjzb.sid = stjy_school.id')->field('stjy_sjzb.*,stjy_school.name as school_name')->where('stjy_sjzb.status_xz = 2')->order('stjy_sjzb.id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as $k=>$v){
            $list[$k]['tid'] = $_GET['tid'];
            $where['tid'] = $_GET['tid'];
            $where['sid'] = $v['sid'];
            $where['qishu'] = $v['qishu'];
            $newid = M('qishu_history')->field('id')->where($where)->getField('id');
            $list[$k]['id'] = is_null($newid)?'new':$newid;
            $temp = M('table_name')->where('id = '.$_GET['tid'])->find();
            $list[$k]['table_name'] = $temp['table_name'];
            $list[$k]['name'] = $temp['name'];

        }
        var_dump($list);
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
	public function sczylb_xq(){
        $qishu = $_GET['qishu'];
        $sid = $_GET['sid'];
        $data = new \Admin\Action\CountSczylAction();
        $list = $data->getSczylbData($qishu,$sid);//获得统计数据
        var_dump($list);
        $this->assign("list",$list);
        // $this->adminDisplay();
	}

	//新增明细详情
	public function xzmxb_xq(){
        $this->adminDisplay();
	}

	//减少明细详情
	public function jsmxb_xq(){
        $this->adminDisplay();
	}

	//经营数据表
	public function jysjb_xq(){
        $this->adminDisplay();
	}

	//经营数据表
	public function tfb_xq(){
        $this->adminDisplay();
	}

}
?>
