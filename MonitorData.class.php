<?php
require_once dirname(__FILE__).'/MonitorBaseData.class.php';

require_once SYS_MODULE.'/monitor/monitoritem/SchemaData.class.php';

class MonitorData extends MonitorBaseData {
    
    /**
    * @var string model 对应的 collection
    */
    protected  $_collection = 't_monitor_item';
    
    /**
    * @var string collection 的 primary key
    */
    protected $_pk = '_id';
    
    /**
    * @var KeyvalDataEngine 获取kv的类
    */
   	protected $_kv;
    
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
    
    /**
    * 根据条件构造查询条件
    * 
    * @param mixed $condition
    */
    public function build_query($condition){
        return array(
            COLLECTION=>$this->_collection,
            QUERY=>$condition,
            FEILDS=>array(
                '_id'=>1,
                'schema_id'=>1,
                'is_meta'=>1, 
                'is_meta'=>1,
                'meta_param'=>1,
                'monitor_ids'=>1
            )
        );
    }
    
    
    /**
    * 验证接口接收的数据是否合法，如果合法，返回解析后的数据，如果不合法，返回false
    * 
    * @param array $params
    */
    public function report_validate($params) {
        
        $ret = false;
        
        //检查mid time
        if ( empty($params['mid']) || empty($params['time']) ) {
            return $ret;
        } else {
            $mid = intval($params['mid']); intval($time = $params['time']);
        }
        
        $monitor = $this->find_by_pk(intval($params['mid']));


        //获取 monitor 的 schema
        if ( $monitor ) {
            $schema_fields = SchemaData::instance()->get_schema_fields($monitor['schema_id']);

            $monitor['schema_fields'] = array();
        
            foreach($schema_fields as $key => $value) {
                //将通过计算获取的字段过滤掉
                if ( $value['is_meta'] == 0 ) 
                    continue;
                
                $monitor['schema_fields'][$value['name']] = '';
            }
        }
        
        //验证schema
        if ( !empty($monitor['schema_fields']) ) { 
        
            $data = json_decode($params['data'], true);
        
            foreach( $data as $key => $value ) {
                if ( isset($monitor['schema_fields'][$key]) ) {
                    $data[$key] = floatval($value);
                } else {
                    unset($data[$key]);
                }
            }
        
         
            $data = array_intersect_key($data, $monitor['schema_fields']);
        
            $fields = array_intersect_key($monitor['schema_fields'], $data);
            
            //判断字段是否一致        
            if ($fields == $monitor['schema_fields']) {
                $ret = $data;
                
                $ret['mid'] = $mid;
                $ret['time'] = $time;
            }
        }
      
        return $ret;        
    }
    
}