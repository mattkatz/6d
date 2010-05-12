<?php if( AuthController::isAuthorized()):?>
		<nav id="admin_menu">
			<a href="<?php echo FrontController::urlFor('post');?>" id="new_post_link" title="new post">new post</a>
			<a href="<?php echo FrontController::urlFor('posts');?>" id="all_posts_link" title="show all posts">posts</a>
			<a href="<?php echo FrontController::urlFor('addressbook');?>" id="addressbook_link" title="show your addressbook">addressbook</a>
			<a href="<?php echo FrontController::urlFor('profile');?>" id="profile_link" title="show your profile">profile</a>
			<a href="<?php echo FrontController::urlFor('logout');?>" id="logout_link">logout</a>
		</nav>
<?php else:?>
<nav id="admin_menu">
	<a href="<?php echo FrontController::urlFor('login');?>" id="login_link">login</a>
</nav>
<?php endif;?>
