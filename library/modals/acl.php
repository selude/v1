<?php
$this->_import('library.modal.selude');
class ACL extends SeludeModal
{
 var $usertype;
 public function getItemsList($usertype='USER')
 {
  $this->usertype = strtoupper($usertype);
  $this->select('distinct p.acl_item_id item_id, i.name item_name, i.alias item_alias')
       ->from('acl_permission p')
       ->join('acl_items i','i.id=p.acl_item_id')
       ->where("FIND_IN_SET('$usertype',p.on_accounts)");
  // echo "<br>$this->query<br>";
  return $this->getEntities();
 }
 
 public function getItemActions($item_id,$usertype='')
 {
  $usertype = !$usertype? $this->usertype: strtoupper($usertype);
  $where_id = is_numeric($item_id)? "AND p.acl_item_id=$item_id":'';
  $where_name = !is_numeric($item_id)? 
                'AND LOWER(i.name) LIKE "%'.strtolower($item_id).'%"':'';
  $this->select('acl_action_id, a.name as action, CONCAT(a.alias," ",i.alias) as action_alias')
       ->from('acl_permission p')
       ->join('acl_items i',"i.id=p.acl_item_id $where_id")
       ->join('acl_actions a','a.id=p.acl_action_id')
       ->where("FIND_IN_SET('$usertype',p.on_accounts) $where_name");
  // echo "<br>$this->query<br>";
  return $this->getEntities();
 }
 
 public function getItemId($item_name)
 {
  $this->select('id, name')
       ->from('acl_items')
       ->where("name LIKE '%$item_name%'");
  // echo "<br>$this->query<br>";
  return $this->getEntity();
 }
 
 public function getItemPermissions()
 {
 }
}