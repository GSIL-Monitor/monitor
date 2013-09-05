<?php
require_once SYS_MODULE.'monitor/MonitorBaseControl.class.php';

require_once dirname(__FILE__).'/GraphData.class.php';

class GraphControl extends MonitorBaseControl {

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
	
	public function params_schema() {
		return array(
			'create' => array(
				'schema_id'		=> array('required'=>true, 'type'=>'int'),
				'name'			=> array('required'=>true, 'type'=>'string'),
				'fields'		=> array('required'=>true, 'type'=>'json')
			),
			'delete' => array(
				'ids'	=> array('required'=>true, 'type'=>'json')
			),
			'update' => array(
				'id'	=> array('required'=>true, 'type'=>'int'),
				'data'	=> array('required'=>true, 'type'=>'json')
			),
			'get' => array(
				'id'	=> array('required'=>true, 'type'=>'int')
			),
			'get_graphs' => array(
				'schema_id'	=> array('required'=>true, 'type'=>'int')
			)
		);
	}
	
	/**
	 * 创建graph
	 * @return Ambigous <number, unknown>
	 */
	public function create() {
		
		$ret = $this->load_params('create');
		
		$ret = GraphData::instance()->validate($ret, 'create');
		
		if ( $ret['code'] == 0 ) {
			if ( $ret['data'] = GraphData::instance()->save($ret['params']) ) {
				$ret['code'] = 0;
			} else {
				$ret['code'] = 2;
			}
		} else {
			$ret['code'] = 1;
		}
		
		
		return $this->get_ret($ret);
	}
	
	public function delete() {
		
		$ret = $this->load_params('delete');
				
		$ret = GraphData::instance()->validate($ret, 'delete');
		
		if ( $ret['code'] == 0 ) {
			GraphData::instance()->delete(array('_id'=>array('$in'=>$ret['params']['ids'])));
		} 
		
		return $this->get_ret($ret);
	}
	
	public function update() {
		
		$ret = $this->load_params('update');
		$ret = GraphData::instance()->validate($ret, 'update');
		
		if ( $ret['code'] == 0 ) {
			$data = $ret['params']['data'];
			$data['_id'] = $ret['params']['id'];
			if ($ret['data'] = GraphData::instance()->save($data)) {
				$ret['code'] = 0;
			} else {
				$ret['code'] = 2;
			}
		} else {
			$ret['code'] = 1;
		}
		
		return $this->get_ret($ret);
	}
	
	public function get() {
		
		$params = $this->load_params('get');
		$ret = GraphData::instance()->validate($params, 'get');
		
		if ( $ret['code'] == 0 ) {
			$ret['data'] = GraphData::instance()->find_by_pk($ret['params']['id']);
		} else {
			$ret['code'] = 1;
		}
		
		return $this->get_ret($ret);
	}
	
	public function get_graphs() {
		
		$ret = $this->load_params('get_graphs');
		
		$ret = GraphData::instance()->validate($ret, 'get_graphs');
		
		if ( $ret['code'] == 0 ) {
			$ret['data'] = GraphData::instance()->find_by_condition(array('schema_id'=>$ret['params']['schema_id']), $ret['params']['page'], $ret['params']['page_length']);
		} else {
			$ret['code'] = 1;
		}
		
		return $this->get_ret($ret);
	}
}