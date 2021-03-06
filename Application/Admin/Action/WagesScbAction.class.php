<?php
namespace Admin\Action;
use Think\Action;
use Admin\Model;
class WagesScbAction extends WagesCommonAction{
	/**
     * 当前模块参数
     */
    public function _infoModule()
    {
        $this->class = M('class')->order('id desc')->select();
        $data = array('info' => array('name' => '市场部工资表详情',
            'description' => '市场部工资表',
        ),
            'menu' => array(
                array('name' => '工资表总表',
                    'url' => url('Wages/index'),
                    'icon' => 'list',
                ),
                array('name' => '行政部工资表',
                    'url' => url('/wages/tableList/table/Xzb'),
                    'icon' => 'list',
                ),
                array('name' => '教学部工资表',
                    'url' => url('/wages/tableList/table/Jxb'),
                    'icon' => 'list',
                ),
                array('name' => '市场部工资表',
                    'url' => url('/wages/tableList/table/Scb'),
                    'icon' => 'list',
                ),
                array('name' => '总监工资表',
                    'url' => url('/wages/tableList/table/Zj'),
                    'icon' => 'list',
                ),
            ),
        );
        return $data;
    }

    public function index(){
        //期数
        $qishu = $_GET['qishu']?$_GET['qishu']:'201808';
        //学校id
        $sid = $_GET['sid']?$_GET['sid']:1;
        $suoshudd = $this->getQishuId($qishu,$sid,18);
        $yuefen = substr($qishu,4,2).'月';
        $ambbz = M('ambbz')->getField('gangwei,danjia');
        $school_name = M('school')->where(array('id'=>$sid))->getField('name');
        $heji = array();//合计
        $fujia = array();//附加表
        $fujia_id = $this->getQishuId($qishu,$sid,39);
        if ($suoshudd){
            $list = M('xzbgz')->where("suoshudd='$suoshudd'")->order('id')->select();
            $heji = $list[ count($list) - 1];
            unset($list[ count($list) - 1]);
            $fujia = M('fjb')->where("suoshudd='$fujia_id'")->getField('field,value');
        }else{
            //实时计算
            $list = M('rycb as rs')
                    ->field('rs.bumen,rs.zhiwu as zhiwei,rs.gangweilx,rs.leixing as zaizhizt,rs.xingming,rs.ruzhirq as ruzhisj,rs.erjibm,yj.edu,yj.jingrentou,yj.yiqims,yj.jiubayyqms')
                    ->join('stjy_scyjb as yj on yj.xingming=rs.xingming')
                    ->where(array('rs.xiaoqu'=>$school_name,'rs.bumen'=>'市场部'))
                    ->select();

            foreach($list as $key=>&$val){
                $val['xuhao'] = $key+1;
                $val['yuefen'] = $yuefen;
                $val['fenxiao'] = $school_name;
                $val['gongzuonx'] = intval(( time() - strtotime($val['ruzhisj']) ) / (365 * 24 * 60 * 60));
                $val['yingchuqts'] = 30;// 应出勤天数 (写死)
                $val['shijicqts'] = 30;// 实际出勤天数 (写死)
                $val['xuexikbj'] = '';//学习卡本金（补逻辑）
            }
            $fujia['jibie'] = M('zxmc')->where(array('zhongxin'=>$school_name))->getField('jibie');
        }
        $this->assign('ambbz',$ambbz);
        $this->assign('fujia',$fujia);
        $this->assign('heji',$heji);
        $this->assign('school_name',$school_name);
        $this->assign('nianyue',substr($qishu,0,4).'年'.substr($qishu,4,2).'月');
        $this->assign('qishu',$qishu);
        $this->assign('sid',$sid);
        $this->assign("list",$list);
        $this->adminDisplay();
    }

    public function index_bak(){
        //期数
        $qishu = $_GET['qishu']?$_GET['qishu']:'201709';
        //学校id
        $sid = $_GET['sid']?$_GET['sid']:1;
        $action = $_GET['action']?$_GET['action']:'';
        // dump($action);die;
        $school = M("school")->where("id = $sid")->find();
        $scb_list = M("scbgzb")->where("qishu = '".$qishu."' and sid = $sid and istijiao = 0")->select();
        // dump($scb_list);die;
        $table = M("scbgzb")->query("select column_name as fieldname,column_comment as beizhu from Information_schema.columns WHERE table_Name='stjy_scbgzb'");
        //查询出课程列表
        $kecheng = M("kecheng")->order("paixu")->select();
        $kecheng_arr = array();
        foreach ($kecheng as $k => $v){
            $kecheng_arr[$v['name']] = $v['ticheng']*100;
        }
        //如果业绩表是修改操作，就从业绩表里取数，否则实时运算
        if(!empty($scb_list)){
            $temp = $scb_list;
            $list = array();
            $extra = array('shijichuqints','xinguwenbdrt','canzhaobdx','gongzuoliang','gudinghj','tuanduiwd','dituijrtjx','weixinjx','jiazhanghuixcbjl','weijinbankhjx','shangkeksjx','jidizskjx','yuedusfjx','beizhu');
            $i = 1;
            foreach($temp as $k=>$v){
                foreach($v as $k2=>$v2){
                    if(in_array($k2,$extra)){
                        $list[$k][$k2]['value'] = "<input class='input do_enter' type='text' name='".$k2."' value='".$v2."'>";
                    }else{
                        if($k2 == 'kechengyeji'){
                            $list[$k]['kechengyj'] = $this->object2array(json_decode($v2));
                        }elseif($k2 == 'id'){
                            $list[$k][$k2]['value'] = $i;
                        }else{
                            $list[$k][$k2]['value'] = $v2;
                        }
                        
                    }
                }
                $i++;
            }
        }else{
            //取当期的市场业绩表信息
            $suoshudingdan = M("qishu_history")->where("tid = 8 and sid = $sid and qishu = $qishu")->getField("id");
            $scyj = M("scyjb")->where("suoshudd = $suoshudingdan")->select();
            //查找本校区市场部中没有业绩的人员
            $meiyeji_arr = $this->getScbrenyuan($sid,$scyj);
            $new_arr = array_merge($scyj,$meiyeji_arr);
            $list = array();
            $i = 1;
            foreach ($new_arr as $sk=>$sc){
                if($sc['xingming'] == "合计"){
                    continue;
                }
                $list[$sk]['id']['value'] = $i;  //序号
                $list[$sk]['xingming']['value'] = $sc['xingming'];   //姓名
                $list[$sk]['yuefen']['value'] = mb_substr($qishu,4).'月';   //月份
                $list[$sk]['fenxiao']['value'] = $school['name'];   //分校名称
                $user = M("renshi")->where("xingming = '".$sc['xingming']."' and sid = $sid")->find();  //在人事资料里取对应信息
                $list[$sk]['bumen']['value'] = $user['bumen'];   //部门名称
                $list[$sk]['erjibm']['value'] = $user['bumen2'];   //二级部门
                $list[$sk]['gangweijb']['value'] = $user['gangweilx'];   //岗位类型
                $list[$sk]['zhiwei']['value'] = $user['zhiwu'];   //岗位类型

                $list[$sk]['bumen']['value'] = $user["bumen"];   //部门
                $list[$sk]['erjibm']['value'] = $user["bumen2"];   //二级部门
                $list[$sk]['gangweijb']['value'] = '';   //岗位级别
                $list[$sk]['zhiwei']['value'] = $user["zhiwu"];  //职位
                if($user['gangweilx'] == 1){
                    $list[$sk]['gangweilx']['value'] = '全职';   //岗位类型
                }elseif($user['gangweilx'] == 2){
                    $list[$sk]['gangweilx']['value'] = '兼职';   //岗位类型
                }else{
                    $list[$sk]['gangweilx']['value'] = '兼职';   //岗位类型
                }
                $list[$sk]['gongzuonx']['value'] = empty($user["ruzhirq"])?'0':floor((time()-strtotime($user['ruzhirq']))/(365*86400))."年";  //工作年限
                $list[$sk]['ruzhisj']['value'] = $user["ruzhirq"];  //入职日期
                $list[$sk]['yingchuqingts']['value'] = date('t', strtotime($qishu."01")); //应出勤天数:返回当期月份的天数
                $list[$sk]['shijichuqints']['value'] = "<input class='input do_enter' type='text' name='shijichuqints' value='0'>";  //实际出勤天数
                $list[$sk]['yuedujingjx']['value'] = $school["ydjjx"];  //月度警戒线
                $list[$sk]['yueduchaorts']['value'] = $school["ydcrtx"];  //月度超人头数
                $list[$sk]['yueduchaocrts']['value'] = $school["ydccrtx"];  //月度超超人头数
                $edu = $list[$sk]['edu']['value'] = 0+M("xxkedb")->where("suoshudd = ".$suoshudingdan." and xingming = '".$sc['xingming']."'")->getField("edu");  //查询出当前期数，此人的学习卡额度信息
                $list[$sk]['rentoushu']['value'] = 0+$sc["rentoushu"];  //人头数
                $jrt = $list[$sk]['jingrentou']['value'] = 0+$sc["jingrentou"];  //净人头数
                //按照课程拼接业绩
                $list[$sk]['kechengyj'] = $this->object2array(json_decode($sc['kechengyj']));
                $list[$sk]['hejiyye']['value'] = 0+$sc["hejiyye"];  //合计营业额
                $list[$sk]['huiyuanldxyye']['value'] = 0+$sc["huiyuanldxyye"];  //会员老带新营业额
                $list[$sk]['xinguwenbdrt']['value'] = "<input class='input do_enter' type='text' name='xinguwenbdrt' value='0'>";  //新顾问保底人头
                $list[$sk]['canzhaobdx']['value'] = "<input class='input do_enter' type='text' name='canzhaobdx' value='0'>";  //参考保底线
                $list[$sk]['gongzuoliang']['value'] = "<input class='input do_enter' type='text' name='gongzuoliang' value='0'>";  //工作量
                $list[$sk]['gudinghj']['value'] = "<input class='input do_enter' type='text' name='gudinghj' value='0'>";  //固定合计
                if(empty($sc['kechengyj'])){
                    $list[$sk]['xuexikajs']['value'] = 0;  //学习卡结算
                }else{
                    $list[$sk]['xuexikajs']['value'] = 0+$this->getXxkjs($sc['kechengyj'],$jrt,$edu,$kecheng_arr,$sc['huiyuanldxyye']);  //学习卡结算
                }
                $list[$sk]['jixiaojj']['value'] = 0+$this->getJxjj($sc['kechengyj'],$kecheng_arr);  //绩效奖金
                $list[$sk]['xiaozhangtdtc']['value'] = 0;  //校长团队提成
                $list[$sk]['tuanduiwd']['value'] = "<input class='input do_enter' type='text' name='tuanduiwd' value='0'>";  //团队稳定
                $list[$sk]['dituijrtjx']['value'] = "<input class='input do_enter' type='text' name='dituijrtjx' value='0'>";  //地推净人头绩效
                $list[$sk]['weixinjx']['value'] = "<input class='input do_enter' type='text' name='weixinjx' value='0'>";  //微信绩效
                $list[$sk]['jiazhanghuixcbjl']['value'] = "<input class='input do_enter' type='text' name='jiazhanghuixcbjl' value='0'>";  //家长会报名现场奖励
                $list[$sk]['liushijtfjxjs']['value'] = $this->getTuifei($qishu,$sid,13,$sc['xingming']);  //流失及退费绩效结算
                $list[$sk]['weijinbankhjx']['value'] = "<input class='input do_enter' type='text' name='weijinbankhjx' value='0'>";  //未进班考核绩效
                $list[$sk]['shangkeksjx']['value'] = "<input class='input do_enter' type='text' name='shangkeksjx' value='0'>";  //上课课时绩效
                $list[$sk]['jidizskjx']['value'] = "<input class='input do_enter' type='text' name='jidizskjx' value='0'>";  //基地招生课绩效
                $gerejce = M("gjjmxb")->where("qishu = '".$qishu."' and zhengjianhao = '".$user['shenfenzhm']."'")->getField("gerenjce");
                $list[$sk]['gongjijin']['value'] = $gerejce?$gerejce:0;  //公积金
                $geresbe = M("sbmxb")->where("qishu = '".$qishu."' and shenfenzhm = '".$user['shenfenzhm']."'")->getField("gerenhj");
                $list[$sk]['gerensb']['value'] = $geresbe?$geresbe:0;  //个人社保
                $list[$sk]['yuedusfjbgz']['value'] = 1895;  //月度实发基本工资
                $list[$sk]['yuedusfjx']['value'] = "<input class='input do_enter' type='text' name='yuedusfjx' value='0'>";  //月度实发绩效
                $list[$sk]['beizhu']['value'] = "<input class='input do_enter' type='text' name='beizhu' value=''>";  //备注
                $i++;
            }
        }
        $this->assign("kecheng",$kecheng);
        $this->assign("qishu",$qishu);
        $this->assign("sid",$sid);
        $this->assign("table",$table);
        $this->assign("list",$list);
        $this->adminDisplay();
    }

    //查找本校区市场部中没有业绩的人员
    public function getScbrenyuan($sid,$scyj){
        $scbry_list = M("renshi")->field("xingming")->where("sid = ".$sid)->select();
        $suoyou = array();
        foreach ($scbry_list as $k=>$v){
            $suoyou[] = $v["xingming"];
        }
        $youyeji = array();
        foreach ($scyj as $k=>$v){
            $youyeji[] = $v['xingming'];
        }
        $arr = array_diff($suoyou,$youyeji);
        $meiyeji = array();
        foreach ($arr as $k=>$v){
            $meiyeji[]["xingming"] = $v;
        }
        return $meiyeji;
    }

    public function save(){
        // 还需要查询该状态是否已经锁定,如果锁定了就返回不成功
        $qishu = $_POST["qishu"];
        $sid = $_POST["sid"];
        $res = M("scbgzb")->where("qishu ='".$qishu."' and sid = $sid")->delete();
        $obj = json_decode($_POST['arr']);
        $n = count($obj);
        $data = array();
        foreach ($obj as $k => $v){
            foreach ($v as $m => $n){
                if($m == 'id')
                    continue;
                if($m == 'kechengyeji'){
                    $data[$k]['kechengyeji'] = json_encode($n);
                }else{
                    $data[$k][$m] = $n;
                }
            }
            $data[$k]['qishu'] = $qishu;
            $data[$k]['sid'] = $sid;
            // $data[$k]['addtime'] = null;
            $data[$k]['addtime'] = date('Y-m-d H:i:s',time());
            // dump($data);die;
        }
        
        $res = M("scbgzb")->addAll($data);
        if($res){
            $this->ajaxReturn(true);
        }else{
            $this->ajaxReturn(false);
        }
    }
}

?>