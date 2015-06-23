function loadMore(obj)
{
 $obj = $(obj);
 var left = Math.max($obj.scrollLeft(), $obj.width());
 if(left-$obj.scrollLeft()==0)
 {
  $item = $obj.find('.loader').prev('.one_item');
  if(!$item.length)
   return;
  else
   var top = $item.attr('data-top');
  // getDailyDeals(top);
  var fn=$obj.attr('data-function')
  getMoreItems(top,fn);
 }
}

function getMoreItems(top=0,name)
{
 var $loader = $('[data-function="'+name+'"]').find('.loader');
 if(!$loader.length)
  return;
 $.post(jsHome+'/get/dailydeals',
        {
         top:top
        },
        function(json)
        {
         $loader.hide();
         $loader.before(json.html);
         if(!json.success)
          $loader.remove();
        },'json');
}

function getTopRatedBooks(top=0)
{
 console.log('getting top rated books')
}