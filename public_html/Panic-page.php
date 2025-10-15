<?php
// Database connection and processing at the top
include 'db.php'; // Ensure this file exists and handles your database connection ($conn)

// Initialize variables
$employees = [];

// Fetch employee data
$query = "SELECT
            s.Name AS name,
            s.department,
            s.tag_id AS employee_id,
            CASE
              WHEN o.Active = 1 THEN 'not-safe'
              ELSE 'safe'
            END AS status
          FROM Staff s
          LEFT JOIN (
            SELECT
              tag_id,
              SUBSTRING_INDEX(GROUP_CONCAT(Active ORDER BY timestamp DESC), ',', 1) as Active
            FROM onsite
            GROUP BY tag_id
          ) o ON s.tag_id = o.tag_id
          ORDER BY s.department, s.Name";

$result = mysqli_query($conn, $query);
if ($result) {
    $employees = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Calculate status counts
    $onSiteCount = 0; // Represents 'not-safe'
    $offSiteCount = 0; // Represents 'safe'
    foreach ($employees as $employee) {
        if ($employee['status'] == 'not-safe') {
            $onSiteCount++;
        } else {
            $offSiteCount++;
        }
    }
} else {
    // Handle database query error
    error_log("Error fetching employees: " . mysqli_error($conn));
    $onSiteCount = 0;
    $offSiteCount = 0;
}

// Group employees by department for card display
$departments = [];
foreach ($employees as $employee) {
    if (!isset($departments[$employee['department']])) {
        $departments[$employee['department']] = [];
    }
    $departments[$employee['department']][] = $employee;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Safety Status Dashboard</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS Variables */
        :root {
            --text-primary: #333;
            --text-secondary: #666;
            --border-color: #e0e0e0;
            --background-light: #F1F5F9;
            --primary-blue: #1D4ED8;
            --dark-blue: #09326d;
            --red-panic: #f51313;
            --red-button: #df0404;
            --green-button: #00b087;
        }

        /* Universal Box-Sizing for consistent layout (Crucial fix) */
        *, *::before, *::after {
            box-sizing: border-box;
        }

        /* Basic styling */
        body {
            font-family: 'Inter', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-light);
        }
        .container {
            display: flex;
            flex-direction: row;
            height: 100vh;
        }

        /* Sidebar styles (Updated to match your new structure) */
        .sidebar {
            width: 260px;
            background-color: white;
            color: var(--text-primary);
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
            transition: width 0.3s;
            border-right: 1px solid var(--border-color);
        }

        .sidebar-header {
            margin-bottom: 2rem;
            padding-left: 0.75rem;
            display: flex; /* Added for centering image */
            justify-content: center; /* Added for centering image */
            align-items: center; /* Added for centering image */
        }
        .sidebar-header img {
            max-width: 100px; /* Consistent logo size */
            height: auto;
        }

        .sidebar-nav {
            flex-grow: 1; /* Allows nav to take available space */
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 0.5rem;
            background-color: transparent; /* Default transparent */
            color: var(--text-primary); /* Default text color */
            transition: background-color 0.2s, color 0.2s;
        }

        /* Specific styling for the active Dashboard item as per your example */
        .nav-item.active {
            background-color: var(--primary-blue);
            color: #FFFFFF;
        }

        .nav-item:hover {
            background-color: #d0d8ff; /* Light blue hover for non-active */
            color: var(--primary-blue);
        }
        .nav-item.active:hover { /* Override hover for active item */
            background-color: var(--dark-blue);
            color: #FFFFFF;
        }


        .nav-item i { /* Boxicons */
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }
        .nav-item.active i {
            color: #FFFFFF; /* White icon for active */
        }


        .sidebar-footer {
            margin-top: auto; /* Pushes to the bottom */
            padding-top: 1rem; /* Space from above */
        }

        .user-profile {
            display: flex;
            align-items: center;
            padding: 0.05rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            background-color: #f0f0f0; /* Light background for profile box */
        }

        .user-profile i { /* For any potential icon in user profile */
            color: var(--text-secondary);
            margin-left: auto;
        }

        .user-profile .profile-image { /* Renamed avatar to profile-image as in your HTML */
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 0.75rem;
            object-fit: cover;
        }

        .user-info {
            flex-grow: 1;
        }

        .user-name {
            display: block;
            font-weight: 600;
            color: var(--text-primary);
        }

        .user-role {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-blue);
            color: #FFFFFF;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .logout-btn:hover {
            background-color: var(--dark-blue);
        }

        /* Logout Modal */
        #logoutModal {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        #logoutModal .logout-modal {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            max-width: 350px;
            width: 90%;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
            font-family: 'Inter', sans-serif;
        }
        #logoutModal .logout-modal p {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            color: #333;
        }
        #logoutModal .logout-modal button {
            margin: 0 10px;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s ease;
        }
        #logoutModal #confirmLogout {
            background-color: var(--red-button);
            color: white;
        }
        #logoutModal #confirmLogout:hover {
            background-color: #c00707;
        }
        #logoutModal #cancelLogout {
            background-color: #ccc;
            color: #333;
        }
        #logoutModal #cancelLogout:hover {
            background-color: #b0b0b0;
        }

        /* Panic Button */
        .panic-button-container {
            position: fixed;
            top: 40px;
            right: 20px;
            z-index: 999;
        }
        .panic-button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 120px;
            height: 50px;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            overflow: hidden;
            background-color: transparent;
        }
        .panic-button .toggle-switch {
            position: relative;
            width: 45px; /* Larger toggle */
            height: 25px; /* Larger toggle */
            border-radius: 12.5px;
            background-color: #918f8f; /* Gray when off */
            transition: background-color 0.3s ease;
        }
        .panic-button.on .toggle-switch {
            background-color: var(--red-panic); /* Red when on */
        }
        .panic-button .toggle-switch::before {
            content: "";
            position: absolute;
            top: 3px;
            left: 3px;
            width: 19px; /* Larger circle */
            height: 19px; /* Larger circle */
            border-radius: 50%;
            background-color: #fff; /* White circle */
            transition: transform 0.3s ease;
        }
        .panic-button.on .toggle-switch::before {
            transform: translateX(20px);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            padding: 20px;
        }
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 28px;
            margin: 0;
            color: #0f4392;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
        }
        /* Upper content sticky */
        .upper-content {
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
            padding-bottom: 20px;
            padding-top: 20px;
            border-radius: 5px;
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        /* Metrics - Adjusted to center the timer better */
        .metrics {
            display: flex;
            justify-content: center; /* Ensures the items (including timer) are centered */
            align-items: center;    /* Aligns them vertically in the center */
            gap: 20px; /* Space between items */
            margin: 0 auto; /* Center the metrics container itself */
            max-width: 900px; /* Keep consistent width */
            margin-bottom: 0;
        }
        .metric {
            background-color: var(--background-light);
            width: 280px;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            font-family: 'Inter', sans-serif;
        }
        .metric.not-safe {
            background-color: rgba(223, 4, 4, 0.38);
            color: #730202;
        }
        .metric.safe {
            background-color: rgba(0, 176, 135, 0.38);
            color: #004d3f;
        }
        .metric span {
            font-size: 18px;
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
        }
        .metric p {
            font-size: 24px;
            margin: 0;
            font-weight: 700;
        }

        /* Timer display styles - Made more prominent and ensured it's a flexible item */
        #panicTimerDisplay {
            min-width: 150px; /* Increased width to accommodate larger font */
            text-align: center;
            font-size: 3.5em; /* Significantly larger font for visibility */
            font-weight: 800; /* Bolder font */
            color: var(--red-panic); /* Make it stand out */
            margin: 0 30px; /* Increased space from metrics */
            background-color: #ffebeb; /* Light red background */
            border-radius: 12px; /* Slightly more rounded corners */
            padding: 20px 25px; /* More padding */
            box-shadow: 0 4px 10px rgba(0,0,0,0.15); /* Stronger shadow */
            display: none; /* Hidden by default, shown when panic is on */
            line-height: 1; /* Adjust line height for better vertical centering of text */
        }

        /* Cards (CRITICAL LAYOUT FIXES) */
        .cards {
            flex: 1 1 auto;
            overflow-y: auto;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
            padding: 20px;
        }
        .card {
            background-color: #FFFFFF;
            border-radius: 8px;
            width: calc(50% - 10px);
            height: 220px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            font-family: 'Inter', sans-serif;
            display: flex;
            flex-direction: column;
        }

        /* Responsive adjustments for smaller screens */
        @media (max-width: 768px) {
            .metrics {
                flex-direction: column; /* Stack metrics on small screens */
                align-items: center;
            }
            #panicTimerDisplay {
                margin: 20px 0; /* Add vertical margin when stacked */
                font-size: 2.8em; /* Slightly smaller on mobile */
                padding: 15px 20px;
            }
            .cards {
                padding: 10px;
                justify-content: center;
            }
            .card {
                width: 90%;
                height: auto;
                max-height: 200px;
            }
        }

        .card h3 {
            text-align: center;
            font-size: 18px;
            margin: 0 0 15px 0;
            font-weight: 700;
            color: #0f4392;
        }
        /* Person layout */
        .person {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            border-bottom: 1px dashed #e0e0e0;
            padding-bottom: 8px;
        }
        .person:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .person p {
            font-size: 15px;
            margin: 0;
            color: #333;
            font-weight: 500;
        }
        .person button {
            padding: 6px 12px;
            border: none;
            border-radius: 20px;
            color: #FFFFFF;
            font-size: 13px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.1s ease;
        }
        .person button:hover {
            transform: translateY(-1px);
        }
        .person button.not-safe {
            background-color: var(--red-button);
        }
        .person button.not-safe:hover {
            background-color: #b30303;
        }
        .person button.safe {
            background-color: var(--green-button);
        }
        .person button.safe:hover {
            background-color: #008f6d;
        }

        /* Footer */
        .footer {
            padding: 20px;
            text-align: center;
            margin-top: 20px;
            font-family: 'Inter', sans-serif;
            color: #0f4392;
            font-weight: 600;
            font-size: 0.9rem;
            border-top: 1px solid var(--border-color);
        }
    </style>
</head>
<body>
    <div class="panic-button-container">
        <button id="panicButton" class="panic-button on" title="Clear Panic">
            <div class="toggle-switch"></div>
        </button>
    </div>

    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="https://github.com/luthandodake10111/iliso--frontend-images-/raw/main/iliso%20logo.png" alt="Logo" />
            </div>

            <nav class="sidebar-nav">
                <a href="Employee.php" class="nav-item active">
                    <i class="bx bx-home"></i>
                    <span>Dashboard</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="user-profile">
        
                   
                </div>

                <button class="logout-btn" id="logoutButton">LOG OUT</button>
            </div>
        </aside>

        <main class="main-content">
            <section class="upper-content">
                <header class="header">
                    <h1>Safety Status</h1>
                    <p style="color: var(--red-panic); font-weight: 600; font-size: 1.1em; margin-top: 10px;">
                        Emergency Mode Active!
                    </p>
                </header>
                <div class="metrics">
                    <div class="metric not-safe">
                        <span>Not Safe</span>
                        <p class="count" id="notSafeCount"><?php echo $onSiteCount; ?></p>
                    </div>
                    <div id="panicTimerDisplay">00:00</div>
                    <div class="metric safe">
                        <span>Safe</span>
                        <p class="count" id="safeCount"><?php echo $offSiteCount; ?></p>
                    </div>
                </div>
            </section>

            <section class="cards" id="cardsContainer">
                <?php foreach ($departments as $department => $deptEmployees): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($department); ?></h3>
                        <?php foreach ($deptEmployees as $employee): ?>
                            <div class="person">
                                <p><?php echo htmlspecialchars($employee['name']); ?></p>
                                <button class="<?php echo $employee['status']; ?>"
                                        data-employee-id="<?php echo htmlspecialchars($employee['employee_id']); ?>">
                                    <?php echo $employee['status'] === 'safe' ? 'Safe' : 'Not Safe'; ?>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </section>

            <div class="footer">Powered By ILISO</div>
        </main>
    </div>

    <div id="logoutModal">
        <div class="logout-modal">
            <p>Are you sure you want to logout?</p>
            <button id="confirmLogout">Yes, Logout</button>
            <button id="cancelLogout">Cancel</button>
        </div>
    </div>

<script src="assets/auth.js"></script>

<script>
    let panicTimerInterval; // Variable to hold the timer interval
    let elapsedSeconds = 0; // Initialize elapsed time for counting up
    let panicStartTime; // To store the time when panic mode started (for accurate stopping)

    async function init() {
        await checkAuth();
    }

    // Function to format time for display
    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }

    // Function to start the panic timer, counting UP
    function startPanicUpTimer() {
        const timerDisplay = document.getElementById('panicTimerDisplay');
        timerDisplay.style.display = 'block'; // Show the timer

        elapsedSeconds = 0; // Reset time to 00:00
        timerDisplay.textContent = formatTime(elapsedSeconds);
        timerDisplay.style.color = 'var(--red-panic)'; // Red while counting up
        timerDisplay.style.backgroundColor = '#ffebeb'; // Light red background

        panicStartTime = Date.now(); // Record the start time

        clearInterval(panicTimerInterval); // Clear any existing timer
        panicTimerInterval = setInterval(() => {
            elapsedSeconds++;
            timerDisplay.textContent = formatTime(elapsedSeconds);
        }, 1000); // Update every second
        console.log("Panic timer started counting up.");
    }

    // Function to stop the panic timer and display final elapsed time
    function stopPanicTimer() {
        clearInterval(panicTimerInterval);
        const timerDisplay = document.getElementById('panicTimerDisplay');
        timerDisplay.style.display = 'block'; // Ensure the timer remains visible

        const endTime = Date.now();
        // Calculate elapsed time based on actual start and end time for accuracy
        const totalElapsedMilliseconds = endTime - panicStartTime;
        const totalElapsedSeconds = Math.floor(totalElapsedMilliseconds / 1000);

        timerDisplay.textContent = formatTime(totalElapsedSeconds); // Show total elapsed time
        timerDisplay.style.color = 'var(--green-button)'; // Change color to green
        timerDisplay.style.backgroundColor = 'rgba(0, 176, 135, 0.38)'; // Change background to green-ish
        
        console.log("Panic timer stopped. Total elapsed time:", formatTime(totalElapsedSeconds));
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Start the timer counting up immediately when the page loads
        // This page is only accessed when panic mode is active.
        startPanicUpTimer();
        // Ensure the panic button reflects the active state
        document.getElementById('panicButton').classList.add('on');


        // Panic Button functionality: Now acts as a "Clear Panic" button
        const panicButton = document.getElementById('panicButton');

        panicButton.addEventListener('click', () => {
            // When clicked, it always turns OFF panic mode, stops the timer, and displays elapsed time
            if (panicButton.classList.contains('on')) { // Only act if panic is ON
                panicButton.classList.remove('on');
                panicButton.classList.add('off'); // Set to 'off' state visually
                console.log("Panic Button clicked: Clearing panic.");
                stopPanicTimer(); // Stop timer and display elapsed time
                localStorage.setItem('panicMode', 'off'); // Set panic mode off in local storage
                // No redirection here, stay on page to see elapsed time
            } else {
                // If button is already off, and somehow clicked, do nothing or handle differently
                console.log("Panic button is already off.");
            }
        });

        // Logout modal logic
        const logoutBtn = document.getElementById('logoutButton');
        const logoutModal = document.getElementById('logoutModal');
        const confirmLogout = document.getElementById('confirmLogout');
        const cancelLogout = document.getElementById('cancelLogout');

        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            logoutModal.style.display = 'flex';
        });

        cancelLogout.addEventListener('click', () => {
            logoutModal.style.display = 'none';
        });

        confirmLogout.addEventListener('click', () => {
            logoutModal.style.display = 'none';
            alert('Logged out successfully!');
            // TODO: Add your actual logout logic here:
            // 1. Clear authentication tokens (e.g., localStorage.removeItem('token');)
            // 2. Redirect to login page (e.g., window.location.href = '/login.html';)
            // 3. Potentially make an API call to invalidate session on backend
        });

        // Close modal on clicking outside the modal box
        logoutModal.addEventListener('click', (e) => {
            if (e.target === logoutModal) {
                logoutModal.style.display = 'none';
            }
        });

        // Animate count up function (simple)
        function animateCountUp(element, target) {
            let current = parseInt(element.textContent);
            const stepTime = 30;
            const increment = Math.max(1, Math.ceil(Math.abs(target - current) / 50));

            if (element.dataset.animationInterval) {
                clearInterval(parseInt(element.dataset.animationInterval));
            }

            const timer = setInterval(() => {
                if (current < target) {
                    current += increment;
                    if (current > target) current = target;
                } else if (current > target) {
                    current -= increment;
                    if (current < target) current = target;
                } else {
                    clearInterval(timer);
                    return;
                }
                element.textContent = current;
                if (current === target) {
                    clearInterval(timer);
                }
            }, stepTime);
            element.dataset.animationInterval = timer;
        }

        // Update safe/not safe counters based on buttons' classes
        function updateCounters() {
            const safeButtons = document.querySelectorAll('.person button.safe');
            const notSafeButtons = document.querySelectorAll('.person button.not-safe');

            animateCountUp(document.getElementById('safeCount'), safeButtons.length);
            animateCountUp(document.getElementById('notSafeCount'), notSafeButtons.length);
        }

        // Toggle safety status on person button click (using event delegation for efficiency)
        document.getElementById('cardsContainer').addEventListener('click', function(event) {
            const button = event.target.closest('.person button');
            if (button) {
                const employeeId = button.dataset.employeeId;
                const currentStatus = button.classList.contains('safe') ? 'safe' : 'not-safe';
                const newStatus = currentStatus === 'safe' ? 'not-safe' : 'safe';

                // AJAX call to update status in the database
                fetch('update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        employee_id: employeeId,
                        status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update button text and class on success
                        button.textContent = newStatus === 'safe' ? 'Safe' : 'Not Safe';
                        button.classList.remove(currentStatus);
                        button.classList.add(newStatus);
                        updateCounters(); // Recalculate and update counts
                    } else {
                        console.error('Failed to update status:', data.message);
                        alert('Failed to update status: ' + (data.message || 'Unknown error.'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating status. Please check server logs.');
                });
            }
        });

        // Initial counter update after PHP renders the content
        updateCounters();
    });
</script>