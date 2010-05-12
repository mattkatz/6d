
<div class="vcard">
  <img style="float:left; margin-right:4px" src="<?php echo ProfileResource::getPhotoUrl($person);?>" alt="photo of {$person->name}" class="photo"/>
 <span class="fn">{$person->name}</span>
 <div class="org">6d</div>
 <a class="email" href="mailto:{$person->email}">{$person->email}</a>
 <div class="adr">
  <div class="street-address"><?php echo unserialize($person->profile)->address;?></div>
  <span class="locality"><?php echo unserialize($person->profile)->city;?></span>
, 
  <span class="region"><?php echo unserialize($person->profile)->state;?></span>
, 
  <span class="postal-code"><?php echo unserialize($person->profile)->zip;?></span>

  <span class="country-name"><?php echo unserialize($person->profile)->country;?></span>

 </div>
 <div class="tel"></div>
</div>
<?php if( AuthController::isAuthorized()):?>
<a href="<?php echo FrontController::urlFor('profile', array('state'=>'modify'));?>" id="edit_link">edit</a>
<?php endif;?>