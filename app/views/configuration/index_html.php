<form action="configuration.php" method="post">
	<fieldset>
		<legend>Datbase settings</legend>
		<p>
			<label for="user_name">User name</label>
			<input type="text" value="<?php echo($this->configuration->user_name);?>" name="user_name" />
		</p>
		
		<p>
			<label for="password">Password</label>
			<input type="password" value="<?php echo($this->configuration->password);?>" name="password" />
		</p>
		
		<p>
			<label for="host">Host</label>
			<input type="text" value="<?php echo($this->configuration->host);?>" name="host" />
		</p>
		
		<p>
			<label for="database">Database name</label>
			<input type="text" value="<?php echo($this->configuration->database);?>" name="database" />
		</p>
		
		<p>
			<label for="prefix">Table prefix</label>
			<input type="text" value="<?php echo($this->configuration->prefix);?>" name="prefix" />
		</p>
		
		<p class="button">
			<input type="submit" value="Save Settings" name="saveButton" />
		</p>
		
	</fieldset>
</form>