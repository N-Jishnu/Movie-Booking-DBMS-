<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Admin | Movie Seat Reservation System</title>
 	

<?php include('./header.php'); ?>
<?php 
session_start();
if(isset($_SESSION['login_id']))
header("location:index.php?page=book");
?>

</head>
<style>
	body{
		width: 100%;
	    height: 100vh;
	    background: linear-gradient(135deg, #007bff, #0056b3);
	    overflow: hidden;
	}
	main#main{
		width: 100%;
		height: 100vh;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.login-container {
		width: 100%;
		max-width: 450px;
		padding: 15px;
	}
	.card {
		border-radius: 10px;
		box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
		overflow: hidden;
		transition: all 0.3s ease;
	}
	.card:hover {
		transform: translateY(-5px);
		box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
	}
	.card-header {
		background: #007bff;
		color: white;
		text-align: center;
		padding: 20px;
	}
	.card-header h3 {
		margin: 0;
		font-weight: 600;
	}
	.card-body {
		padding: 30px;
	}
	.form-group {
		margin-bottom: 25px;
	}
	.form-control {
		border-radius: 5px;
		padding: 12px 15px;
		height: auto;
		border: 1px solid #ddd;
		transition: all 0.3s ease;
	}
	.form-control:focus {
		border-color: #007bff;
		box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
	}
	.btn-primary {
		background: #007bff;
		border: none;
		border-radius: 5px;
		padding: 12px;
		font-weight: 600;
		letter-spacing: 0.5px;
		transition: all 0.3s ease;
	}
	.btn-primary:hover {
		background: #0056b3;
		transform: translateY(-2px);
		box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
	}
	.alert {
		border-radius: 5px;
		margin-bottom: 20px;
	}
	.theater-logo {
		text-align: center;
		margin-bottom: 20px;
	}
	.theater-logo i {
		font-size: 48px;
		color: #007bff;
	}
</style>

<body>

  <main id="main">
    <div class="login-container">
      <div class="card">
        <div class="card-header">
          <div class="theater-logo">
            <i class="fa fa-film"></i>
          </div>
          <h3>Admin Login</h3>
        </div>
        <div class="card-body">
          <form id="login-form">
            <div class="form-group">
              <label for="username" class="control-label">Username</label>
              <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username">
            </div>
            <div class="form-group">
              <label for="password" class="control-label">Password</label>
              <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password">
            </div>
            <div class="form-group text-center">
              <button type="submit" class="btn btn-primary btn-block">Login</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>

  <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>

</body>
<script>
	$('#login-form').submit(function(e){
		e.preventDefault();
		$('#login-form button[type="submit"]').attr('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Logging in...');
		if($(this).find('.alert-danger').length > 0 )
			$(this).find('.alert-danger').remove();
		$.ajax({
			url:'ajax.php?action=login',
			method:'POST',
			data:$(this).serialize(),
			error:err=>{
				console.log(err);
				$('#login-form button[type="submit"]').removeAttr('disabled').html('Login');
			},
			success:function(resp){
				if(resp == 1){
					location.reload('index.php?page=home');
				}else{
					$('#login-form').prepend('<div class="alert alert-danger">Username or password is incorrect.</div>');
					$('#login-form button[type="submit"]').removeAttr('disabled').html('Login');
				}
			}
		});
	});
</script>	
</html>