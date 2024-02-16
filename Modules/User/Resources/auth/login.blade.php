
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Alysei - Login</title>
  <link href="https://fonts.googleapis.com/css?family=Karla:400,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.8.95/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://www.bootstrapdash.com/demo/login-template-free-2/assets/css/login.css">
</head>
<body>
  <main class="d-flex align-items-center min-vh-100 py-3 py-md-0">
    <div class="container">
      <div class="card login-card">
        <div class="row no-gutters">
          <div class="col-md-5">
            <img src="https://www.bootstrapdash.com/demo/login-template-free-2/assets/images/login.jpg" alt="login" class="login-card-img">
          </div>
          <div class="col-md-7">
            <div class="card-body">
              <div class="brand-wrapper">
                <img src="https://www.bootstrapdash.com/demo/login-template-free-2/assets/images/logo.svg" alt="logo" class="logo">
              </div>
              <p class="login-card-description">Sign into your account</p>

              <form method="POST" action="{{ url('login/admin-login') }}">
              	@if(Session::has('err_message'))
					<div class="alert alert-danger">
					  <strong>{{ Session::get('err_message') }}</strong>
					</div>
				@endif
              	@csrf
                  <div class="form-group">
                    <label for="email" class="sr-only">Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email address">
                    <span class="invalid-feedback" role="alert" style="color:red">
		                <strong>{{ $errors->first('email') }}</strong>
		            </span>
                  </div>
                  <div class="form-group mb-4">
                    <label for="password" class="sr-only">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="***********">
                    <span class="invalid-feedback" role="alert" style="color:red">
		                <strong>{{ $errors->first('password') }}</strong>
		            </span>
                  </div>
                  
                  <button type="submit" class="btn btn-block login-btn mb-4">
                                    {{ __('Login') }}
                                </button>
                </form>
                <a href="#!" class="forgot-password-link">Forgot password?</a>
               


				@if (Route::has('password.request'))
				    <a class="text-center" href="{{ route('password.request') }}">
				        {{ __('Forgot Your Password?') }}
				    </a></br>
				@endif

                <nav class="login-card-footer-nav">
                  <a href="#!">Terms of use.</a>
                  <a href="#!">Privacy policy</a>
                </nav>
            </div>
          </div>
        </div>
      </div>
      
    </div>
  </main>
  <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</body>
</html>
