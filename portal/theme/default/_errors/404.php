<!doctype html>
<!--[if lt IE 8 ]><html lang="en" class="no-js ie ie7"><![endif]-->
<!--[if IE 8 ]><html lang="en" class="no-js ie"><![endif]-->
<!--[if (gt IE 8)|!(IE)]><!--><html lang="en" class="no-js"><!--<![endif]-->
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	
	<title>Constellation Admin Skin</title>
	<meta name="description" content="">
	<meta name="author" content="">
	
	<!-- Mobile metas -->
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
	
	<!-- Combined stylesheets load -->
	<link href="css/mini.php?files=reset,common,form,standard,special-pages" rel="stylesheet" type="text/css">
	
	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
	<link rel="icon" type="image/png" href="favicon-large.png">
	
	<!-- Modernizr for support detection, all javascript libs are moved right above </body> for better performance -->
	<script src="js/libs/modernizr.custom.min.js"></script>
	
</head>

<!-- the 'special-page' class is only an identifier for scripts -->
<body class="special-page code-page dark">
	
	<h1>404</h1>
	<p>Page not found</p>
	<section>
		
		<ul class="action-tabs on-form with-children-tip children-tip-left">
			<li><a href="javascript:history.back()" title="Go back"><img src="images/icons/fugue/navigation-180.png" width="16" height="16"></a></li>
		</ul>
		
		<ul class="action-tabs right on-form with-children-tip children-tip-right">
			<li><a href="index.php" title="Go to homepage"><img src="images/icons/fugue/home.png" width="16" height="16"></a></li>
		</ul>
		
		<form class="block-content no-title dark-bg form" method="post" action="">
			<input type="text" name="s" id="s" value="<?php
			
			// Convert requested file to a search string
			if (isset($_SERVER['REQUEST_URI']))
			{
				$pathinfo = pathinfo($_SERVER['REQUEST_URI']);
				
				// For Php < 5.2
				if (!isset($pathinfo['filename']))
				{
					if (isset($pathinfo['extension']) and strlen($pathinfo['extension']) > 0)
					{
						$pathinfo['filename'] = substr($pathinfo['basename'], 0, -strlen($pathinfo['extension'])-1);
					}
					else
					{
						$pathinfo['filename'] = $pathinfo['basename'];
					}
				}
				
				echo htmlspecialchars(str_replace(array('_', '-', '+'), ' ', $pathinfo['filename']));
			}
			
			?>"> &nbsp; 
			<button type="submit">Search</button>
		</form>
		
	</section>
	
	<!--
	
	Updated as v1.5:
	Libs are moved here to improve performance
	
	-->
	
	<!-- Combined JS load -->
	<script src="js/mini.php?files=libs/jquery-1.6.3.min,jquery.tip"></script>
	
</body>
</html>