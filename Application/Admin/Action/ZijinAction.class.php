<?php
namespace Admin\Action;
use Think\Action;
use Admin\Model;
class ZijinAction extends CommonAction{
    /**
     * 当前模块参数
     */
    public function _infoModule()
    {
        $this->class=M('class')->order('id desc')->select();
        $data=array('info'=> array('name'=> '资金管理',
            'description'=> '资金管理',
        ),
            // 'menu'=> array(
            //     array('name'=> '人事列表',
            //         'url'=> url('Renshi/renshi'),
            //         'icon'=> 'list',
            //     ),

            // ),
            // 'add'=> array(
            //     array('name'=> '添加人事',
            //         'url'=> url('Renshi/renshi_add'),
            //     ),
            // ),
        );
        return $data;
    }


    /*****************************************************************************
     *
     *	收款汇总信息
     *	2018-01-22
     *
     *
     *****************************************************************************/
    public function getSchoolId(){

        $username=$_SESSION['username'];
        $temp=M('admin')->where('username="'.$username.'"')->find();

        $school_id=explode(",",$temp['school_id']);
        //dump($school_id);
        return $school_id;
    }

    public function getRoleId(){

        $username=$_SESSION['username'];
        $temp=M('admin')->where('username="'.$username.'"')->find();

        $uid=$temp['id'];
        $rid=M('role_user')->where('user_id='.$uid)->getField('role_id');

        return $rid;
    }



    public function zijinListQishu(){
        $count=M('qishu')->where('isUse',1)->order('name desc,id desc')->count();
        $Page=new \Think\Page($count,15);
        $show=$Page->show();

        $list=M('qishu')->where('isUse',1)->order('name desc,id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

        $this->assign('list',$list);
        $this->assign('fpage',$show);
        $this->adminDisplay();

    }


    public function zijinListDay(){
        $qishu=$_GET['qishu'];
        if(intval($qishu)>0){

            $strDate=$qishu."01";
            $intDays=date('t', strtotime($strDate));

            $this->assign('qishu',$qishu);
            $this->assign('intDays',$intDays);
            $this->adminDisplay();
        }
    }

    public function zijinHuizongQishu(){
        $intQishu=intval($_GET['qishu']);

        $intRoleID=$this->getRoleId();
        $arrSchoolID=$this->getSchoolId();

        if($intRoleID==2 || $intRoleID==3){
            $strSchoolID=implode(",",$arrSchoolID);
            $strSQL_School=" and id in (".$strSchoolID.")";
            $strSQL=" and sid in (".$strSchoolID.")";
        }elseif($intRoleID==4){
            $strSQL="";
        }else{
            $this->error("非法操作，请返回！");
        }

        if($intQishu>0){

            $listAudit=M('pczb')->where(" qishu='".$intQishu."' and status_cw=2 ".$strSQL." " )->select();
            foreach($listAudit as $v){
                $arrAuditPici[]=$v['pici'];
            }
            if(is_array($arrAuditPici) && !empty($arrAuditPici))
                $strPiciSQL=" and addTime in (".implode(",",$arrAuditPici).") ";
            else
                $this->error("此期数没有被审核过的批次数据，请检查！");


            $listSF=M('shoufei_info')->where(" intQiShu='".$intQishu."' ".$strPiciSQL." ".$strSQL." " )->order('id asc')->select();

            foreach($listSF as $key=> $val)
            {
                if(!in_array($val["sid"],$arrSid))
                    $listSchool[$key]=M('school')->where('id="'.$val["sid"].'"')->find();

                if(!in_array($val["aid"],$arrAid))
                    $listAccount[$key]=M('account_detail')->where('id="'.$val["aid"].'"')->find();

                $arrSid[$key]=$val["sid"];
                $arrAid[$key]=$val["aid"];
            }
            foreach($listSchool as $Key=> $Value){
                foreach($listAccount as $KeyA=> $ValueA){
                    $listQishu_HZ[$Value["id"]][$ValueA["id"]]=M('shoufei_info')->field(" sum(`douSF`) as `douSF_qs`, sum(`douSFHJ`) as `douSFHJ_qs`, sum(`douXJSR`) as `douXJSR_qs`, sum(`douXXK`) as `douXXK_qs`, sum(`douRZK`) as `douRZK_qs`, sum(`douQTSR`) as `douQTSR_qs`, sum(`douSXF`) as `douSXF_qs` ")->where(" intQiShu='".$intQishu."' ".$strPiciSQL." and sid='".$Value["id"]."' and aid='".$ValueA["id"]."' ".$strSQL." ")->find();
                }
                $listQishu_HZ[$Value["id"]]["chayi"]=M("shoufei_chayi")->field(" sum(`douXGJ`) as `douXGJ_qs`, sum(`douCY`) as `douCY_qs` ")->where(" intQiShu='".$intQishu."' ".$strPiciSQL." and sid='".$Value["id"]."' ".$strSQL." ")->find();
                $listQishu_HZ[$Value["id"]]["chayi"]["dousfhj_qs"]=M("shoufei_info")->where(" intQiShu='".intval($intQishu)."' ".$strPiciSQL." and sid='".intval($Value["id"])."' ".$strSQL." ")->sum('douSFHJ');
            }

            foreach($listAccount as $K=> $Val){
                $listQishu_HZ["total"][$Val["id"]]=M("shoufei_info")->where(" intQiShu='".intval($intQishu)."' ".$strPiciSQL." and aid='".intval($Val["id"])."' ".$strSQL." ")->field(" sum(`douSF`) as `douSF_total`, sum(`douSFHJ`) as `douSFHJ_total`, sum(`douXJSR`) as `douXJSR_total`, sum(`douXXK`) as `douXXK_total`, sum(`douRZK`) as `douRZK_total`, sum(`douQTSR`) as `douQTSR_total`, sum(`douSXF`) as `douSXF_total` ")->find();
            }

            $listQishu_HZ["total"]["chayi"]["douSFHJ_total"]=M("shoufei_info")->where(" intQiShu='".intval($intQishu)."' ".$strPiciSQL." ".$strSQL." ")->sum("douSFHJ");
            $listQishu_HZ["total"]["chayi"]["douXGJ_total"]=M("shoufei_chayi")->where(" intQiShu='".intval($intQishu)."' ".$strPiciSQL." ".$strSQL." ")->sum("douXGJ");
            $listQishu_HZ["total"]["chayi"]["douCY_total"]=M("shoufei_chayi")->where(" intQiShu='".intval($intQishu)."' ".$strPiciSQL." ".$strSQL." ")->sum("douCY");

            //dump($listQishu_HZ);
        }else{
            $this->error("数据操作有误，请检查！");
        }

        $this->assign('listSchool',$listSchool);
        $this->assign('listAccount',$listAccount);

        $this->assign('listQishu_HZ',$listQishu_HZ);
        $this->adminDisplay("index2");
    }


    public function zijinHuizongPici(){
        $intQishu=intval($_GET['qishu']);
        $intPici=intval($_GET['pici']);

        $intRoleID=$this->getRoleId();
        $arrSchoolID=$this->getSchoolId();

        if($intRoleID==2 || $intRoleID==3){
            $strSchoolID=implode(",",$arrSchoolID);
            $strSQL_School=" and id in (".$strSchoolID.")";
            $strSQL=" and sid in (".$strSchoolID.")";
        }elseif($intRoleID==4){
            $strSQL="";
        }else{
            $this->error("非法操作，请返回！");
        }

        if(intval($intQishu)>0 && intval($intQishu)>0){
            $listSF=M('shoufei_info')->where("addTime='".intval($intPici)."' and intQiShu='".intval($intQishu)."' ".$strSQL." " )->order('id asc')->select();

            foreach($listSF as $key=> $val)
            {
                if(!in_array($val["sid"],$arrSid))
                    $listSchool[$key]=M('school')->where('id="'.$val["sid"].'"')->find();

                if(!in_array($val["aid"],$arrAid))
                    $listAccount[$key]=M('account_detail')->where('id="'.$val["aid"].'"')->find();

                $arrSid[$key]=$val["sid"];
                $arrAid[$key]=$val["aid"];
            }
            foreach($listSchool as $Key=> $Value){
                foreach($listAccount as $KeyA=> $ValueA){
                    $listQishu_HZ[$Value["id"]][$ValueA["id"]]=M('shoufei_info')->field(" sum(`douSF`) as `douSF_qs`, sum(`douSFHJ`) as `douSFHJ_qs`, sum(`douXJSR`) as `douXJSR_qs`, sum(`douXXK`) as `douXXK_qs`, sum(`douRZK`) as `douRZK_qs`, sum(`douQTSR`) as `douQTSR_qs`, sum(`douSXF`) as `douSXF_qs` ")->where("addTime='".intval($intPici)."' and intQiShu='".intval($intQishu)."' and sid='".$Value["id"]."' and aid='".$ValueA["id"]."' ".$strSQL." ")->find();
                }
                $listQishu_HZ[$Value["id"]]["chayi"]=M("shoufei_chayi")->field(" sum(`douXGJ`) as `douXGJ_qs`, sum(`douCY`) as `douCY_qs` ")->where("addTime='".intval($intPici)."' and intQiShu='".intval($intQishu)."' and sid='".$Value["id"]."' ".$strSQL." ")->find();
                $listQishu_HZ[$Value["id"]]["chayi"]["dousfhj_qs"]=M("shoufei_info")->where("addTime='".intval($intPici)."' and intQiShu='".intval($intQishu)."' and sid='".intval($Value["id"])."' ".$strSQL." ")->sum('douSFHJ');
            }


            foreach($listAccount as $K=> $Val){
                $listQishu_HZ["total"][$Val["id"]]=M("shoufei_info")->where("addTime='".intval($intPici)."' and intQiShu='".intval($intQishu)."' and aid='".intval($Val["id"])."' ".$strSQL." ")->field(" sum(`douSF`) as `douSF_total`, sum(`douSFHJ`) as `douSFHJ_total`, sum(`douXJSR`) as `douXJSR_total`, sum(`douXXK`) as `douXXK_total`, sum(`douRZK`) as `douRZK_total`, sum(`douQTSR`) as `douQTSR_total`, sum(`douSXF`) as `douSXF_total` ")->find();
            }

            $listQishu_HZ["total"]["chayi"]["douSFHJ_total"]=M("shoufei_info")->where("addTime='".intval($intPici)."' and intQiShu='".intval($intQishu)."' ".$strSQL." ")->sum("douSFHJ");
            $listQishu_HZ["total"]["chayi"]["douXGJ_total"]=M("shoufei_chayi")->where("addTime='".intval($intPici)."' and intQiShu='".intval($intQishu)."' ".$strSQL." ")->sum("douXGJ");
            $listQishu_HZ["total"]["chayi"]["douCY_total"]=M("shoufei_chayi")->where("addTime='".intval($intPici)."' and intQiShu='".intval($intQishu)."' ".$strSQL." ")->sum("douCY");

            //dump($listQishu_HZ);

        }else{
            $this->error("数据操作有误，请检查！");
        }



        $this->assign('listSchool',$listSchool);
        $this->assign('listAccount',$listAccount);

        $this->assign('qishu',$intQishu);
        $this->assign('pici',$intPici);

        $this->assign('listQishu_HZ',$listQishu_HZ);
        $this->adminDisplay("index1");

    }



    public function zijinHuizong(){

        $strQishu=$_GET['qishu'];
        $strPici=$_GET['pici'];

        //测试数据-参数设置
        //$strQishu="201710";
        //$strPici="8";

        $intRoleID=$this->getRoleId();
        $arrSchoolID=$this->getSchoolId();

        if($intRoleID==2 || $intRoleID==3){
            $strSchoolID=implode(",",$arrSchoolID);
            $strSQL_School=" and id in (".$strSchoolID.")";
            $strSQL=" and sid in (".$strSchoolID.")";

            $listSchool_all=M('school')->where('isuse',1)->order('id desc')->select();
            foreach($listSchool_all as $vs){
                if(in_array($vs['id'],$arrSchoolID)){
                    $strSchoolName .='"'.$vs['subname'].'",';
                }
            }
            $strSchoolName=substr($strSchoolName,0,-1);
            $strSQL_lkl=" and strMerName in (".$strSchoolName.")";
            $strSQL_sqb=" and strSchoolName in (".$strSchoolName.")";
        }elseif($intRoleID==4){
            $strSQL="";
        }else{
            $this->error("非法操作，请返回！");
        }

        $arrRs=M('pczb')->where("qishu='".$strQishu."' and pici='".$strPici."' ")->find();
        if(intval($strQishu)>0 && intval($strPici)>0 && $arrRs["status_cw"]!=2){
            $listSF=M('shoufei_info')->where("addTime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' ".$strSQL." " )->order('id asc')->select();
            $isReset=M('shoufei_info')->where("addTime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' ".$strSQL." " )->sum('douSF');
        }else{
            $this->error("数据操作有误，请检查！");
        }

        //if(is_array($listSF) && !empty($listSF) && abs($isReset)>0)
        if(abs($isReset)>0)
        {
            foreach($listSF as $key=> $val){
                if(!in_array($val["sid"],$arrSid))
                    $listSchool[$key]=M('school')->where('id="'.$val["sid"].'"')->find();

                if(!in_array($val["aid"],$arrAid))
                    $listAccount[$key]=M('account_detail')->where('id="'.$val["aid"].'"')->find();

                $arrSid[$key]=$val["sid"];
                $arrAid[$key]=$val["aid"];
            }
            foreach($listSchool as $Key=> $Value){
                foreach($listAccount as $KeyA=> $ValueA){
                    $listHZBJ[$Value["id"]][$ValueA["id"]]=M('shoufei_info')->where("addTime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' and sid='".$Value["id"]."' and aid='".$ValueA["id"]."' ".$strSQL." ")->find();
                }
            }
        }
        else
        {
            $listSchool=M('school')->where("isuse=1 ")->order('id desc')->select();
            $listAccount=M('account_detail')->where('isUse',1)->order('intOrderID asc,id desc')->select();

            $listLklb=M('lklb')->where("intqishu='".intval($strQishu)."' and addtime='".intval($strPici)."' ")->select();
            $listSqbb=M('sqbb')->where("intqishu='".intval($strQishu)."' and addtime='".intval($strPici)."' ")->select();

            //if(intval($strQishu)>0 && intval($strPici)>0 &&  ( (is_array($listLklb) && !empty($listLklb)) || (is_array($listSqbb) && !empty($listSqbb)) ) )
            if(intval($strQishu)>0 && intval($strPici)>0 &&  ( (is_array($listLklb) && !empty($listLklb)) && (is_array($listSqbb) && !empty($listSqbb)) ) )
            {
                foreach($listSchool as $Key=> $Value){
                    foreach($listAccount as $KeyA=> $ValueA){

                        $lkl_SF=M('lklb')->where("strmername='".$Value["subname"]."' and intqishu='".intval($strQishu)."' and addtime='".intval($strPici)."' and straccountname='".$ValueA["strname"]."' and straccountbank='".$ValueA["strbankname"]."'")->sum('douHZBJ');
                        $lkl_SXF=M('lklb')->where("strmername='".$Value["subname"]."' and intqishu='".intval($strQishu)."' and addtime='".intval($strPici)."' and straccountname='".$ValueA["strname"]."' and straccountbank='".$ValueA["strbankname"]."'")->sum('douSXF');

                        $sqb_SF=M('sqbb')->where("strSchoolName='".$Value["subname"]."' and intqishu='".intval($strQishu)."' and addtime='".intval($strPici)."' and strMerName='".$ValueA["strbankname"]."' and strMerID='".$ValueA["strbankaccount"]."'")->sum('douSSJE');
                        $sqb_SXF=M('sqbb')->where("strSchoolName='".$Value["subname"]."' and intqishu='".intval($strQishu)."' and addtime='".intval($strPici)."' and strMerName='".$ValueA["strbankname"]."' and strMerID='".$ValueA["strbankaccount"]."'")->sum('douSXF');

                        $sfTotal=$lkl_SF + $sqb_SF;
                        $sxfTotal=$lkl_SXF + $sqb_SXF;

                        //echo  M('lklb')->getLastSql();
                        //echo "<br>";

                        if(is_array($listSF) && !empty($listSF)){
                            $isSchoolRs=M('shoufei_info')->where(" sid='".intval($Value["id"])."' and aid='".$ValueA["id"]."' ")->select();
                            if($isSchoolRs){
                                $rs_SF=M('shoufei_info')->where("intqishu='".intval($strQishu)."' and addtime='".intval($strPici)."' and sid='".$Value["id"]."' and aid='".$ValueA["id"]."' ")->find();

                                //dousfhj=dousf-douxxk-dourzk-douqtsr
                                $arrData["douSF"]=floatval($sfTotal);
                                $arrData["douSFHJ"]=floatval($sfTotal) - floatval($rs_SF["douxxk"]) - floatval($rs_SF["dourzk"]) - floatval($rs_SF["douqtsr"]);
                                $arrData["douSXF"]=floatval($sxfTotal);
                                M('shoufei_info')->where("intqishu='".intval($strQishu)."' and addtime='".intval($strPici)."' and sid='".$Value["id"]."' and aid='".$ValueA["id"]."' ")->save($arrData);
//echo M('shoufei_info')->getLastSql()."<br/>";

                                $douSFHJ_total=M("shoufei_info")->where("addtime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' and sid='".intval($Value["id"])."' ")->sum('douSFHJ');
                                $douXJSR_total=M("shoufei_info")->where("addtime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' and sid='".intval($Value["id"])."' ")->sum('douXJSR');
                                $arrCY=M("shoufei_chayi")->where("addtime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' and sid='".intval($Value["id"])."' ")->find();
                                $douCY=number_format((floatval($douSFHJ_total)*100 + floatval($douXJSR_total) -floatval($arrCY["douxgj"]))/100,2,".","");
                                $arrDataCY["douCY"]=$douCY;
                                M('shoufei_chayi')->where("intqishu='".intval($strQishu)."' and addtime='".intval($strPici)."' and sid='".$Value["id"]."' ")->save($arrDataCY);
                            }else{
                                $arrData["sid"]=$Value["id"];
                                $arrData["aid"]=$ValueA["id"];

                                $arrData["douSF"]=floatval($sfTotal);
                                $arrData["douSFHJ"]=floatval($sfTotal);
                                $arrData["douSXF"]=floatval($sxfTotal);

                                $arrData["addTime"]=intval($strPici);
                                $arrData["intQiShu"]=intval($strQishu);
                                $arrData["intCreateDate"]=date('Y-m-d H:i:s');

                                $addID=M('shoufei_info')->add($arrData);
                            }

                        }else{
                            $arrData["sid"]=$Value["id"];
                            $arrData["aid"]=$ValueA["id"];

                            $arrData["douSF"]=floatval($sfTotal);
                            $arrData["douSFHJ"]=floatval($sfTotal);
                            $arrData["douSXF"]=floatval($sxfTotal);

                            $arrData["addTime"]=intval($strPici);
                            $arrData["intQiShu"]=intval($strQishu);
                            $arrData["intCreateDate"]=date('Y-m-d H:i:s');

                            $addID=M('shoufei_info')->add($arrData);
                        }
                    }

                    $listCY=M("shoufei_chayi")->where("addtime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' and sid='".intval($Value["id"])."' ")->select();
                    if(empty($listCY)){
                        $sumXGJ=M("xgjb")->where("addtime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' and strXiaoQu='".$Value["name"]."' ")->sum('douShouRu');
                        $arrDataCY["sid"]=$Value["id"];
                        $arrDataCY["addTime"]=intval($strPici);
                        $arrDataCY["intQiShu"]=intval($strQishu);
                        $arrDataCY["douXGJ"]=floatval($sumXGJ);
                        $arrDataCY["intCreateDate"]=date('Y-m-d H:i:s');
                        M('shoufei_chayi')->add($arrDataCY);
                    }else{
                        $sumXGJ=M("shoufei_chayi")->where("addtime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' and sid='".intval($Value["id"])."' ")->sum('douXGJ');
                        if(floatval($sumXGJ)==0){
                            $sumXGJ_add=M("xgjb")->where("addtime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' and strXiaoQu='".$Value["name"]."' ")->sum('douShouRu');
                            $douXGJ_add["douXGJ"]=floatval($sumXGJ_add);
                            M('shoufei_chayi')->where("addtime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' and sid='".intval($Value["id"])."' ")->save($douXGJ_add);
                        }
                    }
                }

                foreach($listSchool as $Key=> $Value){
                    foreach($listAccount as $KeyA=> $ValueA){
                        $listHZBJ[$Value["id"]][$ValueA["id"]]=M('shoufei_info')->where("addTime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' and sid='".$Value["id"]."' and aid='".$ValueA["id"]."' ".$strSQL." ")->find();
                    }
                }
            }
        }

        foreach($listAccount as $K=> $Val){
            $shoufei_total[$Val["id"]]=M("shoufei_info")->where("addtime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' and aid='".intval($Val["id"])."' ".$strSQL." ")->field(" sum(`douSF`) as `douSF_total`, sum(`douSFHJ`) as `douSFHJ_total`, sum(`douXJSR`) as `douXJSR_total`, sum(`douXXK`) as `douXXK_total`, sum(`douRZK`) as `douRZK_total`, sum(`douQTSR`) as `douQTSR_total`, sum(`douSXF`) as `douSXF_total` ")->find();
        }

        foreach($listSchool as $K=> $Val){
            $listHZBJ[$Val["id"]]["douSFHJ_total"]=M("shoufei_info")->where("addtime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' and sid='".intval($Val["id"])."' ".$strSQL." ")->sum('douSFHJ');
            $listHZBJ[$Val["id"]]["douXJSR_total"]=M("shoufei_info")->where("addtime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' and sid='".intval($Val["id"])."' ".$strSQL." ")->sum('douXJSR');
            $sumXGJ=M("shoufei_chayi")->where("addtime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' and sid='".intval($Val["id"])."' ")->find();
            $arrCY_YS["douCY"]=floatval($listHZBJ[$Val["id"]]["douSFHJ_total"])+floatval($listHZBJ[$Val["id"]]["douXJSR_total"])-floatval($sumXGJ["douxgj"]);
            M('shoufei_chayi')->where("intqishu='".intval($strQishu)."' and addtime='".intval($strPici)."' and sid='".$Val["id"]."' ")->save($arrCY_YS);
            $listHZBJ[$Val["id"]]["chayi"]=M("shoufei_chayi")->where("addtime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' and sid='".intval($Val["id"])."' ".$strSQL." ")->find();
        }

        $listCY_HZ["douSFHJ_total"]=M("shoufei_info")->where("addtime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' ".$strSQL."  ")->sum("douSFHJ");
        $listCY_HZ["douXJSR_total"]=M("shoufei_info")->where("addtime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' ".$strSQL."  ")->sum("douXJSR");
        $listCY_HZ["douXGJ_total"]=M("shoufei_chayi")->where("addtime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' ".$strSQL."  ")->sum("douXGJ");
        $listCY_HZ["douCY_total"]=M("shoufei_chayi")->where("addtime='".intval($strPici)."' and intQiShu='".intval($strQishu)."' ".$strSQL."  ")->sum("douCY");


        $this->assign('listHZBJ',$listHZBJ);
        $this->assign('listSXF',$listSXF);
        $this->assign('listCY_HZ',$listCY_HZ);

        $this->assign('listSchool',$listSchool);
        $this->assign('listAccount',$listAccount);
        $this->assign('shoufei_total',$shoufei_total);
        //$this->assign('school_total',$school_total);

        $this->assign('qishu',$strQishu);
        $this->assign('pici',$strPici);

        $this->adminDisplay();

    }

    public function zijin_num_ajax(){

        $douSF_Z_js=$_POST["douSF_Z_js"]*100;	//倒进去统计结果
        $douSF_D_js=$_POST["douSF_D_js"]*100;	//计算后结果

        $douXJSR_js=$_POST["douXJSR_js"]*100;	//现金收入
        $douXXK_js=$_POST["douXXK_js"]*100;	//学习卡
        $douRZK_js=$_POST["douRZK_js"]*100;	//融资款
        $douQTSR_js=$_POST["douQTSR_js"]*100;	//其他收入

        $floSXF_js=abs($_POST["floSXF_js"])*100;	//手续费


        $strFieldName_js=substr($_POST["strFieldName_js"], 0, -2);
        $strFieldValue_js=floatval($_POST["strFieldValue_js"]);
        $intID_js=intval($_POST["intID_js"]);

        //$intID_js=1;

        $intRoleID=$this->getRoleId();
        $arrSchoolID=$this->getSchoolId();

        if($intRoleID==2 || $intRoleID==3){
            $strSchoolID=implode(",",$arrSchoolID);
            $strSQL_School=" and id in (".$strSchoolID.")";
            $strSQL=" and sid in (".$strSchoolID.")";
        }elseif($intRoleID==4){
            $strSQL="";
        }else{
            $temp['msg']='非法操作，请返回！';
            $temp['status']='NO';
        }


        $reA=M('shoufei_info')->where("id='".$intID_js."'")->find();
        $arrRs=M('pczb')->where("qishu='".$reA["intqishu"]."' and pici='".$reA["addtime"]."' ")->find();

        //if(abs($douSF_Z_js)>0)
        if($arrRs["status_cw"]!=2)
        {
            //最终收费=读取收费-学习卡-融资款-其它收入
            $douSFHJ=number_format((floatval($douSF_Z_js) - floatval($douXXK_js) - floatval($douRZK_js) - floatval($douQTSR_js))/100,2,".","");

            $arrData[$strFieldName_js]=$strFieldValue_js;
            $arrData["douSFHJ"]=$douSFHJ;
            M('shoufei_info')->where("id='".$intID_js."'")->save($arrData);

            //$reA=M('shoufei_info')->where("id='".$intID_js."'")->find();

            $douTotal=M("shoufei_info")->where("addtime='".intval($reA["addtime"])."' and intQiShu='".intval($reA["intqishu"])."' and aid='".intval($reA["aid"])."' ".$strSQL." ")->field(" sum(`douSF`) as `douSF_total`, sum(`douSFHJ`) as `douSFHJ_total`, sum(`douXJSR`) as `douXJSR_total`, sum(`douXXK`) as `douXXK_total`, sum(`douRZK`) as `douRZK_total`, sum(`douQTSR`) as `douQTSR_total`, sum(`douSXF`) as `douSXF_total` ")->find();

            $douTotal_sf=M("shoufei_info")->where("addtime='".intval($reA["addtime"])."' and intQiShu='".intval($reA["intqishu"])."' and sid='".intval($reA["sid"])."' ".$strSQL." ")->sum('douSFHJ');
            $douTotal_xj=M("shoufei_info")->where("addtime='".intval($reA["addtime"])."' and intQiShu='".intval($reA["intqishu"])."' and sid='".intval($reA["sid"])."' ".$strSQL." ")->sum('douXJSR');
            $rsCY=M("shoufei_chayi")->where("addtime='".intval($reA["addtime"])."' and intQiShu='".intval($reA["intqishu"])."' and sid='".intval($reA["sid"])."' ".$strSQL." ")->find();
            $douTotal_s=floatval($douTotal_sf) + floatval($douTotal_xj);
            if(floatval($rsCY["douxgj"]>0)){
                $douCY=floatval($douTotal_s) - floatval($rsCY["douxgj"]);
            }else{
                $douCY="";
            }
            $arrData["douCY"]=$douCY;
            M('shoufei_chayi')->where("id='".$rsCY["id"]."'")->save($arrData);

            $dou_Total_sf=M("shoufei_info")->where("addtime='".intval($reA["addtime"])."' and intQiShu='".intval($reA["intqishu"])."' ".$strSQL."  ")->sum("douSFHJ");
            $dou_Total_xj=M("shoufei_info")->where("addtime='".intval($reA["addtime"])."' and intQiShu='".intval($reA["intqishu"])."' ".$strSQL."  ")->sum("douXJSR");
            $dou_Total_s=floatval($dou_Total_sf) + floatval($dou_Total_xj);

            $douXGJ_total_s=M("shoufei_chayi")->where("addtime='".intval($reA["addtime"])."' and intQiShu='".intval($reA["intqishu"])."' ".$strSQL."  ")->sum("douXGJ");
            $douCY_total_s=M("shoufei_chayi")->where("addtime='".intval($reA["addtime"])."' and intQiShu='".intval($reA["intqishu"])."' ".$strSQL."  ")->sum("douCY");

            $temp["dou_Total_s"]=$dou_Total_s;
            $temp["douXGJ_total_s"]=$douXGJ_total_s;
            $temp["douCY_total_s"]=$douCY_total_s;

            $temp["arrTotal"]=$douTotal;
            $temp["douTotal_s"]=$douTotal_s;

            $temp["cyid"]=$rsCY["id"];
            $temp["douCY"]=$douCY;

            $temp["aid"]=$reA["aid"];
            $temp["msg"]=$douSFHJ;
            $temp["status"]='OK';
        }
        else
        {
            //$temp['msg']='请在正确的收款平台下填写！';
            $temp['msg']='此日期批次已通过审核，无法进行操作！';
            $temp['status']='NO';
        }
        echo json_encode($temp);
        //dump($temp);
    }


    public function zijin_chayi_ajax(){

        $douXGJ_js=abs($_POST["douXGJ_js"]*100);
        $intID_js=$_POST["intID_js"];

        //$intID_js=1;

        $intRoleID=$this->getRoleId();
        $arrSchoolID=$this->getSchoolId();

        if($intRoleID==2 || $intRoleID==3){
            $strSchoolID=implode(",",$arrSchoolID);
            $strSQL_School=" and id in (".$strSchoolID.")";
            $strSQL=" and sid in (".$strSchoolID.")";
        }elseif($intRoleID==4){
            $strSQL="";
        }else{
            $temp['msg']='非法操作，请返回！';
            $temp['status']='NO';
        }

        $rsCY=M("shoufei_chayi")->where("id='".intval($intID_js)."'")->find();
        $arrRs=M('pczb')->where("qishu='".$rsCY["intqishu"]."' and pici='".$rsCY["addtime"]."' ")->find();
        if(intval($intID_js)>0 && $arrRs["status_cw"]!=2)
        {
            //$rsCY=M("shoufei_chayi")->where("id='".intval($intID_js)."'")->find();
            if($douXGJ_js>0){
                $douSFHJ_total=M("shoufei_info")->where("addtime='".intval($rsCY["addtime"])."' and intQiShu='".intval($rsCY["intqishu"])."' and sid='".intval($rsCY["sid"])."' ".$strSQL." ")->sum('douSFHJ');
                $douXJSR_total=M("shoufei_info")->where("addtime='".intval($rsCY["addtime"])."' and intQiShu='".intval($rsCY["intqishu"])."' and sid='".intval($rsCY["sid"])."' ".$strSQL." ")->sum('douXJSR');
                $douCY=number_format((floatval($douSFHJ_total)*100+floatval($douXJSR_total)*100-floatval($douXGJ_js))/100,2,".","");
                $arrData["douCY"]=$douCY;
            }else{
                $arrData["douCY"]="";
            }
            $arrData["douXGJ"]=number_format($douXGJ_js/100,2,".","");
            M('shoufei_chayi')->where("id='".intval($intID_js)."'")->save($arrData);

            $douXGJ_total=M("shoufei_chayi")->where("addtime='".intval($rsCY["addtime"])."' and intQiShu='".intval($rsCY["intqishu"])."' ".$strSQL."  ")->sum("douXGJ");
            $douCY_total=M("shoufei_chayi")->where("addtime='".intval($rsCY["addtime"])."' and intQiShu='".intval($rsCY["intqishu"])."' ".$strSQL."  ")->sum("douCY");

            //douTotal_s
            $temp["douXGJ_total"]=$douXGJ_total;
            $temp["douCY_total"]=$douCY_total;
            $temp["msg"]=$douCY;
            $temp["status"]='OK';
        }
        else
        {
            $temp['msg']='非法操作！';
            $temp['status']='NO';
        }
        echo json_encode($temp);
        //dump($temp);
    }

    public function zijin_chayi_rem_ajax(){

        $textRemarks_js=$_POST["textRemarks_js"];
        $intID_js=$_POST["intID_js"];

        $rsRem=M('shoufei_chayi')->where("id='".intval($intID_js)."'" )->find();

        $arrRs=M('pczb')->where("qishu='".$rsRem["intqishu"]."' and pici='".$rsRem["addtime"]."' ")->find();
        if( $arrRs["status_cw"]!=2 ){
            if(is_array($rsRem) && !empty($rsRem) && $textRemarks_js!="")
            {
                $arrData["textRemarks"]=$textRemarks_js;
                M('shoufei_chayi')->where("id='".intval($intID_js)."'")->save($arrData);

                //$temp['msg']='！';
                //$temp['status']='OK';
            }
            else
            {
                //$temp['msg']='！';
                //$temp['status']='NO';
            }
        }else{
            $temp['msg']='非法操作！';
            $temp['status']='NO';
        }
        echo json_encode($temp);
        //dump($temp);
    }

    public function addZijinHuizong(){

        $sid=$_POST["sid"];
        $aid=$_POST["aid"];

        $douSF	=$_POST["douSF"];
        $douSFHJ=$_POST["douSFHJ"];
        $douXJSR=$_POST["douXJSR"];
        $douXXK	=$_POST["douXXK"];
        $douRZK	=$_POST["douRZK"];
        $douQTSR=$_POST["douQTSR"];
        $douSXF	=$_POST["douSXF"];

        $addTime=$_POST["addTime"];
        $intQiShu=$_POST["intQiShu"];

        $intCreateDate=$_POST["intCreateDate"];

        $intID=$_POST["intID"];


        $douXGJ=$_POST["douXGJ"];
        $douCY=$_POST["douCY"];
        $textRemarks=$_POST["textRemarks"];

        $arrRs=M('pczb')->where("qishu='".intval($intQiShu)."' and pici='".intval($addTime)."' ")->find();
        if($arrRs["status_cw"]!=2){
            if(!empty($intID) && is_array($intID))
            {
                foreach($intCreateDate as $key=>$val)
                {
                    //$arrData["sid"]=$sid[$key];
                    //$arrData["aid"]=$aid[$key];

                    $arrData["douSF"]=$douSF[$key];
                    $arrData["douSFHJ"]=$douSFHJ[$key];
                    $arrData["douXJSR"]=$douXJSR[$key];
                    $arrData["douXXK"]=$douXXK[$key];
                    $arrData["douRZK"]=$douRZK[$key];
                    $arrData["douQTSR"]=$douQTSR[$key];
                    $arrData["douSXF"]=$douSXF[$key];

                    //$arrData["addTime"]=$addTime[$key];
                    //$arrData["intQiShu"]=$intQiShu[$key];

                    $arrData["intCreateDate"]=$intCreateDate[$key];

                    $fid=$intID[$key];

                    M('shoufei_info')->where(array('id'=>$fid))->save($arrData);

                    $arrData_CY["douXGJ"]=$douXGJ[$key];
                    $arrData_CY["douCY"]=$douCY[$key];
                    $arrData_CY["textRemarks"]=$textRemarks[$key];
                    M('shoufei_chayi')->where("sid='".$sid[$key]."' and addTime='".$addTime."' and intQiShu='".$intQiShu."'")->save($arrData_CY);

                    $uQiShu=	$intQiShu[$key];
                }

                $this->success('修改成功',U('zijinIndex/index',array('qishu'=>$uQiShu)));

            }
            else
            {
                foreach($intCreateDate as $key=>$val)
                {
                    $arrData["sid"]=$sid[$key];
                    $arrData["aid"]=$aid[$key];

                    $arrData["douSF"]=$douSF[$key];
                    $arrData["douSFHJ"]=$douSFHJ[$key];
                    $arrData["douXJSR"]=$douXJSR[$key];
                    $arrData["douXXK"]=$douXXK[$key];
                    $arrData["douRZK"]=$douRZK[$key];
                    $arrData["douQTSR"]=$douQTSR[$key];
                    $arrData["douSXF"]=$douSXF[$key];

                    $arrData["addTime"]=$addTime[$key];
                    $arrData["intQiShu"]=$intQiShu[$key];

                    $arrData["intCreateDate"]=$intCreateDate[$key];

                    M('shoufei_info')->add($arrData);

                    $uQiShu=	$intQiShu[$key];
                }
                $this->success('保存成功',U('zijinIndex/index',array('qishu'=>$uQiShu)));

            }
        }else{
            $this->error("此日期批次已通过审核，无法进行操作！");
        }
    }

    public function zijin_rem_ajax(){

        $textPiZhu_js=$_POST["textPiZhu"];
        $intID_js=$_POST["intID"];
        $fieldName_js=$_POST["fieldName"];

        $rsRem=M('shoufei_info')->where("id='".intval($intID_js)."'" )->find();

        $arrRs=M('pczb')->where("qishu='".$rsRem["intqishu"]."' and pici='".$rsRem["addtime"]."' ")->find();
        if($arrRs["status_cw"]!=2)
        {//if($rsRem["dousf"]>0)
            $arrData[$fieldName_js]=$textPiZhu_js;
            M('shoufei_info')->where("id='".intval($intID_js)."'")->save($arrData);

            if(strlen(trim($textPiZhu_js))>0){
                $temp["msg"]='添加 批注信息 成功！';
                $temp["status"]='OK';
            }else{
                $temp["msg"]='批注信息不能为空！';
                $temp["status"]='Yes';
            }
        }
        else
        {
            //$temp['msg']='此项没有内容，无法添加批注！';
            $temp['msg']='此日期批次已通过审核，无法添加批注！';
            $temp['status']='NO';
        }

        echo json_encode($temp);
        //dump($temp);
    }

    public function zijin_getrem_ajax(){

        $intID_js=$_POST["intID"];
        $fieldName_js=$_POST["fieldName"];

        $rsRem=M('shoufei_info')->where("id='".intval($intID_js)."'" )->find();
        //if($rsRem["dousf"]>0)
        //{
        if($rsRem[strtolower($fieldName_js)]!=""){
            $temp["msg"]=$rsRem[strtolower($fieldName_js)];
        }
        $temp["status"]='OK';
        //}
        //else
        //{
        //	$temp['msg']='此项没有内容，无法添加批注！';
        //	$temp['status']='NO';
        //}

        echo json_encode($temp);
        //dump($temp);
    }


    public function zijin_reset_ajax(){

        $intQishu=$_GET['qishu'];
        $intPici=$_GET['pici'];

        $arrRs=M('pczb')->where("qishu='".$intQishu."' and pici='".$intPici."' ")->find();
        if(intval($intQishu)>0 && intval($intPici)>0 && $arrRs["status_cw"]!=2)
        {
            //M("shoufei_info")->where("addtime='".intval($intPici)."' and intQiShu='".intval($intQishu)."'")->delete();
            //M("shoufei_chayi")->where("addtime='".intval($intPici)."' and intQiShu='".intval($intQishu)."'")->delete();
            $arrData["douSF"]="";
            $arrData["douSFHJ"]="";
            $arrData["douSXF"]="";
            M("shoufei_info")->where("addtime='".intval($intPici)."' and intQiShu='".intval($intQishu)."'")->save($arrData);

            $arr["douCY"]="";
            $arr["douXGJ"]="";
            M("shoufei_chayi")->where("addtime='".intval($intPici)."' and intQiShu='".intval($intQishu)."'")->save($arr);

            $arr['status']=true;
            $arr['info']='重置成功';
        }
        else
        {
            $arr['status']=false;
            $arr['info']='操作失败！';
        }
        $this->ajaxReturn($arr);
    }



    public function zijinSchool_lkl(){

        $intQishu=$_GET['qishu'];
        $intPici=$_GET['pici'];
        $sid=$_GET['sid'];

        //$intQishu="201801";
        //$intPici="2";
        //$sid="75";

        if(intval($intQishu)>0 && intval($intPici)>0 && intval($sid)>0)
        {
            $rs=M('school')->where("id='".intval($sid)."' ")->find();
            $list=M('lklb')->where("addtime='".intval($intPici)."' and intQiShu='".intval($intQishu)."' and strmername='".$rs["subname"]."' ")->select();
        }
        $this->assign('list',$list);
        $this->adminDisplay();
    }


    public function zijinSchool_sqb(){

        $intQishu=$_GET['qishu'];
        $intPici=$_GET['pici'];
        $sid=$_GET['sid'];

        //$intQishu="201801";
        //$intPici="2";
        //$sid="75";

        if(intval($intQishu)>0 && intval($intPici)>0 && intval($sid)>0)
        {
            $rs=M('school')->where("id='".intval($sid)."' ")->find();
            $list=M('sqbb')->where("addtime='".intval($intPici)."' and intQiShu='".intval($intQishu)."' and strSchoolName='".$rs["subname"]."' ")->select();
        }
        $this->assign('list',$list);
        $this->adminDisplay();
    }


    public function showChayi(){
        $intQishu=$_GET['qishu'];
        $intPici=$_GET['pici'];
        $sid=$_GET['sid'];
        if(intval($intQishu)>0 && intval($intPici)>0 && intval($sid)>0)
        {
            $rs=M('school')->where("id='".intval($sid)."' ")->find();

            $list_sqbb=M('sqbb')->field("doussje")->where("addtime='".intval($intPici)."' and intQiShu='".intval($intQishu)."' and strSchoolName='".$rs["subname"]."' ")->select();
            $list_lklb=M('lklb')->field("douhzbj")->where("addtime='".intval($intPici)."' and intQiShu='".intval($intQishu)."' and strmername='".$rs["subname"]."' ")->select();
            foreach($list_sqbb as $key1=>$val1){
                $list_total[]=$val1["doussje"];
            }
            foreach($list_lklb as $key2=>$val2){
                $list_total[]=$val2["douhzbj"];
            }

            $list_xgjb=M('xgjb')->field("doushouru")->where("addtime='".intval($intPici)."' and intQiShu='".intval($intQishu)."' and strXiaoQu='".$rs["name"]."' ")->select();
            foreach($list_xgjb as $key=>$val){
                $list_xgj[]=$val["doushouru"];
            }

            if(empty($list_total))
                $list_total=array();

            if(empty($list_xgj))
                $list_xgj=array();

            $result_1=array_diff($list_total,$list_xgj);
            $result_2=array_diff($list_xgj,$list_total);
        }
        $this->assign('result_1',$result_1);//拉卡拉/收钱吧
        $this->assign('result_2',$result_2);//校管家新增数据
        $this->adminDisplay();
    }


    public function showphpinfo(){
        /*
        $intQishu=201710;
        $intPici=11;
        $intAid=1;


        $listAccount=M('account_detail')->where('isUse',1)->order('intOrderID asc,id desc')->select();//

        foreach($listAccount as $K=> $Val){
            $shoufei_total[$Val["id"]]=M("shoufei_info")->where("addtime='".intval($intPici)."' and intQiShu='".intval($intQishu)."' and aid='".intval($Val["id"])."'")->field(" sum(`douSF`) as `douSF_total`, sum(`douSFHJ`) as `douSFHJ_total`, sum(`douXJSR`) as `douXJSR_total`, sum(`douXXK`) as `douXXK_total`, sum(`douRZK`) as `douRZK_total`, sum(`douQTSR`) as `douQTSR_total`, sum(`douSXF`) as `douSXF_total` ")->find();

            //echo  M('shoufei_info')->getLastSql();
            //echo "<br><br>";
        }
        dump($shoufei_total);
        /  *
        M("shoufei_info")
            ->where("addtime='".intval($intPici)."' and intQiShu='".intval($intQishu)."' and aid='".intval($intAid)."'")
            ->field(" sum(`douSF`) as `douSF_total`,
                      sum(`douSFHJ`) as `douSFHJ_total`,
                      sum(`douXJSR`) as `douXJSR_total`,
                      sum(`douXXK`) as `douXXK_total`,
                      sum(`douRZK`) as `douRZK_total`,
                      sum(`douQTSR`) as `douQTSR_total`,
                      sum(`douSXF`) as `douSXF_total`
                   ")
            ->find();
        */
        phpinfo();



        //echo "<br>";
    }


    // 期数审核
    public function checked(){
        $count=$this->checkUpload($_GET);
        if($count){
            $rid=$this->getRid();
            switch($rid){
                case 4:
                    $temp['shoufei_checked']=2;
                    break;
            }
            M('qishu')->where($_GET)->save($temp);// 更新数据总表
            $arr['status']=true;
            $arr['info']='操作成功';
            // 还需要将生成表数据写入数据库并让表格可以下载
            $this->ajaxReturn($arr);
        }else{
            $arr['status']=false;
            $arr['info']='请审核本月所有批次后在操作';
            // 还需要将生成表数据写入数据库并让表格可以下载
            $this->ajaxReturn($arr);
        }

    }

    // 检查当月所有批次是否上传完毕
    public function checkUpload($arr){
        $res=M('pczb')->field('status_cw')->where($arr)->select();
        if(empty($res)){
            return false;
        }
        foreach($res as $v){
            if($v['status_cw']==1){
                return false;
            }
        }
        return true;
    }

    // 取消通过审核操作
    public function cancel(){
        $rid=$this->getRid();
        switch($rid){
            case 4:
                $temp['shoufei_checked']=1;
                break;
        }
        M('qishu')->where($_GET)->save($temp);// 更新数据总表
        $arr['status']=true;
        $arr['info']='操作成功';
        $this->ajaxReturn($arr);
    }
}
?>



















