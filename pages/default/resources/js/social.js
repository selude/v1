(function(d, s, id)//for Linkedin
{
 var js, fjs = d.getElementsByTagName(s)[0];
 if (d.getElementById(id)) {return;}
 js = d.createElement(s); js.id = id;
 js.src = "//connect.facebook.net/en_US/sdk.js";
 fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));//for FB

(function()//for Gplus
{
   var po = document.createElement('script'); 
   po.type = 'text/javascript'; 
   po.async = true;
   // po.src = 'https://apis.google.com/js/client:plusone.js?onload=render';
   po.src = 'https://apis.google.com/js/client:plusone.js';
   var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();//for Gplus

window.fbAsyncInit = function()//for FB
{
 FB.init({
        appId      : '801538769886801',
        version    : 'v2.0',
        status     : true, // check login status
        cookie     : true, // enable cookies to allow the server to access the session
        frictionlessRequests: true,
        xfbml      : false  // on true,parse XFBML,when there are social plugins added 
        });
}

function connectFB()
{
 FB.login(function(response)
 {
  if(!response.authResponse)
  {
   alert('There was an error logging you in through Facebook.\nPlease try again.');
   return false;
  }
  else if(response.status=='connected')
  {
   FB.api('/me?fields=email,name',function(resp)
   {
    // console.log(resp);
    if(response.authResponse)
		connectSocial(resp,'facebook');
	else
	{
	 alert('An error occured while logging you in through Facebook.\nPlease try again.')
	 return false;
	}
   })
  }
  else
  {
   alert('There was an error signing you up through Facebook.\nPlease try again.');
   return false;
  }
 },{scope:'email,publish_actions'});
}

function connectGplus() 
{
  gapi.auth.signIn({'callback':loadPlusAPI,'scope':'email'});
}

function loadPlusAPI(authResult)
{
  if (authResult['status']['signed_in']) 
  {
   gapi.client.load('plus','v1',function()
   {
    var request = gapi.client.plus.people.get( {'userId' : 'me'} );
    request.execute(function(obj)//loadProfile
    {
     console.log(obj)
     response = new Object;
     response.id = obj.id;
     email = !obj['emails'].length? false :obj['emails'].filter(function(v) 
     {
        return v.type === 'account'; // Filter out the primary email
     })[0].value; // get the email from the filtered results, should always be defined.
     if(!email)
     {
      alert('Sorry, we were not able to fetch details from your Google account. Please make sure you have updated all your details (including email) on your Google account before you connect it with us.');
      return;
     }
     response.email = email;
     connectSocial(response,'gplus');
   });
   })
  }
  // else 
  // {
   // alert('An error occurred while connecting your gPlus account. Details:\n'+authResult['error'])
  // }
}

function onLinkedInLoad() 
{
 IN.Event.on(IN, "auth", function()
 {
  $('[name="linkedin"]').attr('disabled',false);
 });
}

function onLinkedInAuth() 
{
 IN.API.Profile("me")
    .fields('id', "email-address")
    .result(function(profiles)
     {
        member = profiles.values[0];
        response = new Object;
        response.id = member.id;
        response.email = member.emailAddress;
        connectSocial(response,'linkedin')
     });
}

gplus = false;
facebook = false;
linkedin = false;
function connectSocial(response,type)
{
 showLoader('Connecting your '+type.toUpperCase()+' account. Please wait..');
 if(type=='gplus')
  gplus = true;
 if(type=='facebook')
  facebook = true;
 if(type=='linkedin')
  linkedin = true;
 $.post
 (jsHome+'/user/ajax/',
  {
	task:'connect',
	action:'social',
	item:type,
	id:response.id,
	email:response.email
  },
  function(data)
  {
   hideLoader();
   if(window[type])
   {
    window[type] = false;
    alert(data.msg);
    if(data.success)
     window.location.reload();
   }
  },'json')
  .fail(function()
  {
   hideLoader();
   alert('There was an error in this request. Please try again.');
  })
}

function sharePost(title,subtitle,detail,link,image)//used in book/index.php
{
 if(image)
 {
  var source=image;
  image=image.split('/')
  image[image.length]='';
  href=image.join('/');
 }
 FB.ui({method:'stream.publish',
		attachment:{name:title,caption:subtitle,description:(detail),href:link,
		media: 
		[
		{
			type: 'image',
			href: href,
			src: source
		}
		]},
		user_prompt_message:'Post to FB about this!'
		},
		function(response)
		{
		 if(!response||response.error)
		  alert('Sorry, an error occured while sharing this post. Please try again.')
		 else
		  alert('Thanks! The post has been shared on your wall :)')
		});
}

function postAction(action,link)
{
 console.log('action:'+action+' link:'+link)
 setTimeout(function()
 {
  // FB.api('me/objects/book','post',
		// {
		 // app_id: 199810196813097,
		 // url: link,
		 // title: "Sample Book",
		 // image: "https://s-static.ak.fbcdn.net/images/devsite/attachment_blank.png",
		 // description: ""
		// },
		// function(response) 
		// {
		 // console.log(response)//handle the response
		// });
  FB.api('me/zunket_app:'+action,'post',
		{
		 book: 'http://zunket.com/books/details/?id=132'
		},
		function(response) 
		{
		 console.log(response)// handle the response
		});
 },5000); 
}
