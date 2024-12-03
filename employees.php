<?php
session_start(); // Start session for success/error messages
$title = "Employees";
require_once "./db.php";

// Handle search submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchSsn = trim($_POST['search']);

    // Validate SSN
    $sql = "SELECT Ssn FROM UW_EMPLOYEE WHERE Ssn = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $searchSsn);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        // Redirect to viewemployee.php with the SSN
        header("Location: viewemployee.php?ssn=" . urlencode($searchSsn));
        exit();
    } else {
        $_SESSION['error'] = "No employee found with the provided SSN.";
        $stmt->close();
        // Redirect back to the same page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle adding an employee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fname'])) {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $ssn = trim($_POST['ssn']);
    $bdate = trim($_POST['bdate']);
    $address = trim($_POST['address']);
    $salary = trim($_POST['salary']);
    $dno = trim($_POST['dno']);

    try {
        $sql = "INSERT INTO UW_EMPLOYEE (Fname, Lname, Ssn, Bdate, Address, Salary, Dno) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssdi", $fname, $lname, $ssn, $bdate, $address, $salary, $dno);
        $stmt->execute();
        $stmt->close();

        // Set success message in session
        $_SESSION['success'] = "Employee added successfully.";
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect to avoid resubmission
        exit();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry error (existing SSN)
            $_SESSION['error'] = "An employee with this SSN already exists. Please use a unique SSN.";
        } elseif ($e->getCode() == 1452) { // Dno not existing
            $_SESSION['error'] = "The specified department number does not exist. Please provide a valid department number.";
        } else {
            $_SESSION['error'] = "An unexpected error occurred. Please try again later.";
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch employees data
$query = "SELECT * FROM EmployeeDetails";
$result = $conn->query($query);

require_once "./templates/header.php";
?>
<div class="grid grid-cols-1 md:grid-cols-2 p-4 gap-6">
    <!-- Add Employee Form -->
    <div class="flex justify-center items-start">
        <div class="bg-base-100 p-6 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-lg font-bold mb-4 text-center">Add New Employee</h2>
            <!-- Display success message -->
            <?php if (isset($_SESSION['success'])): ?>
                <div role="alert" class="alert alert-success mb-4">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6 shrink-0 stroke-current"
                        fill="none"
                        viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><?php echo htmlspecialchars($_SESSION['success']); ?></span>
                </div>
                <?php unset($_SESSION['success']); // Clear message after displaying 
                ?>
            <?php endif; ?>
            <!-- Display error message -->
            <?php if (isset($_SESSION['error'])): ?>
                <div role="alert" class="alert alert-error mb-4">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6 shrink-0 stroke-current"
                        fill="none"
                        viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><?php echo htmlspecialchars($_SESSION['error']); ?></span>
                </div>
                <?php unset($_SESSION['error']); // Clear message after displaying 
                ?>
            <?php endif; ?>
            <form method="POST">
                <label class="form-control w-full mb-4">
                    <span class="label-text">First Name</span>
                    <input type="text" name="fname" placeholder="Enter first name" class="input input-bordered w-full" required maxlength="15" />
                </label>
                <label class="form-control w-full mb-4">
                    <span class="label-text">Last Name</span>
                    <input type="text" name="lname" placeholder="Enter last name" class="input input-bordered w-full" required maxlength="15" />
                </label>
                <label class="form-control w-full mb-4">
                    <span class="label-text">SSN</span>
                    <input type="text" name="ssn" placeholder="Enter SSN" class="input input-bordered w-full" required maxlength="9" />
                </label>
                <label class="form-control w-full mb-4">
                    <span class="label-text">Birth Date</span>
                    <input type="date" name="bdate" class="input input-bordered w-full" required />
                </label>
                <label class="form-control w-full mb-4">
                    <span class="label-text">Address</span>
                    <input type="text" name="address" placeholder="Enter address" class="input input-bordered w-full" required maxlength="30" />
                </label>
                <label class="form-control w-full mb-4">
                    <span class="label-text">Salary</span>
                    <input
                        type="number"
                        name="salary"
                        placeholder="Enter salary"
                        class="input input-bordered w-full"
                        required
                        min="0.01"
                        max="99999999.99"
                        step="0.01" />
                </label>
                <label class="form-control w-full mb-4">
                    <span class="label-text">Department Number</span>
                    <input type="number" name="dno" placeholder="Enter department number" class="input input-bordered w-full" required min="1" step="1" />
                </label>
                <button type="submit" class="btn btn-primary w-full">Add Employee</button>
            </form>
        </div>
    </div>
    <!-- Employee Table -->
    <div>
        <!-- Search Bar -->
        <form method="POST" class="flex justify-between mb-4">
            <label class="input input-bordered flex items-center gap-2 w-full">
                <input
                    type="text"
                    name="search"
                    class="grow"
                    placeholder="Search by SSN"
                    maxlength="9"
                    required />
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 16 16"
                    fill="currentColor"
                    class="h-5 w-5 opacity-70">
                    <path
                        fill-rule="evenodd"
                        d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z"
                        clip-rule="evenodd" />
                </svg>
            </label>
            <button type="submit" class="btn btn-primary ml-2">Search</button>
        </form>
        <div class="overflow-x-auto">
            <!-- Table -->
            <table class="table">
                <thead>
                    <tr>
                        <th class="whitespace-normal">#</th>
                        <th class="whitespace-normal">SSN</th>
                        <th class="whitespace-normal">First Name</th>
                        <th class="whitespace-normal">Last Name</th>
                        <th class="whitespace-normal">Birth Date</th>
                        <th class="whitespace-normal">Address</th>
                        <th class="whitespace-normal">Salary</th>
                        <th class="whitespace-normal">Supervisor SSN</th>
                        <th class="whitespace-normal">Department Number</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- directs each row to viewemployee.php with SSN as a query parameter -->
                    <?php if ($result->num_rows > 0): ?>
                        <?php $counter = 1; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover cursor-pointer" onclick="window.location.href='viewemployee.php?ssn=<?php echo urlencode($row['SSN']); ?>'">
                                <th><?php echo $counter++; ?></th>
                                <td><?php echo htmlspecialchars($row['SSN']); ?></td>
                                <td><?php echo htmlspecialchars($row['First Name']); ?></td>
                                <td><?php echo htmlspecialchars($row['Last Name']); ?></td>
                                <td><?php echo htmlspecialchars($row['Birth Date']); ?></td>
                                <td><?php echo htmlspecialchars($row['Address']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($row['Salary'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($row['Supervisor SSN']); ?></td>
                                <td><?php echo htmlspecialchars($row['Department Number']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No employees found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$conn->close();
require_once "./templates/footer.php";
?>