

var counter = 0;
addRecipient();

function addRecipient() {
  
  
  counter++;
  //console.log(counter);

  var newRecipientCard = $("<div>").addClass("row mb-2 border-bottom pt-2");

  var col0 = $('<div>').addClass("col-md-1 mb-2 counter");
  col0.append('<input name="order" readonly value="'+counter+'" placeholder="Order" type="text" class="form-control"  autocomplete="off">');
  newRecipientCard.append(col0);


  var col1 = $('<div>').addClass("col-md-4 mb-2 counter");
col1.append('<input required placeholder="Email Address" type="email" class="form-control" id="email' + counter + '" name="email" autocomplete="email" oninput="showEmails(' + counter + ')">');
col1.append('<div id="suggestionsContainer' + counter + '"></div>'); // Append a div for suggestions below each input field



  newRecipientCard.append(col1);

  var col2 = $('<div>').addClass("col-md-2 mb-2 counter");
  var select = $('<select>').addClass("form-select").attr("aria-label", "Default select example").attr("name", "action");
  select.append('<option value="0" selected><i class="fa-solid fa-signature"></i> Need to Sign</option>');
  select.append('<option value="1"><i class="fa-solid fa-copy"></i> Receive Copy</option>');
  select.append('<option value="2"><i class="fa-solid fa-clone"></i> Receive Final Copy</option>');
  col2.append(select);
  newRecipientCard.append(col2);

  var col3 = $('<div>').addClass("col-md-2 mb-2 counter");
  col3.append('<button type="button" class="btn btn-danger" onClick="removeRecipient(this)"><i class="fa-solid fa-xmark"></i> Remove</button>');
  newRecipientCard.append(col3);

  $("#recCard").append(newRecipientCard);

  fixNumber();
}




function fixNumber(){

  var countOfInputs = document.querySelectorAll('.counter input[name^="order"]');
  console.log('You have input count of: ' + countOfInputs.length);

  // Update the values of "Order" inputs based on their index
  countOfInputs.forEach(function (input, index) {
    input.value = index + 1;
  });





}



function removeRecipient(button) {
  $(button).closest(".row").remove();
  fixNumber();

  
}



const fileInput = document.getElementById('inputFile');


fileInput.addEventListener('change', function() {
  const files = this.files;

  // Remove any existing <p> elements (optional):
  const existingFileLabels = document.querySelectorAll('.file-label');
  for (let i = 0; i < existingFileLabels.length; i++) {
    existingFileLabels[i].remove();
  }

  // Create and append <p> elements for each file:

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    const fileLabel = document.createElement('p');
    fileLabel.classList.add('file-label'); // Add a class for styling, if needed
    fileLabel.style.margin = '0';
    fileLabel.textContent = file.name;

    // Optionally, you can insert the <p> elements before the file input:
    // fileInput.parentNode.insertBefore(fileLabel, fileInput.nextSibling);

    // Append the <p> elements below the file input:
    fileInput.parentNode.appendChild(fileLabel);
  }
});




function Send(){
  Swal.fire({
    title: "Sent Successfully!",
    text: "Document successfully sent!",
    icon: "success"
  });
}



class myPDfProcess {
  constructor(pdfUrl) {
    this.pdfUrl = pdfUrl;
    this.pdfDoc = null;
    this.pageCount = null;
    this.pageSize = null;
    this.pageWidth = null;
    this.pageHeight = null;
  }

  async fetchPdfBytes() {
    const response = await fetch(this.pdfUrl);
    return await response.arrayBuffer();
  }

  async loadPdfDocument() {
    const pdfBytes = await this.fetchPdfBytes();
    this.pdfDoc = await PDFLib.PDFDocument.load(pdfBytes);
  }

  async getPageSize(pageNumber) {
    if (!this.pdfDoc) {
      await this.loadPdfDocument();
    }

    if (pageNumber <= this.pdfDoc.getPageCount() && pageNumber > 0) {
      const page = this.pdfDoc.getPage(pageNumber - 1); // Page numbers are 0-indexed
      this.pageSize = page.getSize();
    } else {
      console.error('Invalid page number');
    }
  }

  async getPageCount() {
    if (!this.pdfDoc) {
      await this.loadPdfDocument();
    }
    this.pageCount = this.pdfDoc.getPageCount();
  }


  async getPageWidth() {
    if (!this.pdfDoc) {
      await this.loadPdfDocument();
    }
    this.pageWidth = this.pdfDoc.getWidth();
  }

  async getPageHeight() {
    if (!this.pdfDoc) {
      await this.loadPdfDocument();
    }
    this.pageHeight = this.pdfDoc.getHeight();
  }

  displayInfo() {
      console.log(`PDF URL: ${this.pdfUrl}`);
      console.log(`Page Count: ${this.pageCount}`);
      console.log(`Page Size: Width - ${this.pageSize.width}, Height - ${this.pageSize.height}`);
  }

  setImageSize() {
      const imagePages = document.querySelectorAll(".imageContainer");
      for (let i = 0; i < imagePages.length; i++) {
          // Ensure width and height are accessible within this method
          const width = this.pageSize.width;
          const height = this.pageSize.height;

          imagePages[i].style.position = 'relative';
          imagePages[i].style.width = `${width}px`;
          imagePages[i].style.height = `${height}px`;
          imagePages[i].id = 'imageContainer'+i;

          console.log(`${i + 1} imageContainer size set: Width - ${width}px, Height - ${height}px`);

          // add event click

          imagePages[i].addEventListener('click', function (event) {
              const rect = imagePages[i].getBoundingClientRect();
              const x = event.clientX - rect.left;
              const y = event.clientY - rect.top;
          
              console.log(`Page ${i} coordinates: (${x.toFixed(2)}, ${y.toFixed(2)})`);
          
              const newText = document.createElement('div');
              newText.textContent = text;
              newText.style.position = 'absolute';
              newText.style.color = 'orange';
              newText.style.padding = '2px';
              
              newText.style.borderRadius = '4px'; // Set border-radius for rounded corners
              newText.style.fontFamily = 'Segoe UI'; // Set font-family
              newText.style.fontSize = '15px'; // Adjust the font size as needed
              newText.style.border = '2px solid orange'; // Set border properties
          
              // Append the text element temporarily to the document to get its dimensions
              document.body.appendChild(newText);
              
              const newTextWidth = newText.offsetWidth;
              const newTextHeight = newText.offsetHeight;
          
              // Remove the temporary element from the document
              document.body.removeChild(newText);
          
              // Calculate the center position based on the text dimensions
              const centerX = x - newTextWidth / 2;
              const centerY = y - newTextHeight / 2;
          
              newText.style.left = `${centerX}px`;
              newText.style.top = `${centerY}px`;
          
              // Append the text element as a child of the imageContainer
              imagePages[i].appendChild(newText);
          
              console.log('added');
          });
          
          

      }
  }
  
}