<?php
$title = "View Employee";
require_once "./db.php";

if (!isset($_GET['ssn']) || empty($_GET['ssn'])) {
    die("No SSN provided.");
}

$ssn = $_GET['ssn'];

// Fetch employee data
$employeeSql = "SELECT 
                    Fname AS `First Name`, 
                    Minit AS `Middle Initial`, 
                    Lname AS `Last Name`, 
                    Ssn AS `SSN`, 
                    Bdate AS `Birth Date`, 
                    Address, 
                    Sex, 
                    Salary, 
                    Super_ssn AS `Supervisor SSN`, 
                    Dno AS `Department Number`
                FROM UW_EMPLOYEE 
                WHERE Ssn = ?";
$stmt = $conn->prepare($employeeSql);
$stmt->bind_param("s", $ssn);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$stmt->close();

if (!$employee) {
    die("Employee not found.");
}

// Fetch projects the employee works on
$projectSql = "SELECT 
                  P.Pname AS `Project Name`, 
                  P.Pnumber AS `Project Number`, 
                  P.Plocation AS `Location`, 
                  P.Dnum AS `Department Number`, 
                  W.Hours AS `Hours Worked`
               FROM UW_PROJECT P
               INNER JOIN UW_WORKS_ON W ON P.Pnumber = W.Pno
               WHERE W.Essn = ?";
$projectStmt = $conn->prepare($projectSql);
$projectStmt->bind_param("s", $ssn);
$projectStmt->execute();
$projects = $projectStmt->get_result();
$projectStmt->close();

// Fetch dependents of the employee
$dependentsSql = "SELECT 
                      Dependent_name AS `Name`, 
                      Relationship, 
                      Sex, 
                      Bdate AS `Birth Date`
                  FROM UW_DEPENDENT
                  WHERE Essn = ?";
$stmt = $conn->prepare($dependentsSql);
$stmt->bind_param("s", $ssn);
$stmt->execute();
$dependents = $stmt->get_result();
$stmt->close();
$conn->close();
require_once "./templates/header.php";
?>

<div class="container mx-auto p-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body items-center text-center">
                <!-- Avatar -->
                <div class="avatar mb-4">
                    <div class="w-72 rounded-full">
                        <img src="./images/profile.png" alt="Employee Avatar" />
                    </div>
                </div>
                <!-- Employee Information -->
                <h4 class="card-title text-lg font-bold">
                    <?php echo htmlspecialchars($employee['First Name'] . " " . $employee['Last Name']); ?>
                </h4>
                <p>
                    SSN: <?php echo htmlspecialchars($employee['SSN']); ?><br>
                    Address: <?php echo htmlspecialchars($employee['Address']); ?><br>
                    Department #: <?php echo htmlspecialchars($employee['Department Number']); ?>
                </p>
            </div>

            <!-- Dependents Section -->
            <div class="card-body">
                <h5 class="font-bold mb-2">Dependents</h5>
                <ul class="mt-2">
                    <?php if ($dependents->num_rows > 0): ?>
                        <?php while ($dependent = $dependents->fetch_assoc()): ?>
                            <li class="flex justify-between items-start px-4 py-2">
                                <div>
                                    <h6 class="font-bold"><?php echo htmlspecialchars($dependent['Name']); ?></h6>
                                    <span class="text-sm">
                                        <?php echo htmlspecialchars($dependent['Relationship']); ?> -
                                        <?php echo htmlspecialchars($dependent['Birth Date']); ?>
                                    </span>
                                </div>
                                <span class="badge badge-primary">
                                    <?php echo htmlspecialchars($dependent['Sex']); ?>
                                </span>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="text-center">No dependents found.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Employee Details -->
        <div class="card lg:col-span-2 bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="text-xl font-bold mb-4">Employee Details</h2>
                <div class="space-y-4">
                    <?php foreach ($employee as $key => $value): ?>
                        <div class="flex justify-between py-2 border-b border-gray-500">
                            <h6 class="font-bold"><?php echo htmlspecialchars($key); ?>:</h6>
                            <span><?php echo htmlspecialchars($value ?? ''); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Section -->
    <div class="mt-6">
        <h2 class="text-2xl font-bold mb-4">Projects</h2>
        <?php if ($projects->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php while ($project = $projects->fetch_assoc()): ?>
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body">
                            <h3 class="card-title text-lg font-bold"><?php echo htmlspecialchars($project['Project Name']); ?></h3>
                            <p class="text-sm">
                                Project #: <?php echo htmlspecialchars($project['Project Number']); ?><br>
                                Location: <?php echo htmlspecialchars($project['Location']); ?><br>
                                Department #: <?php echo htmlspecialchars($project['Department Number']); ?><br>
                                Hours Worked: <?php echo htmlspecialchars($project['Hours Worked']); ?>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-sm text-gray-500">This employee is not currently assigned to any projects.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once "./templates/footer.php"; ?>