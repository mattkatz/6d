<form action="<?php echo FrontController::urlFor('person');?>" method="post" class="body" id="person_form">
	<fieldset>
		<legend><?php echo ($person->id === 0 ? 'New person' : $person->name);?></legend>
		<p>
			<label for="name">Name</label>
			<input type="text" id="name" name="name" value="{$person->name}" />
		</p>

		<p>
			<label for="title">Email</label>
			<input type="text" id="email" name="email" value="{$person->email}" />
		</p>

		<p>
			<label for="url">Url</label>
			<input type="text" name="url" id="url" value="{$person->url}" />
		</p>
		<p>
			<label for="is_approved">Approved?</label>
			<input type="checkbox" id="is_approved" name="is_approved" value="true"<?php echo $person->is_approved ? ' checked="true"' : null;?> />
		</p>
		<p>
			<button type="submit" name="save_button" id="save_button"><span>Save</span></button>
		</p>
		<input type="hidden" name="id" id="id" value="{$person->id}" />
<?php if($person->id !== null):?>
		<input type="hidden" name="_method" value="put" />
<?php endif;?>
	</fieldset>
</form>
<?php if($person->url !== null):?>
<form action="<?php echo FrontController::urlFor('followers');?>" method="post" id="friend_request_form">
	<input type="hidden" name="person[id]" id="id" value="{$person->id}" />
<?php if(!$person->is_owner):?>
	<?php if($person->public_key !== null && strlen($person->public_key) > 0):?>
	<button id="friend_request_button" type="submit"<?php echo (strlen($person->url) === 0 ? ' disabled="true"':null);?>>Add as friend again</button>
	<?php else:?>
	<button id="friend_request_button" type="submit"<?php echo (strlen($person->url) === 0 ? ' disabled="true"':null);?>>Add as friend</button>
	<?php endif;?>
<?php endif;?>
</form>
<?php endif;?>
