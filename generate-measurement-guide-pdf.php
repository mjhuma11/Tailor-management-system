<?php
/**
 * Generate Measurement Guide PDF
 * Convert HTML guide to PDF format
 */

// This script can be used to generate a PDF from the HTML measurement guide
// You can use libraries like TCPDF, mPDF, or Puppeteer for this

echo "<h2>Measurement Guide PDF Generator</h2>";

$htmlFile = 'assets/guides/measurement-guide-content.html';
$pdfFile = 'assets/guides/measurement-guide.pdf';

if (file_exists($htmlFile)) {
    echo "<p>✓ HTML guide found: $htmlFile</p>";
    
    // Option 1: Using wkhtmltopdf (if installed on server)
    if (shell_exec('which wkhtmltopdf')) {
        $command = "wkhtmltopdf --page-size A4 --margin-top 0.75in --margin-right 0.75in --margin-bottom 0.75in --margin-left 0.75in $htmlFile $pdfFile";
        $output = shell_exec($command);
        
        if (file_exists($pdfFile)) {
            echo "<p>✓ PDF generated successfully: $pdfFile</p>";
            echo "<p><a href='$pdfFile' target='_blank'>View PDF</a></p>";
        } else {
            echo "<p>✗ PDF generation failed</p>";
        }
    } else {
        echo "<p>⚠ wkhtmltopdf not installed. Alternative methods:</p>";
        echo "<ul>";
        echo "<li><strong>Online Converter:</strong> Use online HTML to PDF converters</li>";
        echo "<li><strong>Browser Print:</strong> Open the HTML file and print to PDF</li>";
        echo "<li><strong>PHP Library:</strong> Install mPDF or TCPDF library</li>";
        echo "<li><strong>Node.js:</strong> Use Puppeteer for PDF generation</li>";
        echo "</ul>";
        
        echo "<h3>Manual PDF Generation Steps:</h3>";
        echo "<ol>";
        echo "<li>Open <a href='$htmlFile' target='_blank'>$htmlFile</a> in your browser</li>";
        echo "<li>Press Ctrl+P (or Cmd+P on Mac) to print</li>";
        echo "<li>Select 'Save as PDF' as the destination</li>";
        echo "<li>Save the file as 'measurement-guide.pdf' in the assets/guides/ folder</li>";
        echo "</ol>";
    }
    
    // Option 2: Using mPDF (if library is available)
    if (class_exists('Mpdf\Mpdf')) {
        try {
            require_once 'vendor/autoload.php';
            
            $mpdf = new \Mpdf\Mpdf([
                'format' => 'A4',
                'margin_left' => 20,
                'margin_right' => 20,
                'margin_top' => 20,
                'margin_bottom' => 20
            ]);
            
            $html = file_get_contents($htmlFile);
            $mpdf->WriteHTML($html);
            $mpdf->Output($pdfFile, 'F');
            
            echo "<p>✓ PDF generated using mPDF: $pdfFile</p>";
        } catch (Exception $e) {
            echo "<p>✗ mPDF error: " . $e->getMessage() . "</p>";
        }
    }
    
} else {
    echo "<p>✗ HTML guide not found: $htmlFile</p>";
}

echo "<h3>PDF Generation Options:</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<h4>1. wkhtmltopdf (Recommended)</h4>";
echo "<pre>sudo apt-get install wkhtmltopdf  # Ubuntu/Debian
brew install wkhtmltopdf           # macOS</pre>";

echo "<h4>2. mPDF Library</h4>";
echo "<pre>composer require mpdf/mpdf</pre>";

echo "<h4>3. Browser Method (Manual)</h4>";
echo "<p>Open the HTML file in browser and print to PDF</p>";

echo "<h4>4. Online Converters</h4>";
echo "<ul>";
echo "<li><a href='https://www.html-to-pdf.net/' target='_blank'>HTML to PDF Online</a></li>";
echo "<li><a href='https://pdfcrowd.com/html-to-pdf-api/' target='_blank'>PDFCrowd</a></li>";
echo "</ul>";
echo "</div>";

echo "<h3>Current Status:</h3>";
if (file_exists($pdfFile)) {
    echo "<p style='color: green;'>✓ PDF file exists: <a href='$pdfFile' target='_blank'>$pdfFile</a></p>";
    echo "<p>File size: " . number_format(filesize($pdfFile) / 1024, 1) . " KB</p>";
    echo "<p>Last modified: " . date('Y-m-d H:i:s', filemtime($pdfFile)) . "</p>";
} else {
    echo "<p style='color: orange;'>⚠ PDF file not found. Please generate it using one of the methods above.</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
pre { background: #f1f1f1; padding: 10px; border-radius: 5px; overflow-x: auto; }
h2, h3, h4 { color: #333; }
</style>