<?php
require_once SYS_MODULE.'/monitor/MonitorBaseControl.class.php';

require_once dirname(__FILE__).'/SchemaData.class.php';

class SchemaControl extends MonitorBaseControl {

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
				'name'		=> array('required'=>true),
				'meta_type'	=> array('required'=>true)
			),
			'get' => array(
				'id' => array('required'=>true, 'type'=>'int')
			),
			'get_schemas' => array(
				'page'			=> array('required'=>false, 'type'=>'int'),
				'page_length'	=> array('required'=>false, 'type'=>'int'),
				'meta_type'		=> array('required'=>true, 'type'=>'string')
			),
			'delete' => array(
				'ids'		=>array('requried'=>true, 'type'=>'json')
			),
			'update' => array(
				'id'		=> array('required'=>true, 'type'=>'int'),
				'data'		=> array('requried'=>true, 'type'=>'json')
			)
		);
	}
	
	
	/**
	 * 创建schema
	 * @return Ambigous <number, unknown>
	 */
	public function create() {

		$ret = $this->load_params('create');
		
		if ( $ret['code'] == 0 ) {
			$ret = SchemaData::instance()->validate($ret, 'create');
		}
		
		if ( $ret['code'] == 0 ) {
			$ret['data'] = SchemaData::instance()->save($ret['params']);
		}
		
		return $this->get_ret($ret);
	}
		
	/**
	 * 删除
	 * @return Ambigous <number, unknown>
	 */
	public function delete() {
		
		$ret = $this->load_params('delete');
		
		if ( $ret['code'] == 0 ) {
			$ret = SchemaData::instance()->validate($ret, 'delete');
		}
		
		if ( $ret['code'] == 0 ) {
			SchemaData::instance()->delete(array('_id'=>array('$in'=>$ret['params']['ids'])));
		}
		
		return $this->get_ret($ret);
	}
	
	/**
	 * 更新
	 */
	public function update() {
		
		$scenario = 'update';
	
		$ret = $this->load_params($scenario);
		$ret = SchemaData::instance()->validate($ret, $scenario);

		if ( $ret['code'] == 0 ) {
			$data = $ret['params']['data'];
			$data['_id'] = $ret['params']['id'];
			if ( !($ret['data'] = SchemaData::instance()->save($data)))
				$ret['code'] = 2;
		}
	
		return $this->get_ret($ret);
	
	}
	
	public function get() {
		
		$ret = $this->load_params('get');
		
		$ret = SchemaData::instance()->validate($ret, 'get');

		if ( $ret['code'] == 0 ) {
			$ret['data'] = SchemaData::instance()->find_by_pk($ret['params']['id']);
		}
		
		return $this->get_ret($ret);
		
	}
	
	public function get_schemas() {
		
		$scenario = 'get_schemas';
		
		$ret = $this->load_params($scenario);
		
		$ret = SchemaData::instance()->validate($ret, $scenario);
		
		if ( $ret['code'] == 0 ) {
			$ret['data'] = SchemaData::instance()->find_by_condition(array('meta_type'=>$ret['params']['meta_type']), $ret['params']['page'], $ret['params']['page_length']);
		}
		
		return $this->get_ret($ret);
		
	}
	

	
	
}