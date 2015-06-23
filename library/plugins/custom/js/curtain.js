cl = '';
curtain_open = false;
$(function()
{ 
 $('[data-toggle="curtain"]').click(function(e)
 {
  e.preventDefault();
  openCurtain(this);
 })
 
 $('.curtain_close').click(function()
 {
  curtain_open = false;
  $(this).siblings().children().remove();
  $('#curtain').removeAttr('class');
  $('#curtain').addClass('container-fluid');
  $('body').css('overflow','auto');
  $(this).parent().slideUp();
 })
 
 $(document).scroll(function(e)
 {
  if(curtain_open)
   return false;
 })
})

function addToCurtain(html)
{
 if($('#curtain').children().length)
	$('#curtain').children(':last').append(html);
 else
	$('#curtain').append(html);
}

function openCurtain(obj)
{
 box = $(obj).attr('href');
 if(!box)
  box = $(obj).attr('data-target');
  console.log(box)
 if($('#curtain').children().length)
 {
  $('#curtain').slideUp();
  $('#curtain').children().remove();
 }
 cl = $(box).attr('class');
 s = $(box).attr('style');
 box = $(box).children().clone(true,true);
 $('#curtain').addClass(cl).attr('style',s);
 $('#curtain').append(box);
 if(!curtain_open)
 {
  $('.curtain_wrapper').slideDown();
  $('body').css('overflow','hidden');
 }
 curtain_open=true;
}