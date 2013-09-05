<?php
require_once 'ModuleData.class.php';

require_once SYS_MODULE.'/monitor/AutoId.class.php';
require_once SYS_MODULE.'/monitor/MonitorKeyval.class.php';

class MonitorBaseData extends ModuleData {
    
    /**
    * @var string model 对应的 collection
    */
    protected  $_collection = '';
    
    /**
    * @var string collection 的 primary key
    */
    protected $_pk = '';
    
    /**
    * @var KeyvalDataEngine 获取kv的类
    */
   	protected $_kv;
   	
   	/**
   	 * 默认的页码
   	 * @var int
   	 */
   	public $page = 0;
   	
   	/**
   	 * 默认每页记录数
   	 * @var int
   	 */
   	public $page_length = 10;
    
    static private $_instance;
    
    function __construct($redissvrinfo="",$dbs=""){
        parent::__construct(array(REDIS_GRP=>"",REDIS_SRV=>"",REDIS_PORT=>0),'monitor_datasrc');
        
        $this->_kv = MonitorKeyval::instance();
    }
    
    static public function &instance(){
        if (empty(self::$_instance)) {
            $class = __CLASS__;
            self::$_instance = new $class();
        }
        return self::$_instance;
    }
    
    public function get_collection() {
    	return $this->_collection;
    }
    
    public function set_collection($collection) {
    	return $this->_collection = $collection;
    }
    
    public function get_schema() {
    	return false;
    }
    
    /**
    * 根据主键查找记录
    *  
    * @param mixed $pk
    */
    public function find_by_pk($pk) {
        
        $ret = false;
        
        $q = $this->build_query(array($this->_pk => $pk));

        if ( !empty($this->_kv) ) {
            if ( ($r = $this->_kv->getdata($q)) && !empty($r['data'][0]) )
                $ret = $r['data'][0];
        }

        return $ret;
    }
    
    public function delete($condition) {
    	return $this->get_mongos()->delete($this->get_collection(), $condition, false);
    }
    
    public function find_by_condition($condition, $page=-1, $page_length=-1) {
    
    	$ret = array();
    	
    	$mongo = $this->get_mongos();
    	
    	if ( empty($this->_kv) ) {
    		return false;
    	}
    	
    	if ( empty($mongo) ) {
    		return false;
    	}
    	 
    	$q = $this->build_query($condition);
    	
    	//$data = $this->get_kv_data($q,$this->_kv);
    	
    	$data = $this->_kv->getdata($q, $page, $page_length);
    	
    	if ( !empty($this->_pk) ) {
    		if ( !empty($data['data']) )
	    	foreach( $data['data'] as $key => $value ) {
	    		$ret[$value[$this->_pk]] = $value;
	    	}
    	} else {
    		$ret = $data['data'];
    	}
    
    	return $ret;
    }
    
    public function save($data, $inc_id = true, $upsert = false) {
    	 
    	$ret = false;
    	
    	if ( empty($data['_id'])) {
    		$inc_id && $data['_id'] = AutoId::instance()->inc_id($this->get_collection());
    		$ret = $this->get_mongos()->insert($this->_collection, $data);
    	} elseif($upsert) {
    		$condition = array('_id'=>$data['_id']);unset($data['_id']);
    		$ret = $this->get_mongos()->upsert($this->get_collection(), $condition, $data);
       	} else {
    		$condition = array('_id'=>$data['_id']);unset($data['_id']);
    		$ret = $this->get_mongos()->update($this->get_collection(), array('$set'=>$data), $condition);
    	}
    	
    	if ( $ret ) {
    		$id = isset($data['_id']) ? $data['_id'] : $condition['_id'];
    		$ret = $this->find_by_pk($id);
    	}
    	
    	return $ret;
    }
    
    /**
    * 根据条件构造查询条件
    * 
    * @param mixed $condition
    */
    public function build_query($condition){
        return array(
            COLLECTION=>$this->get_collection(),
            QUERY=>$condition,
            FEILDS=>$this->get_schema()
        );
    }
    
    public function validate($params, $scenario) {
    	$ret = $params;
    	
    	$method_name = 'validate_'.$scenario;
    	
    	if ( method_exists($this, $method_name) ) {
    		$ret = $this->$method_name($ret);
    	}
    	
    	return $ret;
    }
    

}