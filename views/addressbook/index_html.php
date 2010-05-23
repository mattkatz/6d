<div id="addressbook" class="addressbook">
	<header>
		<nav>
			<ul></ul>
		</nav>
	</header>
	<section id="groups">
		<h1>Groups</h1>
		<ul>
		<?php foreach($groups as $key=>$group):?>
			<li rel="<?php echo $group->text;?>" class="<?php echo ($group->id == -1 ? 'selected' : '')?>">
				<span><?php echo $group->text;?></span>
				<input type="checkbox" id="group_<?php echo $key;?>" name="groups" value="<?php echo urlencode($group->text);?>" />
				
			<?php if(!in_array($group->text, array('All Contacts', 'Friend Requests'))):?>
				<form action="<?php echo FrontController::urlFor('group');?>" method="post" class="delete">
					<input type="hidden" value="<?php echo $group->text;?>" name="text" />
					<input type="hidden" value="delete" name="_method" />
					<button><span>Delete</span></button>
				</form>
			<?php endif;?>
			</li>
		<?php endforeach;?>
		</ul>
		<footer>
			<nav>
				<a id="add_group_link" class="add" href="<?php echo FrontController::urlFor('group');?>" title="add a group"><span>+</span></a>
			</nav>
		</footer>
	</section>
	<section id="people">
		<h1>Contacts</h1>
		<?php echo $this->renderView('person/index', null, 'html');?>
		<footer>
			<nav>
				<a id="add_link" class="add" href="<?php echo FrontController::urlFor('addressbook');?>" title="add someone">+</a>
			</nav>
		</footer>
	</section>
	<section id="detail" class="detail"></section>
	<div style="clear: both;"></div>
</div>
<section id="instructions">
	<ul>
		<li>You can delete a group or contact by selecting it and pressing the delete key</li>
		<li>Add a group or contact by clicking on the plus (+) sign under the respective section, filling out a name in the new field and pressing the "enter" or "return" key.
	</ul>
</section>
