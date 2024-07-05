<?php 
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: logout.php");
}else{
    
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AEHR Upload</title>

    <!-- Bootstrap CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/upload.css" rel="stylesheet">

    <script src="https://kit.fontawesome.com/c3cf7a82ce.js" crossorigin="anonymous"></script>

    <!-- Custom CSS for suggestion items -->
    <style>
        .suggestion-item {
            list-style-type: none; /* Remove default list item marker */
            transition: transform 0.2s ease-in-out;
            padding-left: 0; /* Reset left padding */
        }
        .suggestion-item:hover {
            transform: translateX(5px);
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-light">

<form id="UploadForm" method="post" onsubmit="submitForm()" enctype="multipart/form-data">

<?php 

include 'components/header.php';
?>

<div class="container pt-5 bg-light pb-2">
    <h1 class="text-start mt-5 fs-2">Add documents and recipients</h1>
    <div class="card p-4" style="width: 100%;"> 
        <div class="mb-3">
            <button type="button" class="btn btn-primary" onclick="goBack()">Back</button>
        </div>

        <script>
            function goBack() {
                window.history.back();
            }
        </script>

        <div class="mb-3">
            <label for="inputFile" class="form-label">Upload Documents</label>
            <input required class="form-control" type="file" id="inputFile" accept=".pdf" name="pdfFile[]" multiple>
        </div>
        
        
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input required class="form-control" type="text" id="title"  name="title" placeholder="Title">
        </div>

        
        

        <div class="mb-3">
            <h2 class="text-start fs-5">Add recipients</h2>
            
            <!-- Recipients content -->
            <div id="recCard" class="mb-3">
                <!-- Recipient inputs will be dynamically added here -->
            </div>

            <button type="button" id="addRecipientbtn" class="btn mb-3 mt-3" onClick="addRecipient()"><i class="fa-solid fa-plus"></i> Add Recipient</button>
        </div>
    </div>
</div>

<!-- Suggestion container and script -->
<script>
    function showEmails(counter) {
        var input = document.getElementById('email' + counter).value;
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'backend/getEmails.php?input=' + input, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var emailList = JSON.parse(xhr.responseText);
                displaySuggestions(emailList, counter);
            }
        };
        xhr.send();
    }

    function displaySuggestions(emailList, counter) {
        var input = document.getElementById('email' + counter).value;
        var suggestions = [];

        for (var i = 0; i < emailList.length; i++) {
            if (emailList[i].startsWith(input)) {
                suggestions.push(emailList[i]);
            }
        }

        // Display email suggestions below the input field
        var suggestionsContainer = document.getElementById('suggestionsContainer' + counter);
        suggestionsContainer.innerHTML = ''; // Clear previous suggestions

        if (suggestions.length > 0) {
            var ul = document.createElement('ul');
            ul.style.marginTop = '0px';
            ul.style.marginBottom = '0px';
            ul.style.paddingLeft = '0px';

            for (var j = 0; j < suggestions.length; j++) {
                var li = document.createElement('li');
                li.textContent = suggestions[j];
                li.classList.add('suggestion-item'); // Add class for hover effect
                li.onclick = function() {
                    document.getElementById('email' + counter).value = this.textContent;
                    suggestionsContainer.style.display = 'none'; // Hide suggestions container
                };
                ul.appendChild(li);
            }
            suggestionsContainer.appendChild(ul);
            suggestionsContainer.style.display = 'block'; // Show the container
        } else {
            suggestionsContainer.style.display = 'none'; // Hide the container if no suggestions
        }
    }
</script>

<!-- footer -->
<div class="container-fluid border fixed-bottom bg-white">
    <div class="container">
        <div class="row">
            <div class="col text-end h3 pt-3 pb-2">
                <button type="submit" id="send" class="btn" ><i class="fa-solid fa-paper-plane"></i> Send Documents</button>
            </div>
        </div>
    </div>  
</div>

</form> 


<script>
      function submitForm() {


        event.preventDefault();

       
        var countOfInputs = document.querySelectorAll('.counter input[name^="order"]');
        var countOfEmails = document.querySelectorAll('.counter input[name^="email"]');
        var countOfActions = document.querySelectorAll('.counter select[name^="action"]');

        var Json = {
            "orders": []
        };

        countOfInputs.forEach(function (input, index) {
        
            var newOrder = {
                "order": index + 1,
                "action": parseInt(countOfActions[index].value), // Assuming actions are numeric values
                "emails": countOfEmails[index].value.split(',').map(email => email.trim()) // Split emails by comma
            };


            Json.orders.push(newOrder);
        });



    var formData = new FormData(document.getElementById('UploadForm'));

    formData.append('myjson', JSON.stringify(Json));


    console.log("Form data:", formData);


 
   // Show confirmation dialog
Swal.fire({
    icon: 'info',
    title: 'Sending Document!',
    text: 'Are you sure you want to send the document?',
    showCancelButton: true,
    confirmButtonText: 'Yes, send it!',
    cancelButtonText: 'Cancel',
}).then((result) => {
    // Proceed if the user confirms
    if (result.isConfirmed) {
        var myAlert = Swal.fire({
            icon: 'info',
            title: 'Sending Document!',
            text: 'Your document is sending, please wait.',
            timerProgressBar: true,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading(); // Show loading spinner
            }
        });

        // Perform AJAX request to upload the document
        $.ajax({
            type: "POST",
            url: "backend/upload.php", // Change this to your server-side script
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                console.log(response);
                // Handle the server response here
                myAlert.close();

              Swal.fire({
                icon: 'success',
                title: 'Document Sent!',
                text: 'Your document has been successfully sent.',
                timer: 3000, // Automatically close after 3 seconds
                timerProgressBar: true,
                showConfirmButton: true
            }).then(() => {
                window.location.href = 'dashboard.php'; // Redirect to dashboard.php after clicking OK
            });

                // Remove file selected on success (if needed)
                // var input = document.getElementById('inputFile');
                // input.value = null;

                // const existingFileLabels = document.querySelectorAll('.file-label');
                // for (let i = 0; i < existingFileLabels.length; i++) {
                //     existingFileLabels[i].remove();
                // }
            },
            error: function (response) {
                console.log(response);
                myAlert.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong while sending the document: ' + error
                });
            }
        });
    }
});


        console.log('CLicked');


    
      }

    </script>
    
    

<!-- SweetAlert JS -->
<script src="js/sweetAlert/sweetalert2.all.min.js"></script>

<!-- jQuery JS -->
<script type="text/javascript" src="js/jQuery/jquery-3.3.1.slim.min.js"></script>

<!-- Bootstrap CSS JS -->
<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="bootstrap/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="js/upload.js"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

</body>
</html>
