<?php
require_once 'KeyvalDataEngine.class.php';

class MonitorKeyval extends KeyvalDataEngine {
    static private $_instance;
    
    function __construct($redissvrinfo=array(REDIS_GRP=>'monitor_cache',REDIS_SRV=>"",REDIS_PORT=>0),$dbs="monitor_datasrc"){
        parent::__construct($redissvrinfo,$dbs);
    }
    
    static public function &instance(){
        if (empty(self::$_instance)) {
            $class = __CLASS__;
            self::$_instance = new $class();
        }
        return self::$_instance;
    }
    
    
    public function put_cache_data($k,$data,$q) {
        return $data;
    }
}
