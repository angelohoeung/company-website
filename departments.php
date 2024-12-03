<?php
session_start(); // Start session for success/error messages
$title = "Departments";
require_once "./db.php";

// Handle search submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchDnumber = trim($_POST['search']);

    // Validate department number
    $sql = "SELECT Dnumber FROM UW_DEPARTMENT WHERE Dnumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $searchDnumber);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        // Redirect to viewdepartment.php with the department number
        header("Location: viewdepartment.php?dnumber=" . urlencode($searchDnumber));
        exit();
    } else {
        $_SESSION['error'] = "No department found with the provided department number.";
        $stmt->close();
        // redirect to the same page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle adding a department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dname'])) {
    $dname = trim($_POST['dname']);
    $dnumber = trim($_POST['dnumber']);
    $mgr_ssn = trim($_POST['mgr_ssn']);
    $mgr_start_date = trim($_POST['mgr_start_date']);

    try {
        // Check if the manager SSN exists
        $checkManagerSql = "SELECT Ssn FROM UW_EMPLOYEE WHERE Ssn = ?";
        $checkManagerStmt = $conn->prepare($checkManagerSql);
        $checkManagerStmt->bind_param("s", $mgr_ssn);
        $checkManagerStmt->execute();
        $checkManagerResult = $checkManagerStmt->get_result();
        $checkManagerStmt->close();

        if ($checkManagerResult->num_rows === 0) {
            // Manager SSN does not exist
            $_SESSION['error'] = "The specified manager SSN does not exist. Please provide a valid SSN.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

        // Insert into UW_DEPARTMENT
        $sqlDept = "INSERT INTO UW_DEPARTMENT (Dname, Dnumber) VALUES (?, ?)";
        $stmtDept = $conn->prepare($sqlDept);
        $stmtDept->bind_param("si", $dname, $dnumber);
        $stmtDept->execute();
        $stmtDept->close();

        // Insert into UW_MANAGER
        $sqlManager = "INSERT INTO UW_MANAGER (Dnumber, Mgr_ssn, Mgr_start_date) VALUES (?, ?, ?)";
        $stmtManager = $conn->prepare($sqlManager);
        $stmtManager->bind_param("iss", $dnumber, $mgr_ssn, $mgr_start_date);
        $stmtManager->execute();
        $stmtManager->close();

        // Set success message in session
        $_SESSION['success'] = "Department added successfully.";
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect to avoid resubmission
        exit();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry error (existing department number or name)
            $_SESSION['error'] = "A department with this number or name already exists. Please use a unique number or name.";
        } else {
            $_SESSION['error'] = "An unexpected error occurred. Please try again later.";
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch departments data
$query = "
    SELECT 
        D.Dname AS `Department Name`, 
        D.Dnumber AS `Department Number`, 
        M.Mgr_ssn AS `Manager SSN`, 
        M.Mgr_start_date AS `Manager Start Date`
    FROM 
        UW_DEPARTMENT D
    LEFT JOIN 
        UW_MANAGER M ON D.Dnumber = M.Dnumber
";
$result = $conn->query($query);

require_once "./templates/header.php";
?>
<div class="grid grid-cols-1 md:grid-cols-2 p-4 gap-6">
    <!-- Add Department Form -->
    <div class="flex justify-center items-start">
        <div class="bg-base-100 p-6 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-lg font-bold mb-4 text-center">Add New Department</h2>

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
                    <span class="label-text">Department Name</span>
                    <input type="text" name="dname" placeholder="Enter department name" class="input input-bordered w-full" required maxlength="15" />
                </label>
                <label class="form-control w-full mb-4">
                    <span class="label-text">Department Number</span>
                    <input type="number" name="dnumber" placeholder="Enter department number" class="input input-bordered w-full" required min="1" step="1" />
                </label>
                <label class="form-control w-full mb-4">
                    <span class="label-text">Manager SSN</span>
                    <input type="text" name="mgr_ssn" placeholder="Enter manager SSN" class="input input-bordered w-full" required maxlength="9" />
                </label>
                <label class="form-control w-full mb-4">
                    <span class="label-text">Manager Start Date</span>
                    <input type="date" name="mgr_start_date" class="input input-bordered w-full" required />
                </label>
                <button type="submit" class="btn btn-primary w-full">Add Department</button>
            </form>
        </div>
    </div>
    <div>
        <!-- Search Bar -->
        <form method="POST" class="flex justify-between mb-4">
            <label class="input input-bordered flex items-center gap-2 w-full">
                <input
                    type="number"
                    name="search"
                    class="grow [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                    placeholder="Search by Department Number"
                    min="1"
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
        <!-- Departments Table -->
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th class="whitespace-normal">#</th>
                        <th class="whitespace-normal">Department Name</th>
                        <th class="whitespace-normal">Department Number</th>
                        <th class="whitespace-normal">Manager SSN</th>
                        <th class="whitespace-normal">Manager Start Date</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- directs each row to viewdepartment.php with the department number -->
                    <?php if ($result->num_rows > 0): ?>
                        <?php $counter = 1; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover cursor-pointer" onclick="window.location.href='viewdepartment.php?dnumber=<?php echo urlencode($row['Department Number']); ?>'">
                                <th><?php echo $counter++; ?></th>
                                <td><?php echo htmlspecialchars($row['Department Name']); ?></td>
                                <td><?php echo htmlspecialchars($row['Department Number']); ?></td>
                                <td><?php echo htmlspecialchars($row['Manager SSN']); ?></td>
                                <td><?php echo htmlspecialchars($row['Manager Start Date']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No departments found.</td>
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