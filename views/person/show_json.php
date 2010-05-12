{
	"message":"<?php echo UserResource::getUserMessage();?>"
	, "person":{
		"id":"<?php echo $person->id;?>"
		, "name":"<?php echo $person->name;?>"
		, "email":"<?php echo $person->email;?>"
		, "url":"<?php echo $person->url;?>"
		, "is_approved":"<?php echo $person->is_approved;?>"
	}
}