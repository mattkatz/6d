{
	"was_successful": "<?php echo count($errors) > 0 ? 'false' : 'true';?>"
	, "person":{"id":"<?php echo $person->id;?>", "name":"<?php echo $person->name;?>"}
	, "people": [
	<?php foreach($people as $key=>$person):?>
		<?php echo ($key > 0 ? ', ' : null);?>{"id":"<?php echo $person->id;?>", "name": "<?php echo $person->name;?>", "is_owner":<?php echo $person->is_owner ? 'true' : 'false';?>}
	<?php endforeach;?>
	]
	, "user_message":"<?php echo Resource::getUserMessage();?>"
}