<?php
require_once dirname(__FILE__).'/MonitorBaseControl.class.php';
require_once dirname(__FILE__).'/MonitorData.class.php';
require_once dirname(__FILE__).'/monitoritem/MonitorStatData.class.php';
require_once dirname(__FILE__).'/monitoritem/GraphData.class.php';

class MonitorControl extends MonitorBaseControl {
    
    private static $_instance;
    
    /**
    * 返回当前类的一个单例
    * 
    */
    static public function &instance() {
        
        if ( empty(self::$_instance) ) {
            $class = __CLASS__;
            self::$_instance = new $class;
        }
        
        //设置脚本开始执行时间
        self::$_instance->_start_time = microtime(true);
        
        return self::$_instance;
    }
    
    protected function params_schema($scenario='') {
    	return array(
    		'report' => array(
				'mid'	=> array('required'=>true, 'type'=>'int'),
    			'time'	=> array('required'=>true, 'type'=>'int'),
    			'data'	=> array('required'=>true, 'type'=>'json')
    		),
    		'get' => array(
    			'id'	=> array('required'=>true, 'type'=>'int'),
    			'start'	=> array('required'=>true, 'type'=>'int'),
    			'end'	=> array('required'=>true, 'type'=>'int'),
    			'interval' => array('required'=>false, 'type'=>'int')
    		),
    		'get_graph'=> array(
    			'mid' 	=> array('required'=>true, 'type'=>'int'),
    			'gids' 	=> array('required'=>true, 'type'=>'json'),
    			'start' => array('required'=>true, 'type'=>'int'),
    			'end' => array('required'=>true, 'type'=>'int')
    		)
    	);
    }
    
    /**
    * 数据提交接口
    * 
    */
    public function report() {
        
        //获取参数
        $ret = $this->load_params('report');

        //参数验证(检查监控项id是否存在, 验证时间戳是否合法,对提交的数据schema、数据类型 进行验证)
        $ret = MonitorStatData::instance()->validate($ret, 'report');
        
        if ($ret['code'] == 0) {
            //验证成功，保存
            if ( !($ret['data'] = MonitorStatData::instance()->save($ret['params']))) {
            	$ret['code'] = 2;
            }
		}
        
        return $this->get_ret($ret);        
    }
    
    
    public function get_graph() {
    	
    	$ret = $this->load_params('get_graph');
    	$ret['code'] == 0 && $ret = MonitorStatData::instance()->validate($ret, 'get_graph');
    	
    	if ( $ret['code'] == 0 ) {
    		
    		//获取数据对应的时间间隔
    		$ret['interval'] = MonitorStatData::instance()->get_interval_by_time($ret['start'], $ret['end']);
    		
    		//查询监控数据
    		$stat_data = MonitorStatData::instance()->get_data_by_time($ret['params']['mid'], $ret['start'], $ret['end']);

    		//查询graph信息
    		$graphs = GraphData::instance()->find_by_condition(array('_id'=>array('$in'=>$ret['params']['gids'])));

    		
    		var_dump($graphs);
    		
    		//组合数据
    		foreach( $ret['params']['gids'] as $key => $gid ) {
    			$data_set[$key]['gid'] = $gid;
    			$data_set[$key]['name'] = isset($graphs[$gid]['name']) ? $graphs[$gid]['name'] : '';
    			foreach ( $graphs[$gid]['fields'] as $field ) {
    				$field_id = $field['field_id']; unset($field['field_id']);
    				$field_data = array();
    				
    				for ( $time=$ret['start']; $time<=$ret['end']; $time+=$ret['interval']*60  ) {
    					$field_data[] = isset($stat_data[$time][$field_id]) ? $stat_data[$time][$field_id] : 0;
    				}
    				
    				//最大值，最小值，均值
    				$field['max'] = max($field_data);
    				$field['min'] = min($field_data);
    				$field['avg'] = round(array_sum($field_data)/count($field_data), 3);

    				$data_set[$key]['fields'][] = $field;
    				$data_set[$key]['data'][] = $field_data;
    			}
    		}

    		$ret['data_set'] = $data_set;
    	}
    	
    	unset($ret['data']);
    	
    	return $this->get_ret($ret);
    }
    
    public function get() {
    	
		$ret = $this->load_params('get');
       
		$ret['code'] == 0 && $ret = MonitorStatData::instance()->validate($ret, 'get');
	       
		if ( $ret['code'] == 0 ) {
			
			if ( isset($ret['params']['interval']) ) {
				MonitorStatData::instance()->set_collection_by_interval($ret['params']['interval']);
				$ret['interval'] = $ret['params']['interval'];
			} else {
				$ret['interval'] = MonitorStatData::instance()->get_interval_by_time($ret['start'], $ret['end']);
			}
       	
       	
	       	$stat_data = MonitorStatData::instance()->get_data_by_time($ret['params']['id'], $ret['start'], $ret['end']);
	       
	       	$monitor_item = MonitorData::instance()->find_by_pk($ret['params']['id']);
	       
	       	$fields = SchemaFieldData::instance()->find_by_schema($monitor_item['schema_id']);
	       
			foreach( $fields as $key => $value ) {
	       	
	       		$ret['fields'][] = $value['name'];
	       		
	       		$field_data = array();	       
		       	for ( $time=$ret['start']; $time<=$ret['end']; $time+=$ret['interval']*60  ) {
		       		$field_data[] = isset($stat_data[$time][$value['_id']]) ? $stat_data[$time][$value['_id']] : '';
		       	}
		       	
		       	$ret['data'][] = $field_data;
	       }

       }

       return $this->get_ret($ret);
    }
    
   
        
}