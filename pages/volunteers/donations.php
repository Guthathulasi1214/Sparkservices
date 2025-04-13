<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: /soemone/login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = "Donation added successfully!";
}

$stmt = $conn->prepare("
    SELECT d.*, u.full_name 
    FROM donations d
    JOIN users u ON d.user_id = u.id
    WHERE d.user_id = ? 
    ORDER BY d.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Donations - VolunteerHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-amber-50">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/soemone" class="text-xl font-bold text-yellow-700">
                            <i class="fas fa-hands-helping mr-2"></i>Sparkservices
                        </a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="/soemone/pages/volunteers/dashboard.php" class="border-transparent text-gray-500 hover:border-yellow-500 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-home mr-2"></i> Dashboard
                        </a>
                        <a href="/soemone/pages/volunteers/donations.php" class="border-yellow-700 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-donate mr-2 text-yellow-700"></i> Donations
                        </a>
                        <a href="/soemone/pages/volunteers/events.php" class="border-transparent text-gray-500 hover:border-yellow-500 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-calendar-alt mr-2"></i> Events
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-gray-700 mr-4"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <a href="/soemone/logout.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-700 hover:bg-yellow-800">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">My Donations</h2>
                    <a href="/soemone/pages/volunteers/new_donation.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-700 hover:bg-yellow-800">
                        <i class="fas fa-plus mr-2"></i> New Donation
                    </a>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700"><?php echo htmlspecialchars($success_message); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (empty($donations)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-donate text-4xl text-yellow-500 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900">No donations yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by making your first donation.</p>
                        <div class="mt-6">
                            <a href="/soemone/pages/volunteers/new_donation.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-700 hover:bg-yellow-800">
                                <i class="fas fa-plus mr-2"></i> Make a Donation
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-yellow-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($donations as $donation): ?>
                                    <tr class="hover:bg-yellow-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($donation['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                            $<?php echo number_format($donation['amount'], 2); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($donation['payment_method']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $status_class = '';
                                            switch ($donation['status']) {
                                                case 'Completed':
                                                    $status_class = 'bg-green-100 text-green-800';
                                                    break;
                                                case 'Pending':
                                                    $status_class = 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'Failed':
                                                    $status_class = 'bg-red-100 text-red-800';
                                                    break;
                                            }
                                            ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
                                                <?php echo htmlspecialchars($donation['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>