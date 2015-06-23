$(function()
{
 loader();
})

function loader()
{
 $('body').css('position','relative');
 str = '<div class="hidden" id="loader"><div class="loader-container"><div class="img"></div><div class="text"></div></div></div>';
 $('body').prepend(str);
}

function showLoader(msg)
{
 $('#loader').find('.text').text(msg); 
 $('#loader').removeClass('hidden'); 
}
function hideLoader()
{
 $('#loader').addClass('hidden'); 
}