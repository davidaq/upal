<?php

/**
 * Lock
 * 对缓存文件进行锁操作
 * @package
 * @version $id$
 * @copyright 2001-2013 SamPeng
 * @author SamPeng <penglingjun@zhishisoft.com>
 * @license PHP Version 5.2 {@link www.sampeng.org}
 */
class Lock
{
    /**
     * filePath
     * 文件路径
     * @var mixed
     * @access private
     */
    private $filePath;
    private $targetFile;


    /**
     * fileName
     * 文件名，包含完整路径
     * @var mixed
     * @access private
     */
    private $fileName;

    public function Lock($file)
    {
        $this->targetFile = $file;
    }

    public function sh()
    {
        flock($this->targetFile->getHandle(),LOCK_SH);
    }


    public function ex()
    {
        flock($this->targetFile->getHandle(),LOCK_EX);
    }

    public function nb()
    {
        flock($this->targetFile->getHandle(),LOCK_NB);
    }

    public function unlock()
    {
        flock($this->targetFile->getHandle(),LOCK_UN);
    }
}
