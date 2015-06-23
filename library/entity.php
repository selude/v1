<?php
Class Entity extends DB
{
 var $_tables;
 var $query;
 var $_obj;
 public function __construct()
 {
  $this->query = '';
  parent::__construct();
 }
 
 function getResult($one=false)
 {
  $q = trim($this->query);
  $list = $one? $this->fetch($q) :$this->fetchAll($q);
  if(isset($list->error))
  {
   return false;
  }
  $this->_obj = $this->createEntities($list);
  $this->query = '';
  return $this->_obj;
 }
 
 function getEntities()
 {
  return $this->getResult();
 }
 
 function getEntity()
 {
  return $this->getResult(true);
 }
 
 function getQuery()
 {
  return $this->query;
 }
 
 function getTables()
 {
  return $this->getTableEntities();
 }
 
 function getTableEntities()
 {
  $q = $this->query('SHOW TABLES')->from(DB_NAME);
  
  $temp = $this->getEntities();
  if(count((array)$temp))
  {
   $field = 'Tables_in_'.DB_NAME;
   foreach($temp as $table)
    $this->_tables[] = $table->$field;
  }
  else 
   $this->_tables = false;
  // print_r($this->_tables);
  return $this->_tables;
 }
 
 function createEntities($arr,$obj = null)
 {
  $temp = '';
  if(is_array($arr))
  {
   // $key = key($arr);
   foreach($arr as $key=>$value)
   {
    @$temp->$key = $this->createEntities($value,$obj);
   }
   $obj = $temp;
   return $obj;
  }
  else
   return $arr;
 }
 
 public function query($query)
 {
  $this->query = $query;
  return $this;
 }
 
 public function run()
 {
  return $this->q($this->query);
 }
 
 public function select($params)
 {
  $temp = explode(',',$params);
  for($i=0; $i<count($temp); $i++)//store selection names
  {
   $key = strtoupper(trim($temp[$i]));
   $t2 = count(explode('AS',$key)) ?explode('AS',$key) :explode(' ',$key);
   $t2 = $t2[count($t2)-1];
   $this->names[] = $t2;
  }
  $this->query .= "SELECT $params ".$this->query;
  return $this;
 }
 
 public function from($table)
 {
  $this->query .= ' FROM '.$table;
  return $this;
 }
 
 public function where($cond_arr)
 {
  if(is_array($cond_arr))
  {
    $temp = implode(' AND ',$cond_arr);
    $this->query .= ' WHERE '.$temp;
  }
  else
  {
   $this->query .= ' WHERE '.$cond_arr;
  }
  return $this;
 }
 
 public function order($fields,$order='')
 {
  $this->query .= ' ORDER BY '.trim($fields).' '.strtoupper($order);
  return $this;
 }
 
 public function group($fields)
 {
  $fields =  is_array($fields)?implode(', ',$fields):$fields;
  $this->query .= ' GROUP BY '.trim($fields,', ');
  return $this;
 }
 
 public function join($table,$on,$join = 'JOIN')
 {
  $this->query .= " $join ".$table.' ON '.$on;
  return $this;
 }
 
 public function inner_join($table,$on)
 {
  $this->join($table,$on,'INNER JOIN');
  return $this;
 }
 
 public function left_join($table,$on)
 {
  $this->join($table,$on,'LEFT JOIN');
  return $this;
 }
 
 public function right_join($table,$on)
 {
  $this->join($table,$on,'RIGHT JOIN');
  return $this;
 }

 public function limit($top=0,$len=30)
 {
  $this->query .= " LIMIT $top, $len";
  return $this;
 }

 public function union($table,$params='',$query='')
 {
  //Not working
  /*
  if(!$table)
   $this->query .= "UNIION $query";
  else
  {
   $params = '';
   $params = !is_array($params)? explode(',',$params):$params;
   for($i=0; $i<count($params); $i++)
   {
    $key = isset($params[$i])? $params[$i]: key($params);
   }
   $this->query .= "SELECT * FROM ($this->query) UNION ()";
  } */
  $this->query .= ' UNION ';
  return $this;
 }
 
 public function query_append($query)
 {
  $this->query .= $query;
  return $this;
 }
 
 public function _insert($into,$values)
 {
  // print_r($values);die;
  date_default_timezone_set('Asia/Calcutta');
  $this->query = "INSERT INTO $into ";
  $params = '';
  $val= '';
  foreach($values as $key=>$name)
  {
   if(!is_numeric($key))
    $params .= "`$key`, ";
   $val .= "'$name', ";
  }
  $params = trim($params,', ');
  $val = 'VALUES ('.trim($val,', ').')';
  $params = $params? '('.$params.')' : '';
  $this->query .= $params.' '.$val;
  $q = $this->query;
  $this->query = '';
  return $this->insert($q);
 }
 
 public function _update($table,$values,$where,&$error='')
 {
  date_default_timezone_set('Asia/Calcutta');
  $this->query = "UPDATE $table SET ";
  $params = '';
  $val= '';
  foreach($values as $key=>$name)
  {
   // if(is_string($name) && !$name)
    // continue;
   // else 
   if(!is_numeric($key))
   {
    if(preg_match("/$key/i",$name))
     $val .= "`$key` = $name";
    else
     $val .= strtolower($name)=='now()'? "`$key` = CURRENT_TIMESTAMP, ":"`$key` = '$name', ";
   }
  }
  $val = trim($val,', ');
  if(is_array($where))
   $where = implode(' AND ', $where);
  $where = ' WHERE '.trim($where,' AND ');
  $this->query .= $val.$where;
  $q = $this->query;
  $this->query = '';
  // echo $q;
  return parent::q($q,$error);
 }
}