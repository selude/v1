<?php
$this->_import('library.modal.selude');
class Mail extends seludeModal
{
 public static function send($to,$subject,$content,$headers)
 {
  if(is_array($to))
  {
   $count = 0;
   foreach($to as $sendto)
   {
    $resp[] = @mail($sendto,$subject,$content,$headers);
	$count++;
	if($count==20)
	{
	 $count = 0;
	 sleep(10);
	}
   }
  }
  else
  {
   $resp = @mail($to,$subject,$content,$headers);
  }
  return $resp;
 }
}
?>