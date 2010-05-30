{
	"message":"<?php echo UserResource::getUserMessage();?>"
	, "person":{
		"photo_url":"<?php echo ProfileResource::getPhotoUrl($person);?>"
		, "name":"<?php echo $person->name;?>"
		, "email":"<?php echo $person->email;?>"
		, "address":"<?php echo $person->profile->address;?>"
		, "city":"<?php echo $person->profile->city;?>"
		, "state":"<?php echo $perosn->profile->state;?>"
		, "zip":"<?php echo $person->profile->zip;?>"
		, "country":"<?php echo $person->profile->country;?>"
		, "site_name":"<?php echo $person->profile->site_name;?>"
	}
}