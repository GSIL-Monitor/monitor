<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');
header("content-type:text/html;charset=utf-8");

require_once 'MonitorControl.class.php';

class monitor_API extends AppModule {
	
	/**
	 * 数据提交接口
	 */
    public function report(){
        return MonitorControl::instance()->report();
    }
    
    /**
     * 监控图数据接口
     */
    public function get_graph() {
        return MonitorControl::instance()->get_graph();
    }
    
    /**
     * 监控数据接口
     */
    public function get() {
        return MonitorControl::instance()->get();
    }
}
?>
