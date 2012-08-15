<?php
require_once 'Lock.class.php';
class SimpleFile
{
    private $path;
    private $fileName;
    private $handle;
    private $ttl;
    const FREAD_LENGTH = 4012;
    public static $ROOT_PATH = './test';
    const LEVEL = 2;
    public function SimpleFile($cacheKey){
        if(!$this->_fileModeInfo(self::$ROOT_PATH)){
            throw new Exception("Cache Path ".realpath(self::$ROOT_PATH)."  Can't write'");
        }
        $this->_keyToPathHash($cacheKey);
        if(!file_exists($this->path)){
            mkdir($this->path,0777,true);
        }
        $this->fileName=$this->path.'/'.md5($this->path.$cacheKey);
    }

    public static function setRoot($path){
        self::$ROOT_PATH = $path;
        if(!file_exists($path)){
            mkdir($path);
        }
    }
    public function getHandle(){
        if(empty($this->handle)) $this->_open();
        return $this->handle;
    }

    public function getFileName(){
        return $this->fileName;
    }

    public function ttl ($ttl)
    {
        $this->ttl = $ttl;
    }

    public function save($content,$lock = LOCK_EX){
        $this->_open("w+");
        $lock = new Lock($this);
        switch(LOCK_SH){
        case LOCK_NB:
            $lock->nb();
            break;
        case LOCK_EX:
            $lock->ex();
            break;
        default:
            $lock->ex();
        }
        if(!$this->_fileModeInfo($this->fileName)){
            throw new Exception("Cache File ".realpath($this->fileName)."  Can't write'");
        }
        $result = fwrite($this->handle,$content);
        $lock->unlock();
        $this->_close();
        return $result;
    }

    public function rm(){
        return unlink($this->fileName);
    }

    public function read(){
        $this->_open('r');
        $lock = new Lock($this);
        $lock->sh();
        if($this->ttl > 0){
            if(time() - filemtime($this->fileName) >= $this->ttl ){
                $lock->unlock();
                $this->_close();
                unlink($this->fileName);
                return false;
            }
        }
        $content = false;
        while(!feof($this->handle)){
            $content .= fread($this->handle,4012);
        }
        $lock->unlock();
        $this->_close();
        return $content;
    }

    private function _close(){
        fclose($this->handle);
    }

    private function _open($mode = "r"){
        if(!file_exists($this->fileName)){
            touch($this->fileName);
        }

        if(!$this->_fileModeInfo($this->fileName)){
            throw new Exception("Cache File ".realpath($this->fileName)."  Can't write'");
        }

        $this->handle = fopen($this->fileName,$mode);
    }

    private function _keyToPathHash($key){
        $hash =sprintf('%u',crc32(self::$ROOT_PATH.$key));
        $step = strlen($hash)/self::LEVEL;
        $temp_str_split = str_split($hash,$step);
        if(strlen($hash)%self::LEVEL){
            $split_last = array_splice($temp_str_split,-(count($temp_str_split)-(self::LEVEL)));
            foreach($split_last as $key=>$value){
                $temp_str_split[$key] .= $value;
            }
        }
        foreach($temp_str_split as $key=>$value){
            $temp_str_split[$key] = 0;
            for($i=0;$i<strlen($value);$i++){
                $temp_str_split[$key] += $value[$i];
            }
        }
        $this->path = self::$ROOT_PATH.'/'.implode('/',$temp_str_split);
    }

    private function _fileModeInfo($file_path)
    {
        /* 如果不存在，则不可读、不可写、不可改 */
        if (!file_exists($file_path))
        {
            return false;
        }
        $mark = 0;
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
        {
            /* 测试文件 */
            $test_file = $file_path . '/cf_test.txt';
            /* 如果是目录 */
            if (is_dir($file_path))
            {
                /* 检查目录是否可读 */
                $dir = @opendir($file_path);
                if ($dir === false)
                {
                    return $mark; //如果目录打开失败，直接返回目录不可修改、不可写、不可读
                }
                if (@readdir($dir) !== false)
                {
                    $mark ^= 1; //目录可读 001，目录不可读 000
                }
                @closedir($dir);
                /* 检查目录是否可写 */
                $fp = @fopen($test_file, 'wb');
                if ($fp === false)
                {
                    return $mark; //如果目录中的文件创建失败，返回不可写。
                }
                if (@fwrite($fp, 'directory access testing.') !== false)
                {
                    $mark ^= 2; //目录可写可读011，目录可写不可读 010
                }
                @fclose($fp);
                @unlink($test_file);
                /* 检查目录是否可修改 */
                $fp = @fopen($test_file, 'ab+');
                if ($fp === false)
                {
                    return $mark;
                }
                if (@fwrite($fp, "modify test.\r\n") !== false)
                {
                    $mark ^= 4;
                }
                @fclose($fp);
                /* 检查目录下是否有执行rename()函数的权限 */
                if (@rename($test_file, $test_file) !== false)
                {
                    $mark ^= 8;
                }
                @unlink($test_file);
            }
            /* 如果是文件 */
            elseif (is_file($file_path))
            {
                /* 以读方式打开 */
                $fp = @fopen($file_path, 'rb');
                if ($fp)
                {
                    $mark ^= 1; //可读 001
                }
                @fclose($fp);
                /* 试着修改文件 */
                $fp = @fopen($file_path, 'ab+');
                if ($fp && @fwrite($fp, '') !== false)
                {
                    $mark ^= 6; //可修改可写可读 111，不可修改可写可读011...
                }
                @fclose($fp);
                /* 检查目录下是否有执行rename()函数的权限 */
                if (@rename($test_file, $test_file) !== false)
                {
                    $mark ^= 8;
                }
            }
        }
        else
        {
            if (@is_readable($file_path))
            {
                $mark ^= 1;
            }
            if (@is_writable($file_path))
            {
                $mark ^= 14;
            }
        }
        return $mark;
    }
}
