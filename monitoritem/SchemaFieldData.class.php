<?php
require_once SYS_MODULE.'/monitor/MonitorBaseData.class.php';

class SchemaFieldData extends MonitorBaseData {
    
    /**
    * @var string model 对应的 collection
    */
    protected $_collection = 't_schema_field';
    
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
                'schema_id '=>1,
                'sn'=>1,
                'name'=>1, 
                'is_meta'=>1,
                'expr'=>1
            );
    }
    
    
    public function find_by_schema($schema_id, $page='', $page_length='') {
        
        $condition = array('schema_id' => $schema_id);
        $ret = $this->find_by_condition($condition, $page, $page_length);
    	 
    	return $ret;
        
    }
    
    public function batch_insert($data) {
    	foreach( $data as $key => $insert_data ) {
    		$data[$key]['_id'] = AutoId::instance()->inc_id($this->get_collection());
    		$data[$key]['sn'] = AutoId::instance()->inc_id($this->get_collection().'_'.$data[$key]['schema_id'].'_SN');
    	}
  
    	if ( $this->get_mongos()->batch_insert($this->_collection, $data) ) {
    		return $data;
    	} else {
    		return false;
    	}
    		    	
    }
    
    
    protected function validate_create($data) {
    	$ret = $data;
    	
    	foreach($ret['params']['data'] as $key => $value ) {
	    	$ret['params']['data'][$key]['schema_id'] = intval($value['schema_id']);
	    	$ret['params']['data'][$key]['is_meta'] = intval($value['is_meta']);
    	}
    	 
    	return $ret;
    }
    
    protected function validate_update($data) {
    	$ret = $data;
    	
    	if ( isset($ret['params']['data']['schema_id']) ) {
    		$ret['params']['data']['schema_id'] = intval($ret['params']['data']['schema_id']);
    	}
    	
    	if ( isset($ret['params']['data']['is_meta']) ) {
    		$ret['params']['data']['is_meta'] = intval($ret['params']['data']['is_meta']);
    	}
    	 
    	return $ret;
    }
    
    protected function validate_get_fields($data) {
    	
    	$ret = $data;
    		
    	$ret['params']['page'] = isset($ret['params']['page']) ? intval($ret['params']['page']) : $this->page;
    	$ret['params']['page_length'] = isset($ret['params']['page_length']) ? intval($ret['params']['page_length']) : $this->page_length;

    	return $ret;
    }
    
}