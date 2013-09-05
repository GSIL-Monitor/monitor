<?php
ini_set('display_errors', 'on');

require_once dirname(__FILE__).'/ItemControl.class.php';
require_once dirname(__FILE__).'/GraphControl.class.php';
require_once dirname(__FILE__).'/SchemaControl.class.php';
require_once dirname(__FILE__).'/SchemaFieldControl.class.php';

class monitor_monitoritem_API extends AppModule {
    
	/**
	 * 创建监控项接口
	 */
	public function create_metaitem(){
		return ItemControl::instance()->create_metaitem();
    }
    
    /**
     * 创建分组监控项
     */
    public function create_groupitem(){
    	return ItemControl::instance()->create_groupitem();
    }
    
    /**
     * 删除监控项
     */
    public function del_items(){
    	return ItemControl::instance()->delete();
    }
    
    /**
     * 更新监控项
     */
    public function update_item(){
    	return ItemControl::instance()->update();
    }
    
    /**
     * 根据 id 根据监控项
     */
    public function get_item(){
    	return ItemControl::instance()->get_item();
    }
    
    /**
     * 根据条件查询监控项
     */
    public function get_items(){
    	 return ItemControl::instance()->get_items();
    }
    
    /**
     * 创建schema
     */
    public function create_schema() {
    	return SchemaControl::instance()->create();
    }
    
    public function del_schemas() {
    	return SchemaControl::instance()->delete();
    	 
    }
    
    public function update_schema() {
    	return SchemaControl::instance()->update();
    
    }
    
    public function get_schema() {
    	return SchemaControl::instance()->get();
    
    }
    
    public function get_schemas() {
    	return SchemaControl::instance()->get_schemas();
    }
    
    public function add_schema_fields() {
    	return SchemaFieldControl::instance()->create();
    }
    
    public function update_schema_field() {
    	return SchemaFieldControl::instance()->update();
    }
    
    public function get_schema_fields() {
    	return SchemaFieldControl::instance()->get_fields();    
    }
    
    /**
     * 创建监控图
     */
    public function create_graph() {
    	return GraphControl::instance()->create();
    }
    
    public function del_graph() {
    	return GraphControl::instance()->delete();    
    }
    
    public function update_graph() {
    	return GraphControl::instance()->update();
    
    }
    
    public function get_graph() {
    	return GraphControl::instance()->get(); 
    }
    
    public function get_graphs() {
    	return GraphControl::instance()->get_graphs();
    }
    
  
}
?>