<?php if(count($rows) > 0):?>
<table>
	<thead>
		<tr>
			<?php foreach($rows[0] as $key=>$field):?>
			<th><?php echo $key;?></th>
			<?php endforeach;?>
		</tr>
	</thead>
	<tbody>
<?php foreach($rows as $i=>$row):?>
		<tr>
	<?php foreach($row as $key=>$value):?>		
			<td><?php echo ($value === null || strlen($value) === 0) ? '&nbsp;' : $value;?></td>
	<?php endforeach;?>
		</tr>
<?php endforeach;?>
	</tbody>
	<tfoot>
	</tfoot>
</table>
<?php else:?>
No results.
<?php endif;?>