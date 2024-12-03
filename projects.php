<?php
session_start(); // Start session for success/error messages
$title = "Projects";
require_once "./db.php";

// Handle search submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchPnumber = trim($_POST['search']);

    // Validate project number
    $sql = "SELECT Pnumber FROM UW_PROJECT WHERE Pnumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $searchPnumber);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        // Redirect to viewproject.php with the project number
        header("Location: viewproject.php?pnumber=" . urlencode($searchPnumber));
        exit();
    } else {
        $_SESSION['error'] = "No project found with the provided project number.";
        $stmt->close();
        // redirect back to the same page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle adding a project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pname'])) {
    $pname = trim($_POST['pname']);
    $pnumber = trim($_POST['pnumber']);
    $plocation = trim($_POST['plocation']);
    $dnum = trim($_POST['dnum']);

    try {
        $sql = "INSERT INTO UW_PROJECT (Pname, Pnumber, Plocation, Dnum) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisi", $pname, $pnumber, $plocation, $dnum);
        $stmt->execute();
        $stmt->close();

        // Set success message in session
        $_SESSION['success'] = "Project added successfully.";
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect to avoid resubmission
        exit();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry error (existing project number or name)
            $_SESSION['error'] = "A project with this number or name already exists. Please use a unique number or name.";
        } elseif ($e->getCode() == 1452) { // Dnum not existing
            $_SESSION['error'] = "The specified department number does not exist. Please provide a valid department number.";
        } else {
            $_SESSION['error'] = "An unexpected error occurred. Please try again later.";
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch projects data
$query = "SELECT Pname AS `Project Name`, Pnumber AS `Project Number`, Plocation AS `Location`, Dnum AS `Department Number` 
          FROM UW_PROJECT";
$result = $conn->query($query);

require_once "./templates/header.php";
?>
<div class="grid grid-cols-1 md:grid-cols-2 p-4 gap-6">
    <!-- Add Project Form -->
    <div class="flex justify-center items-start">
        <div class="bg-base-100 p-6 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-lg font-bold mb-4 text-center">Add New Project</h2>

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
                    <span class="label-text">Project Name</span>
                    <input type="text" name="pname" placeholder="Enter project name" class="input input-bordered w-full" required maxlength="15" />
                </label>
                <label class="form-control w-full mb-4">
                    <span class="label-text">Project Number</span>
                    <input type="number" name="pnumber" placeholder="Enter project number" class="input input-bordered w-full" required min="1" step="1" />
                </label>
                <label class="form-control w-full mb-4">
                    <span class="label-text">Location</span>
                    <input type="text" name="plocation" placeholder="Enter location" class="input input-bordered w-full" required maxlength="15" />
                </label>
                <label class="form-control w-full mb-4">
                    <span class="label-text">Department Number</span>
                    <input type="number" name="dnum" placeholder="Enter department number" class="input input-bordered w-full" required min="1" step="1" />
                </label>
                <button type="submit" class="btn btn-primary w-full">Add Project</button>
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
                    placeholder="Search by Project Number"
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
        <!-- Projects Table -->
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th class="whitespace-normal">#</th>
                        <th class="whitespace-normal">Project Name</th>
                        <th class="whitespace-normal">Project Number</th>
                        <th class="whitespace-normal">Location</th>
                        <th class="whitespace-normal">Department Number</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- directs each row to viewproject.php with the project number as a query parameter -->
                    <?php if ($result->num_rows > 0): ?>
                        <?php $counter = 1; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover cursor-pointer" onclick="window.location.href='viewproject.php?pnumber=<?php echo urlencode($row['Project Number']); ?>'">
                                <th><?php echo $counter++; ?></th>
                                <td><?php echo htmlspecialchars($row['Project Name']); ?></td>
                                <td><?php echo htmlspecialchars($row['Project Number']); ?></td>
                                <td><?php echo htmlspecialchars($row['Location']); ?></td>
                                <td><?php echo htmlspecialchars($row['Department Number']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No projects found.</td>
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