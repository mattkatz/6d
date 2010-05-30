<script type="text/javascript">
<?php if($photo['error_message'] !== null && strlen($photo['error_message']) > 0):?>
	top.photoWasUploaded('{$photo_name}', '{$file_name}', '{$photo_path}');
<?php else:?>
	alert('<?php echo $photo['error_message'];?>');
<?php endif;?>
</script>
