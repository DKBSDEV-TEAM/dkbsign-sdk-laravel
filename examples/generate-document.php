<?php

declare(strict_types=1);

/**
 * Build a minimal but structurally valid PDF (correct xref offsets).
 */
function build_test_pdf(array $lines): string
{
    $stream = "BT\n/F1 12 Tf\n";
    $y = 750;
    foreach ($lines as $line) {
        $escaped = addcslashes($line, '\\()');
        $stream .= "1 0 0 1 50 {$y} Tm\n({$escaped}) Tj\n";
        $y -= 22;
    }
    $stream .= "ET";
    $streamLength = strlen($stream);

    $parts = [
        1 => '<< /Type /Catalog /Pages 2 0 R >>',
        2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
        4 => "<< /Length {$streamLength} >>\nstream\n{$stream}\nendstream",
        5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
    ];

    $pdf = "%PDF-1.4\n";
    $offsets = [0 => 0];

    for ($i = 1; $i <= 5; $i++) {
        $offsets[$i] = strlen($pdf);
        $pdf .= "{$i} 0 obj\n{$parts[$i]}\nendobj\n";
    }

    $xrefPos = strlen($pdf);
    $pdf .= "xref\n0 6\n0000000000 65535 f \n";
    for ($i = 1; $i <= 5; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";

    return $pdf;
}

$path = __DIR__.'/storage/document.pdf';
$pdf = build_test_pdf([
    'Document test - DKBSign SDK Laravel',
    'Signature self-sign - test local',
]);

file_put_contents($path, $pdf);

echo "Created valid PDF: {$path} (".filesize($path)." bytes)\n";
