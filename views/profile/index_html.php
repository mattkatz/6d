
<section class="vcard">
	<img style="float:left; margin-right:4px" src="<?php echo ProfileResource::getPhotoUrl($person);?>" alt="photo of {$person->name}" class="photo"/>
	<span class="fn name">{$person->name}</span>
	<a class="email" href="mailto:{$person->email}">{$person->email}</a>
	<address class="adr">
		<p class="street-address">
			<?php echo unserialize($person->profile)->address;?>
		</p>
		<p class="locality">
			<?php echo $person->profile->city;?>
		</p>,
		<p class="region"><?php echo $person->profile->state;?></p>, 
		<p class="postal-code"><?php echo $person->profile->zip;?></p>
		<p class="country-name"><?php echo $person->profile->country;?></p>
		<p class="fn org"><?php echo $person->profile->site_name;?></p>
	</address>
</section>
<?php if( AuthController::isAuthorized()):?>
<a href="<?php echo FrontController::urlFor('profile', array('state'=>'modify'));?>" id="edit_link">edit</a>
<?php endif;?>