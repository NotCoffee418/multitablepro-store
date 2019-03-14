<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<?php if (isset($page_description)): ?>
	<meta name="description" content="<?php echo $page_description; ?>">
	<?php endif; ?>

	<title><?php
		if (isset($page_title))
			echo $page_title;
		else echo $this->SiteSettings->get('site_title');
	?></title>

	<!-- <link rel="icon" href="/favicon.ico"> -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
	<link href="/css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
	<a class="navbar-brand" href="/"><?php echo $this->SiteSettings->get('site_title'); ?></a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>

	<div class="collapse navbar-collapse" id="navbarsExampleDefault">
		<ul class="nav navbar-nav mr-auto">
			<li class="nav-item active">
				<a class="nav-link" href="/download">Download</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="/store/multitable-pro">Buy</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="/support">Support</a>
			</li>
		</ul>
		<div class="dropdown-divider"></div>
		<div class="nav navbar-nav pull-md-right">

			<ul class="nav navbar-nav mr-auto">
			<?php if (isset($user)): ?>
				<li class="nav nav-item">
					<a class="nav-link disabled" href="#">Hello, <?php echo $user->first_name; ?>!</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="/user">My Licenses</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="/user/logout">Log out</a>
				</li>
			<?php else: ?>
				<li class="nav-item">
					<a class="btn btn-outline-light ml-3" role="button" rel="modal:open" href="/modals/login">Customer Portal</a>
				</li>
				<li class="nav-item">
					<a class="btn btn-outline-info ml-3" role="button" href="/download">Try for free!</a>
				</li>
			<?php endif; ?>
			</ul>
		</div>
	</div>
</nav>
