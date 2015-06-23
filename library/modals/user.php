<?php
$this->_import('library.modal.selude');
// $this->_import('library.modal.log');
class User extends SeludeModal
{
 public function authenticate($cred,
 $table='users',$fields=array('email'=>'email','pwd'=>'password'),
 $open=false)
 {
  $result = array();
  if(!isset($cred['email']) || !isset($cred['pwd']))
  {
   $result['success'] = false;
   $result['msg'] = 'Please send the username with key "email" and password with key "pwd" for the passed credential array!';
   return $result;
  }
  $where = '';
  if(is_array($fields['email']))
  {
   foreach($fields['email'] as $field)
    $where .= "`$field` = '$cred[email]' OR";
   $where = trim($where,' OR');
  }
  else
   $where .= "`$fields[email]` = '$cred[email]'";
  $this->select('*')->from($table)->where($where);
  // echo "<br>$this->query<br>"; 
  // die;
  $data = $this->getEntity();//gets one row
  if($data)
  {
   $temp = $data->status=='BLOCKED'?'This account has been blocked. Please contact Administrator for details':'';
   if($temp)
   {
    $result['success'] = false;
	$result['msg'] = $temp;
	return $result;
   }
  }
  if($open && $data)
  {
   $result['success'] = true;
   $result['msg'] = 'User logged in successfully!';
  }
  else if($data)
  {
   if(md5($cred['pwd'])==$data->$fields['pwd'])
   {
    $result['success'] = true;
    $result['msg'] = 'User logged in successfully!';
   }
   else
   {
    $result['success'] = false;
    $result['msg'] = 'Invalid password!';
   }
  }
  else
  {
   $result['success'] = false;
   $result['msg'] = 'Invalid ';
   $result['msg'] .= is_array($fields['email']) ?implode('/',$fields['email']) :$fields['email'];
   $result['msg'] .= ": No email as '$cred[email]' was found in our system";
  }
  if($result['success'])
  {
   $this->select('user_profile_in as profile_table, fk_name as agent')
		->from('user_definitions')
		->where("user_type = '$data->user_type'");
   // echo "<br>$this->query<br>";
   // die;
   $profile = $this->getEntity();
   // print_r($profile);die;
   $this->select('*')
		->from('users u')
		->join("$profile->profile_table p","$profile->agent = u.id")
		->where("u.id = $data->id");
	
   // echo "<br>$this->query<br>";
   $data = $this->getEntity();
   // print_r($data);die;
   unset($data->$fields['pwd']);
   session::set('user',$data);
  }
  return $result;
 }
 
 public static function getCurrent()
 {
  $user = session::get('user');
  return $user;
 }
 
 public function getActivities($top=0)
 {
  $user = session::get('user');
  if(!$user)
   return false;
  $this->select('*')
       ->from('user_activity')
       ->where('user_id='.$user->id.' AND status="OK"')
       ->order('cdate DESC, udate DESC')
       ->limit($top);
  // echo $this->query;
  return $this->getEntities();
 }
 
 public function getNotifications($top=0)
 {
  $user = session::get('user');
  if(!$user)
   return false;
  $this->select('*')
       ->from('user_notifications')
       ->where('userid='.$user->id.' AND status<>"2"')
       ->order('cdate DESC, udate DESC')
       ->limit($top);
  // echo $this->query;
  return $this->getEntities();
 }
 
 public static function generateToken($len=10,$prefix=false)
 {
  $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $token = '';
  for($i=0; $i<$len; $i++)
   $token .= $str[rand(0,61)];
  if($prefix)
   $token = $prefix.$token;
  return $token;
 }

}