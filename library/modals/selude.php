<?php
require_once LIB_PATH.'/entity.php';
require_once LIB_PATH.'/router.php';
$this->_import('library.modal.image');

class SeludeModal extends Entity
{
 function __construct()
 {
  parent::connect();
 }
}

class View
{
 public static function _getBlocks($pagename=page,$viewname=view,$join=null,$table=null,$on=null,$condition=null)
 {
  $temp = new Entity;
  $temp->select('v.*')
       ->from('s_views v')
       ->join('s_pages p',"p.id=v.page_id")
       ->where("v.name = '$viewname'");
  $res = $temp->getEntity();
  if($condition)
  {
   if(isset($condition['skip']))
    $where[] = is_array($condition['skip'])?"vb.block_rank NOT IN ($condition[skip])"
               :"vb.block_rank<>$condition[skip]";
   else
    $where[] = is_array($condition)?"vb.block_rank IN ($condition)":$condition;
    //send conditions in string form to filter by specific conditions
  }
  if(!$join)
    $where[] ='linked_id IS NULL';
  $where[] = "p.name='$pagename'";
  $where[] = "v.name='$viewname'";
  $where[] = "vb.valid_from<=now()";
  // $where .= " AND (valid_till<>0 AND valid_till>=now()) OR (valid_till='0000-00-00 00:00:00')";
  $where[] = "p.id=$res->page_id AND v.id=$res->id";
  $where[] = "vb.status='VERIFIED'";
  $res = '';
  if($join)
  {
   $where = implode(' AND ',$where);
   $join = strtolower(str_replace(' ','_',$join));
   $on = is_array($on)? implode(' AND ',$on):$on;
   $temp->select("vb.*,$table.*")
        ->from('view_blocks vb')
        ->$join($table,$on)
        ->join('s_views v',"v.id IN (vb.onviews) OR FIND_IN_SET(v.id,(vb.onviews))")
        ->join('s_pages p',"v.page_id IN (vb.onpages) OR FIND_IN_SET(v.page_id,(vb.onpages)) AND v.page_id=p.id")
        ->where($where)
        ->order('vb.block_rank','ASC');
   $temp = $temp->getEntities();
  }
  else
  {
   $where[] = 'vb.anchor_name IS NULL';
   $where[] = 'vb.linked_id IS NULL';
   $where = implode(' AND ',$where);
   $temp->select('vb.*')
        ->from('view_blocks vb')
        ->join('s_views v',"v.id IN (vb.onviews) OR FIND_IN_SET(v.id,(vb.onviews))")
        ->join('s_pages p',"v.page_id IN (vb.onpages) OR FIND_IN_SET(v.page_id,(vb.onpages)) AND v.page_id=p.id")
        ->where($where)
        ->order('vb.block_rank','ASC');
   $temp = $temp->getEntities();
  }
  return self::sortByRank($temp);
 }

 public static function sortByRank($data)
 {
  $temp = array();
  if($data)
  {
   foreach($data as $key=>$div)
   {
    if(strtolower($div->block_type)=='slider')
    {
     @$temp[$div->block_rank]->block_type = $div->block_type;
     @$temp[$div->block_rank]->class = $div->class;
     @$temp[$div->block_rank]->style = $div->style;
     @$temp[$div->block_rank]->src = $div->src;
     @$temp[$div->block_rank]->detail = $div->detail;
     @$temp[$div->block_rank]->blocks[] = $div;
    }
    else
     @$temp[$div->block_rank] = $div;
   }
  }
  return $temp;
 }
 
 public static function _drawBlocks($pagename=page, $viewname=view, $join=null, $table=null, $on=null,$condition=null)
 {
  $list = self::_getBlocks($pagename,$viewname,$join,$table,$on,$condition);
  // print_r($list);
  if($list)//there is something to draw
  {
   $level = level;
   foreach($list as $rank=>$block)
   {
    // echo "<br>";print_r($block);
    switch(strtolower($block->block_type))
    {
     case 'image':
     case 'img':
         // echo $block->anchor_name.'<br>';
         $mapped_link = ($block->anchor_name!=NULL)? trim($block->anchor_name):false;
         // echo $block->$mapped_link;
         $a_open = $mapped_link? 
                   "<a href='".App::_route(baseURL.DS.$block->$mapped_link)."'>":'';
         $a_close = $a_open? "</a>":'';
         $class = trim($block->class)?" class='$block->class'":'';
         $style = trim($block->style)?" style='$block->style'":'';
         $start = "<div $class $style block-rank=$rank><img class='img img-responsive' src='$level$block->src'>";
         $end = '</div>';
         echo $a_open.$start;
         echo $end.$a_close;
     break;
     
     case 'div':
         $class = trim($block->class)?" class='$block->class'":'';
         $style = trim($block->style)?" style='$block->style'":'';
         $start = "<div $class $style block-rank=$rank>";
         $end = '</div>';
         echo $start;
         $src = trim($block->src);
         if($src && strtolower($src)!='self')
          _import($src);
         eval(trim($block->detail));
         echo $end;
     break;
     
     case 'slider':
         // echo $block->$mapped_link;
         $class = trim($block->class)?" class='$block->class'":'';
         $style = trim($block->style)?" style='$block->style'":'';
         $start = "<div $class $style block-rank=$rank>";
         $end = '</div>';
         echo $start;
         $src = trim($block->src);
         if($src && strtolower($src)!='self')
          _import($src);
         eval(trim($block->detail));
         echo $end;/* 
         ob_start();
         eval(trim($block->detail));
         $block = ob_get_contents();
         ob_end_clean();
         if($block)
         {
            echo $start.$block.$end;
         } */
     break;
    }
   }
  }
 }
}

class App
{
 public static function base()
 {
  return baseURL.DS.CURRENT_PAGE.DS.CURRENT_VIEW.DS;
 }
 
 public static function root()
 {
  return str_replace('\\',DS,dirname(dirname(dirname(__FILE__))));
 }
 
 public static function page()
 {
  return baseURL.DS.CURRENT_PAGE.DS;
 }
 
 public static function _route($link,$optional=true)
 {
  return sRoute::getOptimisedRoute($link,$optional);
 }
 
 public static function _getLink($link,$optional=true)
 {
  return sRoute::getOptimisedRoute($link);
 }
}