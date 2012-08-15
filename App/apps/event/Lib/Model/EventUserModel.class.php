<?php
    /**
     * EventUserModel 
     * 活动用户项
     * @uses BaseModel
     * @package 
     * @version $id$
     * @copyright 2009-2011 SamPeng 
     * @author SamPeng <sampeng87@gmail.com> 
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
    class EventUserModel extends BaseModel{
        /**
         * getUserList 
         * 获得用户列表
         * @param mixed $action 
         * @param mixed $eventId 
         * @param mixed $limit 
         * @access public
         * @return void
         */
        public function getUserList($map,$limit,$page=null){
            if( isset( $page ) ){
                return $this->where( $map )->field( 'distinct(uid),status,action,cTime,contact,id' )->order( 'cTime DESC' )->findPage($limit);
            }else{
                return $this->where( $map )->order( 'cTime DESC' )->limit( '0,'.$limit )->findAll();
            }
        }

        /**
         * hasUser 
         * 是否已经有了这个用户的关注
         * @param mixed $uid 
         * @param mixed $id 
         * @param mixed $action 
         * @access public
         * @return void
         */
        public function hasUser( $uid,$id,$action=NULL ){
            $map['uid']     = $uid;
            $map['eventId'] = $id;
            $map['action']  = $action;
            return $this->where( $map )->find();
        }
    }
