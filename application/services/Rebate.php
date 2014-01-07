<?php
class Application_Service_Rebate
{
    private $accInfo=null;
    private $feType=null;
    public function rebateCal(array $data,$periodflag){
        //如果没有找到对应账户退出
        if(!$this->hasAccount($data['fes_id'], $data['account'])){
            return;
        }
        //如果没有匹配到交易品种则抛出异常
        if(!$this->hasFeType($data['fes_id'], $data['fetype'])){
            $dbMissedFetype=new Application_Model_DbTable_MissedFetype();
            $dbMissedFetype->addNewMissedType($data['fes_id'], $data['fetype']);
            return;
        }else{
            $this->calculateRebate($data,$periodflag);
        }
        
        $this->accInfo=null;
        $this->feType=null;
    }
    /**
     * 判断账户号是否存在于系统中
     * Enter description here ...
     * @param unknown_type $fesId
     * @param unknown_type $account
     */
    private function hasAccount($fesId,$account){
        $dbFeAccount=new Application_Model_DbTable_FeAccount();
        $res=$dbFeAccount->getAccountByFesAndAccnum($fesId,$account);
        if(count($res)==1){
            $this->accInfo=$res;
            return true;
        }else{
            return false;
        }
    }
    /**
     * 判断交易类型是否有效
     * Enter description here ...
     * @param unknown_type $fesId
     * @param unknown_type $feName
     */
    private function hasFeType($fesId,$feName){
        $dbFeBaseType=new Application_Model_DbTable_FeBaseType();
        $res=$dbFeBaseType->getFeTypeByFesIdAndFeName($fesId,$feName);
        if(count($res)==1){
            $this->feType=$res;
            return true;
        }else{
            return false;
        }
    }
    
    private function calculateRebate($data,$periodflag){
        $uid=$this->accInfo['user_id'];
        $userData['fes_rec_id']=$data['fes_id'];
        $userData['user_id']=$uid;
        $userData['fe_account_num']=$data['account'];
        $userData['fe_account_name']=$this->accInfo['account_name'];
        $userData['fe_name']=$data['fetype'];
        //$userData['fe_quantity']=$data['quantity']/$this->feType['transform'];
        $userData['fe_quantity']=$data['quantity'];
        $userData['fe_time']=$data['date_time'];
        $userData['clearflag']='0';
        $userData['type']=0;
        $userData['periodflag']=$data['periodflag'];
        //取得用户对应的标准
        $dbBrokerageStandare=new Application_Model_DbTable_BrokerageStandard();
        $userStand=$dbBrokerageStandare->getUserStandardByIdAndFesId($uid,$data['fes_id'],$periodflag);
        
        
        $userData['rebate']=$this->getRebate($userStand, $userData['fe_quantity']);
        
        $dbFeDetail=new Application_Model_DbTable_FeDetail();
        $res=$dbFeDetail->fetchAll("fe_account_num='".$userData['fe_account_num']."' and fe_name='".$userData['fe_name']."' and periodflag='".$userData['periodflag']."'");
        $count=count($res);
        if(0==$count){//如果没有记录则直接插入新记录
            //执行插入操作
            $subUserId=$this->accInfo['sub_user_id'];
            //如果帐号对应没有下级的话
            if(""==$subUserId){
                $records[]=$userData;
            }else{
                $subUserData=$userData;
                $subUserData['type']=1;
                $subUserData['sub_user_id']=$subUserId;
                $userData['sub_user_id']=$subUserId;
                //取得下级对应的返佣标准
                $subUserStandard=$dbBrokerageStandare->getSubUserStandareByIdAndFesId($uid,$subUserId,$periodflag,$data['fes_id']);
                $subUserData['rebate']=$this->getRebate($subUserStandard, $subUserData['fe_quantity']);
                $userData['rebate']=$userData['rebate']-$subUserData['rebate'];
                if($userData['rebate']==0){
                    $records[]=$subUserData;
                }else{
                    $records[]=$userData;
                    $records[]=$subUserData;
                }
            }
            $dbFeDetail->addRecords($records);
        }else{
            //执行更新操作
            $subUserId=$this->accInfo['sub_user_id'];
            //如果账号对应没有下级的话
            if(""==$subUserId){
                if(count($res)==1&&0==$res[0]['type']){
                    $dbFeDetail->update($userData, "id='".$res[0]['id']."'");
                }else{
                    throw new Exception('周期内计算数据出现异常重复，请查看用户对应的记录是否唯一');
                }
                
            }else{//如果账号对应有下级
                $subUserData=$userData;
                $subUserData['type']=1;
                $subUserData['sub_user_id']=$subUserId;
                $userData['sub_user_id']=$subUserId;
                //取得下级对应的返佣标准
                $subUserStandard=$dbBrokerageStandare->getSubUserStandareByIdAndFesId($uid,$subUserId,$periodflag,$data['fes_id']);
                $subUserData['rebate']=$this->getRebate($subUserStandard, $subUserData['fe_quantity']);
                $userData['rebate']=$userData['rebate']-$subUserData['rebate'];


                if(count($res)==1&&1==$res[0]['type']){
                    $dbFeDetail->update($subUserData, "id='".$res[0]['id']."'");
                    if($userData['rebate']!=0){
                        $dbFeDetail->insert($userData);
                    }
                }
                if(count($res)==2){
                    foreach ($res as $re){
                        if(1==$re['type']){
                            $dbFeDetail->update($subUserData, "id='".$re['id']."'");
                        }
                        if(0==$re['type']){
                            if($userData['rebate']!=0){
                                $dbFeDetail->update($userData, "id='".$re['id']."'");
                            }else{
                                $dbFeDetail->delete("id='".$re['id']."'");
                            }
                        }
                    }
                }
                
            }
        }
        
        
        
    }
    /**
     * 计算返佣
     * @param unknown_type $standard 返佣标准
     * @param unknown_type $quantity 手数
     */
    private function getRebate($standard,$quantity){
        $standardArr=(array)json_decode($standard->standard,true);
        $arrKey=array_keys($standardArr);
        if(in_array($this->feType['id'], $arrKey)){
            $var=$this->feType['id'];
            //echo $this->feType['name'].'|'.$dataArr[$var]*$quantity.'<br/>';
            $rebate=$standardArr[$var]*$quantity;
        }
        //TODO
        return $rebate;
    }
    
    
}
