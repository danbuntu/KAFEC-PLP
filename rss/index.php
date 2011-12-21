



<style> <!--
p { font: 11px arial, san-serif; margin-top: 2px;}
-->
</style>

<?php

echo 'index';

require("simplepie/simplepie.inc");

$feed = new SimplePie();
$feed->set_feed_url('http://mahara.midkent.ac.uk/artefact/blog/atom.php?artefact=30&view=138');

$feed->force_feed(true);

// array of feeds
//$feeds = array(
//  "http://mahara.midkent.ac.uk/artefact/blog/atom.php?artefact=30&view=138",
//    "http://www.tuxmachines.org/node/feed"
//);

// Run SimplePie.
$feed->init();

// This makes sure that the content is sent to the browser as text/html and the UTF-8 character set (since we didn't change it).
$feed->handle_content_type();


if ($feed->error())
{
	echo $feed->error();
}

// Let's begin our XHTML webpage code.  The DOCTYPE is supposed to be the very first thing, so we'll keep it on the same line as the closing-PHP tag.
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
        "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Sample SimplePie Page</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
</head>
<body>

	<div class="header">
		<h1><a href="<?php echo $feed->get_permalink(); ?>"><?php echo $feed->get_title(); ?></a></h1>
		<p><?php echo $feed->get_description(); ?></p>
	</div>

	<?php
	/*
	Here, we'll loop through all of the items in the feed, and $item represents the current item in the loop.
	*/
	foreach ($feed->get_items() as $item):
	?>

		<div class="item">
			<h2><a href="<?php echo $item->get_permalink(); ?>"><?php echo $item->get_title(); ?></a></h2>
			<p><?php echo $item->get_description(); ?></p>
			<p><small>Posted on <?php echo $item->get_date('j F Y | g:i a'); ?></small></p>
		</div>

	<?php endforeach; ?>

</body>
</html>
