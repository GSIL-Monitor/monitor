<?php
require_once dirname(__FILE__).'/../MonitorData.class.php';

require_once dirname(__FILE__).'/MonitorStatMQ.class.php';
require_once dirname(__FILE__).'/GraphData.class.php';


class MonitorStatData extends MonitorBaseData {
    
    /**
    * @var string model 对应的 collection
    */
    protected  $_collection = 't_stat_minute';
    
    /**
    * @var string collection 的 primary key
    */
    protected $_pk = '_id';
    
    private $_buckets = 5;
    
    
    /**
    * @var KeyvalDataEngine 获取kv的类
    */
    protected $_kv;
    
    static private $_instance;
    
    static public function &instance(){
        if (empty(self::$_instance)) {
            $class = __CLASS__;
            self::$_instance = new $class();
        }
        return self::$_instance;
    }
    
    public function set_collection_by_time($start, $end) {
    	
    	$interval = $this->get_interval_by_time($start, $end);
    	
	   	return $this->set_collection_by_interval($interval);
    }
    
    public function set_collection_by_interval($interval) {
    	
    	if ( !in_array($interval, array(1, 5, 30, 120, 1440)) ) {
    		return false;
    	} 
    	if ( $interval == 1 ) {
    		$collection = 't_stat_minute';
    	} else {
    		$collection = 't_stat_minute_'.$interval;
    	}
    	 
    	return $this->_collection = $collection;
    	
    }
    
    public function save($data) {
        return $this->save_db($data);
    }
    
    /**
     * 将数据保存到数据库中
     * @param array $data
     */
    
    private function save_db($data) {
    	
    	$retdata = $data;
    	
    	$id = $this->generate_id($data);
    	$mid = $data['mid'];
    	$time = $data['time'];
    	
    	unset($data['time']);unset($data['mid']);
    	
    	$savedata['$set']['h'] = $this->get_bucket_id($mid);
    	$savedata['$set']['t'] = $time;
    	$savedata['$set']['d.'.$mid] = $data['data'];
        
        $ret = $this->get_mongos()->upsert(
        	$this->_collection,
        	array('_id'=>$id), 
        	$savedata,
        	$insert
    	);
        
        if ( $ret ) {
        	$ret = $retdata;
        }

        return $ret;
    }
    
    private function save_mq($data) {
    	$ret = MonitorStatMQ::instance()->mq_write($data, false);
    	if ( $ret ) {
    		$ret = $data;
    	}
    	return $ret;
    }
    
    
    public function generate_id($data) {
    	$bucket_id = $this->get_bucket_id($data['mid']);
    	$id = floatval($bucket_id * 10000000000000 + $data['time']);
    	return $id;
    }
    
    public function get_bucket_id($mid) {
    	return $mid % $this->_buckets;
    }
    
    
    public function find_by_condition($condition) {

    	$ret = array();
    	
    	$m = $condition['m']; unset($condition['m']);
    	
    	$q = $this->build_query($condition);
    	
    	$q[FEILDS]['d.'.$m] = 1;
    	
    	$data = $this->get_kv_data($q,$this->_kv);
    	
    	foreach( $data as $bdata ) {
    		if (!empty($bdata['d'])) {
    			foreach( $bdata['d'] as $mid => $mdata ) {
    				$mdata = array('m'=>$mid, 't'=>$bdata['t']) + $mdata;
    				$ret[] = $mdata;
    			}
    		}
    	}
 	
		return $ret;
    }
    
    
    public function get_data_by_time($mid, $start, $end) {
    	
    	$ret = array();
    	
    	$start_id = $this->generate_id(array('mid'=>$mid, 'time'=>$start));
    	$end_id = $this->generate_id(array('mid'=>$mid, 'time'=>$end));
    	
    	$this->set_collection_by_time($start, $end);

    	$condition = array('_id'=>array('$gte'=>$start_id, '$lte'=>$end_id), 'm'=>$mid);
    	
    	$data = $this->find_by_condition($condition);
    	
    	foreach( $data as $key => $value ) {
    		$time = $value['t']; unset($value['t'], $value['m']);
    		$ret[$time] = $value;
    	}
    	
    	return $ret;    	
    }
    
    public function get_interval_by_time($start, $end) {
    	
    	$interval = 1;
    	
    	$time = $end - $start;
    	
    	$day = 24*60*60;
    	
    	if ( $time < $day*0.5 ) {
    		$interval = 1;
    	} elseif ( $time < $day ) {
    		$interval = 5;
    	} elseif ( $time < $day*7 ) {
    		$interval = 30;
    	} elseif ( $time < $day*14 ) {
    		$interval = 120;
    	} else{
    		$interval = 1440;
    	}

    	return $interval;
    }
    
	public function build_query($condition){
        return array(
            COLLECTION=>$this->_collection,
            QUERY=>$condition,
            FEILDS=>array(
                '_id'=>1,
            	't'=>1
            )
        );
    }

    
    protected function validate_report($data) {
    	
    	$ret = $data;
    	
    	unset($ret['params']['data']);
    	
    	    
    	//将时间戳转换成整分钟
    	$ret['params']['time'] = ceil($data['params']['time']/60)*60;
    	
    	//检查监控项是否存在
    	if ( !($monitor_item = MonitorData::instance()->find_by_pk($data['params']['mid'])) ) {
    		$ret['code'] = 1101;
    		$ret['desc'] = 'mid不存在';
    	}
    	
    	//检查对应schema的字段
    	if ( !($schema_fields = SchemaFieldData::instance()->find_by_schema($monitor_item['schema_id'])) ) {
    		$ret['code'] = 1101;
    		$ret['desc'] = 'monitor_item对应的schema无字段，请先添加字段';
    	}

    	foreach( $schema_fields as $field ) {
    		//meta字段
    		if ( $field['is_meta'] == 1 && isset($data['params']['data'][$field['name']]) ) {
    			$ret['params']['data'][$field['_id']] = floatval($data['params']['data'][$field['name']]);
    		} elseif( $field['is_meta'] == 0 ) {
    			//需要计算的字段
    			$ret['params']['data'][$field['name']] = '';
    		} else {
    			$ret['code'] = 1103;
    			$ret['desc'] = 'data中的字段与schema不一致';
    		}
    	}
    	
    	return $ret;
    }
    
    protected function validate_get_graph($data) {
    	
    	$ret = $data;
    	
    	//检查监控项是否存在
    	if ( !($monitor_item = MonitorData::instance()->find_by_pk($data['params']['mid'])) ) {
    		$ret['code'] = 1101;
    		$ret['desc'] = 'mid不存在';
    	}
    	
    	foreach( $data['params']['gids'] as $gid ) {
	    	if ( !($graph = GraphData::instance()->find_by_pk($gid)) ) {
	    		$ret['code'] = 1101;
	    		$ret['desc'] = 'gid不存在';
	    	} elseif ( $graph['schema_id'] != $monitor_item['schema_id'] ) {
	    		$ret['code'] = 1101;
	    		$ret['desc'] = 'monitor_item与graph的schema不一致';	    		
	    	}
    	}
    	
    	//将时间戳转换成整分钟
    	$ret['start'] = ceil($data['params']['start']/60)*60;
    	$ret['end'] = ceil($data['params']['end']/60.0)*60;
    	
    	return $ret;
    }
    
    protected function validate_get($data) {
    	
    	$ret = $data;
    	 
    	//检查监控项是否存在
    	if ( !($monitor_item = MonitorData::instance()->find_by_pk($data['params']['id'])) ) {
    		$ret['code'] = 1101;
    		$ret['desc'] = 'mid不存在';
    	}
    	
    	if ( isset($ret['params']['interval']) && !in_array($ret['params']['interval'], array(1, 5, 30, 120, 1440)) ) {
    		$ret['code'] = 1101;
    		$ret['desc'] = 'interval错误';
    	}
    	 
    	//将时间戳转换成整分钟
    	$ret['start'] = ceil($data['params']['start']/60)*60;
    	$ret['end'] = ceil($data['params']['end']/60.0)*60;
    	 
    	return $ret;
    }
       
}