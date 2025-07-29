<?php
require_once "../includes/config.php";
require_once "../fpdf/fpdf.php";

function generate_receipt_pdf($pdo, $order_id, $uid, $save_path) {
    $stmt = $pdo->prepare("
        SELECT o.*, 
               u.USERNAME, u.EMAIL,
               CONCAT(a.UNIT_NUMBER, ', ', a.STREET, ', ', a.CITY, ', ', s.STATE_NAME, ', ', c.COUNTRY_NAME) AS shipping_address
        FROM orders o
        JOIN users u ON o.UID = u.UID
        JOIN Address a ON o.SHIPPING_ADDRESS_ID = a.ADDID
        JOIN States s ON a.STATE_ID = s.STATE_ID
        JOIN Countries c ON a.COUNTRY_ID = c.COUNTRY_ID
        WHERE o.ORDER_ID = :id AND o.UID = :uid
    ");
    $stmt->execute(["id" => $order_id, "uid" => $uid]);
    $order = $stmt->fetch();

    if (!$order) {
        return false;
    }

    $item_stmt = $pdo->prepare("
        SELECT oi.*, p.PRODUCT_NAME
        FROM order_items oi
        JOIN products p ON oi.PID = p.PID
        WHERE oi.ORDER_ID = :id
    ");
    $item_stmt->execute(["id" => $order_id]);
    $items = $item_stmt->fetchAll();

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, "Order Receipt", 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln(5);

    $pdf->Cell(0, 10, "Order ID: " . $order["ORDER_ID"], 0, 1);
    $pdf->Cell(0, 10, "Customer: " . $order["USERNAME"], 0, 1);
    $pdf->Cell(0, 10, "Email: " . $order["EMAIL"], 0, 1);
    $pdf->Cell(0, 10, "Shipping Address: " . $order["shipping_address"], 0, 1);
    $pdf->Cell(0, 10, "Date: " . date("Y-m-d H:i", strtotime($order["CREATED_AT"])), 0, 1);
    $pdf->Cell(0, 10, "Payment Method: " . $order["PAYMENT_METHOD"], 0, 1);
    $pdf->Cell(0, 10, "Payment Status: " . $order["PAYMENT_STATUS"], 0, 1);
    $pdf->Cell(0, 10, "Order Status: " . $order["STATUS"], 0, 1);

    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(90, 10, "Product", 1);
    $pdf->Cell(30, 10, "Unit Price", 1);
    $pdf->Cell(30, 10, "Qty", 1);
    $pdf->Cell(40, 10, "Subtotal", 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);
    foreach ($items as $item) {
        $pdf->Cell(90, 10, $item["PRODUCT_NAME"], 1);
        $pdf->Cell(30, 10, "RM " . number_format($item["UNIT_PRICE"], 2), 1);
        $pdf->Cell(30, 10, $item["QUANTITY"], 1);
        $pdf->Cell(40, 10, "RM " . number_format($item["SUBTOTAL"], 2), 1);
        $pdf->Ln();
    }

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(150, 10, "Total", 1);
    $pdf->Cell(40, 10, "RM " . number_format($order["TOTAL_AMOUNT"], 2), 1);

    return $pdf->Output('F', $save_path); // Save to file
}
