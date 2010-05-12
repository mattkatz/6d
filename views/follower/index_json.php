{
	"was_successful": "<?php echo count($errors) > 0 ? 'false' : 'true';?>"
	, "person":{"id":"<?php echo $friend->id;?>", "name":"<?php echo $friend->name;?>"}
	, "people": [
	<?php foreach($people as $key=>$person):?>
		<?php echo ($key > 0 ? ', ' : null);?>{"id":"<?php echo $person->id;?>", "name": "<?php echo $person->name;?>"}
	<?php endforeach;?>
	]
	, "user_message":"<?php echo Resource::getUserMessage();?>"
}