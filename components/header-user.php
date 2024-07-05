<!-- header -->
<div class="container-fluid border fixed-top bg-white">
    <div class="container">
        <div class="row pt-3 pb-3">

            <div class="col h3 d-block">
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>">
    

    <nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><img
        src="/images/ATS-PH-LOGO.png"
        alt="Aehr Test Systems Logo"
        class=""
        style="max-width: 200px;"
    ></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="#">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Features</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Pricing</a>
        </li>
        <li class="nav-item">
          <a class="nav-link disabled" aria-disabled="true">Disabled</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

</a>

            </div>

            <div class="col text-end h3">
                <div class="dropdown">
                
                    <button id="accountBtn" class="btn btn-white dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-user"></i>
                        <?php 
                      
                        echo $_SESSION['user_full_name'];
                        
                        ?>
                    </button>
                    <ul class="dropdown-menu">
                        <!-- <li><a class="dropdown-item" href="account.php">Account</a></li> -->
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>

            </div>
        </div>
    </div>  
</div>