<?php
require_once SYS_MODULE.'monitor/MonitorBaseData.class.php';

require_once SYS_MODULE.'monitor/monitoritem/SchemaFieldData.class.php';


class ItemData extends MonitorBaseData {
    
    /**
    * @var string model 对应的 collection
    */
    protected  $_collection = 't_monitor_item';
    
    /**
    * @var string collection 的 primary key
    */
    protected  $_pk = '_id';
    
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
            'is_meta'=>1,
            'desc'=>1,
            'meta_param'=>1,
            'monitor_ids'=>1
    	); 
    }
    
    public function save($data) {
    	
    	$ret = false;
    	
    	if ( empty($data['_id']) ) {
    		$data['_id'] = AutoId::instance()->inc_id('itemdata_id');
    		$ret = $this->get_mongos()->insert($this->_collection, $data);
    	} else {
    		$condition = array('_id'=>$data['_id']);
    		unset($data['_id']);
    		$ret = $this->get_mongos()->update($this->_collection, array('$set'=>$data), $condition);
    	}
    	if ( $ret ) {
    		$id = isset($data['_id']) ? $data['_id'] : $condition['_id'];
    		$ret = $this->find_by_pk($id);
    	} 

    	return $ret;
    }
    
    

    /**
     * 数据验证
     * @param array $data
     * @param string $scenario
     */
    
    protected function validate_metaitem($data) {
    	
    	$ret = $data;

    	if ( !SchemaData::instance()->find_by_pk($data['params']['schema_id']) ) {
    		$ret['code'] = 1011;
    		$ret['desc'] = 'schema不存在';
    	}

    	return $ret;
    }
    
    protected function validate_groupitem($data) {

    	$ret = $data;
    	
    	//验证schema是否一致
		$schema_ids = $this->get_monitor_schema_id($ret['params']['monitor_ids']);
    	$schema_ids = array_flip(array_flip($schema_ids));
    			
    	if (count($schema_ids) == 1) {
    		$ret['params']['schema_id'] = array_pop($schema_ids);
    	} else {
    		$ret['code'] = 2011;
    		$ret['desc'] = 'metaitem的shema不一致';
    	}

    	return $ret;
    }
    
    protected function validate_get_item($data) {
    	return $data;
    }
    
    protected function validate_get_items($data) {
    	
    	$ret = $data;
       		
    	$ret['params']['page'] = isset($ret['params']['page']) ? intval($ret['params']['page']) : $this->page;
    	$ret['params']['page_length'] = isset($ret['params']['page_length']) ? intval($ret['params']['page_length']) : $this->page_length;
    	 
    	return $ret;
    }
    
    protected function validate_delete($data) {
		return $data;
    }
    
    protected function validate_update($data) {
    	return $data;
    }
    
    
    public function get_monitor_schema_id($monitor_ids) {
    	$condition = array('_id'=>array('$in'=>$monitor_ids));
    	$q = $this->build_query($condition);

    	
    	$ret = array();
    	if ( !empty($this->_kv) ) {
            if ( ($r = $this->_kv->getdata($q)) && !empty($r['data']) ) {
	            foreach( $r['data'] as $key => $value) {
	            	$ret[$value['_id']] = $value['schema_id'];
	            }
            }
        }
        
        
        
        
        
        return $ret;
    	
    	
    }



}