$(function()
{
 $('.list-container .one-joint').on('click',function()
 {
  $(this).parent().next().slideToggle();
  $(this).next().toggleClass('folder-open');
  $(this).next().toggleClass('folder-close');
 })
})