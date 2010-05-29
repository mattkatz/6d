<form action="<?php echo FrontController::urlFor('profile');?>" method="post" id="person_form">
	<fieldset>
		<legend>{$person->name}</legend>
		<p>
			<label for="photo_url">Pic:</label>
			<input type="text" id="photo_url" name="profile[photo_url]" value="<?php echo $person->profile->photo_url;?>" />
		</p>
        <p>
			<label for="name">Name:</label>
			<input type="text" id="name" name="name" value="{$person->name}" />
		</p>
		
		<p>
			<label for="email">Email:</label>
			<input type="text" id="email" name="email" value="{$person->email}" />
		</p>
		
		<p>
			<label for="address">Address:</label>
			<input type="text" id="profile[address]" name="profile[address]" value="<?php echo $person->profile->address;?>" />
		</p>
		
		<p>
			<label for="city">City:</label>
			<input type="text" id="profile[city]" name="profile[city]" value="<?php echo $person->profile->city;?>" />
		</p>
		
		<p>
			<label for="profile[state]">State:</label>
			<input type="text" id="profile[state]" name="profile[state]" value="<?php echo $person->profile->state;?>" />
		</p>

		<p>
			<label for="profile[zip]">Zip:</label>
			<input type="text" id="profile[zip]" name="profile[zip]" value="<?php echo $person->profile->zip;?>" />
		</p>

		<p>
			<label for="profile[country]">Country:</label>
			<input type="text" id="profile[country]" name="profile[country]" value="<?php echo $person->profile->country;?>" />
		</p>
		
		<p>
			<label for="profile[site_name]">Site Name:</label>
			<input type="text" id="profile[site_name]" name="profile[site_name]" value="<?php echo $person->profile->site_name;?>" />
		</p>
		<p>
			<label for="profile[site_description]">Site Description:</label>
			<input type="text" id="profile[site_description]" name="profile[site_description]" value="<?php echo $person->profile->site_description;?>" />
		</p>
		<p>
			<button type="submit" name="save_button" id="save_button"><span>Save</span></button>
		</p>
		<input type="hidden" id="id" name="id" value="{$person->id}" />
		<input type="hidden" name="_method" value="put" />
	</fieldset>
</form>