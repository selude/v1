# v1
First version of the Selude CMS. Stable and working.
This CMS is meant for quick development of your website by simply working on the template part.
What is Selude?
Selude is derive from 'Elude', meaning, escape from or avoid in a skillful way. That's what we intended intended when we created selude! Everytime you wish to create a website, you've to worry about a CMS/framework, learn it, undestand it and then code it and often tweak it!
With Selude, all you need to know if structure of your HTML pages, flow between them and then implement the same!
Selude works on the concept of pagelets wherein each block in a HTML page could become a pagelet, if you specify them so in the 
view definition.
The structure of Selude is as following:
- config : All configuration and route definitions are here
- library: All library files are here. They are what execute and process each request. Also contain the plugins, modals and classes inherited by views
- pages: That's where your code shall lie. You'll create a new directory under this for every new page (such as user, settings, etc.) you would create. Each page has a controller file, a modal file (helper/processor) and a layout definition file which defines layout of various views under that page (such as 'account','privacy','notifications' views under 'settings' page). Each page has it's own resources (JS/CSS/Images) and view pagelets. These view pagelets can be shared with other page's views by including them in that page's view definitions like this: ...'views'=>array('home'=>'user.home'),...
- store: This is where you store all your publicly shared data, such as user images, stock images, etc.
- .htacess : Defines route forwarding and other apache directives
- error.phtml : Error page to be shown on various instances. Customize it as you need it.
- sitemap.xml : Used by search engines for indexing
- index.php : Simply includes the bootfile (boot.php) which processes each incoming request.
