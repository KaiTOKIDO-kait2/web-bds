<header id="header" class="transparent-header-modern fixed-header-bg-white w-100">
            <div class="top-header bg-secondary">
                <div class="container">
                    <div class="row">
                        <div class="col-md-8">
                            <ul class="top-contact list-text-white  d-table">
                                <li><a href="#"><i class="fas fa-phone-alt text-success mr-1"></i>+92 302 34 34 418</a></li>
                                <li><a href="#"><i class="fas fa-envelope text-success mr-1"></i>scriptandtools@webpenter.com</a></li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="top-contact float-right">
                                <ul class="list-text-white d-table">
								<li><i class="fas fa-user text-success mr-1"></i>
								<?php  if(isset($_SESSION['uemail']))
								{ ?>
								<a href="logout.php">Đăng xuất</a>&nbsp;&nbsp;<?php } else { ?>
								<a href="login.php">Đăng nhập</a>&nbsp;&nbsp;
								
								| </li>
								<li><i class="fas fa-user-plus text-success mr-1"></i><a href="register.php"> Đăng ký</li><?php } ?>
								</ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="main-nav secondary-nav hover-success-nav py-2">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <nav class="navbar navbar-expand-lg navbar-light p-0"> <a class="navbar-brand position-relative" href="index.php"><img class="nav-logo" src="admin/assets/img/logo.png" alt="Logo" style="height:44px;width:auto;max-width:220px;object-fit:contain;display:block;"></a>
                                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span> </button>
                                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                                    <ul class="navbar-nav mr-auto">
                                        <li class="nav-item dropdown"> <a class="nav-link" href="index.php" role="button" aria-haspopup="true" aria-expanded="false">Trang chủ</a></li>
										
										<li class="nav-item"> <a class="nav-link" href="about.php">Giới thiệu</a> </li>
										
                                        <li class="nav-item"> <a class="nav-link" href="contact.php">Liên hệ</a> </li>										
										
                                        <li class="nav-item"> <a class="nav-link" href="property.php">Bất động sản</a> </li>
                                        
                                        <li class="nav-item"> <a class="nav-link" href="agent.php">Môi giới</a> </li>

										
										<?php  if(isset($_SESSION['uemail']))
										{ ?>
										<li class="nav-item dropdown">
											<a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Tài khoản của tôi</a>
											<ul class="dropdown-menu">
												<li class="nav-item"> <a class="nav-link" href="profile.php">Hồ sơ</a> </li>
												<!-- <li class="nav-item"> <a class="nav-link" href="request.php">Property Request</a> </li> -->
												<li class="nav-item"> <a class="nav-link" href="feature.php">Bất động sản của bạn</a> </li>
												<li class="nav-item"> <a class="nav-link" href="logout.php">Đăng xuất</a> </li>	
											</ul>
                                        </li>
										<?php } else { ?>
										<li class="nav-item"> <a class="nav-link" href="login.php">Đăng nhập/Đăng ký</a> </li>
										<?php } ?>
										
                                    </ul>
                                    
									
									<a class="btn btn-success d-none d-xl-block" style="border-radius:30px;" href="submitproperty.php">Đăng tin bất động sản</a> 
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </header>