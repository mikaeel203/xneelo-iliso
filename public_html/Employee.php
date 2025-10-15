<?php
session_start();
include 'db.php';
// Handle AJAX requests before outputting any HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json'); // Important for JS to read JSON
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['action'])) {
        switch ($input['action']) {
            case 'add_employee':
                $name = $conn->real_escape_string($input['name']);
                $email = $conn->real_escape_string($input['email']);
                $department = $conn->real_escape_string($input['department']);
                $tag_id = $conn->real_escape_string($input['employee_id']);
                $sql = "INSERT INTO Staff (tag_id, Name, email, department)
                                  VALUES ('$tag_id', '$name', '$email', '$department')";
                if ($conn->query($sql)) {
                    echo json_encode(['success' => true, 'id' => $tag_id]);
                } else {
                    echo json_encode(['success' => false, 'error' => $conn->error]);
                }
                exit;
            case 'update_employee':
                $name = $conn->real_escape_string($input['name']);
                $email = $conn->real_escape_string($input['email']);
                $department = $conn->real_escape_string($input['department']);
                $tag_id = $conn->real_escape_string($input['tag_id']);
                $sql = "UPDATE Staff SET Name = '$name', email = '$email', department = '$department' WHERE tag_id = '$tag_id'";
                if ($conn->query($sql)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $conn->error]);
                }
                exit;
            case 'delete_employee':
                $empId = $conn->real_escape_string($input['empId']);
                $sql = "DELETE FROM Staff WHERE tag_id = '$empId'";
                if ($conn->query($sql)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $conn->error]);
                }
                exit;
            default:
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
                exit;
        }
    }
    echo json_encode(['success' => false, 'error' => 'Missing action']);
    exit;
}
// Only fetch employees and continue if it's not a POST request
$employees = [];
$sql = "SELECT tag_id, Name, email, department FROM Staff ORDER BY Name ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = [
            'id' => $row['tag_id'],
            'name' => $row['Name'],
            'email' => $row['email'],
            'department' => $row['department'],
            'employee_id' => $row['tag_id']
        ];
    }
}

$filter = isset($_GET['status']) ? $_GET['status'] : 'all';

$query = "
  SELECT
    s.Name AS name,
    s.department,
    s.email,
    s.tag_id AS employee_id,
    o.latest_scan AS clock_in,
    CASE
      WHEN o.Active = 1 THEN 'On Site'
      ELSE 'Off Site'
    END AS status
  FROM Staff s
  LEFT JOIN (
    SELECT 
      tag_id, 
      MAX(timestamp) as latest_scan, 
      SUBSTRING_INDEX(GROUP_CONCAT(Active ORDER BY timestamp DESC), ',', 1) as Active
    FROM onsite
    GROUP BY tag_id
  ) o ON s.tag_id = o.tag_id
";

// Apply filter based on the status parameter
if ($filter !== 'all') {
    if ($filter === 'on') {
        $query .= " WHERE o.Active = 1";
    } elseif ($filter === 'off') {
        $query .= " WHERE (o.Active = 0 OR o.Active IS NULL)";
    }
}

$query .= " ORDER BY o.latest_scan DESC";

$result = mysqli_query($conn, $query);
$employees = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link href="https://cdn.boxicons.com/fonts/basic/boxicons.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" rel="stylesheet" />
    <title>Employee Information</title>

    <style>
    /* === Global Styles === */
html, body {
    font-family: 'Inter', sans-serif;
    background-color: var(--main-bg);
    color: var(--text-primary);
    transition: background-color 0.3s, color 0.3s;
    height: 100%;
    margin: 0;
    overflow: hidden; 
}

/* === Modal Overlay (Reusable) === */
.modal-overlay,
#employeeModal,
#panic-confirmation-modal {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    width: 100%; height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

/* === Panic Modal Specific === */
#panic-confirmation-modal {
    z-index: 10000;
    display: flex;
    background-color: rgba(0, 0, 0, 0.6);
}

#panic-confirmation-modal .modal-content {
    background-color: #fefefe;
    padding: 30px;
    border: 1px solid #888;
    border-radius: 10px;
    width: 90%;
    max-width: 550px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    text-align: center;
}

#panic-confirmation-modal h2 {
    margin-top: 0;
    color: #333;
    font-size: 24px;
}

#panic-confirmation-modal p {
    color: #666;
    margin-bottom: 20px;
}

/* === Warning Section === */
.warning-section {
    background-color: #fff3cd;
    border: 1px solid #ffeeba;
    color: #856404;
    padding: 12px;
    margin: 20px 0;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    font-size: 15px;
}

.warning-section i {
    font-size: 20px;
}

/* === Button Groups === */
.button-group {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 25px;
}

.button-group button {
    padding: 12px 25px;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

#confirm-continue {
    background-color: #dc3545;
    color: white;
}
#confirm-continue:hover {
    background-color: #c82333;
    transform: translateY(-2px);
}

#cancel-panic {
    background-color: #6c757d;
    color: white;
}
#cancel-panic:hover {
    background-color: #5a6268;
    transform: translateY(-2px);
}

/* === Sidebar === */
.sidebar {
    width: 200px;
    background-color: white;
    color: var(--text-primary);
    display: flex;
    flex-direction: column;
    padding: 24px 24px 0px 24px;
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
    max-width: 100px;
    height: auto;
}

.nav-menu {
    list-style: none;
    padding: 0;
    margin: 20px 0;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    border-radius: 6px;
    background-color: #00459f;
    color: white;
    cursor: pointer;
    transition: background-color 0.3s ease;
    /* margin-bottom: 100px; */
    padding-left: 35px
}

.nav-item:hover {
    background-color: #3367D6;
}

.nav-item.active {
    background-color: #003370;
    font-weight: bold;
}

/* === Logout Modal === */
.logout-modal {
    background: white;
    padding: 25px;
    border-radius: 8px;
    max-width: 400px;
    text-align: center;
}

/* === Employee Modal === */
#employeeModal {
  
    z-index: 1001;
    overflow: auto;
}

#employeeModal .modal-content {
    background: white;
    border-radius: 8px;
    padding: 25px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* === Close Button === */
.close-btn {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.close-btn:hover {
    color: #333;
}

/* === Form Inputs === */
form input, form select {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    width: 100%;
    margin-bottom: 15px;
    box-sizing: border-box;
}

/* === Form Buttons === */
.form-buttons {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.form-buttons button {
    flex: 1;
    padding: 12px;
    font-weight: 600;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.form-buttons button:first-child {
    background-color: #00459f;
    color: white;
}

.form-buttons button:last-child {
    background-color: #f0f0f0;
    color: #333;
    border: 1px solid #ddd;
}

/* === Main Content === */
.dashboard-container {
    display: flex;
    min-height: 100vh;
    background-color:rgb(227, 226, 226);
}

.main-board {
    padding: 25px 20px 0px 20px;
    width: calc(100% - 250px);
    flex-grow: 1;
    background-color: #F1F5F9;
}



/* === Search & Sort === */
.search-contain {
    display: flex;
    justify-content: space-between;
    align-items: center;

}

.search-bar {
    display: flex;
    gap: 10px;
    margin: left 10px;
}

.search-icon-input {
    position: relative;
    width: 250px;
}

.search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
}

.search-box {
    padding-left: 30px;
    padding: 10px;
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.3s, box-shadow 0.3s;
}

.search-box:focus {
    border-color: #004aad;
    box-shadow: 0 0 5px rgba(0, 74, 173, 0.4);
}

#sortByStatus {
    width: 150px;
    padding: 8px 12px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background-color: #f9f9f9;
    color: #333;
    outline: none;
    transition: border-color 0.3s, box-shadow 0.3s;
    margin: 0px 0px 0px 22px;
}

#sortByStatus:focus {
    border-color: #004aad; 
    box-shadow: 0 0 5px rgba(0, 74, 173, 0.5);
}

/* === Table Styling === */
table {
    width: 100%;
    min-width: 600px;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: white;
    font-family: Arial, sans-serif;
}

th {
    padding: 12px;
    background-color: #f5f5f5;
    border-bottom: 2px solid #ccc;
    text-align: left;
    position: sticky;
    top: 0;
    z-index: 2;
}

td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}

tr.selected {
    background-color: #e0e0e0;
    transition: background-color 0.3s;
}

tr:hover:not(.selected) {
    background-color: #f9f9f9;
    cursor: pointer;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-weight: 600;
  font-size: 0.75rem;
  display: inline-block;
}

.status-on {
  color: var(--status-on);
  background-color: rgba(0, 176, 135, 0.38);
}

.status-off {
  color: var(--status-off);
  background-color: rgba(223, 4, 4, 0.38);
}

 /* === Pagination === */
 .pagination {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.pagination button {
    padding: 8px 16px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    border-radius: 10px;
    border: 2px solid;
    transition: transform 0.2s;
}

.pagination button:nth-child(1) {
    background-color: #a2b4d6;
    border-color: #204a87;
    color: black;
}

.pagination button:nth-child(2) {
    background-color: #f19999;
    border-color: #d11e1e;
    color: black;
} 

/* === Add Employee Button === */
.add-employee-button {
    display: flex;
    justify-content: center;
}

.add-employee-button button {
    background-color: #00459f;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 10px 20px;
    font-weight: bold;
    font-size: 14px;
    /* height: 30px; */
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    transition: background-color 0.3s, transform 0.2s;
}

.add-employee-button button:hover {
    background-color: #3367D6;
    transform: translateY(-1px);
}

/* === Footer === */
footer {
    text-align: center;
    /* padding: 20px 0px 0px 0px; */
    font-size: 14px;
    color: #666;
}

body > ul.nav-menu.bottom-menu,
body > #logoutButton {
    display: none !important;
}

/* === Action Buttons === */
.table-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin: 20px 0 15px;
}

.action-button {
    padding: 10px 20px;
    font-size: 14px;
    font-weight: bold;
    border-radius: 6px;
    border: 1px solid #ddd;
    background-color: #f0f0f0;
    color: #333;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
}

.action-button:hover {
    background-color: #e0e0e0;
    transform: translateY(-1px);
}

#editEmployeeBtn {
    background-color: #00459f;
    color: white;
    border: none;
}

#editEmployeeBtn:hover {
    background-color: #3367D6;
}

#deleteEmployeeBtn {
    background-color: #dc3545;
    color: white;
    border: none;
}

#deleteEmployeeBtn:hover {
    background-color: #c82333;
}

/* === Panic Button === */
.panic-button-container {
    position: fixed;
    top: 40px;
    right: 20px;
    z-index: 9999;
}

.panic-button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 120px;
    height: 50px;
    border: none;
    border-radius: 15px;
    background-color: transparent;
    cursor: pointer;
    transition: all 0.3s ease;
    overflow: hidden;
}

.panic-button .toggle-switch {
    position: relative;
    width: 50px;
    height: 28px;
    border-radius: 20px;
    background-color: lightgray;
    transition: background-color 0.3s ease;
}

.panic-button.on .toggle-switch {
    background-color: #f51313;
}

.panic-button .toggle-switch::before {
    content: "";
    position: absolute;
    top: 3px;
    left: 3px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background-color: #fff;
    transition: transform 0.3s ease;
}

.panic-button.on .toggle-switch::before {
    transform: translateX(14px);
}

.active-employees{
  background-color: white;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: var(--shadow);
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    transition: background-color 0.3s;
    overflow: hidden;

}

@media (max-width: 1024px) {
  .sidebar {
    display: none;
  }
}
</style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="https://github.com/luthandodake10111/iliso--frontend-images-/raw/main/iliso%20logo.png" alt="Logo" />
            </div>

            <!-- <ul class="nav-menu top-menu" id="topMenu">
                 <li class="nav-link" id="addminButton"><i class="bx bx-user"></i><span>dashboard</span></li>
                <li class="nav-link" id="viewadmin"><i class="bx bx-home"></i><span>Dashboard</span></li>
            </ul> -->

            <nav class="sidebar-nav">
                <a id="viewadmin" class="nav-item">
                    <i class="bx bx-home"></i>
                    <span>Dashboard</span>
                </a>
            </nav>

            <!-- <div style="flex-grow: 1;"></div> -->
            <div style="margin-top: auto; padding: 20px 0; border-top: 1px solid #e0e0e0;">
                

                <ul class="nav-menu bottom-menu" id="sidebarLogout">
                    <li class="nav-item" id="logoutButton" style="padding: 10px; display: flex; align-items: center; gap: 10px; padding-left: 35px">
                        <i class="bx bx-log-out"></i><span>Log-Out</span>
                    </li>
                </ul>
            </div>
        </aside>

        <div class="main-board">
            <div class="welcome-msg"><h2>Welcome 👋🏻</h2></div>

            <section class="active-employees">
                <div class="search-contain">
                    <h3>All Employees</h3>
                    <div class="search-bar">
                        <div class="search-icon-input">
                            <i class="bx bx-search-alt search-icon"></i>
                            <input type="text" class="search-box" placeholder="    Search" id="searchBox" />
                        </div>
                    
                        <select id="sortByStatus">
                          <optgroup label="General Sort">
                            <option value="all">All</option>
                            <option value="name_asc">Name (A-Z)</option>
                            <option value="name_desc">Name (Z-A)</option>
                            <option value="department">Department</option>
                          </optgroup>
                          <optgroup label="Status Filter">
                            <option value="on-site">On Site</option>
                            <option value="off-site">Off Site</option>
                          </optgroup>
                        </select>

                        <div class="add-employee-button">
                    <button id="employeeButton" class="nav-link">
                        <i class="bx bx-user-plus"></i><span>Add New Employee</span>
                    </button>
                </div>
                    </div>
                  
                    
                </div>

                <div id="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Department</th>
                                <th>Email</th>
                                <th>Employee ID</th> 
                                <th>Status</th> 
                              </tr>
                        </thead>
                        <tbody id="employeeTableBody"></tbody>
                    </table>
                </div>

                <div class="table-actions">
                    <button class="action-button" id="exportBtn">Export Data</button>
                    <button id="editEmployeeBtn" 
                    class="action-button">Edit Employee</button>
                    <button id="deleteEmployeeBtn" class="action-button">Delete Employee</button>
                </div>

                <div class="pagination" id="pagination"></div>

                
            </section>
            <footer>
                <div class="footer-slogan" id="updatePagination">Powered By ILISO</div>
            </footer>
        </div>
    </div> 
    <div class="panic-button-container">
        <button id="panic-button" class="panic-button off">
            <span class="toggle-switch"></span>
        </button>
    </div>

    <div id="panic-confirmation-modal" style="display: none;">
        <div class="modal-content">
            <h2>Activate Panic Mode</h2>
            <p>Are you sure you want to activate the panic mode?</p>
            <div class="warning-section">
                <i class="bx bx-error-circle"></i>
                <span>Warning</span>
                <p>This will trigger an alert to all users and emergency systems.</p>
            </div>
            <div class="button-group">
                <button id="confirm-continue">Continue</button>
                <button id="cancel-panic">Cancel</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="logoutModal">
        <div class="logout-modal">
            <p>⚠️ Are you sure you want to log out?</p>
            <div class="button-group">
                <button id="confirmLogoutBtn" style="background-color: #dc3545; color: white;">Yes, log me out</button>

                <button id="cancelLogoutBtn" style="background-color: #6c757d; color: white;">Cancel</button>

            </div>
        </div>
    </div>

    <div class="modal-overlay" id="employeeModal">
        <div class="modal-content">
            <span class="close-btn" onclick="document.getElementById('employeeModal').style.display='none'">&times;</span>
            <h2 id="modalTitle">Add Employee</h2>
            <form id="employee-form" onsubmit="return false;">
                <input type="text" placeholder="Employee Name" id="employeeName" required />
                <select name="Dept" id="empDept" required>
                    <option value="" selected disabled>Select Department</option>
                    <option value="HR">HR</option>
                    <option value="IT">IT</option>
                    <option value="Finance">Finance</option>
                    <option value="Security">Security</option>
                    <option value="Operations">Operations</option>
                </select>
                <input type="email" placeholder="Email" id="email" autocomplete="email" required />
                <input type="text" placeholder="Employee ID (Tag ID)" id="employeeId" required />
                <div class="form-buttons">
                    <button id="submitemployee" type="submit">Submit</button>
                    <button id="cancelemployee" type="button" onclick="document.getElementById('employeeModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // document.getElementById("addminButton").addEventListener("click", function () {
    //     window.location.href = "Dashboard.php";
    // });


    document.getElementById("viewadmin").addEventListener("click", function () {
        window.location.href = "Employee.php";
    });

    const employeeDataFromPHP = <?php echo json_encode($employees); ?>;

    let dummyEmployees = employeeDataFromPHP.map((emp) => ({
        id: emp.id ?? emp.employee_id,
        name: emp.name,
        department: emp.department,
        email: emp.email,
        empId: emp.employee_id,
        status: emp.status || "Unknown"
    }));

    let currentPage = 1;
    const rowsPerPage = 7;
    let selectedRow = null;
    let editingEmployee = null;

    function selectRow(row) {
        if (selectedRow) selectedRow.classList.remove("selected");
        selectedRow = row;
        row.classList.add("selected");
    }

    function renderTable(data, page) {
        const tbody = document.getElementById("employeeTableBody");
        tbody.innerHTML = "";
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const paginatedData = data.slice(start, end);

        paginatedData.forEach((emp) => {
            const row = document.createElement("tr");
            row.dataset.id = emp.id;

            const statusClass = emp.status.toLowerCase().includes("on") ? "status-on" : "status-off";

            row.innerHTML = `
                <td>${emp.name}</td>
                <td>${emp.department}</td>
                <td>${emp.email}</td>
                <td>${emp.empId}</td>
                <td><span class="status-badge ${statusClass}">${emp.status}</span></td>
            `;
            row.addEventListener("click", () => selectRow(row));
            tbody.appendChild(row);
        });

        updatePagination(data.length, page);
    }

    function updatePagination(totalItems, currentPage) {
        const totalPages = Math.ceil(totalItems / rowsPerPage);
        const paginationDiv = document.querySelector(".pagination");
        paginationDiv.innerHTML = "";

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement("button");
            btn.textContent = i;
            if (i === currentPage) btn.style.backgroundColor = "#A2B4D6";
            btn.onclick = () => {
                window.currentPage = i;
                renderTable(dummyEmployees, window.currentPage);
            };
            paginationDiv.appendChild(btn);
        }
    }

    function handleEdit() {
        if (!selectedRow) return alert("Select a row to edit.");
        const empId = selectedRow.cells[3].textContent;
        const emp = dummyEmployees.find((e) => e.empId === empId);
        if (!emp) return;

        editingEmployee = emp;
        document.getElementById("modalTitle").textContent = "Edit Employee";
        document.getElementById("employeeName").value = emp.name;
        document.getElementById("empDept").value = emp.department;
        document.getElementById("email").value = emp.email;
        document.getElementById("employeeId").value = emp.empId;
        document.getElementById("employeeId").disabled = true;
        document.getElementById("employeeModal").style.display = "flex";
    }

    function handleDelete() {
        if (!selectedRow) return alert("Select a row to delete.");
        if (!confirm("Are you sure you want to delete this employee?")) return;

        const empId = selectedRow.cells[3].textContent;
        fetch("Employee.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                action: "delete_employee",
                empId: empId
            })
        })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                dummyEmployees = dummyEmployees.filter((e) => e.empId !== empId);
                selectedRow.remove();
                selectedRow = null;
                renderTable(dummyEmployees, currentPage);
                alert("Employee deleted successfully!");
            } else {
                alert("Failed to delete employee: " + (data.error || "Unknown error"));
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            alert("Error deleting employee.");
        });
    }

    function filterAndSortEmployees() {
        const sortValue = document.getElementById("sortByStatus").value;
        let filteredEmployees = [...dummyEmployees];

        if (sortValue === "on-site") {
            filteredEmployees = filteredEmployees.filter((emp) =>
                emp.status.toLowerCase().includes("on")
            );
        } else if (sortValue === "off-site") {
            filteredEmployees = filteredEmployees.filter((emp) =>
                emp.status.toLowerCase().includes("off")
            );
        }

        if (sortValue === "name_asc") {
            filteredEmployees.sort((a, b) => a.name.localeCompare(b.name));
        } else if (sortValue === "name_desc") {
            filteredEmployees.sort((a, b) => b.name.localeCompare(a.name));
        } else if (sortValue === "department") {
            filteredEmployees.sort((a, b) => a.department.localeCompare(b.department));
        }

        currentPage = 1;
        renderTable(filteredEmployees, currentPage);
    }

    document.addEventListener("DOMContentLoaded", () => {
        document.getElementById("logoutButton").addEventListener("click", () => {
            document.getElementById("logoutModal").style.display = "flex";
        });

        document.getElementById("confirmLogoutBtn").addEventListener("click", () => {
            window.location.href = "https://atracker.lcstudio-incubate.co.za/";
        });

        document.getElementById("cancelLogoutBtn").addEventListener("click", () => {
            document.getElementById("logoutModal").style.display = "none";
        });

        document.getElementById("employeeButton").addEventListener("click", () => {
            editingEmployee = null;
            document.getElementById("modalTitle").textContent = "Add Employee";
            document.getElementById("employee-form").reset();
            document.getElementById("employeeId").disabled = false;
            document.getElementById("employeeModal").style.display = "flex";
        });

        document.getElementById("editEmployeeBtn").addEventListener("click", handleEdit);
        document.getElementById("deleteEmployeeBtn").addEventListener("click", handleDelete);

        document.getElementById("employee-form").addEventListener("submit", function (e) {
            e.preventDefault();
            const name = document.getElementById("employeeName").value.trim();
            const dept = document.getElementById("empDept").value;
            const email = document.getElementById("email").value.trim();
            const id = document.getElementById("employeeId").value.trim();

            if (!name || !dept || !email || !id) {
                return alert("Please fill in all fields.");
            }

            const action = editingEmployee ? "update_employee" : "add_employee";
            const requestData = editingEmployee
                ? { action, tag_id: id, name, department: dept, email }
                : { action, name, department: dept, email, employee_id: id };

            fetch("Employee.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(requestData)
            })
            .then((res) => res.json())
            .then((data) => {
                if (data.success) {
                    if (editingEmployee) {
                        const index = dummyEmployees.findIndex((e) => e.empId === editingEmployee.empId);
                        if (index > -1) {
                            dummyEmployees[index] = {
                                id,
                                name,
                                department: dept,
                                email,
                                empId: id,
                                status: dummyEmployees[index].status // Keep existing status
                            };
                        }
                        alert("Employee updated successfully!");
                    } else {
                        dummyEmployees.push({
                            id,
                            name,
                            department: dept,
                            email,
                            empId: id,
                            status: "Off Site"
                        });
                        currentPage = Math.ceil(dummyEmployees.length / rowsPerPage);
                        alert("Employee added successfully!");
                    }
                    renderTable(dummyEmployees, currentPage);
                    document.getElementById("employeeModal").style.display = "none";
                    this.reset();
                } else {
                    alert("Error: " + (data.error || "Unknown error occurred"));
                }
            })
            .catch((error) => {
                console.error("Error:", error);
                alert("Error processing request.");
            });
        });

        document.getElementById("searchBox").addEventListener("input", function () {
            const searchTerm = this.value.toLowerCase();
            const filtered = dummyEmployees.filter((emp) =>
                emp.name.toLowerCase().includes(searchTerm) ||
                emp.empId.toLowerCase().includes(searchTerm) ||
                emp.department.toLowerCase().includes(searchTerm) ||
                emp.email.toLowerCase().includes(searchTerm)
            );
            renderTable(filtered, 1);
        });

        document.getElementById("sortByStatus").addEventListener("change", filterAndSortEmployees);

        // Initial render
        renderTable(dummyEmployees, currentPage);

        // PANIC button 
       const panicButton = document.getElementById('panic-button');
    const panicConfirmationModal = document.getElementById('panic-confirmation-modal');
    const confirmContinueBtn = document.getElementById('confirm-continue');
    const cancelPanicBtn = document.getElementById('cancel-panic');

    panicButton?.addEventListener('click', () => {
      if (panicButton.classList.contains('off')) {
        panicConfirmationModal.style.display = 'flex';
      } else {
        panicButton.classList.remove('on');
        panicButton.classList.add('off');
        console.log('Panic Mode OFF');
      }
    });

    confirmContinueBtn?.addEventListener('click', () => {
      panicButton.classList.remove('off');
      panicButton.classList.add('on');
      panicConfirmationModal.style.display = 'none';
      console.log('Panic Mode ON');
      window.location.href = "Panic-page.php";
    });

    cancelPanicBtn?.addEventListener('click', () => {
      panicConfirmationModal.style.display = 'none';
    });
  });

    // ===== Export to Google Sheets (via proxy) =====
    document.getElementById("exportBtn")?.addEventListener("click", () => {
        console.log(":repeat: Export button clicked");
        fetch("https://atracker.lcstudio-incubate.co.za/public_html/getAllData.php")
            .then(res => {
                console.log(":satellite_antenna: Response Status:", res.status);
                if (!res.ok) throw new Error("Server responded with status: " + res.status);
                return res.json();
            })
            .then(data => {
                console.log(":white_check_mark: Fetched data:", data);
                if (!Array.isArray(data) || data.length === 0) {
                    alert("No data to export.");
                    return;
                }
                return fetch("https://atracker.lcstudio-incubate.co.za/public_html/proxyToSheets.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data)
                });
            })
            .then(res => res.text())
            .then(result => {
                console.log(":white_check_mark: Google Sheets response:", result);
                alert(":white_check_mark: Export successful: " + result);
            })
            .catch(err => {
                console.error(":x: Export failed:", err);
                alert(":x: Export failed: " + err.message);
            });
    });

    
    // Auto-refresh the page every 35 seconds
  setInterval(() => {
    location.reload();
  }, 35000);
</script>

</body>
</html>