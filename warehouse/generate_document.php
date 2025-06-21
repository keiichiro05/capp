<?php
include('../konekdb.php');
session_start();

if(!isset($_SESSION['username'])) {
    header("location:../index.php?status=please login first");
    exit();
}

if (isset($_GET['doc'])) {
    $doc_number = mysqli_real_escape_string($mysqli, $_GET['doc']);
    $doc_query = mysqli_query($mysqli, "SELECT * FROM request_documents WHERE doc_number='$doc_number'");
    $document = mysqli_fetch_assoc($doc_query);
    
    if ($document) {
        // Include TCPDF library
        require_once('../tcpdf/tcpdf.php');
        
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Warehouse System');
        $pdf->SetTitle('Purchase Request ' . $doc_number);
        $pdf->SetSubject('Purchase Request');
        $pdf->SetKeywords('Purchase, Request, Document');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        
        // Add a page
        $pdf->AddPage();
        
        // Set some content
        $html = $document['content'];
        
        // Output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Close and output PDF document
        $pdf->Output('Purchase_Request_' . $doc_number . '.pdf', 'I');
    } else {
        die('Document not found');
    }
} else {
    die('Invalid request');
}
?>