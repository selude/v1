<?php
_import('library.modal.selude');
class Log
{
 public function __construct()
 {
  $table = new Table;
  $list = $table->getTablesList();
  if(count($list))
  {
   foreach($list as $table)
   {
    $this->$table = new Table($table);
   }
  }
 } 
}

class Table extends SeludeModal
{
 var $table;
 public function __construct($table=null)
 {
  if($table)
   @$this->table = $table;
  // echo '<br>New table object created for table: '.$this->table;
 }
 
 function getTablesList()
 {
  $list = $this->getTableEntities();//in entity.php
  return $list;
 }
 
 public function set($values,$where=null,$in_db=true)
 {
  $update = false;
  $in_db = $in_db? $in_db:session::get('user');
  if($where && $in_db)
  {
   $where = is_array($where) ?implode(' AND ',$where) :$where;
   $this->select('*')
        ->from($this->table)
        ->where($where);
   // echo $this->query.'<br>';
   // die;
   $result = $this->getEntity();
   $update = $result?true:false;
   $res = $update?
         $this->_update($this->table,$values,$where):
         $this->_insert($this->table,$values);
  }
  else//for guest users, save the info in session
  {
   $log = session::get('log',array());
   $log[] = $values;
   session::set('log',$log);
   $res = true;
  }
  return $res;
 }
 
 public function _push($values,$in_db=true)
 {
  if($in_db)
  {
   return $this->_insert($this->table,$values);
  }
  else//for guest users, save the info in session
  {
   $log = session::get('log',array());
   $log[] = $values;
   session::set('log',$log);
   $res = true;
  }
 }
}