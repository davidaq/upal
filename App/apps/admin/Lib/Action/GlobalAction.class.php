<?php
class GlobalAction extends AdministratorAction {

    private function __isValidRequest($field, $array = 'post') {
        $field = is_array($field) ? $field : explode(',', $field);
        $array = $array == 'post' ? $_POST : $_GET;
        foreach ($field as $v){
            $v = trim($v);
            if ( !isset($array[$v]) || $array[$v] == '' ) return false;
        }
        return true;
    }

    /** 系统配置 - 站点配置 **/

    //站点设置
    public function siteopt() {
        $site_opt = model('Xdata')->lget('siteopt');
        if(!$site_opt['site_logo']){
            $site_opt['site_logo']='logo.png';
            $this->assign('site_logo',THEME_URL.'/images/'.$site_opt['site_logo']);
        }
        $this->assign($site_opt);
        require_once ADDON_PATH . '/libs/Io/Dir.class.php';
        $theme_list = new Dir(SITE_PATH.'/public/themes/');
        $expression_list = new Dir(SITE_PATH.'/public/themes/'.$site_opt['site_theme'].'/images/expression/');
        $this->assign('expression_list',$expression_list->toArray());
        $this->assign('theme_list',$theme_list->toArray());

        $this->display();
    }

    //设置站点
    public function doSetSiteOpt() {
        if (empty($_POST)) {
            $this->error('参数错误');
        }

        //验证数字参数
        if( intval($_POST['max_post_time'])<0 
             || intval($_POST['max_refresh_time'])<0 
             || intval($_POST['max_following'])<0
             || intval($_POST['max_search_time'])<0  
        ){
            $this->error('数字变量的值必须大于等于0');
        }
        $_POST['max_post_time'] = intval($_POST['max_post_time']);
        $_POST['max_refresh_time'] = intval($_POST['max_refresh_time']);
        $_POST['max_following'] = intval($_POST['max_following']);
        $_POST['max_search_time'] = intval($_POST['max_search_time']);

        if (intval($_POST['length']) <= 0) {
            $this->error('全站微博、评论字数限制的值必须大于0');
        }

        //保存LOGO
        if(!empty($_FILES['site_logo']['name'])){
            $logo_options['save_to_db'] = false;
            $logo = X('Xattach')->upload('site_logo',$logo_options);
            if($logo['status']){
                $logofile = UPLOAD_URL.'/'.$logo['info'][0]['savepath'].$logo['info'][0]['savename'];
            }
            $_POST['site_logo'] = $logofile;
        }

        if(!empty($_FILES['banner_logo']['name'])){
            $logo_options['save_to_db'] = false;
            $logo = X('Xattach')->upload('site_logo',$logo_options);
            if($logo['status']){
                $logofile = UPLOAD_URL.'/'.$logo['info'][0]['savepath'].$logo['info'][0]['savename'];
            }
            $_POST['banner_logo'] = $logofile;
        }



        $_POST['site_name']                 = t($_POST['site_name']);
        $_POST['site_slogan']               = t($_POST['site_slogan']);
        $_POST['site_header_keywords']      = t($_POST['site_header_keywords']);
        $_POST['site_header_description']   = t($_POST['site_header_description']);
        $_POST['site_closed']               = intval($_POST['site_closed']);
        $_POST['site_closed_reason']        = t($_POST['site_closed_reason']);
        $_POST['site_icp']                  = t($_POST['site_icp']);
        $_POST['site_verify']               = isset($_POST['site_verify']) ? $_POST['site_verify'] : '';
        $_POST['expression']                = t($_POST['expression']);
        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '3';
        $data[] = '全局 - 站点配置 ';
        $site_opt = model('Xdata')->lget('siteopt');
        $data[] = $site_opt;
        if( $_POST['__hash__'] ) unset( $_POST['__hash__'] );
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $res = model('Xdata')->lput('siteopt', $_POST);
        if ($res) {
            //表情需要flush一下
            model('Expression')->getAllExpression(true);
            $this->assign('jumpUrl', U('admin/Global/siteopt'));
            $this->success('保存成功');
        }else {
            $this->error('保存失败');
        }
    }

    /** 系统配置 - 注册配置 **/

    public function register() {
        $register = model('Xdata')->lget('register');
        $this->assign($register);
        $invite   = model('Invite')->getSet();
        $this->assign($invite);
        $this->display();
    }

    public function doSetRegisterOpt() {

        $invite_set['invite_set'] = t($_POST['invite_set']);

        $invite   = model('Invite')->getSet();

        $site_opt = model('Xdata')->lget('register');

        unset($_POST['invite_set']);
        if ( model('Xdata')->lput('register', $_POST) && model('Xdata')->lput('inviteset', $invite_set) ) {
            $this->assign('jumpUrl', U('admin/Global/register'));

            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '3';
            $data[] = '全局 - 注册配置 ';
            $site_opt['invite_set'] = $invite['invite_set'];
            if( $site_opt['__hash__'] ) unset( $site_opt['__hash__'] );
            $data[] = $site_opt;
            $_POST['invite_set'] = $invite_set['invite_set'];
            if( $_POST['__hash__'] ) unset( $_POST['__hash__'] );
            $data[] = $_POST;
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);

            $this->success('保存成功');
        }else {
            $this->error('保存失败');
        }
    }

    /** 系统配置 - 积分配置 **/
    //积分类别设置
    public function creditType(){
        $creditType = M('credit_type')->order('id ASC')->findAll();
        $this->assign('creditType',$creditType);
        $this->display();
    }
    public function editCreditType(){
        $type   = $_GET['type'];
        if($cid = intval($_GET['cid'])){
            $creditType = M('credit_type')->where("`id`=$cid")->find();//积分类别
            if (!$creditType) $this->error('无此积分类型');
            $this->assign('creditType',$creditType);
        }

        $this->assign('type', $type);
        $this->display();
    }
    public function doAddCreditType(){
        if ( !$this->__isValidRequest('name') ) $this->error('数据不完整');

        $_POST = array_map('t',$_POST);
        $_POST = array_map('h',$_POST);

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '1';
        $data[] = '全局 - 积分配置  - 积分类型';
        if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $res = M('credit_type')->add($_POST);
        if ($res) {
            $db_prefix  = C('DB_PREFIX');
            $model = M('');
            $setting = $model->query("ALTER TABLE {$db_prefix}credit_setting ADD {$_POST['name']} INT(11) DEFAULT 0;");
            $user    = $model->query("ALTER TABLE {$db_prefix}credit_user ADD {$_POST['name']} INT(11) DEFAULT 0;");
			// 清缓存
            F('_service_credit_type', null);
            $this->assign('jumpUrl', U('admin/Global/creditType'));
            $this->success('保存成功');
        }else {
            $this->error('保存失败');
        }
    }
    public function doEditCreditType(){
        if ( !$this->__isValidRequest('id,name') ) $this->error('数据不完整');

        $_POST = array_map('t',$_POST);
        $_POST = array_map('h',$_POST);
        $creditTypeDao = M('credit_type');
        //获取原字段名
        $oldName = $creditTypeDao->find($_POST['id']);
        //修改字段名
        $res = $creditTypeDao->save($_POST);

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '3';
        $data[] = '全局 - 积分配置 - 积分类型 ';
        $data[] = $oldName;
        if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        if ($res) {
            $db_prefix  = C('DB_PREFIX');
            $model = M('');
            $setting = $model->query("ALTER TABLE {$db_prefix}credit_setting CHANGE {$oldName['name']} {$_POST['name']} INT(11);");
            $user    = $model->query("ALTER TABLE {$db_prefix}credit_user CHANGE {$oldName['name']} {$_POST['name']} INT(11);");
			// 清缓存
            F('_service_credit_type', null);
            $this->assign('jumpUrl', U('admin/Global/creditType'));
            $this->success('保存成功');
        }else {
            $this->error('保存失败');
        }
    }
    public function doDeleteCreditType(){
        $ids = t($_POST['ids']);
        $ids = explode(',', $ids);
        if ( empty($ids) ) {echo 0; return ;}

        $map['id'] = array('in', $ids);
        $creditTypeDao = M('credit_type');
        //获取字段名
        $typeName = $creditTypeDao->where($map)->findAll();

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '2';
        $data[] = '全局 - 积分配置 - 积分类型 ';
        $data[] = $typeName;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        //清除type信息和对应字段
        $res = M('credit_type')->where($map)->delete();
        if ($res){
            $db_prefix  = C('DB_PREFIX');
            $model = M('');
            foreach($typeName as $v){
                $setting = $model->query("ALTER TABLE {$db_prefix}credit_setting DROP {$v['name']};");
                $user    = $model->query("ALTER TABLE {$db_prefix}credit_user DROP {$v['name']};");
            }
			// 清缓存
            F('_service_credit_type', null);
            echo 1;
        }else{
            echo 0;
        }
    }
    //积分规则设置
    public function credit() {
        $list = M('credit_setting')->order('type ASC')->findPage(30);
        $creditType = M('credit_type')->order('id ASC')->findAll();
        $this->assign('creditType',$creditType);
        $this->assign($list);
        $this->display();
    }
    public function addCredit() {
        $creditType = M('credit_type')->order('id ASC')->findAll();//积分类别
        $this->assign('creditType',$creditType);
        $this->assign('type','add');
        $this->display('editCredit');
    }
    public function doAddCredit() {
        $name = trim($_POST['name']);
        if($name == "" && $_POST['name'] != ""){
            $this->error('名称不能为空格');
        }
        if ( !$this->__isValidRequest('name') ) $this->error('数据不完整');

        $_POST = array_map('t',$_POST);
        $_POST = array_map('h',$_POST);

        $creditType = M('credit_type')->order('id ASC')->findAll();
        foreach($creditType as $v){
            if(!is_numeric($_POST[$v['name']])){
                $this->error($v['alias'].'的值必须为数字！');
            }
        }

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '1';
        $data[] = '全局 - 积分配置 - 积分规则 ';
        if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $res = M('credit_setting')->add($_POST);
        if ($res) {
			// 清缓存
            F('_service_credit_rules', null);
            $this->assign('jumpUrl', U('admin/Global/credit'));
            $this->success('保存成功');
        }else {
            $this->error('保存失败');
        }
    }
    public function editCredit() {
        $cid    = intval($_GET['cid']);
        $credit = M('credit_setting')->where("`id`=$cid")->find();
        if (!$credit) $this->error('无此积分规则');

        $creditType = M('credit_type')->order('id ASC')->findAll();//积分类别
        $this->assign('creditType',$creditType);

        $this->assign('credit', $credit);
        $this->assign('type', 'edit');
        $this->display();
    }
    public function doEditCredit() {
        $name = trim($_POST['name']);
        if($name == "" && $_POST['name'] != ""){
            $this->error('名称不能为空格');
        }
        if ( !$this->__isValidRequest('id,name') ) $this->error('数据不完整');

        $_POST = array_map('t',$_POST);
        $_POST = array_map('h',$_POST);

        $creditType = M('credit_type')->order('id ASC')->findAll();
        foreach($creditType as $v){
            if(!is_numeric($_POST[$v['name']])){
                $this->error($v['alias'].'的值必须为数字！');
            }
        }

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '3';
        $data[] = '全局 - 积分配置 - 积分规则 ';
        $credit_info = M('credit_setting')->where('id='.$_POST['id'])->find();
        $data[] = $credit_info;
        $_POST['info'] = $credit_info['info'];
        if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $res = M('credit_setting')->save($_POST);
        if ($res) {
			// 清缓存
            F('_service_credit_rules', null);
            $this->assign('jumpUrl', U('admin/Global/credit'));
            $this->success('保存成功');
        }else {
            $this->error('保存失败');
        }
    }
    public function doDeleteCredit() {
        $ids = t($_POST['ids']);
        $ids = explode(',', $ids);
        if ( empty($ids) ) {echo 0; return ;}

        $map['id'] = array('in', $ids);

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '2';
        $data[] = '全局 - 积分配置 - 积分规则 ';
        $data[] = M('credit_setting')->where('id='.$_POST['id'])->find();
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $res = M('credit_setting')->where($map)->delete();
        if ($res) {
			// 清缓存
            F('_service_credit_rules', null);
            echo 1;
        } else {
        	echo 0;
        }
    }
    //批量用户积分设置
    public function creditUser(){
        $creditType = M('credit_type')->order('id ASC')->findAll();
        $this->assign('creditType',$creditType);
        $this->assign('grounlist',model('UserGroup')->getUserGroupByMap('','user_group_id,title'));
        $this->display();
    }
    public function doCreditUser(){
        set_time_limit(0);
        //查询用户ID
        $_POST['uId'] && $map['uid'] = array('in',explode(',',t($_POST['uId'])));
        $_POST['gId']!='all' && $map['admin_level'] = intval($_POST['gId']);
        $_POST['active']!='all' && $map['is_active'] = intval($_POST['active']);
        $user = D('User','home')->where($map)->field('uid')->findAll();
        if($user == false){
            $this->error('查询失败，没有这样条件的人');
        }
        //组装积分规则
        $setCredit = X('Credit');
        $creditType = $setCredit->getCreditType();
        foreach($creditType as $v){
            $action[$v['name']] = intval($_POST[$v['name']]);
        }



        if($_POST['action'] == 'set'){//积分修改为
            foreach($user as $v){
                $setCredit->setUserCredit($v['uid'],$action,'reset');
                if($setCredit->getInfo()===false)$this->error('保存失败');
            }
        }else{//增减积分
            foreach($user as $v){
                $setCredit->setUserCredit($v['uid'],$action);
                if($setCredit->getInfo()===false)$this->error('保存失败');
            }
        }

        $this->assign('jumpUrl', U('admin/Global/creditUser'));

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '1';
        if( $_POST['action'] == 'set' ){
            $data[] = '全局 - 积分配置 - 设置用户积分 - 积分修改操作 ';
        }else{
            $data[] = '全局 - 积分配置 - 设置用户积分 - 积分增减操作 ';
        }
        $data['1'] = $action;
        $data['1']['uid'] = $_POST['uId'];
        $data['1']['gId'] = $_POST['gId'];
        $data['1']['active'] = $_POST['active'];
        $data['1']['action'] = $_POST['action'];
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $this->success('保存成功');
    }

    /** 系统配置 - 邀请配置 **/

    //邀请配置
    function invite(){
        $data = model('Invite')->getSet();
        $this->assign( $data );
        $this->display();
    }

    //邀请码发放
    function invitecode(){
        $num = intval($_POST['send_type_num']);
        $user = t($_POST['send_type_user']);

        if($_POST['send_type']==1){
            $user = M('user')->where('is_init=1 AND is_active=1')->field('uid')->findall();
            foreach ($user as $key=>$value){
                model('Invite')->sendcode($value['uid'],$num);
            }
        }else{
            $user = explode(',', $user);
            foreach ($user as $k=>$v){
                model('Invite')->sendcode($v,$num);
                x('Notify')->sendIn($v,'admin_sendinvitecode',array('num'=>$num)); //通知发送
            }
        }

        if( $_POST ){
            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '1';
            $data[] = '全局 - 邀请配置 ';
            if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
            $data[] = $_POST;
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);
        }


        $this->success('操作成功');
    }

    /** 系统配置 - 公告配置 **/

    public function announcement() {
        if ($_POST) {
            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '3';
            $data[] = '全局 - 公告配置 ';
            $data[] = model('Xdata')->lget('announcement');
            if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
            $data[] = $_POST;
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);

            unset($data);
            $data['is_open'] = intval($_POST['is_open']);
            $data['content'] = t($_POST['content'], false, ENT_QUOTES);
            model('Xdata')->lput('announcement', $data);

            F('_home_user_action_announcement', null);

            $this->assign('jumpUrl', U('admin/Global/announcement'));
            $this->success('保存成功');
        }else {
            $announcement = model('Xdata')->lget('announcement');
            $this->assign($announcement);
            $this->display();
        }
    }

    /** 系统配置 - 邮件配置 **/

    public function email(){
        if($_POST){

            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '3';
            $data[] = '全局 - 邮件配置 ';
            $data[] = model('Xdata')->lget('email');
            if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
            $data[] = $_POST;
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);

            unset($_POST['__hash__']);
            model('Xdata')->lput('email',$_POST);
            $this->assign('jumpUrl', U('admin/Global/email'));
            $this->success('保存成功');
        }else{
            $email = model('Xdata')->lget('email');
            $this->assign($email);
            $this->display();
        }
    }

    /** 系统配置 - 附件配置 **/

    public function attachConfig() {
        if ($_POST) {

            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '3';
            $data[] = '全局 - 附件配置 ';
            $data[] = model('Xdata')->lget('attach');
            if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
            $data[] = $_POST;
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);

            $_POST['attach_path_rule']       = t($_POST['attach_path_rule']);
            $_POST['attach_max_size']        = floatval($_POST['attach_max_size']);
            $_POST['attach_allow_extension'] = t($_POST['attach_allow_extension']);
            $this->assign('jumpUrl', U('admin/Global/attachConfig'));
            if ( model('Xdata')->lput('attach', $_POST) )
                $this->success('保存成功');
            else
                $this->error('保存失败');

        }else {
            $data = model('Xdata')->lget('attach');
            $this->assign($data);
            $this->display();
        }
    }

    /** 系统配置 - 文章配置 **/

    public function document() {
        $data = M('document')->order('`display_order` ASC,`document_id` ASC')->findAll();
        $this->assign('data', $data);
        $this->display();
    }

    public function addDocument() {
        $this->assign('type', 'add');
        $this->display('editDocument');
    }

    public function editDocument() {
        $map['document_id'] = intval($_GET['id']);
        $document = M('document')->where($map)->find();
        if ( empty($document) )
            $this->error('该文章不存在');
        $this->assign($document);

        $this->assign('type', 'edit');
        $this->display();
    }

    public function doEditDocument()
    {
        if (($_POST['document_id'] = intval($_POST['document_id'])) <= 0)
            unset($_POST['document_id']);

        // 格式化数据
        $_POST['title']         = t($_POST['title']);
        $_POST['is_active']     = intval($_POST['is_active']);
        $_POST['is_on_footer']  = intval($_POST['is_on_footer']);
        $_POST['last_editor_id']= $this->mid;
        $_POST['mtime']         = time();
        if (preg_match('/^\s*((?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*\.)?[a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z]+)+(?:\:[0-9]*)?(?:\/[^\x{2e80}-\x{9fff}\s<\'\"“”‘’]*)?)\s*$/u', strip_tags(html_entity_decode($_POST['content'], ENT_QUOTES, 'UTF-8')), $url)
            || preg_match('/^\s*((?:mailto):\/\/[a-zA-Z0-9_]+@[a-zA-Z0-9][a-zA-Z0-9\.]*[a-zA-Z0-9])\s*$/u', strip_tags(html_entity_decode($_POST['content'], ENT_QUOTES, 'UTF-8')), $url)) {
            $_POST['content'] = h($url[1]);
        } else {
            $_POST['content'] = t(h($_POST['content']));
        }
        if (!isset($_POST['document_id'])) {
            // 新建文章
            $_POST['author_id'] = $this->mid;
            $_POST['ctime']     = $_POST['mtime'];
        }

        // 数据检查
        if (empty($_POST['title']))
            $this->error('标题不能为空');

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = isset($_POST['document_id']) ? '3' : '1';
        $data[] = '全局 - 文章配置 ';
        isset($_POST['document_id']) && $data[] =  model('Xdata')->lget('platform');
        if( $_POST['__hash__'] ) unset( $_POST['__hash__'] );
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        // 提交
        $res = isset($_POST['document_id']) ? M('document')->save($_POST) : M('document')->add($_POST);

        if ($res) {
        	// 清理缓存
        	F('_action_footer_document', null);
            if ( isset($_POST['document_id']) ) {
                $this->assign('jumpUrl', U('admin/Global/document'));
            } else {
                // 为排序方便, 新建完毕后, 将display_order设置为ad_id
                M('document')->where("`document_id`=$res")->setField('display_order', $res);
                $this->assign('jumpUrl', U('admin/Global/addDocument'));
            }
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    public function doDeleteDocument()
    {
        if (empty($_POST['ids'])) {
            echo 0;
            exit ;
        }

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '2';
        $data[] = '全局 - 文章配置 ';
        $data[] = model('Xdata')->lget('platform');
        if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $map['document_id'] = array('in', t($_POST['ids']));
        echo M('document')->where($map)->delete() ? '1' : '0';
        // 清理缓存
        F('_action_footer_document', null);
    }

    public function doDocumentOrder() {
        $_POST['document_id']   = intval($_POST['document_id']);
        $_POST['baseid']        = intval($_POST['baseid']);
        if ( $_POST['document_id'] <= 0 || $_POST['baseid'] <= 0 ) {
            echo 0;
            exit;
        }

        // 获取详情
        $map['document_id'] = array('in', array($_POST['document_id'], $_POST['baseid']));
        $res = M('document')->where($map)->field('document_id,display_order')->findAll();
        if ( count($res) < 2 ) {
            echo 0;
            exit;
        }

        //转为结果集为array('id'=>'order')的格式
        foreach($res as $v) {
            $order[$v['document_id']] = intval($v['display_order']);
        }
        unset($res);

        //交换order值
        $res =         M('document')->where('`document_id`=' . $_POST['document_id'])->setField(  'display_order', $order[$_POST['baseid']] );
        $res = $res && M('document')->where('`document_id`=' . $_POST['baseid'])->setField( 'display_order', $order[$_POST['document_id']]  );

        if ($res) {
        	// 清理缓存
        	F('_action_footer_document', null);
        	echo 1;
        } else {
        	echo 0;
        }
    }

    /** 审核配置 **/
    public function audit(){
        $audit = model('Xdata')->lget('audit');
        $this->assign($audit);
        $this->display();
    }

    public function doSaveAudit(){
        if($_POST){
            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '3';
            $data[] = '全局 - 审核配置 ';
            $data[] = model('Xdata')->lget('audit', $map);
            if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
            $data[] = $_POST;
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);

            model('Xdata')->lput('audit', $_POST);
        }
        $this->assign('jumpUrl', U('admin/Global/audit'));
        $this->success("配置成功");
    }

	public function testSendEmail(){
		$service = service('Mail');
		$subject = '这是一封测试邮件';
		$content = '这是一封来自'.SITE_URL.'的测试邮件，您能收到这封邮件表明邮件服务器已配置正确。<br />
					如果您不清楚这封邮件的来由，请删除，为给您带来的不便表示歉意';
		echo ( $info = $service->send_email($_POST['testSendEmailTo'], $subject, $content) )?$info:'1';
	}
}
