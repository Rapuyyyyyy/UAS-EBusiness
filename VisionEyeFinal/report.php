<?php
@include 'config.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('location:login.php');
    exit;
}

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$where_clause = '';
$params = [];

if ($start_date && $end_date) {
    $where_clause = "WHERE STR_TO_DATE(placed_on, '%d-%b-%Y') BETWEEN STR_TO_DATE(?, '%Y-%m-%d') AND STR_TO_DATE(?, '%Y-%m-%d')";
    $params[] = $start_date;
    $params[] = $end_date;
}

$query = "SELECT * FROM orders $where_clause ORDER BY placed_on DESC";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Laporan Penjualan</title>
   <link rel="stylesheet" href="css/admin_style.css">
   <style>
      table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 20px;
         background: #fff;
         box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
         border-radius: 6px;
         overflow: hidden;
      }
      th, td {
         padding: 12px 16px;
         text-align: left;
         border-bottom: 1px solid #eee;
      }
      th {
         background-color: #2ecc71;
         color: white;
         font-weight: 600;
      }
      tr:hover td {
         background-color: #f1f1f1;
      }
      .btn {
         margin-right: 10px;
      }
   </style>
</head>
<body>

<?php include 'admin_header.php'; ?>

<section class="orders">
    <h1 class="title">Sales Report</h1>

    <form method="GET" style="margin-bottom: 1rem; text-align:center">
        <label>Date:</label>
        <input type="date" name="start_date" value="<?= $start_date ?>">
        <input type="date" name="end_date" value="<?= $end_date ?>">
        <button type="submit" class="btn">View</button>
        <a href="export_excel.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" class="btn">Ekspor Excel</a>
    </form>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Product</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $i => $order): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= $order['placed_on'] ?></td>
                            <td><?= htmlspecialchars($order['name']) ?></td>
                            <td><?= htmlspecialchars($order['email']) ?></td>
                            <td><?= htmlspecialchars($order['total_products']) ?></td>
                            <td><?= formatRupiah($order['total_price']) ?></td>
                            <td><?= htmlspecialchars($order['payment_status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">Tidak ada data transaksi</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script src="js/script.js"></script>
</body>
</html>
