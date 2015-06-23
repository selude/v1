$(function()
{ 
 var prevTop = 0;
 //------Puts search bar on top on scroll of window-----
 $(document).scroll(function(e)
 {
  scroll_to = parseInt($(this).scrollTop());
  var $top = $('.top_wrapper');
  if(scroll_to>prevTop)
  {
   var px_top = '30px';
   var px_bottom = '10px';
  }
  else
  {
   var px_top = '60px';
   var px_bottom = '30px';
   prevTop = scroll_to;
  }
   $top.stop().animate({'padding-top':px_top,'padding-bottom':px_bottom},'fast')
 })
 //------------------------------------------------
 
 //-----toggle BootStrap's and custom plugins--------------------- 
 $('[data-toggle="tooltip"]').tooltip();//initialise tooltip 
 //---------------------------------------------------------------
 var slider = setInterval(slideImages,3000);
 $(document).on('click','.marker',function()
 {
  var i = $(this).index();
  image_i = i;
  clearInterval(slider);
  slideImages()
  slider = setInterval(slideImages,3000);
 })
 
 if($('#brands .container').length)
 {
  var $obj = $('#brands .container')
  setInterval(function()
  {
   var $fc = $obj.children().first();
   var l = parseInt($fc.outerWidth())+50;
   l = '-'+l+'px'
   $obj.children().first().animate({'margin-left':l},4000,'linear',function()
   {
    var $new = $fc.removeAttr('style').clone();
    $fc.remove();
    $obj.children().first().css('margin-left','3px').end().last().after($new);
   })
  },0);
 }
})

var image_i = 0;
function slideImages()
{
 i = image_i;
 var $obj = $('.images_wrapper>.images_container');
 var count = $obj.find('img').length;
 var per = parseFloat(100/count);
 var left = i>0?parseFloat(i*100):0;
 $obj.parent().animate({left:-left+'%'},1000).siblings('.marker_wrapper').find('.marker').eq(i).addClass('active').siblings().removeClass('active');
 image_i += i==count-1?-(count-1):1;
}

function validateQuery()
{
 var msg = '';
 var val = $('[name="name"]').val();
 if(!val)
  msg = 'Please enter your name';
  
 val = $('[name="email"]').val();
 if(!val || !val.match(/[a-zA-Z0-9]+(\.\_)*@[a-zA-Z0-9]+\.[a-z]+/))
  msg += '\nPlease enter a valid email';
  
 val = $('[name="contact"]').val();
 if(!val || !val.match(/[0-9]{10,13}/))
  msg += '\nPlease enter a valid contact number';
  
 val = $('[name="query"]').val();
 if(!val)
  msg += '\nPlease enter your query';
 
 if(!msg)
  return true;
 else
 {
  alert(msg);
  return false;
 }
}