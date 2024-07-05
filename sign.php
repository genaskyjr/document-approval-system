<?php
//user must be logged
//user signer must equal to current signer..
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
session_start();
if (!isset($_SESSION['user_id'])) {

    if(isset($_GET['id'])){
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $message = $baseUrl . '/sign.php?id='. $_GET['id'];
        $_SESSION['link'] = $message;
    }


    header("Location: index.php");
}else{
    
    //check if may signature
        if (strpos($_SESSION['user_signature'], '.png') === false) {
        header('Location: account.php');
        exit; // Ensure that script execution stops after redirecting
    }



    //get curent email
    //get current signer
    $current_email = $_SESSION['user_email'];

    include 'backend/function.php';
    $current_signer_emails = getEmailsFromPDFid($_GET['id']);


    if (!in_array($current_email, $current_signer_emails)) {
        header("Location: logout.php");
    }else{

     
        

        $fullname = $_SESSION['user_full_name'];
        $email = $_SESSION['user_email'];
        $dir = getPdfDirectory($_GET['id']);

        // Unserialize the data to get an array of file paths
        $pdfPathsArray = unserialize($dir);


        $signature = $_SESSION['user_signature'];

        $id = $_GET['id'];
        echo '<input style="display: none;" type="text" id="pdfDir" value="' . htmlspecialchars($dir, ENT_QUOTES, 'UTF-8') . '">';
        echo '<input style="display: none;" type="text" id="fullname" value="' . htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8') . '">';
        echo '<input style="display: none;" type="text" id="email" value="' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '">';
        echo '<input style="display: none;" type="text" id="signature" value="' . htmlspecialchars($signature, ENT_QUOTES, 'UTF-8') . '">';
        
        
        
        
   
        echo '<input style="display: none;" type="text" id="id" value="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '">';
        
        


    }

  

}




?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Add this meta tag to allow scripts from unpkg.com -->
<meta http-equiv="Content-Security-Policy" content="script-src 'self' unpkg.com">

  <title>AEHR Sign</title>

  <!-- Bootstrap CSS -->
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom Css -->
  <link href="css/sign.css" rel="stylesheet">


    <!-- Bootstrap CSS JS -->
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script src="https://kit.fontawesome.com/c3cf7a82ce.js" crossorigin="anonymous"></script>


    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .pdf-container {
            width: 100%;
            height: 600px; /* Adjust the height as needed */
        }

        .pdf-object {
            width: 100%;
            height: 100%;
        }

        .pdf-pages{
          height: 200px;
          width: 100%;
        }


    </style>



</head>
<body class="bg-light">


<?php 

include 'components/header.php';

?>


<!-- Sign document -->
<div class="container pt-5 bg-light pb-2">
  <h1 class="text-start mt-5 fs-2">Sign Document</h1>

  <div class="card" style="width: 100%; height: 450px;">


    <div class="row g-0 text-center" >


      <!-- drag -->
      <div id="fieldsViewer" class="col-2 border overflow-auto bg-light p-3 text-start" style="height: 450px;">

      <?php 
//echo $current_signer_email;
//echo $current_email;
//echo $dir;
?>


      <h4 class="text-center mb-4">Fields</h4>
        <ul id="sortable" class="list-group">

        <li class="list-group-item mb-1"  id="Signature"><i class="fa-solid fa-signature"></i> Signature</li>
          <li class="list-group-item mb-1"  id="GeneratedSignature"><i class="fa-solid fa-hammer"></i> Generated Signature</li>
          <li class="list-group-item mb-1"  id="DateSigned"><i class="fa-solid fa-calendar-days"></i> Date Signed</li>
          <li class="list-group-item mb-1"  id="FullName"><i class="fa-solid fa-address-card"></i> Full Name</li>
          <!-- <li class="list-group-item mb-1"  id="Email"><i class="fa-solid fa-envelope"></i> Email</li> -->
          <li class="list-group-item mb-1"  id="Company"><i class="fa-solid fa-building"></i> Company</li>
          <!-- <li class="list-group-item mb-1"  id="IPAddress"><i class="fa-solid fa-location-dot"></i> IP Address</li> -->
          
          
          <!-- Add more list items as needed -->
        </ul>

      </div>
        

          <!-- pdf view -->
      <div class="col-8 border overflow-auto bg-light p-3" style="height: 450px;">


        <div class="pdf-container" id="pdf-container"></div>


      </div>


      <!-- files viewer -->

      
      <div id="PDFlIST" class="col-2 border overflow-auto bg-light p-3 text-start" style="height: 450px;">
    <h4 class="text-center mb-4">PDF List</h4>

    <ul id="sortable1" class="list-group">
        <?php foreach ($pdfPathsArray as $index => $pdfPath): ?>
            <?php $itemId = 'pdfItem_' . $index; ?>
            <?php $isActive = $index === 0 ? 'active' : ''; ?>
            <li id="<?= $itemId ?>" class="list-group-item mb-1  <?= $isActive ?>" data-pdf-url="<?= 'backend/'.$pdfPath ?>" onclick="setPdfUrl(this)">
                <?= substr(rawurldecode(basename(rawurldecode($pdfPath))), 11) ?>
                
               
                
                
            </li>
        <?php endforeach; ?>
    </ul>

    <script>
        function setPdfUrl(clickedItem) {
            // Remove 'active' class from all list items
            const allItems = document.querySelectorAll('#sortable1 li');
            allItems.forEach(item => item.classList.remove('active'));
            
            // Add 'active' class to the clicked item
            clickedItem.classList.add('active');
            
            // Retrieve the PDF URL from data-pdf-url attribute
            const pdfUrl = clickedItem.getAttribute('data-pdf-url');
            console.log('Selected PDF URL:', pdfUrl);

            countPDF = allItems.length;
            loadpdf(pdfUrl,countPDF);

            // Do anything else you need with the selected PDF URL
        }

        // Activate the first item by default
        window.onload = function() {
            const firstListItem = document.querySelector('#sortable1 li:first-child');
            setPdfUrl(firstListItem);
        };

    </script>
</div>


<!-- end -->





</div>


  




  </div>

  
</div>




<!-- footer -->
<div class="container-fluid border fixed-bottom bg-white">
    <div class="container">
        <div class="row">

        <!-- <img
        src="images/Aehr-Test-Systems-Logo.png"
        alt="Aehr Test Systems Logo"
        class=""
        style="max-width: 200px;"

        
      > -->



<!--       
            <div class="col text-end h3 pt-3 pb-2">
            <button type="submit" id="send" class="btn" onClick="Preview();">Preview</button>
            </div> -->

            <div class="col text-end h3 pt-3 pb-2">
            
                <button type="submit" id="send" class="btn" onClick="modifyPDF();"><i class="fa-solid fa-check"></i> Approve</button>
            </div>





        </div>
    </div>  
</div>


</div>

<script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.js"></script>

<script src="https://unpkg.com/downloadjs@1.4.7/download.js" type="text/javascript"></script>

    <!-- SweetAlert JS -->
    <script src="js/sweetAlert/sweetalert2.all.min.js"></script>

    <!-- jQuery JS -->
    <script type="text/javascript" src="js/jQuery/jquery-3.3.1.slim.min.js"></script>

    <meta http-equiv="Content-Security-Policy" content="script-src 'self' https://mozilla.github.io/;">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-sha256/0.9.0/sha256.min.js"></script>


    <script>





var requestJsonModify = {
  "data": [
    
  ]
};

    // {
    //   "modifiedPdfBase64": 42,
    //   "pdfUrlKO": 612
    // },{
    //   "modifiedPdfBase64": 42,
    //   "pdfUrlKO": 612
    // }



async function doRequest(requestJsonModify,id){
    
    var myAlert = Swal.fire({
        icon: 'info',
        title: 'Getting Ready!',
        text: 'Document is getting ready, please wait.',
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading(); // Show loading spinner
        }
    }); 

    
    
 
    var formData = new FormData();

    var jsonData = JSON.stringify(requestJsonModify);

    formData.append('jsonData', jsonData);
    formData.append('id', id);

    console.log(formData); // This will not display the JSON data in the console


    $.ajax({
        type: "POST",
        url: "backend/sign.php",
        data: formData, // Use the FormData object here
        processData: false, // Prevent jQuery from automatically processing the FormData
        contentType: false, // Prevent jQuery from automatically setting the content type
        success: function(response) {
            console.log("Success: ", response);
            // Handle success response here
            
            
            Swal.fire({
                icon: 'success',
                title: 'Document Approved!',
                text: 'Document Approved successfully.',
                timer: 3000, // Automatically close after 3 seconds
                timerProgressBar: true,
                showConfirmButton: true
            }).then((result) => {
                // Redirect to view-pdf.php?id=528 after the alert is closed
                window.location.href = 'view-pdf.php?id='+document.getElementById("id").value;;
            });

        },
        error: function(xhr, status, error) {
            console.error("Error:", error);
            // Handle error response here
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Something went wrong while sending the document: ' + error
            });
        }
    });


}





var existingJson = {
        "marks": [
        
        ]
        };

        // PDF.js setup

        var text = null;
        var ipaddress = null;

        // Variables to store click coordinates and page number
        let clickX, clickY, pageNumber;
        
        
        

function loadpdf(pdfUrl,countPDF) {

    const pdfContainer = document.getElementById('pdf-container');
    pdfContainer.innerHTML = ''; // Clear previous PDF content

    // Fetch the PDF document
    pdfjsLib.getDocument(pdfUrl).promise.then(pdfDoc => {
        // Define DPI (adjust as needed)
        const DPI = 72;

        // Iterate through all pages
        for (let currentPage = 1; currentPage <= pdfDoc.numPages; currentPage++) {
            // Create a canvas element for each page
            
            const canvas = document.createElement('canvas');
            canvas.id = `pdf-canvas-${currentPage}`;
            canvas.classList.add('position-relative'); // Add a common class for styling if needed
            canvas.classList.add('mb-3');
            pdfContainer.appendChild(canvas);

            // Get the current page
            pdfDoc.getPage(currentPage).then(page => {
                // Set the desired DPI for the PDF
                const scale = DPI / 72; // Assuming 1 inch = 72 points in PDF
                
                var page_rot = page.rotate;

                // Set the canvas size to match the page size in PDF units
                const viewport = page.getViewport({ scale: scale });
                const pdfWidth = viewport.width;
                const pdfHeight = viewport.height;
                canvas.width = pdfWidth;
                canvas.height = pdfHeight;

                // Render the page on the canvas
                const context = canvas.getContext('2d');
                page.render({ canvasContext: context, viewport: viewport });

                // Add click event listener to the canvas
                canvas.addEventListener('click', (event) => {
                    // Get click coordinates in canvas units
                    const clickX = event.clientX - canvas.getBoundingClientRect().left;
                    const clickY = event.clientY - canvas.getBoundingClientRect().top;

                    // Calculate equivalent PDF coordinates
                    const pdfX = (clickX / canvas.width) * pdfWidth;
                    const pdfY = (clickY / canvas.height) * pdfHeight;

                    // Store the current page number
                    const pageNumber = currentPage;

                    if (text.includes('users_folder')) {
                        imageWidth = 90;
                        imageHeight =  25;
                        startX = clickX - imageWidth / 2;
                        startY = clickY - imageHeight / 2;
                        const img = new Image();
                        img.onload = function() {
                            context.drawImage(img, startX, startY, imageWidth, imageHeight);
                        };
                        img.src = window.location.origin + '/' + text;
                    } else {


                        
                            const textWidth = context.measureText(text).width;
                            const textHeight = 12; // Adjust as needed
                            var startX = clickX - textWidth / 2;
                            var startY = clickY + textHeight / 2;
                            context.fillStyle = 'black';
                            context.font = '12px Helvetica';
                            context.fillText(text, startX, startY);
                        


                    }

                    existingJson.marks.push({
                        "x": clickX,
                        "y": clickY,
                        "w": pdfWidth,
                        "h": pdfHeight,
                        "page": pageNumber,
                        "text": text,
                        "pdfUrl" : pdfUrl,
                        "countPDF": countPDF,
                        "page_rot": page_rot
                    });
                    
    
    
                    modifyPDFandsavetoJson(pdfUrl);
                    console.log(pdfUrl);

                    
                });
            });
        }
    });
}

      

async function modifyPDF() {
    var idValue = document.getElementById("id").value;

    doRequest(requestJsonModify,idValue);
    console.log(requestJsonModify);


}



var tempbase64 = null;
var previousPdfUrlKO = null;



async function modifyPDFandsavetoJson(pdfUrlKO) {
    
    
    const { PDFDocument , degrees} = PDFLib;

    var existingPdfBytes;

    // Check if pdfUrlKO has changed from the previous call
    if (pdfUrlKO !== previousPdfUrlKO) {
        // If pdfUrlKO has changed, reset tempbase64 to null
        tempbase64 = null;
        // Update previousPdfUrlKO to the new value
        previousPdfUrlKO = pdfUrlKO;
    }

    // Load the PDF bytes
    existingPdfBytes = await fetch(pdfUrlKO).then(res => res.arrayBuffer());

    // If tempbase64 is not null, use it as the PDF bytes
    if (tempbase64 != null) {
        existingPdfBytes = tempbase64;
    }

    const pdfDoc = await PDFDocument.load(existingPdfBytes);
    const pages = pdfDoc.getPages();

    for (const mark of existingJson.marks) {
        const { x, y, w, h, text, page, page_rot} = mark;

        if (page <= pages.length) {
            const currentPage = pages[page - 1];
            const pdfCoordinates = webpageToPdfCoordinates(x, y, w, h, page_rot);

            if (mark.text.includes("users_folder")) {
                const imageurl = window.location.origin + '/' + mark.text;
                const pngImageBytes = await fetch(imageurl).then((res) => res.arrayBuffer());
                const pngImage = await pdfDoc.embedPng(pngImageBytes);
                const pngDims = pngImage.scale(0.5);

                const imageWidth = 90;
                const imageHeight = 25;
                const startX = pdfCoordinates.x - imageWidth / 2;
                const startY = pdfCoordinates.y - imageHeight / 2;

                currentPage.drawImage(pngImage, {
                    x: startX,
                    y: startY,
                    width: imageWidth,
                    height: imageHeight,
                    rotate: degrees(page_rot),
                });
            } else {

                    const fontSize = 12;
                    const font = await pdfDoc.embedFont(PDFLib.StandardFonts.Helvetica);
                    const textWidth = font.widthOfTextAtSize(text, fontSize);
                    const textHeight = font.heightAtSize(fontSize);
                    const x = pdfCoordinates.x - textWidth / 2;
                    const y = pdfCoordinates.y - textHeight / 2;

                    currentPage.drawText(text, {
                        x,
                        y,
                        size: fontSize,
                        color: PDFLib.rgb(0, 0, 0), // Black color
                        rotate: degrees(page_rot),
                    });
                


            }
        } else {
            console.error(`Page ${page} does not exist in the PDF.`);
        }
    }

    // Save the modified PDF as base64
    const modifiedPdfBase64 = await pdfDoc.saveAsBase64();

    // Store the modified base64 as tempbase64
    tempbase64 = modifiedPdfBase64;

    console.log(tempbase64);

    // Push the modified PDF base64 and PDF URL into requestJsonModify
    requestJsonModify.data.push({
        "modifiedPdfBase64": modifiedPdfBase64,
        "pdfUrlKO": pdfUrlKO
    });

    console.log('requestJsonModify push');

    // Remove the first mark from existingJson
    existingJson.marks.shift();
    console.log('existingJson remove');

    return modifiedPdfBase64;
}









function webpageToPdfCoordinates(webpageX, webpageY, pdfWidth, pdfHeight, pageRot) {
    let pdfX, pdfY;

    switch (pageRot) {
        case 90:
            pdfX = webpageY; // Swap X and Y
            pdfY = pdfWidth - webpageX; // Reverse Y
            break;
        case 180:
            pdfX = pdfWidth - webpageX; // Reverse both X and Y
            pdfY = pdfHeight - webpageY;
            break;
        case 270:
            pdfX = pdfHeight - webpageY; // Reverse X
            pdfY = webpageX; // Swap X and Y
            break;
        default: // No rotation
            pdfX = webpageX; // No change
            pdfY = pdfHeight - webpageY; // Reverse Y
            break;
    }

    return { x: pdfX, y: pdfY };
}




    var fullName = null;


    const listItems = document.querySelectorAll('#sortable .list-group-item');

    //select signature
    listItems[0].classList.add('active');

    text = 'Signature';
    const signature = document.getElementById('signature').value;
    text = signature;



    function generateSignature() {
        fullName = document.getElementById('fullname').value;
        var signature = sha256(fullName).substring(0, 6);
        var date = new Date();
        var text = signature;
        return text;
    }




    listItems.forEach(item => {
    item.addEventListener('click', function() {
        // Remove 'active' class from all list items
        listItems.forEach(otherItem => {
        otherItem.classList.remove('active');
        });

        // Add 'active' class to the clicked item
        item.classList.add('active');


        if (item === listItems[0]) {
            text = 'Signature';
            const signature = document.getElementById('signature').value;
            text = signature;

        } else if (item === listItems[1]) {
            text = 'Date Signed';
            var date = new Date();
            text = generateSignature();

        }else if (item === listItems[2]) {
            text = 'Date Signed';
            var date = new Date();
            text = date.toLocaleDateString('en-PH', { timeZone: 'Asia/Manila' });
        } else if (item === listItems[3]) {
            text = 'Full Name';
            const fullname = document.getElementById('fullname').value;
            text = fullname;
        // } else if (item === listItems[4]) {
        //     //text = 'Email';
        //     const email = document.getElementById('email').value;
        //     text = email;
        // 
        } 
        else if (item === listItems[4]) {
            text = 'Aehr Test Systems';
        } 
        // else if (item === listItems[6]) {
        //     text = 'IP Address';
        //     text = ipaddress;
        // }
        console.log(text);

    });
    });


    getUserIP();

    async function getUserIP() {
        try {
            const response = await fetch('https://api.ipify.org?format=json');
            const data = await response.json();

            if (data && data.ip) {
                const userIP = data.ip;
                ipaddress = userIP;
                return userIP;
            } else {
      
                return null;
            }
        } catch (error) {
            console.error('Error fetching user IP:', error.message);
            return null;
        }
    }




</script>


</body>

</html>

