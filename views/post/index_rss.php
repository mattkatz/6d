<?php echo '<?xml version="1.0" encoding="UTF-8"?>';?>
<rss version="2.0"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title><?php echo $owner->profile->site_name;?></title>
		<link><?php echo FrontController::urlFor(null);?></link>
		<description><?php echo $owner->profile->site_name;?></description>
		<language>en</language>
		<ttl>240</ttl>
		<?php foreach($posts as $post):?>
		<item>
			<title><?php echo htmlentities($post->title);?></title>
			<description><?php echo htmlentities($post->description);?></description>
			<pubDate><?php echo date('r', strtotime($post->post_date));?></pubDate>
			<?php if($post->tags !== null && strlen($post->tags) > 0):?>
			<?php foreach(explode(',', $post->tags) as $tag):?>
			<category><?php echo $tag;?></category>
			<?php endforeach;?>
			<?php endif;?>
			<guid><?php echo FrontController::urlFor(null) . $post->custom_url;?></guid>
			<link><?php echo FrontController::urlFor(null) . $post->custom_url;?></link>
			<content:encoded><![CDATA[<?php echo $post->body;?>]]></content:encoded>
		</item>
		<?php endforeach;?>
	</channel>
</rss>