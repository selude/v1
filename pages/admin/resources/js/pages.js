$(function()
{
 $('#pageError').modal();
 $('.pages-list li .down-arrow').click(function()
 {
  $(this).next().next().slideToggle();
  $(this).next().next().toggleClass('hidden')
 })
 
 $('.views-list a').click(function(e)
 {
  // e.preventDefault();
  link = $(this).attr('href');
  $('#viewWindow').attr('src',link);
  $(this).siblings().not(':first').removeClass('active');
  $(this).addClass('active');
 })
 
 $('#deletePagePopup').on('shown.bs.modal',function(e)//adds data to confirm box on click of 'delete page'
 {
  title  = $(e.relatedTarget).attr('rel');
  $('.delete_page').html(title);
  $('#deletePagePopup').find('[name="deletePage"]').attr('rel',title);
 })
 
 $('#deleteViewPopup').on('shown.bs.modal',function(e)//adds data to confirm box on click of 'delete view'
 {
  data  = $(e.relatedTarget).attr('rel').split('_');
  $('.parent_page').html(data[0]);
  $('.delete_view').html(data[1].replace('View',''));
 })
 
 $('#addView').on('shown.bs.modal',function(e)//adds data to confirm box on click of 'delete view'
 {
  $el  = $(e.relatedTarget);
  if($el.attr('rel'))//opening in edit mode
  {
   $('.view_editfield').removeClass('hidden');
   $el = $el.parent().prev();
   label = $el.find('[name="viewLabel"]').text().toLowerCase().replace('not available','');
   name = $el.find('[name="viewName"]').text().replace('View','');
   author = $el.find('[name="viewAuthor"]').text().toLowerCase().replace('not available','');
   desc = $el.find('[name="viewDesc"]').text().trim().toLowerCase().replace('no description available','');
   image = $el.find('[name="viewImage"]').css('background-image');
   $(this).find('input[name="viewLabel"]').val(label);
   $(this).find('input[name="viewName"]').val(name);
   $(this).find('input[name="viewAuthor"]').val(author);
   $(this).find('textarea[name="viewDesc"]').val(desc);
   $(this).find('[name="viewBG"]').css('background-image',image);
   $(this).find('[name="edit"]').val(name);
  }
  else
  {
   $(this).find('input[name="viewLabel"]').val('');
   $(this).find('input[name="viewName"]').val('');
   $(this).find('input[name="viewAuthor"]').val('');
   $(this).find('textarea[name="viewDesc"]').val('');
   $(this).find('[name="viewBG"]').css('background-image','');
  }
 })
 
 $('input[type="checkbox"]').change(function()
 {
  $next = $(this).parent().next();
  if($next.length)
  {
   $next.toggle().toggleClass('hidden');
  }
 })
 
 $('.addViewBtn').click(function()
 {
  pagename = $(this).parent().prev().find('[name="pagename"]').text().trim();
  $('#addView').find('input[name="page"]').val(pagename)
 })

 $('[name="deleteView"]').on('click',function()
 {
  // pagename = $(this).attr('rel');
  pagename = 'sdfsdf';
  // $('#deleteViewPopup').modal('hide');
  // showLoader('Deleting page '+pagename+'. Please wait...');
  $.post(jsHome+'/admin/ajax',
  {
   task:'deletePage',
   page:pagename
  },
  function(html)
  {
   hideLoader();
   alert(html);
  })
 }) 
})