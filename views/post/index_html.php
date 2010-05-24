<?php class_exists('PostResource') || require('resources/PostResource.php');?>
<?php if($posts == null):?>
	<article class="hentry">
		<?php if(AuthController::isAuthorized()):?>
		<p>There are no posts right now.</p>
		<a href="<?php echo FrontController::urlFor('post');?>">Create a new one</a>
		<?php else:?>
		<p>There are no posts here.</p>
		<?php endif;?>
	</article>
<?php else:?>
	<?php foreach($posts as $key=>$post):?>
	<article class="hentry<?php echo ($key === 0 ? ' first': null);?> <?php echo $post->type;?>">
		<?php switch($post->type){
			case('link'):?>
		<header class="<?php echo !$post->is_published ? 'private' : null;?>">
			<a href="<?php echo $post->body;?>" title="<?php echo $post->title;?>"><?php echo $post->title;?></a>
		</header>
		<section class="entry-content">
			<p><?php echo $post->description;?></p>
		<?php if( AuthController::isAuthorized()):?>
			<form action="<?php echo FrontController::urlFor('post');?>" method="post" onsubmit="return confirm('Are you sure you want to delete <?php echo $post->title;?>?');">
				<input type="hidden" name="id" value="<?php echo $post->id;?>" />
				<input type="hidden" name="_method" value="delete" />
				<input type="submit" name="delete_button" value="delete post" />
		        <a href="<?php echo FrontController::urlFor('post', array('id'=>$post->id));?>">edit</a>
			</form>
		<?php endif;?>
		</section>
			<?php
				break;
			case('photo'):?>
		<header class="<?php echo !$post->is_published ? 'private' : null;?>">
			<?php if(stripos($post->body, '<a') !== false):?>
				<?php echo $post->body;?>
			<?php else:?>
			<img src="<?php echo $post->body;?>" alt="<?php echo $post->title;?>" />
			<?php endif;?>
		</header>
		<section class="entry-content">
			<p><?php echo $post->description;?></p>
		<?php if( AuthController::isAuthorized()):?>
			<form action="<?php echo FrontController::urlFor('post');?>" method="post" onsubmit="return confirm('Are you sure you want to delete <?php echo $post->title;?>?');">
				<input type="hidden" name="id" value="<?php echo $post->id;?>" />
				<input type="hidden" name="_method" value="delete" />
				<input type="submit" name="delete_button" value="delete post" />
		        <a href="<?php echo FrontController::urlFor('post', array('id'=>$post->id));?>">edit</a>
			</form>
		<?php endif;?>
		</section>
			<?php
				break;
			default:?>
		<header class="<?php echo !$post->is_published ? 'private' : null;?>">
			<h1><a href="<?php echo FrontController::urlFor($post->custom_url);?>" rel="bookmark" title="<?php echo $post->title;?>"><?php echo $post->title;?></a></h1>
		</header>
		<section class="entry-content">
			<?php echo $post->body;?>
			<?php if( AuthController::isAuthorized()):?>
				<form action="<?php echo FrontController::urlFor('post');?>" method="post" onsubmit="return confirm('Are you sure you want to delete <?php echo $post->title;?>?');">
					<input type="hidden" name="id" value="<?php echo $post->id;?>" />
					<input type="hidden" name="_method" value="delete" />
					<input type="submit" name="delete_button" value="delete post" />
			        <a href="<?php echo FrontController::urlFor('post', array('id'=>$post->id));?>">edit</a>
				</form>
			<?php endif;?>
		</section>
		<?php 
			break;
		}?>
		<footer class="post-info">
			<abbr title="<?php echo $post->date;?>">
				<span class="day"><?php echo date('jS', strtotime($post->post_date));?></span>
				<span class="month"><?php echo date('M', strtotime($post->post_date));?></span>
				<span class="year"><?php echo date('Y', strtotime($post->post_date));?></span>
			</abbr>
			<aside rel="author">
				<img width="52" height="52" src="<?php echo PostResource::getAuthorUrl($post);?>" alt="<?php echo $post->source;?> photo" />
				<p><?php echo $post->sourc;?></p>
			</aside>
		</footer>
	</article>
	<?php endforeach;?>
<?php endif;?>
	<nav class="pager">
	<?php if(count($posts) > 0 && $page > 1):?>
		<a href="<?php echo FrontController::urlFor(($name === 'index' ? null : $name . '/')) . ($page > 1 ? $page-1 : null);?>" title="View newer posts"> ← newer</a>
	<?php else:?>
		<span> ← newer</span>
	<?php endif;?>
<?php if(count($posts) >= $limit):?>
		<a href="<?php echo FrontController::urlFor(($name === 'index' ? null : $name . '/')) . ($page === 0 ? $page+2 : $page+1);?>" title="View older posts">older → </a>
<?php else:?>
		<span>older → </span>
<?php endif;?>
	</nav>
