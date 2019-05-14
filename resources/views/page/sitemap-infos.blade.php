<?php 
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<!-- generated-on="<?php echo date("d F Y h ");?>h 11 min" -->
<!-- Debug: Total comment count: 23 -->
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">	<url>
		<loc><?php echo $sitemap_url;?></loc>
		<lastmod><?php echo date("Y-m-d");?>T00:00:00+00:00</lastmod>
		<changefreq>daily</changefreq>
		<priority>1.0</priority>
	</url>
<!-- Debug: Start Postings -->
<!-- Debug: Priority report of postID 1180: Comments: 0 of 23 = 0 points -->
	<?php
	foreach ($urls as $url=>$date_updated){		
	?>
		<url>
			<loc><?php echo $url;?></loc>
			<lastmod><?php echo $date_updated;?></lastmod>
			<changefreq>monthly</changefreq>
			<priority>0.2</priority>
		</url>
	<?php
	}
	?>
</urlset>