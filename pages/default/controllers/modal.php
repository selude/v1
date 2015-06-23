<?php
$this->_import('library.modal.user');//library module
$this->_import('default.mails.phtml');//user script for sending mails
class defaultModal extends User
{
 function index($data)
 {
 }
 
 function login($data)
 {
  $result = parent::authenticate($data);
  // print_r($result);
  return $result;
 }
 
 function signup($data)
 {
  // $result = parent::authenticate($data);
  $email = $data['email'];
  $pwd = $data['pwd'];
  $cpwd = $data['cpwd'];
  $success = true;
  if(!preg_match('/[a-zA-Z0-9\.]\@[a-zA-Z0-9]\.*/',$email))
  {
   $success = false;
   $msg = 'Please enter a valid email address';
  }
  else if($success && $pwd!=$cpwd)
  {
   $success = false;
   $msg = 'The two passwords are not same';
  }
  if($success)//fields validated, proceed to signup
  {
   $this->select('*')->from('users')
		->where("email = '$email' OR username= '$email'");
   $check = $this->getEntity();
   if($check)
   {
    if($check->status=='BLOCKED')
	{
	 $success = false;
	 $msg = 'Sorry, you cannot signup with this email address; it has been blocked at '.baseURL.'. For details, please contact support';
	}
    else if($check->status=='PENDING')
	{
	 $success = false;
	 $msg = 'This email address is already pending for activation. If you have lost the activation link, please follow the \'Forgot Password\' link';
	}
	else
	{
	 $success = false;
	 $msg = 'This email address is already registered with an account. If you believe there is a mistake, please contact our support desk';
	}
   }
   else
   {
    $data = array();
    $data['email'] = $email;
    $data['password'] = md5($pwd);
    $data['token'] = User::generateToken(30);
    $res = $this->_insert('users',$data);
    if($res)
	{
	 $this->_insert('user_profile',array('uid'=>$res));
     $success = true;
	 $data['user_id'] = $res;
	}
	$mail = new UserMail;
	$sent = $mail->forWelcomeActivation($data);
	$msg = $sent? 'User account created successfully and activation mail sent to user ID: '.$res: 'Account created successfully but failed to send the mail';
   }
  }
  $result['success'] = $success;
  $result['msg'] = $msg;  
  // print_r($result);
  return $result;
 }

 function contact()
 {
  $name = sRequest::_post('name');
  $email = sRequest::_post('email');
  $contact = sRequest::_post('contact');
  $query = sRequest::_post('query');
  
  if(!$name || !$email || !$contact || !$query)
   return false;
  
  $footer = '<br><br>This is a system generated mail. Please <b>DO NOT</b> reply to this mail as it will not be delivered to any email address';
  $query = "Hi Admin!<br>There is a new query from $email. Please find the details below:<br>
            Name: $name<br>
            Email: $email<br>
            Contact number: $contact<br>
            Query: <br><i>$query</i>$footer";
  // Always set content-type when sending HTML email
  $headers = "MIME-Version: 1.0" . "\r\n";
  $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

  // More headers
  // $headers .= 'From: <webmaster@example.com>' . "\r\n";
  // $headers .= 'Cc: myboss@example.com' . "\r\n";
  $domain = str_replace('www.','',$_SERVER['HTTP_HOST']);
  $admin = 'info@'.$domain;//your admin's email ID
  
  mail($admin,'Query from '.$email,$query, $headers."From:$name <$email>"."\r\n");//send mail to you//send mail to you
  
  $msg = "Hi $name!,<br>Thanks for contacting us. We have recieved your query and you'll hear back from us very soon. If you've an urgent query, you can contact us directly on the numbers provided on our website.
  <br><br>Warm regards,<br><b>Team ".$domain.'</b>'.$footer;
  mail($email,'Thanks for contacting us',$msg,$headers."From: ".$domain." <$admin>"."\r\n");//semd mail to user
  
  return true;
 }
}