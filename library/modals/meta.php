<?php
$this->_import('library.modal.selude');
class Meta extends SeludeModal
{
 public static function getMeta($for_page,$for_view=CURRENT_VIEW)
 {
  $temp = false;
  $meta = '';
  if(file_exists("meta/$for_page/$for_view.ini"))
  {
   $temp = parse_ini_file("meta/$for_page/$for_view.ini",true);
  }
  if($temp && count($temp))
  {
   foreach($temp as $key=>$val)
    @$meta->$key = $val;
  }
  else
   $meta = false;
  return $meta;
 }
}
?>