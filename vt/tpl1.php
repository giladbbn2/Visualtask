<!DOCTYPE html>
<html>
	<head>
		<meta content="text/html;charset=utf-8" http-equiv="Content-Type" />
		<meta content="utf-8"/>    
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 4 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    
		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
		<title>tpl1 - render example</title>

		<!-- bootstrap 4 -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">

		<!-- visual task grid -->
		<link rel="stylesheet" href="/css/grid.visualtask.css" />

	</head>
	<body>

		<div id="graph1"></div>
		<div id="graph2"></div>
		<div id="vtgerror"></div>

		<script>


		var task = <?php echo json_encode($task); ?>;

		</script>

		<!-- Base64 -->
		<script src="/js/base64.js"></script>

		<!-- Plotly -->
		<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>

		<!-- VisualTask -->
		<script src="/js/visualtask.js"></script>

	    <!-- VisualTask Grid -->
	    <script src="/js/grid.visualtask.js"></script>

		<script>		

		window.VisualTaskConfig = <?php echo json_encode($config); ?>;

		if (typeof window.VisualTaskConfig.isAutoQuery !== "undefined" && window.VisualTaskConfig.isAutoQuery)
			VisualTask.query(task);

		</script>

	</body>
</html>

