<?php
require_once 'ModuleData.class.php';

class AutoId extends ModuleData
{
    private $_collection = 't_ids';
    
    private $_pk = '_id';
    
    static private $_instance;
    
    function __construct($redissvrinfo="",$dbs=""){
        parent::__construct(array(REDIS_GRP=>"",REDIS_SRV=>"",REDIS_PORT=>0),'monitor_datasrc');
    }
    
    static public function &instance(){
        if (empty(self::$_instance)) {
            $class = __CLASS__;
            self::$_instance = new $class();
        }
        return self::$_instance;
    }
    
    /**
     * 生成自增id
     * @param string $id_key
     * @param number $inc
     * @return integer
     */
    public function inc_id($id_key, $inc = 1) {
        
        /*
        $command = array(
            'findandmodify'=>$this->_collection,
            'update'=>array('$inc'=>array('id'=>$inc)),
            'query'=>array('name'=>$id_key),
            'new'=>true,
            'upsert'=>true
        );
        */

        $id = $this->get_mongos()->find_modify(
            $this->_collection,
            array('name'=>$id_key),
            '',
            array('$inc'=>array('id'=>$inc)),
            true,
            true           
        );

        return $id['id'];
    }
}
?>
