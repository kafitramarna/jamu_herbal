<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login/login.php");
    exit;
}

require "../php/config.php";

// Fungsi untuk memformat angka ke format Rupiah
function formatRupiah($angka)
{
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Data untuk Today Sale (penjualan hari ini)
$today = date('Y-m-d');
$todaySaleQuery = "SELECT COUNT(*) as total_sales FROM orders WHERE DATE(transaction_time) = '$today'";
$todaySaleResult = mysqli_query($conn, $todaySaleQuery);
$todaySale = mysqli_fetch_assoc($todaySaleResult)['total_sales'];

// Data untuk Total Sale (total penjualan)
$totalSaleQuery = "SELECT COUNT(*) as total_sales FROM orders";
$totalSaleResult = mysqli_query($conn, $totalSaleQuery);
$totalSale = mysqli_fetch_assoc($totalSaleResult)['total_sales'];

// Data untuk Today Revenue (pendapatan hari ini)
$todayRevenueQuery = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE DATE(transaction_time) = '$today'";
$todayRevenueResult = mysqli_query($conn, $todayRevenueQuery);
$todayRevenue = mysqli_fetch_assoc($todayRevenueResult)['total_revenue'] ?? 0;

// Data untuk Total Revenue (total pendapatan)
$totalRevenueQuery = "SELECT SUM(total_amount) as total_revenue FROM orders";
$totalRevenueResult = mysqli_query($conn, $totalRevenueQuery);
$totalRevenue = mysqli_fetch_assoc($totalRevenueResult)['total_revenue'] ?? 0;

// Data untuk grafik penjualan per bulan (6 bulan terakhir)
$monthlySalesQuery = "SELECT 
                        YEAR(transaction_time) as year,
                        MONTH(transaction_time) as month,
                        COUNT(*) as total_sales,
                        SUM(total_amount) as total_revenue
                     FROM 
                        orders
                     WHERE 
                        transaction_time >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                     GROUP BY 
                        YEAR(transaction_time), MONTH(transaction_time)
                     ORDER BY 
                        year ASC, month ASC";
$monthlySalesResult = mysqli_query($conn, $monthlySalesQuery);

$months = [];
$sales = [];
$revenues = [];

while ($row = mysqli_fetch_assoc($monthlySalesResult)) {
    $monthName = date('M', mktime(0, 0, 0, $row['month'], 1));
    $months[] = $monthName . ' ' . $row['year'];
    $sales[] = $row['total_sales'];
    $revenues[] = $row['total_revenue'];
}

// Data untuk grafik penjualan per provinsi (top 3)
$provinceSalesQuery = "SELECT 
                        u.province_name,
                        COUNT(o.id) as total_sales
                     FROM 
                        orders o
                     JOIN 
                        users u ON o.user_id = u.id
                     WHERE 
                        u.province_name IS NOT NULL
                     GROUP BY 
                        u.province_name
                     ORDER BY 
                        total_sales DESC
                     LIMIT 3";
$provinceSalesResult = mysqli_query($conn, $provinceSalesQuery);

$provinces = [];
$provinceSalesData = [];

while ($row = mysqli_fetch_assoc($provinceSalesResult)) {
    $provinces[] = $row['province_name'] ?: 'Unknown';
    $provinceSalesData[] = $row['total_sales'];
}

// Data untuk pesanan terbaru
$recentOrdersQuery = "SELECT 
                        o.id, 
                        o.order_id, 
                        u.full_name, 
                        o.total_amount, 
                        o.transaction_time, 
                        o.transaction_status
                    FROM 
                        orders o
                    JOIN 
                        users u ON o.user_id = u.id
                    ORDER BY 
                        o.transaction_time DESC
                    LIMIT 5";
$recentOrdersResult = mysqli_query($conn, $recentOrdersQuery);
$recentOrders = [];

while ($row = mysqli_fetch_assoc($recentOrdersResult)) {
    $recentOrders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Dashboard Admin - Jamu Herbal</title>
    <link rel="icon" type="image/png" href="../assets/img/logo-nav.png" />
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/style.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Dashboard Styles with Theme Support */
        :root {
            --primary-color: #e74c3c;
            --primary-color-hover: #c0392b;
        }

        /* Light theme (default) */
        .light-theme {
            --card-bg: #f8f9fa;
            --card-text: #333333;
            --chart-grid: rgba(0, 0, 0, 0.1);
            --chart-text: #333333;
            --border-color: #dee2e6;
            --table-header: #f2f2f2;
            --table-hover: #f5f5f5;
        }

        /* Dark theme */
        .dark-theme {
            --card-bg: #2b2d3e;
            --card-text: #ffffff;
            --chart-grid: rgba(255, 255, 255, 0.1);
            --chart-text: #ffffff;
            --border-color: #3f4156;
            --table-header: #343a40;
            --table-hover: #32343a;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background-color: var(--card-bg);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            color: var(--card-text);
        }

        .stat-card .icon {
            font-size: 32px;
            margin-right: 15px;
            color: var(--primary-color);
        }

        .stat-card .info h4 {
            font-size: 14px;
            margin: 0 0 5px;
            opacity: 0.7;
        }

        .stat-card .info h2 {
            font-size: 24px;
            margin: 0;
            font-weight: 700;
        }

        .charts-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .chart-card {
            background-color: var(--card-bg);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .chart-header h3 {
            margin: 0;
            color: var(--card-text);
        }

        .chart-header a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .recent-orders {
            background-color: var(--card-bg);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .recent-orders .table {
            width: 100%;
            border-collapse: collapse;
            color: var(--card-text);
        }

        .recent-orders .table th,
        .recent-orders .table td {
            padding: 12px 15px;
            text-align: left;
        }

        .recent-orders .table thead tr {
            border-bottom: 1px solid var(--border-color);
            background-color: var(--table-header);
        }

        .recent-orders .table tbody tr:not(:last-child) {
            border-bottom: 1px solid var(--border-color);
        }

        .recent-orders .table tbody tr:hover {
            background-color: var(--table-hover);
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background-color: #2ecc71;
            color: white;
        }

        .badge-warning {
            background-color: #f39c12;
            color: white;
        }

        .badge-danger {
            background-color: #e74c3c;
            color: white;
        }

        .badge-info {
            background-color: #3498db;
            color: white;
        }

        @media (max-width: 1200px) {
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .stats-cards {
                grid-template-columns: 1fr;
            }
        }

        #content {
            position: relative;
        }

        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            z-index: 100;
        }

        .back-to-top:hover {
            background: var(--primary-color-hover);
        }
    </style>
</head>

<body>
    <?php include '../components/sidebar.php'; ?>

    <section id="content">
        <nav>
            <i class='bx bx-menu'></i>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search..." />
                    <button type="submit" class="search-btn">
                        <i class='bx bx-search'></i>
                    </button>
                </div>
            </form>
            <input type="checkbox" id="switch-mode" hidden />
            <label for="switch-mode" class="switch-mode"></label>
        </nav>

        <main id="main">
            <div class="head-title">
                <div class="left">
                    <h1>Dashboard</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Beranda</a></li>
                    </ul>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="icon">
                        <i class='bx bx-trending-up'></i>
                    </div>
                    <div class="info">
                        <h4>Penjualan Hari Ini</h4>
                        <h2><?= $todaySale ?></h2>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="icon">
                        <i class='bx bx-bar-chart-alt-2'></i>
                    </div>
                    <div class="info">
                        <h4>Total Penjualan</h4>
                        <h2><?= $totalSale ?></h2>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="icon">
                        <i class='bx bx-line-chart'></i>
                    </div>
                    <div class="info">
                        <h4>Pendapatan Hari Ini</h4>
                        <h2><?= formatRupiah($todayRevenue) ?></h2>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="icon">
                        <i class='bx bx-pie-chart-alt'></i>
                    </div>
                    <div class="info">
                        <h4>Total Pendapatan</h4>
                        <h2><?= formatRupiah($totalRevenue) ?></h2>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-container">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Penjualan Per Daerah</h3>
                        <a href="pesanan/table_pesanan.php">Lihat Semua</a>
                    </div>
                    <div class="chart-container">
                        <canvas id="worldwideSalesChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Penjualan & Pendapatan</h3>
                        <a href="pesanan/table_pesanan.php">Lihat Semua</a>
                    </div>
                    <div class="chart-container">
                        <canvas id="salesRevenueChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="recent-orders">
                <div class="chart-header">
                    <h3>Pesanan Terbaru</h3>
                    <a href="pesanan/table_pesanan.php">Lihat Semua</a>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentOrders)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">Tidak ada pesanan terbaru</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['order_id']) ?></td>
                                    <td><?= htmlspecialchars($order['full_name']) ?></td>
                                    <td><?= formatRupiah($order['total_amount']) ?></td>
                                    <td><?= date('d-m-Y H:i', strtotime($order['transaction_time'])) ?></td>
                                    <td>
                                        <?php
                                        $status = strtolower($order['transaction_status']);
                                        $badgeClass = 'badge-info';

                                        if ($status === 'completed') {
                                            $badgeClass = 'badge-success';
                                        } elseif ($status === 'shipping' || $status === 'settlement') {
                                            $badgeClass = 'badge-warning';
                                        } elseif ($status === 'pending') {
                                            $badgeClass = 'badge-info';
                                        } elseif ($status === 'failed' || $status === 'expired') {
                                            $badgeClass = 'badge-danger';
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Back to top button -->
            <a class="back-to-top" href="/admin/dashboard.php">
                <i class='bx bx-up-arrow-alt'></i>
            </a>
            <!-- <button class="back-to-top" onclick="scrollToTop()"> -->

            <!-- <i class='bx bx-up-arrow-alt'></i> -->

            <!-- </button> -->
        </main>
    </section>

    <script src="../assets/js/script.js"></script>
    <script>
        // Function to scroll to top
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Initialize theme based on current state
        function setTheme() {
            const body = document.body;
            const isDarkMode = document.getElementById('switch-mode').checked;

            if (isDarkMode) {
                body.classList.add('dark-theme');
                body.classList.remove('light-theme');
            } else {
                body.classList.add('light-theme');
                body.classList.remove('dark-theme');
            }

            // Update charts if they exist
            if (window.provinceSalesChart && window.salesRevenueChart) {
                updateChartsTheme();
            }
        }

        // Initialize charts with current theme
        let provinceSalesChart, salesRevenueChart;

        function updateChartsTheme() {
            const isDarkMode = document.getElementById('switch-mode').checked;
            const textColor = isDarkMode ? '#ffffff' : '#333333';
            const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

            // Update charts options
            provinceSalesChart.options.scales.y.grid.color = gridColor;
            provinceSalesChart.options.scales.x.grid.color = gridColor;
            provinceSalesChart.options.scales.y.ticks.color = textColor;
            provinceSalesChart.options.scales.x.ticks.color = textColor;
            provinceSalesChart.options.plugins.legend.labels.color = textColor;
            provinceSalesChart.update();

            salesRevenueChart.options.scales.y.grid.color = gridColor;
            salesRevenueChart.options.scales.x.grid.color = gridColor;
            salesRevenueChart.options.scales.y.ticks.color = textColor;
            salesRevenueChart.options.scales.x.ticks.color = textColor;
            salesRevenueChart.options.plugins.legend.labels.color = textColor;
            salesRevenueChart.update();
        }

        // Listen for theme changes
        document.getElementById('switch-mode').addEventListener('change', setTheme);

        // Chart.js Configuration
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial theme
            setTheme();

            const isDarkMode = document.getElementById('switch-mode').checked;
            const textColor = isDarkMode ? '#ffffff' : '#333333';
            const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

            // Worldwide Sales Chart
            const provinceSalesCtx = document.getElementById('worldwideSalesChart').getContext('2d');
            provinceSalesChart = new Chart(provinceSalesCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($provinces) ?>,
                    datasets: [{
                        label: 'Jumlah Penjualan',
                        data: <?= json_encode($provinceSalesData) ?>,
                        backgroundColor: '#e74c3c',
                        borderColor: '#e74c3c',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: textColor
                            }
                        },
                        x: {
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: textColor
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: textColor
                            }
                        }
                    }
                }
            });

            // Sales & Revenue Chart
            const salesRevenueCtx = document.getElementById('salesRevenueChart').getContext('2d');
            salesRevenueChart = new Chart(salesRevenueCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($months) ?>,
                    datasets: [{
                            label: 'Penjualan',
                            data: <?= json_encode($sales) ?>,
                            backgroundColor: 'rgba(231, 76, 60, 0.2)',
                            borderColor: '#e74c3c',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Pendapatan (dalam ribuan)',
                            data: <?= json_encode(array_map(function ($revenue) {
                                        return $revenue / 1000;
                                    }, $revenues)) ?>,
                            backgroundColor: 'rgba(52, 152, 219, 0.2)',
                            borderColor: '#3498db',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: textColor
                            }
                        },
                        x: {
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: textColor
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: textColor
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>