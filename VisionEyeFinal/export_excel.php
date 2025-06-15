<?php
@include 'config.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_penjualan.xls");

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$where_clause = '';
$params = [];

if ($start_date && $end_date) {
    $where_clause = "WHERE placed_on BETWEEN ? AND ?";
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

$total_omzet = array_sum(array_column($orders, 'total_price'));
$total_order = count($orders);

// Hitung total produk terjual dari kolom total_products
$total_produk_terjual = 0;
foreach ($orders as $order) {
    $produk_arr = explode(',', $order['total_products']);
    foreach ($produk_arr as $produk) {
        preg_match('/\((\d+)\)/', $produk, $match);
        if (isset($match[1])) {
            $total_produk_terjual += (int)$match[1];
        }
    }
}
?>

<table border="1">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Produk</th>
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
            <tr>
                <td colspan="5" style="text-align:right;"><strong>Total Order</strong></td>
                <td colspan="2"><strong><?= $total_order ?></strong></td>
            </tr>
            <tr>
                <td colspan="5" style="text-align:right;"><strong>Total Produk Terjual</strong></td>
                <td colspan="2"><strong><?= $total_produk_terjual ?></strong></td>
            </tr>
            <tr>
                <td colspan="5" style="text-align:right;"><strong>Total Omzet</strong></td>
                <td colspan="2"><strong><?= formatRupiah($total_omzet) ?></strong></td>
            </tr>
        <?php else: ?>
            <tr><td colspan="7">Tidak ada data</td></tr>
        <?php endif; ?>
    </tbody>
</table>
