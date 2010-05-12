<style type="text/css">
	dl dd{
		float: left;
		position: relative;
	}
	#zoom{
		width: 100%;
		height: 100%;
	}
	#zoom p{
		margin-bottom: 0;
	}
	dl dd label{
		display: block;
		position: absolute;
	}
</style>

<dl>
<?php for($i=0; $i < count($photos); $i++):?>
	<?php $image = $photos[$i];?>
	<?php if(strpos($image->src, 'http://') === false):?>
		<dd>
			<label id="thumb_<?php echo $i;?>"></label>
			<a class="thumbnail" href="<?php echo $this->getBigSrc($image->src);?>" title="">
				<img src="<?php echo $this->getLittleSrc($image->src);?>" width="<?php echo $this->getThumbnailWidth($image->src);?>" />
			</a>
		</dd>
	<?php endif;?>
<?php endfor;?>
</dl>