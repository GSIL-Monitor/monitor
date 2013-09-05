<?php
require_once SYS_MODULE.'monitor/MonitorBaseControl.class.php';

require_once dirname(__FILE__).'/ItemData.class.php';

class ItemControl extends MonitorBaseControl {

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
			'create_metaitem' => array(
				'schema_id'	=> array('required'=>true, 'type'=>'int'),
				'desc'		=> array('required'=>true, 'type'=>'string'),
				'meta_param'=> array('required'=>true, 'type'=>'json')
			),
			'create_groupitem' => array(
				'desc'			=> array('required'=>true, 'type'=>'int'),
				'monitor_ids'	=> array('required'=>true, 'type'=>'json')
			),
			'delete'=> array(
				'ids' => array('required'=>true, 'type'=>'json')
			),
			'get_item' => array(
				'id'	=> array('required'=>true, 'type'=>'int')
			),
			'get_items' => array(
				'page'			=> array('required'=>false, 'type'=>'int'),
				'page_length'	=> array('required'=>false, 'type'=>'int'),
				'schema_id'		=> array('required'=>true, 'type'=>'int'),
				'is_meta'		=> array('required'=>false, 'type'=>'int')
			),
			'update' => array(
				'id'	=> array('required'=>true, 'type'=>'int'),
				'data'	=> array('required'=>true, 'type'=>'json')
			)
		);
	}
	
	/**
	 * 创建metaitem
	 */
	public function create_metaitem() {
		$ret = $this->create('metaitem');
		return $ret;
	}
	
	/**
	 * 创建gruopitem
	 */
	public function create_groupitem() {
		$ret = $this->create('groupitem');
		return $ret;
	}
	
	/**
	 * 创建monitor_item
	 * @return array
	 */
	private function create($item_type) {
	
		$ret = $this->load_params('create_'.$item_type);
		
		//验证数据
		if ( $ret['code'] == 0 ) {
			$ret = ItemData::instance()->validate($ret, $item_type);
		}
		
		//验证通过
		if ( $ret['code'] == 0 ) {
			$data = $ret['params'];
			$data['is_meta'] = $item_type === 'metaitem' ? 1 : 0;
	
			if ( !($ret['data'] = ItemData::instance()->save($data)) ) {
				$ret['code'] = 2;
			}
		}
	
		return $this->get_ret($ret);
	}
	
	public function delete() {
		
		$ret = $this->load_params('delete');
		
		if ( $ret['code'] == 0 ) {
			$ret = ItemData::instance()->validate($ret, 'delete');
		}
		
		if ( $ret['code'] == 0 ) {
			ItemData::instance()->delete(array('_id'=>array('$in'=>$ret['params']['ids'])));
		}
		
		return $this->get_ret($ret);
	}
	
	public function update() {
		
		$ret = $this->load_params('update');
		$ret = ItemData::instance()->validate($ret, 'update');

		if ( $ret['code'] == 0 ) {
			$data = $ret['params']['data'];
			$data['_id'] = $ret['params']['id'];
			
			if ( !($ret['data'] = ItemData::instance()->save($data))) {
				$ret['code'] = 2;
			}
		}
		
		return $this->get_ret($ret);
	}
	
	public function get_item() {
		
		$ret = $this->load_params('get_item');
		
		if ( $ret['code'] == 0 ) {
			$ret = ItemData::instance()->validate($ret, 'get_item');
		}
		
		if ( $ret['code'] == 0 ) {
			$ret['data'] = ItemData::instance()->find_by_pk($ret['params']['id']);
		}
		
		return $this->get_ret($ret);
	}
	
	public function get_items() {
	
		$scenario = 'get_items';
	
		$params = $this->load_params($scenario);

		$ret = ItemData::instance()->validate($params, $scenario);
		
		if ( $ret['code'] == 0 ) {
			
			$page = $ret['params']['page'] - 1;
			$page_length = $ret['params']['page_length'];
			unset($ret['params']['page']);unset($ret['params']['page_length']);
		
			$ret['data'] = ItemData::instance()->find_by_condition($ret['params'], $page, $page_length);
		}
	
		return $this->get_ret($ret);
	}	
	
	
	
	
	
	
}