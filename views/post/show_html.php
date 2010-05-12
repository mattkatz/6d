<?php class_exists('UserResource') || require('resources/UserResource.php');?>
<article>
<?php if($post == null):?>
	<p>Sorry, the page you're looking for doesn't exist.</p>
<?php else:?>
	<header>
		<h1><?php echo $post->title;?></h1>			
	</header>
	<div class="entry-content">
		<?php echo $post->body;?>
	</div>
	<footer>
		<p class="tags"><?php echo $post->tags;?></p>
		<abbr title="<?php echo $post->post_date;?>"><?php echo date('jS M Y', strtotime($post->post_date));?></abbr>
<?php if($post->source !== null && strlen($post->source) > 0):?>
		<p class="source"><?php echo $post->source;?></p>
<?php endif;?>
<?php if( AuthController::isAuthorized()):?>
			<form action="<?php echo FrontController::urlFor('post');?>" method="post" onsubmit="return confirm('Are you sure you want to delete <?php echo $post->title;?>?');">
				<input type="hidden" name="id" value="<?php echo $post->id;?>" />
				<input type="hidden" name="_method" value="delete" />
				<input type="submit" name="delete_button" value="delete post" />
		        <a href="<?php echo FrontController::urlFor('post', array('id'=>$post->id));?>">edit</a>
			</form>
<?php endif;?>			
	</footer>
<?php endif;?>
</article>
