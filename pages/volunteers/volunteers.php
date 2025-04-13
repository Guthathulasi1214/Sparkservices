<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
requireLogin();

$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->prepare("
    SELECT u.*, 
        COUNT(DISTINCT er.id) as total_events,
        COUNT(DISTINCT d.id) as total_donations,
        SUM(d.amount) as total_donation_amount
    FROM users u
    LEFT JOIN event_registrations er ON u.id = er.user_id
    LEFT JOIN donations d ON u.id = d.user_id
    WHERE u.role = 'volunteer'
    GROUP BY u.id
    ORDER BY u.full_name ASC
");
$stmt->execute();
$volunteers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteers - VolunteerHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-yellow-50">
    <div class="min-h-screen">
        <nav class="bg-white shadow-lg border-b-2 border-yellow-700">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="/soemone" class="font-bold text-xl text-yellow-700">
                            <i class="fas fa-hands-helping mr-2 text-yellow-600"></i>Sparkservices
                        </a>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/soemone/pages/volunteers/dashboard.php" class="text-gray-700 hover:text-yellow-700">
                            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                        </a>
                        <a href="/soemone/pages/volunteers/events.php" class="text-gray-700 hover:text-yellow-700">
                            <i class="fas fa-calendar-alt mr-1"></i>Events
                        </a>
                        <a href="/soemone/pages/volunteers/donations.php" class="text-gray-700 hover:text-yellow-700">
                            <i class="fas fa-hand-holding-usd mr-1"></i>Donations
                        </a>
                        <a href="/soemone/pages/volunteers/volunteers.php" class="text-yellow-700 font-semibold border-b-2 border-yellow-500">
                            <i class="fas fa-users mr-1"></i>Volunteers
                        </a>
                        <a href="/soemone/pages/volunteers/profile.php" class="text-gray-700 hover:text-yellow-700">
                            <i class="fas fa-user mr-1"></i>Profile
                        </a>
                        <a href="/soemone/logout.php" class="text-gray-700 hover:text-yellow-700">
                            <i class="fas fa-sign-out-alt mr-1"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="bg-gradient-to-r from-yellow-700 via-amber-600 to-orange-500 text-white">
            <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                        Our Volunteers
                    </h1>
                    <p class="mt-6 max-w-2xl mx-auto text-xl text-amber-100">
                        Meet the amazing people who make our community stronger.
                    </p>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <label for="search" class="block text-sm font-medium text-gray-700">Search Volunteers</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" name="search" id="search" class="focus:ring-yellow-500 focus:border-yellow-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md" placeholder="Search by name, email...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($volunteers as $volunteer): ?>
                    <div class="bg-white overflow-hidden shadow-lg rounded-lg transform transition duration-300 hover:scale-105">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-full bg-gradient-to-r from-yellow-100 to-amber-100 flex items-center justify-center">
                                        <i class="fas fa-user text-amber-600 text-xl"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($volunteer['full_name']); ?></h3>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($volunteer['email']); ?></p>
                                </div>
                            </div>
                            
                            <div class="mt-4 space-y-3">
                                <?php if ($volunteer['phone']): ?>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-phone mr-2 text-amber-500"></i>
                                    <?php echo htmlspecialchars($volunteer['phone']); ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($volunteer['address']): ?>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-map-marker-alt mr-2 text-amber-500"></i>
                                    <?php echo htmlspecialchars($volunteer['address']); ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-calendar-check mr-2 text-amber-500"></i>
                                    Participated in <?php echo $volunteer['total_events']; ?> events
                                </div>
                                
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-donate mr-2 text-amber-500"></i>
                                    <?php echo $volunteer['total_donations']; ?> donations ($<?php echo number_format($volunteer['total_donation_amount'] ?? 0, 2); ?>)                                </div>
                            </div>

                            <?php if ($volunteer['bio']): ?>
                            <div class="mt-4">
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($volunteer['bio']); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($volunteers)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-users text-6xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900">No Volunteers Found</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        There are currently no volunteers registered in the system.
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <footer class="bg-gradient-to-r from-yellow-700 via-amber-600 to-orange-500 text-white shadow mt-12">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-amber-100 text-sm">
                    &copy; <?php echo date('Y'); ?> VolunteerHub. All rights reserved.
                </p>
            </div>
        </footer>
    </div>

    <script>
        // Simple search functionality
        document.getElementById('search').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const volunteerCards = document.querySelectorAll('.grid > div');

            volunteerCards.forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                const email = card.querySelector('p').textContent.toLowerCase();
                const bio = card.querySelector('.text-gray-600')?.textContent.toLowerCase() || '';
                
                const matchesSearch = name.includes(searchTerm) || 
                                    email.includes(searchTerm) || 
                                    bio.includes(searchTerm);

                card.style.display = matchesSearch ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>