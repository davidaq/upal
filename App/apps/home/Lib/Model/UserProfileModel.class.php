<?php

class UserProfileModel extends UserModel {
    var $tableName = 'user_profile';

    /* 见 home/UserModel/getUserList
    function getUserList(){
        return $this->table(C('DB_PREFIX').'user')->findall();
    }
    */

    //统一提取用户资料
    function getUserInfo($space = false){
        $userInfoList                      = $this->where('uid='.$this->uid)->field('id,uid,module,data,type')->findall();
        $userInfo                          = $this->dataProcess( $userInfoList ,$space);
        $userInfo['detail']		           = $this->table(C('DB_PREFIX').'user')->where("uid={$this->uid}")->find();
        $userInfo['base']['completeness']  = 100;
        return $userInfo;
    }

    //数据处理
    private function dataProcess( $userInfoList,$space ){
        $fieldList = $this->data_field(false,$space);
        foreach ($userInfoList as $value){
            if( $value['type'] == 'info' ){
                $database[ $value['module'] ] = unserialize( $value['data'] );
            }else{
                $data[ 'profile' ]['list'][] = array_merge( array('module'=>$value['module'],'id'=>$value['id']) , unserialize($value['data']) );
            }
        }
        $data['profile']['completeness'] = round( count( array_unique( getSubByKey( $data[ 'profile' ]['list'] ,'module') ) ) / 2 , 2) *100;
        foreach ($fieldList as $key=>$value){
            foreach ( $value as $k=>$v){
                $t = $database[$key][$k];
                if( $t ) $complete++;
                $data[$key]['list'][]  = array('field' => $k,'name'  => $v,'value' => $t );

            }
            $data[$key]['completeness'] = round( $complete/count($value) , 2 ) * 100 ;
            unset($complete);
        }

        unset($userInfoList);
        unset($fieldList);
        unset($database);
        return $data;
    }

    //统一存储用户资料
    function doSave( $module , $savedata , $type='info' , $multi=false  ){
        if(!$module) return false;
        $data['uid']    = $this->uid;
        $data['module'] = $module;
        $data['type']   = $type;
        foreach ($savedata as $k=>$v){
        	$savedata[$k]=keyWordFilter($v);
        }
        if( $this->where($data)->count()!=0 && $multi==false){
            $this->setField( 'data' , serialize( $savedata) ,$data);
        }else{
            $data['data'] = serialize( $savedata );
            return $this->add( $data );
        }
    }

    //获取信息
    function getProfiles($uid){
        $list = $this->where( 'uid='.$uid )->order('id ASC')->findall();
        foreach ($list as $value){
            $unserData = unserialize( $value['data'] );
            $data[] = array_merge( array('module'=>$value['module'],'id'=>$value['id']) , $unserData );
        }
        return $data;
    }

    function delProfile($intId,$uid){
        return $this->where("id=$intId AND uid=$uid")->delete();
    }


	//更新个人情况
    function upintro(){
        $fieldList = $this->data_field( 'intro' );
        foreach ($fieldList as $key=>$value){
            $data[$key] = t( msubstr( $_POST['intro'][$key],0,70,'utf-8',false ) );
        }
        $this->dosave('intro',$data);
	   	$data['message'] = '更新完成';
		$data['boolen']  = 1;
		return $data;
    }

	//更新联系方式
    function upcontact(){
        $fieldList = $this->data_field( 'contact' );
        foreach ($fieldList as $key=>$value){
            $data[$key] = t( msubstr( $_POST['contact'][$key],0,70,'utf-8',false ) );
        }
        $this->dosave('contact',$data);
	   	$data['message'] = L('update_done');
		$data['boolen']  = 1;
		return $data;
    }
}