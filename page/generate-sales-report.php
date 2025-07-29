<?php
require_once "../includes/base.php";
require_once "../includes/config.php";
require_once "../fpdf/fpdf.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../page/login.php");
    exit;
}

// Use current or selected month/year
$month = req("month", date("m"));
$year = req("year", date("Y"));
$monthName = date("F", mktime(0, 0, 0, $month, 10));

// Prepare data
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$all_days = [];
for ($d = 1; $d <= $days_in_month; $d++) {
    $all_days[] = sprintf("%04d-%02d-%02d", $year, $month, $d);
}

// Fetch daily sales
$stmt = $pdo->prepare("
    SELECT DATE(CREATED_AT) AS day, SUM(TOTAL_AMOUNT) AS total
    FROM orders
    WHERE MONTH(CREATED_AT) = :month AND YEAR(CREATED_AT) = :year AND PAYMENT_STATUS = 'Paid'
    GROUP BY day
");
$stmt->execute(["month" => $month, "year" => $year]);

$sales = [];
foreach ($stmt->fetchAll() as $row) {
    $sales[$row["day"]] = (float) $row["total"];
}

// Generate PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, "Sales Report - $monthName $year", 0, 1, 'C');
$pdf->Ln(10);

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Date', 1);
$pdf->Cell(60, 10, 'Sales (RM)', 1);
$pdf->Cell(60, 10, 'Accumulated (RM)', 1);
$pdf->Ln();

// Table Data
$pdf->SetFont('Arial', '', 12);
$total = 0;
foreach ($all_days as $day) {
    $day_total = $sales[$day] ?? 0;
    $total += $day_total;

    $pdf->Cell(40, 10, $day, 1);
    $pdf->Cell(60, 10, number_format($day_total, 2), 1);
    $pdf->Cell(60, 10, number_format($total, 2), 1);
    $pdf->Ln();
}

// Output
$filename = "sales-report-$year-$month.pdf";
$pdf->Output("I", $filename);
exit;
?>
