<?php
	require_once __DIR__ . '/../bootstrap.php';
?>
<!doctype html>
<html dir="ltr" lang="en">
	<head>
		<meta charset="utf-8">
		<title>Tasks</title>
		<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
		<link rel="stylesheet" href="/assets/main.css">
	</head>
	<body>
		<p><label>Sort by 
			<select id="sel-task-sort">
				<option>Priority</option>
				<option>Title (disables rearranging)</option>
			</select>
		</label></p>
		<div id="div-columns-container">
			<?php require APPDIR . 'public/ajax/board.php' ?>
		</div>
		<script type="text/javascript" src="https://code.jquery.com/git/jquery-3.x-git.min.js"></script>
		<script type="text/javascript" src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
		<script type="text/javascript" src="/assets/main.js"></script>
	</body>
</html>
