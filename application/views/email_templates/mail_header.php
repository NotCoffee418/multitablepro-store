<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo $subject; ?></title>
	<style media="all" type="text/css">
		<?php
		// Referencing any css file does not work
		// including full bootstrap makes the message too large for gmail to display it
		// manually copy chunks instead when needed
		// https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.css
		?>
		body {
			margin: 0;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
			font-size: 1rem;
			font-weight: 400;
			line-height: 1.5;
			color: #212529;
			text-align: left;
			background-color: #fff;
		}
		.container {
			width: 100%;
			padding-right: 15px;
			padding-left: 15px;
			margin-right: auto;
			margin-left: auto;
		}

		@media (min-width: 576px) {
			.container {
				max-width: 540px;
			}
		}

		@media (min-width: 768px) {
			.container {
				max-width: 720px;
			}
		}

		@media (min-width: 992px) {
			.container {
				max-width: 960px;
			}
		}

		@media (min-width: 1200px) {
			.container {
				max-width: 1140px;
			}
		}

		article, aside, figcaption, figure, footer, header, hgroup, main, nav, section {
			display: block;
		}
		.bg-secondary {
			background-color: #6c757d !important;
		}
		.bg-light {
			background-color: #f8f9fa !important;
		}
		.text-muted {
			color: #6c757d !important;
		}
		small, .small {
			font-size: 80%;
			font-weight: 400;
		}
		.text-center {
			text-align: center !important;
		}
		hr {
			height: 1px;
			background: #000;
			border: 0;
		}

		.m-3 {
			margin: 1rem !important;
		}
		.pt-3 {
			padding-top: 1rem !important;
		}
		.pb-3 {
			padding-bottom: 1rem !important;
		}
		.p-3 {
			padding: 3rem !important;
		}
	</style>
</head>
<body class="bg-secondary text-light pt-3 pb-3">
<div class="container bg-light p-3">
	<div class="text-center pb-3">
		<img  style="background:red; width:150px; height:150px;">
	</div>

