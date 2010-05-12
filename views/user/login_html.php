<form action="<?php echo FrontController::urlFor('login');?>" method="post">
	<fieldset>
		<legend>Sign in</legend>
		<p>
			<input type="text" value="" id="email" name="email" />
			<label for="email">Email</label>
		</p>
		
		<p>
			<input type="password" value="" id="password" name="password" />
			<label for="password">Password</label>
		</p>
		<p>
			<input type="submit" value="Login" />
		</p>
	</fieldset>
</form>