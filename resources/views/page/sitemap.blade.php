<?php 
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<sitemapindex xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">
	<sitemap>
		<loc><?php echo config("app.sitemap_url");?>/sitemap-artist.xml</loc>
		<lastmod><?php echo date("Y-m-d");?></lastmod>
	</sitemap>
	<sitemap>
		<loc><?php echo config("app.sitemap_url");?>/sitemap-album.xml</loc>
		<lastmod><?php echo date("Y-m-d");?></lastmod>
   </sitemap>
   <sitemap>
		<loc><?php echo config("app.sitemap_url");?>/sitemap-title.xml</loc>
		<lastmod><?php echo date("Y-m-d");?></lastmod>
   </sitemap>
</sitemapindex>