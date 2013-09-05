<?php
require_once 'MessageQueue.class.php';

class MonitorStatMQ extends MessageQueue{
    static private $_instance;

    function __construct($redissvrinfo="",$dbs=""){
        parent::__construct(array(MQSOURCE=>'monitor_mqsrc'),'monitor_datasrc');
        $this->_nodata_cnt = 0;
    }

    static public function &instance(){
        if (!isset(self::$_instance)) {
            $class = __CLASS__;
            self::$_instance = new $class();
        }
        return self::$_instance;
    }
    
    
    public function get_mqkey(){
        return md5(__class__);
    }
}