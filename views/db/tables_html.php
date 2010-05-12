<?php echo Resource::getUserMessage();?>
<ul>
<?php foreach($tables as $table):?>
	<li>
		<a href="javascript:void(0);" class="tables" title="<?php echo $table->$field_name;?>"><?php echo String::truncate($table->$field_name, 26);?></a>
		<form action="<?php echo FrontController::urlFor('db/table');?>" method="post">
			<input type="hidden" name="_method" value="delete" />
			<input type="hidden" name="db_name" value="<?php echo $db_name;?>" />
			<input type="hidden" name="table_name" value="<?php echo $table->$field_name;?>" />
			<a href="#" class="delete" db="<?php echo $db_name;?>" table="<?echo $table->$field_name;?>" title="delete">x</a>
		</form>
	</li>
<?php endforeach;?>
</ul>
