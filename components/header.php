<!-- header -->
<div class="container-fluid border fixed-top bg-white">
    <div class="container">
        <div class="row pt-3 pb-3">

            <div class="col h3 d-block">


<!--<a href="/dashboard.php">-->
<!--    <img src="/images/ATS-PH-LOGO.png" alt="Aehr Test Systems Logo" class="logo" style="max-width: 200px;">-->
<!--</a>-->

<!--<a href="/dashboard.php" class="home-link">Home</a>-->

<?php 
if($_SESSION['user_role'] == 1){
    echo '<a href="dashboard.php">
        <img src="/images/ATS-PH-LOGO.png" alt="Aehr Test Systems Logo" class="logo" style="max-width: 200px;">
    </a>';

    echo '<a href="dashboard.php" class="home-link">Home</a>';
    
    
} else {
    echo '<a href="/dashboard.php">
        <img src="/images/ATS-PH-LOGO.png" alt="Aehr Test Systems Logo" class="logo" style="max-width: 200px;">
    </a>';

    echo '<a href="/dashboard.php" class="home-link">Home</a>';
}
?>




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
                        <li><a class="dropdown-item" href="account.php">Account</a></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>

            </div>
        </div>
    </div>  
</div>