<div class="container">
	<h4>Don't have an account yet?</h4>
	<a type="button" class="btn btn-success" href="/user/register">Create an account</a>
	<hr class="divider">
	<h4>Log in</h4>
	<form action="/user/login">
		<input type="email" id="inputEmail" class="form-control" placeholder="Email address" required="" autofocus="">
		<input type="password" id="inputPassword" class="form-control" placeholder="Password" required="">
		<div class="checkbox">
			<label><input type="checkbox" value="remember-me"> Remember me</label>
		</div>
		<button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
	</form>
</div>
