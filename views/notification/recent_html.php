
<?php if($posts != null):?>
<dl>
<?php foreach($posts as $key=>$post):?>
	<dt></dt>
	<?php foreach($post as $p):?>
	<dd><?php echo $p->title;?></dd>
	<?php endforeach;?>
<?php endforeach;?>
</dl>
<?php else:?>
<h1>There are no posts right now</h1>
<?php endif;?>
