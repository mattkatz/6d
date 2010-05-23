<?php
	class_exists('UserResource') || require('resources/UserResource.php');
	class_exists('ProfileResource') || require('resources/ProfileResource.php');
?><!doctype html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>{$title}</title>
		<link rel="icon" href="<?php echo FrontController::urlFor('images');?>6dicon.png"/>
		<meta name="description" content="{$description}"/>
		<meta name="keywords" content="{$keywords}"/>
		<meta name="viewport" content="width=980"/>
  		<link rel="stylesheet" type="text/css" href="<?php echo FrontController::urlFor('themes');?>css/reset.css" media="screen" />
	  	<link rel="stylesheet" type="text/css" href="<?php echo FrontController::urlFor('themes');?>css/default.css" media="screen" />	
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo FrontController::urlFor('js');?>MooEditable/MooEditable.css">
		{$resource_css}
		<script type="text/javascript" charset="utf-8" src="<?php echo FrontController::urlFor('js');?>mootools-core.js"></script>
		<script type="text/javascript" charset="utf-8" src="<?php echo FrontController::urlFor('js');?>mootools-more.js"></script>
		<script type="text/javascript" charset="utf-8" src="<?php echo FrontController::urlFor('js');?>NotificationCenter.js"></script>
		<script type="text/javascript" charset="utf-8" src="<?php echo FrontController::urlFor('js');?>MooEditable/MooEditable.js"></script>
		<script type="text/javascript" charset="utf-8" src="<?php echo FrontController::urlFor('js');?>default.js" id="default_script" rel="<?php echo urlencode(FrontController::urlFor(null));?>"></script>
		<!--[if IE]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		{$resource_js}
	</head>
	<body>
		<div class="frame">
			<header id="banner">
				<h1><a href="<?php echo FrontController::urlFor(null);?>" title="Home"><span><?php echo $this->owner->profile->site_name;?></span></a></h1>
				<nav>
					<ul>
	<?php $pages = Post::findPublishedPages();?>
	<?php while($pages != null && $page = array_shift($pages)):?>
		<?php if(!$page->isHomePage($this->getHome_page_post_id())):?>
						<li><a href="<?php echo FrontController::urlFor($page->custom_url);?>" title="<?php echo $page->description;?>"><?php echo $page->title;?></a></li>
		<?php endif;?>
	<?php endwhile;?>
					</ul>
				</nav>

			</header>
			<aside id="author">
				<?php $person = Person::findOwner();$person->profile = unserialize($person->profile);?>
				<a href="<?php echo FrontController::urlFor(null);?>" title="Go back to my home page">
					<img src="<?php echo ProfileResource::getPhotoUrl($person);?>" alt="photo of <?php echo $person->name;?>" class="author" />
				</a>
			  	<footer id="tweets"></footer>
			</aside>
			<section id="content">
				<div class="user_message"<?php echo (Resource::getUserMessage()==null ? 'style="display:none;"' : null);?>>
					<?php echo Resource::getUserMessage();?>
				</div>
				{$output}
			</section>
			<?php require('menu.php');?>
			<?php require('footer.php');?>
		</div>
		<noscript>requires Javascript. Please either turn on Javascript or get a browser that supports Javascript to use 6d.</noscript>
	</body>
</html>
