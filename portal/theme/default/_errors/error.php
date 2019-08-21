<?php
	
	// Lib
	require('libError.php');
	
	/*
	 * You can check here whether or not to display error details
	 * For example : show only for local IP ranges (127.0.*, 192.168.*...)
	 * Report data (see below) will be encoded anyway to protect you code
	 * 
	 * Example
	 * if ($_SERVER['SERVER_ADDR'] == '127.0.0.1')
	 * {
	 * 		// Dev, display errors
	 * 		$showDetails = true;
	 * }
	 * else
	 * {
	 * 		// Production, no display
	 * 		$showDetails = false;
	 * }
	 * 
	 */
	$showDetails = true;
	
	// If exception
	if (isset($exception))
	{
		$errno = $exception->getCode();
		$errstr = $exception->getMessage();
		$errfile = $exception->getFile();
		$errline = $exception->getLine();
		$errcontext = array();
		$stack = $exception->getTrace();
	}
	else
	{
		// Error mode
		$exception = false;
		
		// Stack backtrace
		$stack = debug_backtrace();
		array_shift($stack);			// Remove require('error.php') and other error handling functions
		while (isset($stack[0]['function']) and ($stack[0]['function'] === 'userErrorHandler' or $stack[0]['function'] === 'trigger_error'))
		{
			// Remove user trigger_error and custom error handler functions call
			array_shift($stack);
		}
		
		// If no error data
		if (!isset($errno))
		{
			// No error data provided, default error
			$errno = 0;
			$errstr = 'System encountered an unknown error';
			$errfile = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '(unknown)';
			$errline = 0;
			$errcontext = array();
		}
	}
	
	// Stack description
	$stackDesc = array();
	if (is_array($stack) and count($stack) > 0)
	{
		$stackDesc[] = '<ul class="picto-list icon-top with-line-spacing">';
		
		foreach ($stack as $level)
		{
			// Check infos
			$file = isset($level['file']) ? $level['file'] : '(unknown file)';
			$line = isset($level['line']) ? $level['line'] : 0;
			$function = isset($level['function']) ? $level['function'] : '(unknown function)';
			if (isset($level['class']) and strlen($level['class']) > 0)
			{
				$function = $level['class'].'::'.$function;
			}
			
			// Arguments
			$args = array();
			if (isset($level['args']))
			{
				foreach ($level['args'] as $arg)
				{
					$args[] = describeVar($arg);
				}
			}
			
			$stackDesc[] = '	<li class="force-wrap"><b>'.htmlspecialchars(trimDocumentRoot($file, 40)).'</b> @ line <b>'.$line.'</b>: '.$function.'('.implode(', ', $args).')</li>';
		}
		
		$stackDesc[] ='</ul>';
	}
	
	// Compose data for error reporting
	$report = array(
		'time' => time(),
		'type' => $exception ? 'exception' : 'error',
		'url' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
		'number' => $errno,
		'message' => $errstr,
		'file' => $errfile,
		'line' => $errline,
		'context' => '<ul>'.buildSimpleVarList($errcontext).'</ul>',
		'stack' => implode("\n", $stackDesc)
	);
	
	// Session identifier
	if (session_id() == '')
	{
		session_start();
	}
	
	// Encoded data for reporting
	$encodedReport = encode(json_encode($report), session_id());

?><!doctype html>
<!--[if lt IE 8 ]><html lang="en" class="no-js ie ie7"><![endif]-->
<!--[if IE 8 ]><html lang="en" class="no-js ie"><![endif]-->
<!--[if (gt IE 8)|!(IE)]><!--><html lang="en" class="no-js"><!--<![endif]-->
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	
	<title>Constellation Admin Skin</title>
	<meta name="description" content="">
	<meta name="author" content="">
	<meta name="robots" content="none">
	
	<!-- Mobile metas -->
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
	
	<!-- Combined stylesheets load -->
	<link href="css/mini.php?files=reset,common,form,standard,special-pages,simple-lists" rel="stylesheet" type="text/css">
	
	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
	<link rel="icon" type="image/png" href="favicon-large.png">
	
	<!-- Modernizr for support detection, all javascript libs are moved right above </body> for better performance -->
	<script src="js/libs/modernizr.custom.min.js"></script>
	
</head>

<!-- the 'special-page' class is only an identifier for scripts -->
<body class="special-page error-bg red dark">
	
	<section id="error-desc">
		
		<ul class="action-tabs with-children-tip children-tip-left">
			<li><a href="javascript:history.back()" title="Go back"><img src="images/icons/fugue/navigation-180.png" width="16" height="16"></a></li>
			<li><a href="javascript:window.location.reload()" title="Reload page"><img src="images/icons/fugue/arrow-circle.png" width="16" height="16"></a></li>
		</ul>
		
		<ul class="action-tabs right with-children-tip children-tip-right">
			<li><a href="#" title="Show/hide<br>error details" onClick="$(document.body).toggleClass('with-log'); return false;">
				<img src="images/icons/fugue/application-monitor.png" width="16" height="16">
			</a></li>
		</ul>
		
		<div class="block-border"><div class="block-content">
				
			<h1>Admin</h1>
			<div class="block-header">System error</div>
			
			<h2>Error description</h2>
			
			<h5>Message</h5>
			<p>An error occurred while processing your request. Please return to the previous page and check everything before trying again. If the same error occurs again, please contact your system administrator or report error (see below).</p>
			
			<p><b>Event type:</b> <?php echo $exception ? 'exception' : 'error'; ?><br>
			<b>Page:</b> <?php echo isset($_SERVER['REQUEST_URI']) ? htmlspecialchars(shortenFilePath($_SERVER['REQUEST_URI'], 40)) : '(undefined)'; ?></p>
			
			<form class="form" id="send-report" method="post" action="_errors/sendReport.php">
				<input type="hidden" name="report" id="report" value="<?php echo htmlspecialchars($encodedReport); ?>">
				<fieldset class="grey-bg no-margin collapse">
					<legend><a href="#">Report error</a></legend>
					
					<p>
						<label for="description" class="light float-left">To report this error, please explain how it happened and click below:</label>
						<textarea name="description" id="description" class="full-width" rows="4"></textarea>
					</p>
					
					<p>
						<label for="report-sender" class="grey">Your e-mail address (optional)</label>
						<span class="float-left"><button type="submit" class="full-width">Report</button></span>
						<input type="text" name="sender" id="sender" value="" class="full-width">
					</p>
				</fieldset>
			</form>
		</div></div>
	</section>
	
<?php if ($showDetails):

?>	<section id="error-log">
		<div class="block-border"><div class="block-content">
				
			<h1>Error details</h1>
			
			<div class="fieldset grey-bg with-margin">
				<p><b>Message</b><br>
				<?php echo htmlspecialchars($errstr); ?></p>
			</div>
			
			<ul class="picto-list">
				<li class="icon-tag-small"><b>Php error level:</b> <?php echo $errno; ?></li>
				<li class="icon-doc-small"><b>File:</b> <?php echo trimDocumentRoot($errfile, 40); ?></li>
				<li class="icon-pin-small"><b>Line:</b> <?php echo $errline; ?></li>
			</ul>
			
			<ul class="collapsible-list with-bg">
				<li class="close">
					<b class="toggle"></b>
					<span><b>Context:</b></span>
					<ul class="with-icon no-toggle-icon">
						<?php
						
						if (is_array($errcontext) and count($errcontext) > 0)
						{
							echo buildVarList($errcontext);
						}
						else
						{
							echo '<li><span><em>(empty)</em></span></li>';
						}
						
						// Preserve indentation
						echo "\n";
						
						?>
					</ul>
				</li>
			</ul>
			
			<h2>Stack backtrace</h2>
			<?php
			
			if (count($stackDesc) > 0)
			{
				echo implode("\n".'			', $stackDesc);
			}
			else
			{
				echo '<p class="grey"><em>(empty)</em></p>';
			}
			
			// Preserve indentation
			echo "\n";
			
			?>
			
		</div></div>
	</section>

<?php endif;

?>	<!--
	
	Updated as v1.5:
	Libs are moved here to improve performance
	
	-->
	
	<!-- Combined JS load -->
	<script src="js/mini.php?files=libs/jquery-1.6.3.min,common,standard,jquery.tip,list"></script>
	<!--[if lte IE 8]><script src="js/standard.ie.js"></script><![endif]-->
	
	<!-- Ajax error report -->
	<script>
	
		$(document).ready(function()
		{
			$('#send-report').submit(function(event)
			{
				// Stop full page load
				event.preventDefault();
				
				var submitBt = $(this).find('button[type=submit]');
				submitBt.disableBt();
					
				// Target url
				var target = $(this).attr('action');
				if (!target || target == '')
				{
					// Page url without hash
					target = document.location.href.match(/^([^#]+)/)[1];
				}
				
				// Request
				var data = {
					a: $('#a').val(),
					report: $('#report').val(),
					description: $('#description').val(),
					sender: $('#sender').val()
				};
				
				// Send
				$.ajax({
					url: target,
					dataType: 'json',
					type: 'POST',
					data: data,
					success: function(data, textStatus, XMLHttpRequest)
					{
						if (data.valid)
						{
							$('#send-report').removeBlockMessages().blockMessage('Report sent, thank you for your help!', {type: 'success'});
						}
						else
						{
							// Message
							$('#send-report').removeBlockMessages().blockMessage('An unexpected error occured, please try again', {type: 'error'});
							
							submitBt.enableBt();
						}
					},
					error: function(XMLHttpRequest, textStatus, errorThrown)
					{
						// Message
						$('#send-report').removeBlockMessages().blockMessage('Error while contacting server, please try again', {type: 'error'});
						
						submitBt.enableBt();
					}
				});
				
				// Message
				$('#send-report').removeBlockMessages().blockMessage('Please wait, sending report...', {type: 'loading'});
			});
		});
	
	</script>
	
</body>
</html>
