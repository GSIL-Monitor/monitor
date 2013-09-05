<?php
require_once 'ModuleControl.class.php';

class MonitorBaseControl extends ModuleControl {
	
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
	
	
	protected function params_rule() {
		return array();
	}
	
	
	protected function load_params($scenario) {
		
		$ret['code'] = 0;
		$ret['params'] = array();
		
		$params_schema = $this->params_schema();
		
		//$scenario_params = isset($require_params[$scenario]) ? $require_params[$scenario] : array_pop($require_params);
		
		$scenario_params = $params_schema[$scenario];
				
		$request_params = AppRequest::instance()->params;
		
		foreach( $scenario_params as $params_key => $params ) {
			if ( isset($request_params[$params_key]) ) {
				if ( isset($params['type']) ) {
					switch ($params['type']) {
						case 'int' :
							$ret['params'][$params_key] = intval($request_params[$params_key]);
							break;
						case 'json' :
							if ( is_string($request_params[$params_key]) ) {
								$ret['params'][$params_key] = json_decode($request_params[$params_key], true);
								if (!is_array($ret['params'][$params_key]) ) {
									$ret['code'] = 1;
									$ret['desc'] = $params_key.'格式错误';
								}
							} elseif( is_array($request_params[$params_key]) ) {
								$ret['params'][$params_key] = $request_params[$params_key];
							} else {
								$ret['code'] = 1;
								$ret['desc'] = $params_key.'格式错误';
							}
							break;
						default :
							$ret['params'][$params_key] = $request_params[$params_key];
					}
				} else {
					$ret['params'][$params_key] = $request_params[$params_key];
				}
			} elseif( $params['required'] ) {
				$ret['code'] = 1;
				$ret['desc'] = '缺少参数：'.$params_key;
			}
		}
		
		return $ret;
	}
	
	
	/**
	 * 返回数据
	 * @param mix $ret_data
	 * @return mix $ret
	 */
	protected function get_ret($ret_data) {
		$ret = array(
			'e'=> array("code"=>0, "provider"=>"monitor", "desc"=>''),
			'cost'=> 0,
		);
	
		$ret['e']['code'] = isset($ret_data['code']) ? $ret_data['code'] : 0;
		$ret['e']['desc'] = isset($ret_data['desc']) ? $ret_data['desc'] : '';
		
		$ret = $ret + array_diff_key($ret_data, $ret['e']);
		
		$ret['cost'] = round(microtime(true) - $this->_start_time, 3);
		
		//unset($ret['params']);

	
		return $ret;
	}
	
}