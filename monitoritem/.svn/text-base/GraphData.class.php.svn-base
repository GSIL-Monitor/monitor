<?php
require_once SYS_MODULE.'monitor/MonitorBaseData.class.php';

class GraphData extends MonitorBaseData {
    
    /**
    * @var string model 对应的 collection
    */
    protected $_collection = 't_graph';
    
    /**
    * @var string collection 的 primary key
    */
    protected $_pk = '_id';
        
    static private $_instance;
    
    static public function &instance(){
        if (empty(self::$_instance)) {
            $class = __CLASS__;
            self::$_instance = new $class();
        }
        return self::$_instance;
    }
    
    
    public function get_schema() {
    	return array(
                '_id'=>1,
                'schema_id'=>1,
                'name'=>1,
            	'fields'=>1
            );
    }
    
        
    protected function validate_create($data) {
    	$ret = $data;   	
    	return $ret;    	
    }
    
    protected function validate_delete($data) {
    	return $data;
    }
    
    public function validate_update($data) {
    	return $data;
    }
    
    protected function validate_get($data) {
    	$ret = $data;
    	
    	if ( $ret['params']['id'] < 1 ) {
    		$ret['code'] = 1;
    	}
    	
    	return $ret;
    }
    
    protected function validate_get_graphs($data) {
    	
    	$ret = $data;

    	$ret['params']['page'] = isset($ret['params']['page']) ? intval($ret['params']['page']) : $this->page;
    	$ret['params']['page_length'] = isset($ret['params']['page_length']) ? intval($ret['params']['page_length']) : $this->page_length;
    	
    	return $ret;
    }
    
    



}