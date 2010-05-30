<header>
	<nav>
		<ul></ul>
	</nav>
</header>
<section id="groups">
	<h1>Groups</h1>
	<ul>
	<?php foreach($groups as $key=>$group):?>
		<li class="<?php echo ($group->id == -1 ? 'selected' : '')?>" rel="<?php echo urlencode($group->text);?>">
			<input type="checkbox" id="group_checkbox_<?php echo $key;?>" name="groups" value="<?php echo urlencode($group->text);?>" />
			<span rel="<?php echo urlencode($group->text);?>" ><?php echo $group->text;?></span>
		</li>
	<?php endforeach;?>
	</ul>
</section>
<section id="people">
	<h1>Contacts</h1>
	<?php echo $this->renderView('person/index', null, 'phtml');?>
</section>
