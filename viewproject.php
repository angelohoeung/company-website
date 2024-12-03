<?php
session_start(); // Start the session

$title = "View Project";
require_once "./db.php";

if (!isset($_GET['pnumber']) || empty($_GET['pnumber'])) {
    die("No Project Number provided.");
}

$pnumber = $_GET['pnumber'];

// Fetch project data
$projectSql = "SELECT 
                   Pname AS `Project Name`, 
                   Pnumber AS `Project Number`, 
                   Plocation AS `Location`, 
                   Dnum AS `Department Number`
               FROM UW_PROJECT 
               WHERE Pnumber = ?";
$stmt = $conn->prepare($projectSql);
$stmt->bind_param("i", $pnumber);
$stmt->execute();
$projectResult = $stmt->get_result();
$project = $projectResult->fetch_assoc();
$stmt->close();

if (!$project) {
    die("Project not found.");
}

// Fetch employees assigned to the project
$employeesSql = "SELECT 
                     E.Ssn AS `SSN`, 
                     CONCAT(E.Fname, ' ', E.Lname) AS `Full Name`, 
                     E.Address AS `Address`, 
                     W.Hours AS `Hours Worked`
                 FROM UW_EMPLOYEE E
                 INNER JOIN UW_WORKS_ON W ON E.Ssn = W.Essn
                 WHERE W.Pno = ?";
$stmt = $conn->prepare($employeesSql);
$stmt->bind_param("i", $pnumber);
$stmt->execute();
$employees = $stmt->get_result();
$stmt->close();

// Fetch employees not assigned to the project
$unassignedSql = "SELECT 
                      Ssn, 
                      CONCAT(Fname, ' ', Lname) AS `Full Name`
                  FROM UW_EMPLOYEE
                  WHERE Ssn NOT IN (SELECT Essn FROM UW_WORKS_ON WHERE Pno = ?)";
$stmt = $conn->prepare($unassignedSql);
$stmt->bind_param("i", $pnumber);
$stmt->execute();
$unassignedEmployees = $stmt->get_result();
$stmt->close();

// Handle adding an employee to the project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_ssn'])) {
    $addSsn = $_POST['add_ssn'];

    $addSql = "INSERT INTO UW_WORKS_ON (Essn, Pno, Hours) VALUES (?, ?, 0)";
    $stmt = $conn->prepare($addSql);
    $stmt->bind_param("si", $addSsn, $pnumber);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Employee assigned to the project successfully.";
    } else {
        $_SESSION['error'] = "Failed to assign employee to the project.";
    }

    $stmt->close();

    // Redirect back to the same page
    header("Location: viewproject.php?pnumber=" . urlencode($pnumber));
    exit();
}

// Handle removing an employee from the project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_ssn'])) {
    $removeSsn = $_POST['remove_ssn'];

    $deleteSql = "DELETE FROM UW_WORKS_ON WHERE Essn = ? AND Pno = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("si", $removeSsn, $pnumber);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Employee removed from the project successfully.";
    } else {
        $_SESSION['error'] = "Failed to remove the employee from the project.";
    }

    $stmt->close();

    // Redirect back to the same page
    header("Location: viewproject.php?pnumber=" . urlencode($pnumber));
    exit();
}
$conn->close();
require_once "./templates/header.php";
?>

<div class="container mx-auto p-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Project Details -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="text-xl font-bold mb-4">Project Details</h2>
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
                <div class="space-y-4">
                    <?php foreach ($project as $key => $value): ?>
                        <div class="flex justify-between py-2 border-b border-gray-500">
                            <h6 class="font-bold"><?php echo htmlspecialchars($key); ?>:</h6>
                            <span><?php echo htmlspecialchars($value ?? ''); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Add Employee Dropdown -->
                <form method="POST" class="mt-6">
                    <label class="form-control w-full mb-4">
                        <span class="label-text">Add Employee to Project</span>
                        <select name="add_ssn" class="select select-bordered w-full" required>
                            <option value="" disabled selected>Select an Employee</option>
                            <?php while ($employee = $unassignedEmployees->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($employee['Ssn']); ?>">
                                    <?php echo htmlspecialchars($employee['Full Name'] . " (SSN: " . $employee['Ssn'] . ")"); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </label>
                    <button type="submit" class="btn btn-primary w-full">Add Employee</button>
                </form>
            </div>
        </div>

        <!-- Employees Assigned to the Project -->
        <div class="lg:col-span-2">
            <h2 class="text-2xl font-bold mb-4">Assigned Employees</h2>
            <?php if ($employees->num_rows > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php while ($employee = $employees->fetch_assoc()): ?>
                        <div class="card bg-base-100 shadow-xl">
                            <div class="card-body items-center text-center">
                                <h3 class="card-title text-lg font-bold"><?php echo htmlspecialchars($employee['Full Name']); ?></h3>
                                <p class="text-sm">
                                    SSN: <?php echo htmlspecialchars($employee['SSN']); ?><br>
                                    Address: <?php echo htmlspecialchars($employee['Address']); ?><br>
                                    Hours Worked: <?php echo htmlspecialchars($employee['Hours Worked']); ?>
                                </p>
                                <form method="POST" class="mt-4">
                                    <input type="hidden" name="remove_ssn" value="<?php echo htmlspecialchars($employee['SSN']); ?>">
                                    <button type="submit" class="btn btn-error w-full">Remove</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-gray-500">No employees are currently assigned to this project.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once "./templates/footer.php"; ?>