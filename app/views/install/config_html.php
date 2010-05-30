<form action="<?php echo FrontController::urlFor('install');?>" method="post">
	<fieldset>
		<legend>Datbase settings</legend>
		<p>
			<label for="user_name">User name</label>
			<input type="text" value="{$configuration->user_name}" name="user_name" />
		</p>
		
		<p>
			<label for="password">Password</label>
			<input type="password" value="{$configuration->password}" name="password" />
		</p>
		
		<p>
			<label for="host">Host</label>
			<input type="text" value="{$configuration->host}" name="host" />
		</p>
		
		<p>
			<label for="database">Database name</label>
			<input type="text" value="{$configuration->database}" name="database" />
		</p>
		
		<p>
			<label for="prefix">Table prefix</label>
			<input type="text" value="{$configuration->prefix}" name="prefix" />
		</p>
		
		<p>
			<label for="prefix">Theme</label>
			<input type="text" value="{$configuration->theme}" name="theme" />
		</p>
		<p>
			<label for="email">Admin Email</label>
			<input type="text" value="{$configuration->email}" name="email" />
		</p>
		<p>
			<label for="site_password">Site Password</label>
			<input type="password" value="{$configuration->site_password}" name="site_password" />
		</p>

		<p class="button">
			<input type="submit" value="Save Settings" name="saveButton" />
		</p>
		<input type="hidden" value="put" name="_method" />
		<input type="hidden" value="<?php echo(($configuration->db_type === null || strlen($configuration->db_type) === 0) ? 'MySql' : $configuration->db_type);?>" name="db_type" />		
	</fieldset>
</form>