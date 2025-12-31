<!doctype html>
<html lang="en">
<head>
  <title>Webleb</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <!-- Bootstrap CSS v5.2.1 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <style>
        body {
  background-color: #071014;
  background-image: -webkit-linear-gradient(160deg, #071014 0%, #0db8de 100%);
  background-image: -moz-linear-gradient(160deg, #071014 0%, #0db8de 100%);
  background-image: -o-linear-gradient(160deg, #071014 0%, #0db8de 100%);
  background-image: linear-gradient(160deg, #071014 0%, #0db8de 100%);  
    font-family: 'Arial', sans-serif;
    height: 100vh;
}
.login-box {
    margin-top: 75px;
    background: #1A2226;
    text-align: center;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);
    border-radius: 15px;
}
.login-title {
    text-align: center;
    font-size: 30px;
    letter-spacing: 2px;
    margin-top: 35px;
    font-weight: bold;
    color: #0DB8DE;
}
.login-form {
    margin-top: 25px;
    text-align: left;
}
input[type=text] {
    background-color: #1A2226;
    border: none;
    border-bottom: 2px solid #0DB8DE;
    border-top: 0px;
    border-radius: 0px;
    outline: 0;
    margin-bottom: 20px;
    padding-left: 0px;
    color: #ECF0F5;
}
input[type=password] {
    background-color: #1A2226;
    border: none;
    border-bottom: 2px solid #0DB8DE;
    border-radius: 0px;
    margin-bottom: 20px;
    color: #ECF0F5;
}
.form-group {
margin: 30px;
}
.form-control:focus {
    box-shadow: none;
    background-color: #1A2226;
    color: #ECF0F5;
}
.form-control-label {
    font-size: 15px;
    color: #6C6C6C;
    letter-spacing: 1px;
}
.btn-outline-primary {
  width: 200px;
  height: 50px;
  border-radius: 15px;
  cursor: pointer;
  border-color:#0DB8DE!important;
  color:#0DB8DE;
  margin-top: 30px;
}
.btn-outline-primary:hover{
  background-color: #071014!important;
  background-image: -webkit-linear-gradient(160deg, #071014 0%, #0db8de 100%)!important;
  background-image: -moz-linear-gradient(160deg, #071014 0%, #0db8de 100%)!important;
  background-image: -o-linear-gradient(160deg, #071014 0%, #0db8de 100%)!important;
  background-image: linear-gradient(160deg, #071014 0%, #0db8de 100%)!important;
  
  color: #ffffff!important;
}
.login-button {
    padding-right: 0px;
    text-align: right;
    margin-bottom: 25px;
}
.login-text {
    padding-left: 0px;
    color: #A2A4A4;
}

    </style>
</head>
<body class="d-flex align-items-center">
  <div class="container">
    <div class="row justify-content-center" style="margin:20px;">
      <div class="col-lg-6 col-md-8 login-box">
        <div class="col-lg-12 login-title">
          Login
        </div>
        <div class="col-lg-12 login-form">
          <form>
            <div class="form-group">
              <label class="form-control-label">Email</label>
              <input type="text" class="form-control">
            </div>
                <div class="form-group">
                  <label class="form-control-label">Password</label>
                  <input type="password" class="form-control">
                </div>
                <div class="col-12 login-btm login-button justify-content-center d-flex" style="gap: 15px;">
                  <button type="submit" class="btn btn-outline-primary">login</button>
                  <a href="index.php" class="btn btn-outline-primary" role="button">create account</a>
                </div>
            </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js"
      integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous">
      </script>
</body>
</html>

            