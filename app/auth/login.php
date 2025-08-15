<?php
require_once __DIR__ . '/../config/init.php';
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html lang="en" class="light-theme">

<head>
    <?php include '../partials/header.php'; ?>
</head>

<body>

    <div class="login-bg-overlay au-sign-in-basic"></div>

    <!--start wrapper-->
    <div class="wrapper">
        <div class="container">
            <div class="row">
                <div class="col-xl-4 col-lg-5 col-md-7 mx-auto mt-5">
                    <?php include '../partials/alert.php'; ?>
                    <div class="card radius-10">
                        <div class="card-body p-4">
                            <div class="text-center">
                                <h4>Sign In</h4>
                                <p>Sign In to your account</p>
                            </div>
                            <form class="form-body row g-3" method="POST" action="proses_login.php">
                                <div class="col-12">
                                    <label for="inputEmail" class="form-label">Email</label>
                                    <input name="email" type="email" class="form-control" id="inputEmail" placeholder="abc@example.com">
                                </div>
                                <div class="col-12">
                                    <label for="inputPassword" class="form-label">Password</label>
                                    <div class="input-group">
                                        <input name="password" type="password" class="form-control" id="inputPassword" placeholder="Your password">
                                        <span class="input-group-text" onclick="togglePasswordVisibility()" style="cursor: pointer;">
                                            <i id="eyeIcon" class="bi bi-eye"></i>
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <a href="authentication-reset-password-basic.html">Forgot Password?</a>
                                </div>
                                <div class="col-12 col-lg-12">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Sign In</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="my-5">
            <div>
                <div class="text-center">
                    <p class="my-2">Copyright <?= $APP_NAME ?></p>
                </div>
            </div>
        </footer>
    </div>
    <!--end wrapper-->

    <?php include '../partials/script.php'; ?>

</body>

</html>