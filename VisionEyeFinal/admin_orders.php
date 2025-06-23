<?php
@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit;
}

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$where_clause = '';
$params = [];

if ($start_date && $end_date) {
   $where_clause = "WHERE placed_on BETWEEN ? AND ?";
   $params[] = $start_date;
   $params[] = $end_date;
}

if(isset($_POST['update_order'])){
   $order_id = $_POST['order_id'];
   $update_payment = $_POST['update_payment'] ?? '';

   if($update_payment != ''){
      $update_payment = filter_var($update_payment, FILTER_SANITIZE_STRING);
      $update_orders = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
      $update_orders->execute([$update_payment, $order_id]);
      $message[] = 'Payment has been updated!';
   } else {
      $message[] = 'Please select a payment status before updating.';
   }
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_orders = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_orders->execute([$delete_id]);
   header('location:admin_orders.php');
   exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Orders</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php include 'admin_header.php'; ?>


<section class="placed-orders">

   <h1 class="title">Placed Orders</h1>

   <form method="get" style="margin-bottom: 1rem; text-align: center;">
      <label>Date:</label>
      <input type="date" name="start_date" value="<?= $start_date ?>">
      <input type="date" name="end_date" value="<?= $end_date ?>">
      <button type="submit" class="btn">View</button>
      <a href="admin_orders.php" class="btn">Reset</a>
   </form>

   <div class="box-container">

      <?php
         $query = "SELECT * FROM `orders` $where_clause ORDER BY placed_on DESC";
         $select_orders = $conn->prepare($query);
         $select_orders->execute($params);
         if($select_orders->rowCount() > 0){
            while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
      ?>
      <div class="box">
         <p> user id : <span><?= $fetch_orders['user_id']; ?></span> </p>
         <p> placed on : <span><?= $fetch_orders['placed_on']; ?></span> </p>
         <p> name : <span><?= $fetch_orders['name']; ?></span> </p>
         <p> email : <span><?= $fetch_orders['email']; ?></span> </p>
         <p> number : <span><?= $fetch_orders['number']; ?></span> </p>
         <p> address : <span><?= $fetch_orders['address']; ?></span> </p>
         <p> total price : <span>Rp<?= $fetch_orders['total_price']; ?>/-</span> </p>
         <p> payment method : <span><?= $fetch_orders['method']; ?></span> </p>
         <p> payment status : <span><?= $fetch_orders['payment_status']; ?></span> </p>
         <p><strong>Ordered Products:</strong></p>
         <ul>
         <?php
            $order_id = $fetch_orders['id'];
            $stmt_items = $conn->prepare("SELECT p.name, p.image, oi.quantity FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
            if ($stmt_items->execute([$order_id])) {
               $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
               if ($items) {
                  foreach ($items as $item) {
                     echo "<li><img src='uploaded_img/{$item['image']}' width='40' style='vertical-align:middle;margin-right:8px;'> {$item['name']} ({$item['quantity']})</li>";
                  }
               } else {
                  echo "<li>No items found.</li>";
               }
            } else {
               echo "<li>Error loading items.</li>";
            }
         ?>
         </ul>
         <form action="" method="POST">
            <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
            <select name="update_payment" class="drop-down">
               <option value="" selected disabled>Update status</option>
               <option value="pending">pending</option>
               <option value="completed">completed</option>
            </select>
            <div class="flex-btn">
               <input type="submit" name="update_order" class="option-btn" value="update">
               <a href="admin_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('Delete this order?');">delete</a>
            </div>
         </form>
      </div>
      <?php
         }
      } else {
         echo '<p class="empty">No orders placed yet!</p>';
      }
      ?>

   </div>

</section>

<script src="js/script.js"></script>
</body>
</html>
