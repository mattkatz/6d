<section id="addressbook" style="display: none;"></section>
<form action="<?php echo FrontController::urlFor('post');?>" method="post" id="post_form">
	<div id="post_menu">
		<ul>
			<li>
				<a href="<?php echo FrontController::urlFor('addressbook.html');?>" id="address" title="Address">Address</a>
			</li>
			<!--<li>
				<a href="<?php echo FrontController::urlFor('photos.html');?>" id="photos_link" title="Photo Browser">Photo Browser</a>
			</li>-->
			<li>
				<a href="javascript:void(0);" id="make_home_link" class="icon <?php echo $post->isHomePage($this->getHome_page_post_id()) ? 'not_home' : 'home';?>" title="<?php echo $post->isHomePage($this->getHome_page_post_id()) ? 'Make this a regular post' : 'Make this post your home page';?>"><span><?php echo $post->isHomePage($this->getHome_page_post_id()) ? 'Make post' : 'Make home';?></span></a>
			</li>
			<li>
				<a href="javascript:void(0);" id="publish_link" title="<?php echo $post->is_published ? 'Make this post private. It won\'t show up on your public site.' : 'Make this post public. This will show up on your public site.';?>" class="icon <?php echo $post->is_published ? 'public' : 'private';?>"><span><?php echo $post->is_published ? 'Make private' : 'Make public';?></span></a>				
			</li>
			<li>
				<a href="javascript:void(0);" id="options_hide_link" title="Hide and show the options panel">Hide</a>
			</li>
		</ul>
		<aside id="panel_window"></aside>
	</div>
	<fieldset id="options">
		<section id="send_to_list">
			<label for="send_to_list">To</label>
			<ul></ul>
		</section>
		<section>
			<label for="title" class="inline">Title:</label>
			<input type="text" id="title" name="title" value="{$post->title}" />
			<input type="checkbox" style="width:14px;display: none;" id="is_published" name="is_published" value="true"<?php echo $post->is_published ? ' checked="true"' : '';?> />
			
			<input type="checkbox" style="width:14px;display: none;" value="true" id="make_home_page" name="make_home_page"<?php echo $post->isHomePage($this->getHome_page_post_id()) ? ' checked="true"' : null;?> />
		</section>
		<section>
			<label for="source" class="inline">Author:</label>
			<input type="text" name="source" id="source" value="{$post->source}" />
		</section>
		<section>
			<label for="post_date" class="inline">Post Date:</label>
			<input type="text" value="{$post->post_date}" name="post_date" id="post_date" />
		</section>
		<section>
			<label for="tags" class="inline">Tags:</label>
			<input type="text" name="tags" id="tags" value="{$post->tags}" />
		</section>		
		<section class="type">
			<label for="type">Type:</label>
			<select id="type" name="type">
<?php foreach(array('post'=>'Post', 'page'=>'Page', 'quote'=>'Quote', 'photo'=>'Photo', 'album'=>'Album', 'video'=>'Video', 'link'=>'Link') as $key=>$value):?>
			<option value="<?php echo $key;?>"<?php echo $post->type === $key ? ' selected="true"' : '';?>><?php echo $value;?></option>
<?php endforeach;?>
			</select>
		</section>
		<section id="excerpt">
			<label for="description">Excerpt:</label>
			<textarea name="description" id="description" rows="3">{$post->description}</textarea>
		</section>
	</fieldset>
	<fieldset>
		<article>
			<textarea name="body" id="body" cols="50" rows="20">{$post->body}</textarea>
		</article>
		<input type="hidden" name="id" value="{$post->id}" />
		<input type="hidden" name="source" value="{$post->source}" />
	</fieldset>
<?php if($post->id !== null):?>
	<input type="hidden" value="put" name="_method" />
<?php endif;?>
	<button type="submit" name="save_button" id="save_button" style="display: block;">Save</button>
<?php if(strlen($post->source) > 0):?>
	<button type="submit" name="reblog" id="reblog">Reblog</button>
<?php endif;?>
</form>
	<!--<form enctype="multipart/form-data" target="upload_target" method="post" id="media_form" action="<?php echo FrontController::urlFor('photo');?>">
		<input type="hidden" value="put" name="_method" />
		<fieldset>
			<legend>Media</legend>
	                 <section>
				<label for="photo" id="photo_label">Add a photo</label>
				<input type="file" name="photo" id="photo" />
	                 </section>
	                 <input type="hidden" name="MAX_FILE_SIZE" value="{$max_filesize}" />
			<iframe src="<?php echo FrontController::urlFor('empty');?>" id="upload_target" name="upload_target" style="width:10;height:10;border:none;"></iframe>
		</fieldset>
	</form>
	<dl id="photos"></dl>-->
