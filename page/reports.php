<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("Location: ../page/login.php");
    exit;
}

// Month and year from query or default to current
$month = req("month", date("m"));
$year = req("year", date("Y"));

// Get all days of the selected month
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$all_days = [];
for ($d = 1; $d <= $days_in_month; $d++) {
    $all_days[] = sprintf("%04d-%02d-%02d", $year, $month, $d);
}

// Query orders for the selected month and year
$stmt = $pdo->prepare("
    SELECT DATE(CREATED_AT) AS order_day, SUM(TOTAL_AMOUNT) AS daily_total
    FROM orders
    WHERE MONTH(CREATED_AT) = :month AND YEAR(CREATED_AT) = :year AND PAYMENT_STATUS = 'Paid'
    GROUP BY order_day
");
$stmt->execute([
    "month" => $month,
    "year" => $year
]);

// Prepare sales data
$sales = [];
foreach ($stmt->fetchAll() as $row) {
    $sales[$row["order_day"]] = (float) $row["daily_total"];
}

// Match full month dates with sales or 0
$dates = $totals = [];
foreach ($all_days as $day) {
    $dates[] = $day;
    $totals[] = $sales[$day] ?? 0;
}

// Generate accumulated (cumulative) totals
$accumulated_totals = [];
$running_total = 0;
foreach ($totals as $amount) {
    $running_total += $amount;
    $accumulated_totals[] = $running_total;
}

$json_accumulated = json_encode($accumulated_totals);

// Encode for Chart.js
$json_dates = json_encode($dates);
$json_totals = json_encode($totals);

// Fetch low stock products
$low_stock_stmt = $pdo->query("
    SELECT PRODUCT_NAME, PRODUCT_QUANTITY, PRODUCT_STOCK_ALERT
    FROM products
    WHERE PRODUCT_STOCK_ALERT IS NOT NULL
      AND PRODUCT_STOCK_ALERT > 0
      AND PRODUCT_QUANTITY <= PRODUCT_STOCK_ALERT
");

$low_stock_products = $low_stock_stmt->fetchAll();

include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sales Reports | Admin Panel</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            width: 100%;
            max-width: 900px;
            margin: auto;
            margin-top: 40px;
        }
        .month-selector {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
    </style>
</head>
<body class="bg-light">
<?php include "../includes/sidebar.php"; ?>

<div class="container">
    <h1>Sales Reports</h1>

    <form method="get" class="month-selector">
        <select name="month" class="input-field">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= $m == $month ? "selected" : "" ?>>
                    <?= date("F", mktime(0, 0, 0, $m, 10)) ?>
                </option>
            <?php endfor; ?>
        </select>

        <select name="year" class="input-field">
            <?php for ($y = date("Y"); $y >= 2022; $y--): ?>
                <option value="<?= $y ?>" <?= $y == $year ? "selected" : "" ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>

        <button class="btn btn-blue" type="submit">View</button>
    </form>
</div>

<div class="container chart-container">
    <canvas id="salesChart"></canvas>
</div>

<script>
const labels = <?= $json_dates ?>;
const data = <?= $json_totals ?>;
const accumulated = <?= $json_accumulated ?>;

const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Daily Sales (RM)',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                yAxisID: 'y',
                type: 'bar'
            },
            {
                label: 'Accumulated Sales (RM)',
                data: accumulated,
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                fill: false,
                tension: 0.1,
                yAxisID: 'y',
                type: 'line'
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'RM'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Date'
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'RM ' + context.formattedValue;
                    }
                }
            }
        }
    }
});

</script>
<div class="container">
    <div class="login-options">
<a href="generate-sales-report.php?month=<?= $month ?>&year=<?= $year ?>" class="btn btn-green" target="_blank">Download PDF Report</a>
</div>
</div>

<?php if (!empty($low_stock_products)): ?>
<div class="container" style="margin-top: 50px;">
    <h2>⚠️ Low Stock Alerts</h2>
    <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
        <thead>
            <tr style="background-color: #f9f9f9;">
                <th style="border: 1px solid #ccc; padding: 10px;">Product Name</th>
                <th style="border: 1px solid #ccc; padding: 10px;">Current Quantity</th>
                <th style="border: 1px solid #ccc; padding: 10px;">Alert Threshold</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($low_stock_products as $prod): ?>
            <tr>
                <td style="border: 1px solid #ccc; padding: 10px;"><?= htmlspecialchars($prod["PRODUCT_NAME"]) ?></td>
                <td style="border: 1px solid #ccc; padding: 10px;"><?= $prod["PRODUCT_QUANTITY"] ?></td>
                <td style="border: 1px solid #ccc; padding: 10px;"><?= $prod["PRODUCT_STOCK_ALERT"] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<div class="login-options">
<a href="generate-sales-report.php?month=<?= $month ?>&year=<?= $year ?>" class="btn btn-green" target="_blank">Download PDF Report</a>
</div>


</body>
</html>
