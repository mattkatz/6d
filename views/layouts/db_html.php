<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Joey's log: {$title}</title>
		<link rel="icon" href="<?php echo FrontController::urlFor('images');?>favicon.png"/>
		<meta name="description" content="{$description}"/>
		<meta name="keywords" content="{$keywords}"/>
		<meta name="viewport" content="width=980"/>
		<link rel="stylesheet" type="text/css" href="<?php echo FrontController::urlFor('themes');?>css/reset.css" media="all" />
		<link rel="stylesheet" type="text/css" href="<?php echo FrontController::urlFor('themes');?>css/default.css" media="all" />
		{$resource_css}
		<script type="text/javascript" language="javascript" src="<?php echo FrontController::urlFor('js');?>mootools-core.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo FrontController::urlFor('js');?>mootools-more.js"></script>
		
	</head>
	<body>
		<?php echo UserResource::getUserMessage();?>			
		{$output}
	</body>
</html>