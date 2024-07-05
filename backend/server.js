import express from 'express';
import bodyParser from 'body-parser';
import cors from 'cors';
import { PDFDocument } from 'pdf-lib';
import fetch from 'node-fetch';
import fs from 'fs';


const app = express();
const port = 3000;
const localhost = 'http://localhost';

app.use(cors()); // Enable CORS
app.use(bodyParser.json()); // Ensure bodyParser is applied before route definition

app.post('/server', async (req, res) => {
  try {
    // Verify the structure of the request body
    const data = req.body;
    console.log('Received POST request with data:', data);

    // Access the URL property within the marks array
    const pdfUrl = `${localhost}:${port}/${data.marks[0].url}`;

    console.log('PDF URL:', pdfUrl);

    await modifyPDFAndSave(
      pdfUrl,
      data.marks[0].x,
      data.marks[0].y,
      data.marks[0].w,
      data.marks[0].h,
      data.marks[0].text,
      data.marks[0].page
    );

   

    console.log('PDF modified successfully!');
    res.status(200).json({ message: 'POST request received successfully' });
  } catch (error) {
    console.error('Error modifying PDF:', error);
    res.status(500).json({ error: 'Internal Server Error' });
  }
});

app.listen(port, () => {
  console.log(`Server listening at http://localhost:${port}`);
});

async function modifyPDFAndSave(pdfUrl, x, y, width, height, text, whatpage, outputFileName) {
  try {
    // Fetch the existing PDF
    const existingPdfBytes = await fetch(pdfUrl).then((res) => res.arrayBuffer());
    const pdfDoc = await PDFDocument.load(existingPdfBytes);

    // Modify the PDF
    const pages = pdfDoc.getPages();
    const page = pages[whatpage - 1];

    const pdfCoordinates = webpageToPdfCoordinates(x, y, height);
    page.drawText(text, {
      x: pdfCoordinates.x,
      y: pdfCoordinates.y,
      size: 12,
      color: PDFDocument.rgb(254 / 255, 90 / 255, 29 / 255),
    });

    // Save the modified PDF
    const modifiedPdfBytes = await pdfDoc.save();

    // Use fs to write the modified PDF to a file
    fs.writeFileSync(outputFileName, Buffer.from(modifiedPdfBytes));

    console.log(`PDF modified and saved to ${outputFileName} successfully!`);
  } catch (error) {
    console.error('Error modifying and saving PDF:', error);
    throw error; // Propagate the error for further handling if needed
  }
}


function webpageToPdfCoordinates(webpageX, webpageY, pdfHeight) {
  const pdfX = webpageX;
  const pdfY = pdfHeight - webpageY;

  return { x: pdfX, y: pdfY };
}
