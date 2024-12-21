<?php
session_start();
include('db.php'); // Include your DB connection file

// Admin login check
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Pagination variables
$limit = 10; // Number of orders per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page number
$offset = ($page - 1) * $limit; // Offset for the SQL query

// Get the total number of orders for pagination
$totalOrdersQuery = $conn->query("SELECT COUNT(*) AS total FROM orders");
$totalOrders = $totalOrdersQuery->fetch_assoc();
$totalPages = ceil($totalOrders['total'] / $limit);

// Get orders with products and pagination
$ordersQuery = $conn->prepare("
    SELECT o.id AS order_id, o.total_price, o.status, o.created_at, 
           u.username, u.email, 
           GROUP_CONCAT(p.name SEPARATOR ', ') AS product_names
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    GROUP BY o.id
    ORDER BY o.created_at DESC 
    LIMIT ? OFFSET ?");
$ordersQuery->bind_param("ii", $limit, $offset);
$ordersQuery->execute();
$ordersResult = $ordersQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 1200px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .table thead {
            background-color: #007bff;
            color: white;
        }
        .pagination {
            justify-content: center;
        }
        .pagination a {
            color: #007bff;
            padding: 8px 16px;
            text-decoration: none;
        }
        .pagination a:hover {
            background-color: #f1f1f1;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Admin - Manage Orders</h2>
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Products</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $ordersResult->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td><?php echo htmlspecialchars($order['email']); ?></td>
                                <td><?php echo htmlspecialchars($order['product_names']); ?></td>
                                <td>₱<?php echo number_format($order['total_price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($order['status']); ?></td>
                                <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php } ?>
            </ul>
        </nav>

        <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
