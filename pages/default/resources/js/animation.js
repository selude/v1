$(function()
{
 $('.product_slider').length? setInterval(animateProducts,5000):null;
})

function animateProducts()
{
 var $img = $('.product_slider').find('.wrapper').children('.active');
 var $next = $img.next().length ?$img.next() :$img.siblings().first();
 $img.fadeOut('slow').removeClass('active');
 $next.fadeIn('slow').addClass('active');
}