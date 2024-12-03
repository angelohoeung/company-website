<?php
session_start();

$title = "View Department";
require_once "./db.php";

if (!isset($_GET['dnumber']) || empty($_GET['dnumber'])) {
    die("No Department Number provided.");
}

$dnumber = $_GET['dnumber'];

// Fetch department data
$departmentSql = "SELECT 
                      D.Dname AS `Department Name`, 
                      D.Dnumber AS `Department Number`
                  FROM UW_DEPARTMENT D
                  WHERE D.Dnumber = ?";
$stmt = $conn->prepare($departmentSql);
$stmt->bind_param("i", $dnumber);
$stmt->execute();
$departmentResult = $stmt->get_result();
$department = $departmentResult->fetch_assoc();
$stmt->close();

if (!$department) {
    die("Department not found.");
}

// Fetch manager details
$managerSql = "SELECT 
                   CONCAT(E.Fname, ' ', E.Lname) AS `Manager Name`, 
                   E.Ssn AS `Manager SSN`, 
                   M.Mgr_start_date AS `Start Date`
               FROM UW_MANAGER M
               INNER JOIN UW_EMPLOYEE E ON M.Mgr_ssn = E.Ssn
               WHERE M.Dnumber = ?";
$stmt = $conn->prepare($managerSql);
$stmt->bind_param("i", $dnumber);
$stmt->execute();
$managerResult = $stmt->get_result();
$manager = $managerResult->fetch_assoc();
$stmt->close();

// Fetch department locations
$locationsSql = "SELECT Dlocation 
                 FROM UW_DEPT_LOCATIONS 
                 WHERE Dnumber = ?";
$stmt = $conn->prepare($locationsSql);
$stmt->bind_param("i", $dnumber);
$stmt->execute();
$locationsResult = $stmt->get_result();
$locations = [];
while ($row = $locationsResult->fetch_assoc()) {
    $locations[] = $row['Dlocation'];
}
$stmt->close();

// Fetch employees not in this department
$unassignedEmployeesSql = "SELECT 
                               Ssn, 
                               CONCAT(Fname, ' ', Lname) AS `Full Name`
                           FROM UW_EMPLOYEE
                           WHERE Dno != ? OR Dno IS NULL";
$stmt = $conn->prepare($unassignedEmployeesSql);
$stmt->bind_param("i", $dnumber);
$stmt->execute();
$unassignedEmployees = $stmt->get_result();
$stmt->close();

// Handle adding an employee to the department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_ssn'])) {
    $addSsn = $_POST['add_ssn'];

    $updateSql = "CALL AddEmployeeToDepartment(?, ?)";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("si", $addSsn, $dnumber);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Employee successfully added to the department.";
    } else {
        $_SESSION['error'] = "Failed to add employee to the department.";
    }

    $stmt->close();

    // Redirect back to the same page
    header("Location: viewdepartment.php?dnumber=" . urlencode($dnumber));
    exit();
}

// Fetch employees in the department
$employeesSql = "SELECT 
                     Ssn AS `SSN`, 
                     CONCAT(Fname, ' ', Lname) AS `Full Name`, 
                     Address, 
                     Sex, 
                     Salary
                 FROM UW_EMPLOYEE
                 WHERE Dno = ?";
$stmt = $conn->prepare($employeesSql);
$stmt->bind_param("i", $dnumber);
$stmt->execute();
$employees = $stmt->get_result();
$stmt->close();
$conn->close();
require_once "./templates/header.php";
?>

<div class="container mx-auto p-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Department Details -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="text-xl font-bold mb-4">Department Details</h2>
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
                    <?php foreach ($department as $key => $value): ?>
                        <div class="flex justify-between py-2 border-b border-gray-500">
                            <h6 class="font-bold"><?php echo htmlspecialchars($key); ?>:</h6>
                            <span><?php echo htmlspecialchars($value ?? ''); ?></span>
                        </div>
                    <?php endforeach; ?>
                    <!-- Manager Details -->
                    <?php if ($manager): ?>
                        <div class="flex flex-col mt-4">
                            <h6 class="text-lg font-bold">Manager</h6>
                            <p>
                                Name: <?php echo htmlspecialchars($manager['Manager Name']); ?><br>
                                SSN: <?php echo htmlspecialchars($manager['Manager SSN']); ?><br>
                                Start Date: <?php echo htmlspecialchars($manager['Start Date']); ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="text-sm text-gray-500 mt-4">No manager assigned to this department.</div>
                    <?php endif; ?>
                    <!-- Display Locations -->
                    <div class="flex flex-col mt-4">
                        <h6 class="text-lg font-bold">Locations</h6>
                        <?php if (!empty($locations)): ?>
                            <ul class="list-disc list-inside">
                                <?php foreach ($locations as $location): ?>
                                    <li><?php echo htmlspecialchars($location); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">No location assigned to this department.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Add Employee to Department -->
                <form method="POST" class="mt-6">
                    <label class="form-control w-full mb-4">
                        <span class="label-text">Add Employee to Department</span>
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

        <!-- Employees in the Department -->
        <div class="lg:col-span-2">
            <h2 class="text-2xl font-bold mb-4">Employees in Department</h2>
            <?php if ($employees->num_rows > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php while ($employee = $employees->fetch_assoc()): ?>
                        <div class="card bg-base-100 shadow-xl">
                            <div class="card-body items-center text-center">
                                <h3 class="card-title text-lg font-bold"><?php echo htmlspecialchars($employee['Full Name']); ?></h3>
                                <p class="text-sm">
                                    SSN: <?php echo htmlspecialchars($employee['SSN']); ?><br>
                                    Address: <?php echo htmlspecialchars($employee['Address']); ?><br>
                                    Salary: <?php echo htmlspecialchars(number_format($employee['Salary'], 2)); ?>
                                </p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-gray-500">No employees are currently assigned to this department.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once "./templates/footer.php"; ?>