<?php 
include_once 'function.php';

switch ($do_type){
case 'before_publish': //发布前检验	
 	$options['userId']		=	$this->mid;
	$options['max_size']    =   10 * 1024 * 1024;
	$info	=	X('Xattach')->upload('weibo_file', $options);

	if ($info['status']) {
        $result['boolen']    = 1;
        $result['file_id']  = $info['info'][0]['id'];
        $result['file_ext']  = $info['info'][0]['extension'];
        $result['file_name'] = $info['info'][0]['name'];
        $result['file_url']  = __UPLOAD__ . '/' . $info['info'][0]['savepath'] . $info['info'][0]['savename'];
	} else {
        $result['boolen']  = 0;
        $result['message'] = $info['info'];
	}
    exit( json_encode($result) );
    break;

case 'publish':  //发布处理
	$type_data = intval($type_data);
	$info 	   = model('Attach')->field('id,extension,name,savepath,savename')->find($type_data);
    $typedata['file_id']   = $info['id'];
    $typedata['file_ext']  = $info['extension'];
    $typedata['file_name'] = $info['name'];
    $typedata['file_url']  = $info['savepath'] . $info['savename'];
    break;

case 'after_publish': //发布完成后的处理
    break;
}