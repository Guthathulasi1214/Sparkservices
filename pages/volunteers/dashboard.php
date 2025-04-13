<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
requireLogin();

$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->prepare("
    SELECT * FROM users 
    WHERE id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("
    SELECT * FROM events 
    WHERE status = 'Open' 
    AND date_time >= CURDATE()
    ORDER BY date_time ASC 
    LIMIT 5
");
$stmt->execute();
$upcomingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent donations
$stmt = $conn->prepare("
    SELECT * FROM donations 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recentDonations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-bg {
            background-image: url('/api/placeholder/1600/400');
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .hero-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(161, 98, 7, 0.85);
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
        .footer-bg {
            background-image: url('/api/placeholder/1600/300');
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .footer-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(161, 98, 7, 0.9);
            z-index: 1;
        }
        .footer-content {
            position: relative;
            z-index: 2;
        }
    </style>
</head>
<body class="bg-yellow-50">
    <div class="min-h-screen">
        <nav class="bg-white shadow-lg border-b-4 border-yellow-500">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="/soemone" class="font-bold text-xl text-yellow-700">
                            <i class="fas fa-hands-helping mr-2"></i>Sparkservices
                        </a>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/soemone/pages/volunteers/dashboard.php" class="text-gray-600 hover:text-yellow-700 transition duration-300">
                            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                        </a>
                        <a href="/soemone/pages/volunteers/events.php" class="text-gray-600 hover:text-yellow-700 transition duration-300">
                            <i class="fas fa-calendar-alt mr-1"></i>Events
                        </a>
                        <a href="/soemone/pages/volunteers/donations.php" class="text-gray-600 hover:text-yellow-700 transition duration-300">
                            <i class="fas fa-hand-holding-usd mr-1"></i>Donations
                        </a>
                        <a href="/soemone/pages/volunteers/volunteers.php" class="text-gray-600 hover:text-yellow-700 transition duration-300">
                            <i class="fas fa-users mr-1"></i>Volunteers
                        </a>
                        <a href="/soemone/pages/volunteers/profile.php" class="text-gray-600 hover:text-yellow-700 transition duration-300">
                            <i class="fas fa-user mr-1"></i>Profile
                        </a>
                        <a href="/soemone/logout.php" class="text-gray-600 hover:text-yellow-700 transition duration-300">
                            <i class="fas fa-sign-out-alt mr-1"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        <div class="hero-bg">
            <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8 hero-content">
                <div class="text-center">
                    <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl text-white">
                        Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!
                    </h1>
                    <p class="mt-6 max-w-2xl mx-auto text-xl text-yellow-100">
                        Your volunteer dashboard is ready to help you make a difference in the community.
                    </p>
                    <div class="mt-8">
                        <a href="/soemone/pages/volunteers/events.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-yellow-700 bg-white hover:bg-yellow-50 transition duration-300">
                            Find Events
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-8">
                <div class="bg-white shadow-lg rounded-lg p-6 transform transition duration-300 hover:scale-105 border-l-4 border-yellow-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-gradient-to-r from-yellow-100 to-amber-100 text-yellow-700">
                            <i class="fas fa-calendar text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Upcoming Events</h3>
                            <p class="text-2xl font-semibold text-yellow-700"><?php echo count($upcomingEvents); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-lg rounded-lg p-6 transform transition duration-300 hover:scale-105 border-l-4 border-yellow-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-gradient-to-r from-yellow-100 to-amber-100 text-yellow-700">
                            <i class="fas fa-hand-holding-usd text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Total Donations</h3>
                            <p class="text-2xl font-semibold text-yellow-700"><?php 
                                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM donations WHERE user_id = ?");
                                $stmt->execute([$_SESSION['user_id']]);
                                echo $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                            ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-lg rounded-lg p-6 transform transition duration-300 hover:scale-105 border-l-4 border-yellow-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-gradient-to-r from-yellow-100 to-amber-100 text-yellow-700">
                            <i class="fas fa-user text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Account Type</h3>
                            <p class="text-2xl font-semibold text-yellow-700"><?php echo ucfirst($user['role']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-lg rounded-lg p-6 mb-8 border-t-4 border-yellow-500">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-calendar-check text-yellow-700 mr-2"></i>
                        Upcoming Events
                    </h2>
                    <a href="/soemone/pages/volunteers/events.php" class="text-yellow-700 hover:text-yellow-800 flex items-center font-medium transition duration-300">
                        View All Events <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-yellow-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">Event</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">Location</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($upcomingEvents as $event): ?>
                            <tr class="hover:bg-yellow-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($event['title']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo date('M d, Y h:i A', strtotime($event['date_time'])); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($event['location']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $event['status'] === 'Open' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo ucfirst($event['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white shadow-lg rounded-lg p-6 border-t-4 border-yellow-500">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-donate text-yellow-700 mr-2"></i>
                        Recent Donations
                    </h2>
                    <a href="/soemone/pages/volunteers/donations.php" class="text-yellow-700 hover:text-yellow-800 flex items-center font-medium transition duration-300">
                        View All Donations <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-yellow-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">Payment Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recentDonations as $donation): ?>
                            <tr class="hover:bg-yellow-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo date('M d, Y', strtotime($donation['created_at'])); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-yellow-700">$<?php echo number_format($donation['amount'], 2); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($donation['payment_method']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $donation['status'] === 'Approved' ? 'bg-yellow-100 text-yellow-800' : 
                                               ($donation['status'] === 'Pending' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800'); ?>">
                                        <?php echo ucfirst($donation['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <footer class="footer-bg mt-12">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 footer-content">
                <div class="flex flex-col md:flex-row md:justify-between items-center">
                    <div class="flex items-center mb-4 md:mb-0">
                        <i class="fas fa-hands-helping text-2xl mr-2 text-white"></i>
                        <span class="font-bold text-xl text-white">Sparkservices</span>
                    </div>
                    <p class="text-yellow-100 text-sm">
                        &copy; <?php echo date('Y'); ?> Sparkservices. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>