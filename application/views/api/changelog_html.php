<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>User-relative Changelogs</title>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
	</head>
	<body>
		<?php foreach ($data as $v):
			$branchDisplay = $v->branch == "RELEASE" ?
				"" : " (". ucfirst(strtolower($v->branch)) .")";
		?>
			<div class="card m-2">
				<div class="card-body">
					<h4 class="card-title">v<?php echo $v->version . $branchDisplay; ?></h4>
					<p class="card-text">
						<strong>Release date: </strong><?php echo date('Y-m-d',strtotime($v->release_date)); ?><br>
						<strong>Changes:</strong><br>
						<?php echo nl2br($v->changelog); ?>
					</p>
				</div>
			</div>
		<?php endforeach; ?>
	</body>
</html>
