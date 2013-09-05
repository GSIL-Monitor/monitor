<?php
require_once SYS_MODULE.'monitor/MonitorBaseControl.class.php';

require_once dirname(__FILE__).'/SchemaFieldData.class.php';

class SchemaFieldControl extends MonitorBaseControl {

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
					'data'	=> array('required'=>true, 'type'=>'json')
				),
				'delete' => array('ids'=>1),
				'update' => array(
					'id'	=>array('required'=>true, 'type'=>'int'),
					'data'	=>array('required'=>true, 'type'=>'json')
				),
				'get_fields' => array(
					'page'			=> array('required'=>false, 'type'=>'int'),
					'page_length'	=> array('required'=>false, 'type'=>'int'),
					'schema_id'		=> array('required'=>true, 'type'=>'int')
				)
		);
	}
	
	
	public function delete() {
		$scenario = 'delete';
		
		$params = $this->load_params($scenario);
		
		$ret = ItemData::instance()->validate($params, $scenario);

		
		if ( $ret['code'] == 0 ) {
			ItemData::instance()->delete(array('_id'=>array('$in'=>$ret['data']['ids'])));
		}
		
		return $this->get_ret($ret);
	}

	
	/**
	 * 创建monitor_item
	 * @return array
	 */
	public function create() {
		
		$ret = $this->load_params('create');


		//验证数据
		$ret = SchemaFieldData::instance()->validate($ret, 'create');

		
		//验证通过
		if ( $ret['code'] == 0 ) {
			if ( $ret['data'] = SchemaFieldData::instance()->batch_insert($ret['params']['data']) ) {
				$ret['code'] = 0;
			} else {
				$ret['code'] = 2;
			}
		} else {
			$ret['code'] = 1;
		}

		
		return $this->get_ret($ret);
	}
	
	
	public function update() {
		
		$ret = $this->load_params('update');
		$ret = SchemaFieldData::instance()->validate($ret, 'update');

		
		if ( $ret['code'] == 0 ) {
			
			$data = $ret['params']['data'];
			$data['_id'] = $ret['params']['id'];
			
			if( $ret['data'] = SchemaFieldData::instance()->save($data) ) {
				$ret['code'] = 0;
			} else {
				$ret['code'] = 2;
			}
		} else {
			$ret['code'] = 1;
		}
		

		return $this->get_ret($ret);
	}
	
	public function get_fields() {
		
		$ret = $this->load_params('get_fields');
		
		$ret = SchemaFieldData::instance()->validate($ret, 'get_fields');

		if ( $ret['code'] == 0 ) {
			$ret['data'] = SchemaFieldData::instance()->find_by_schema($ret['params']['schema_id'], $ret['params']['page'], $ret['params']['page_length']);
		} else {
			$ret['code'] = 1;
		}
			
		return $this->get_ret($ret);
	}
	
	
		
	
}