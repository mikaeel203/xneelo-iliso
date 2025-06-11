// ==========================================
// 🎩 Magical Employee Dashboard Engine v1.0 🎩
// WARNING: Contains traces of logic and mystery.
// ==========================================

// ~~~ CONTROL CENTER: DOM War Room ~~~
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const profileButton = document.getElementById('profileButton');
const userInfoDropdown = document.getElementById('userInfoDropdown');
const adminModal = document.getElementById('adminModal');
const employeeModal = document.getElementById('employeeModal');
const logoutModal = document.getElementById('logoutModal');
const submitadminButton = document.getElementById('submitadmin');
const canceladminButton = document.getElementById('canceladmin');
const submitemployeeButton = document.getElementById('submitemployee');
const cancelemployeeButton = document.getElementById('cancelemployee');
const employeeTableBody = document.getElementById('employeeTableBody');
const pagination = document.getElementById('pagination');
const searchBox = document.getElementById('searchBox');
const sortByStatus = document.getElementById('sortByStatus');
const dataInfo = document.getElementById('dataInfo');
const loginForm = document.getElementById('login-form'); // Login form
const errorMessageElement = document.getElementById('error-message'); // Error message display

// ~~~ BACKSTAGE: Tracking the Unsuspecting ~~~
let allEmployees = [];
let currentPage = 1;
const pageSize = 5;

// ~~~ OPERATION: Slide the Sidebar (Shhh!) ~~~
menuToggle.addEventListener('click', () => {
    sidebar.classList.toggle('mobile-open');  // Click to reveal classified material.
});

// ~~~ PROFILE POP-UP TRAP ~~~
profileButton.addEventListener('click', () => {
    const isVisible = userInfoDropdown.style.display === 'block';
    userInfoDropdown.style.display = isVisible ? 'none' : 'block';
});

// ~~~ NINJA MOVE: Click Outside to Vanish ~~~
document.addEventListener('click', (e) => {
    if (!profileButton.contains(e.target) && !userInfoDropdown.contains(e.target)) {
        userInfoDropdown.style.display = 'none';
    }
});

// ~~~ THE SACRED BUTTON RITUALS ~~~
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', (e) => {
        if (e.target.textContent.trim() === 'Add New Admin') adminModal.style.display = 'flex';
        if (e.target.textContent.trim() === 'Add New Employee') employeeModal.style.display = 'flex';
        if (e.target.textContent.trim() === 'Log-Out') logoutModal.style.display = 'flex';
    });
});

// ~~~ I DO... OR DO I? Modal Love Stories ~~~
submitadminButton.addEventListener('click', () => adminModal.style.display = 'none');
canceladminButton.addEventListener('click', () => adminModal.style.display = 'none');
// submitemployeeButton.addEventListener('click', () => employeeModal.style.display = 'none'); // REMOVED - Handled below
cancelemployeeButton.addEventListener('click', () => employeeModal.style.display = 'none');

// ~~~ DATA FETCHED FROM THE MATRIX ~~~
fetch('./assets/employees_data.json')
    .then(res => res.json())
    .then(data => {
        allEmployees = data.allEmployees || [];
        renderEmployeeTable(); // May or may not summon unicorns.
    })
    .catch(err => console.error('Error loading employees:', err));

// ~~~ THE GREAT TABLE SUMMONING ~~~
function renderEmployeeTable() {
    employeeTableBody.innerHTML = ''; // Goodbye old truths.

    const searchTerm = searchBox.value.toLowerCase();
    const selectedStatus = sortByStatus.value;

    const filteredEmployees = allEmployees.filter(emp => {
        const matchSearch = emp.name.toLowerCase().includes(searchTerm)
            || emp.department.toLowerCase().includes(searchTerm)
            || emp.email.toLowerCase().includes(searchTerm)
            || emp.id.toLowerCase().includes(searchTerm);
        const matchStatus = selectedStatus === 'all' || emp.status === selectedStatus;
        return matchSearch && matchStatus;
    });

    const start = (currentPage - 1) * pageSize;
    const paginated = filteredEmployees.slice(start, start + pageSize);

    paginated.forEach(emp => {
        const row = document.createElement('tr');
        row.innerHTML = `
      <td>${emp.name}</td>
      <td>${emp.department}</td>
      <td>${emp.time}</td>
      <td>${emp.email}</td>
      <td>${emp.id}</td>
      <td><button class="${emp.status === 'On Site' ? 'onsite' : 'offsite'}">${emp.status}</button></td>
    `;
        employeeTableBody.appendChild(row);
    });

    updateDataInfo(filteredEmployees.length);
    updatePagination(filteredEmployees.length);
}

// ~~~ STAT BOARD: Probably Accurate ~~~
function updateDataInfo(total) {
    const startEntry = (currentPage - 1) * pageSize + 1;
    const endEntry = Math.min(currentPage * pageSize, total);
    dataInfo.textContent = `Showing data ${startEntry} to ${endEntry} of ${total} entries`;
}

// ~~~ THE PAGINATION CIRCUS 🎪 ~~~
function updatePagination(total) {
    pagination.innerHTML = '';
    const totalPages = Math.ceil(total / pageSize);

    const prev = document.createElement('a');
    prev.textContent = 'Previous';
    prev.href = '#';
    prev.className = currentPage === 1 ? 'disabled' : '';
    prev.addEventListener('click', (e) => {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            renderEmployeeTable();
        }
    });
    pagination.appendChild(prev);

    for (let i = 1; i <= totalPages; i++) {
        const page = document.createElement('a');
        page.textContent = i;
        page.href = '#';
        page.className = i === currentPage ? 'active' : '';
        page.addEventListener('click', (e) => {
            e.preventDefault();
            currentPage = i;
            renderEmployeeTable();
        });
        pagination.appendChild(page);
    }

    const next = document.createElement('a');
    next.textContent = 'Next';
    next.href = '#';
    next.className = currentPage === totalPages ? 'disabled' : '';
    next.addEventListener('click', (e) => {
        e.preventDefault();
        if (currentPage < totalPages) {
            currentPage++;
            renderEmployeeTable();
        }
    });
    pagination.appendChild(next);
}

// ~~~ THE FILTERED REBELLION ~~~
searchBox.addEventListener('input', () => {
    currentPage = 1;
    renderEmployeeTable();
});

sortByStatus.addEventListener('change', () => {
    currentPage = 1;
    renderEmployeeTable();
});

// ~~~ ESCAPE ROUTE: Logout Drama ~~~
document.addEventListener("DOMContentLoaded", () => {
    const logoutButton = document.getElementById("logoutButton");
    const logoutModal = document.getElementById("logoutModal");
    const confirmLogoutBtn = document.getElementById("confirmLogoutBtn");
    const cancelLogoutBtn = document.getElementById("cancelLogoutBtn");

    if (logoutButton) {
        logoutButton.addEventListener("click", () => {
            logoutModal.style.display = "flex";
        });
    }

    if (confirmLogoutBtn) {
        confirmLogoutBtn.addEventListener("click", () => {
            logoutModal.innerHTML = `
        <div class="logout-modal">
          <p>✅ Logged out successfully!</p>
        </div>
      `;
            setTimeout(() => {
                window.location.href = "login.html";
            }, 2000);
        });
    }

    if (cancelLogoutBtn) {
        cancelLogoutBtn.addEventListener("click", () => {
            logoutModal.style.display = "none";
        });
    }
});

// ~~~ METRICS THAT MAY OR MAY NOT BE WATCHING YOU ~~~
const counts = {
    onSite: 0,
    offSite: 0,
    unmarked: 0,
};

function updateCount(type) {
    if (counts[type] !== undefined) {
        counts[type]++;
        document.getElementById(`${type}Count`).textContent = counts[type];
    }
}

// ~~~ ADD EMPLOYEE FUNCTIONALITY (INTEGRATED WITH PHP) ~~~
submitemployeeButton.addEventListener('click', (event) => {
    event.preventDefault(); // Prevent the default form submission
    employeeModal.style.display = 'none';

    // Get the values from the input fields
    const employeeName = document.getElementById('employeeName').value;
    const empDept = document.getElementById('empDept').value;
    const email = document.getElementById('email').value;
    const employeeId = document.getElementById('employeeId').value;

    // Basic validation (you should add more robust validation)
    if (!employeeName || !empDept || !email || !employeeId) {
        alert('Please fill in all fields.');
        return;
    }

    // Create an object with the employee data
    const employeeData = {
        name: employeeName,
        department: empDept,
        email: email,
        employeeId: employeeId
    };

    // Send the data to the PHP script using fetch
    fetch('add_employee.php', { // Replace 'add_employee.php' with the actual path to your PHP script
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(employeeData)
    })
        .then(response => response.json()) // Parse the JSON response from PHP
        .then(data => {
            if (data.success) {
                // Add the new employee to the allEmployees array
                allEmployees.push({
                    name: employeeName,
                    department: empDept,
                    email: email,
                    id: employeeId,
                    status: 'Unmarked', // Or whatever default status you want
                    time: new Date().toLocaleTimeString() // Or however you want to format the time
                });

                // Re-render the employee table to include the new employee
                renderEmployeeTable();

                // Optionally clear the form fields
                document.getElementById('employeeName').value = '';
                document.getElementById('empDept').value = '';
                document.getElementById('email').value = '';
                document.getElementById('employeeId').value = '';

                alert('Employee added successfully!');
            } else {
                alert('Error adding employee: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding the employee.');
        });
});
