<form action="<?php echo FrontController::urlFor('follower');?>" method="post" class="body" id="friend_request_form">
	<fieldset>
		<legend><?php echo ($person->name == null ? 'New friend' : $person->name);?></legend>
		<p>
			<label for="email">Email</label>
			<span id="email">{$person->email}</span>
		</p>

		<p>
			<label for="url">Url</label>
			<input type="text" name="url" id="url" value="{$person->url}" />
		</p>
		<p>
			<button type="submit" name="cofirm">Confirm as a friend</button>
		</p>
		<input type="hidden" name="_method" value="put" />
		<input type="hidden" name="id" id="id" value="{$person->id}" />
	</fieldset>
</form>
<form action="<?php echo FrontController::urlFor('follower');?>" method="post">
	<input type="hidden" name="id" id="id" value="{$person->id}" />
	<input type="hidden" name="_method" value="delete" />
	<input type="submit" name="delete_button" value="Delete" />
</form>
