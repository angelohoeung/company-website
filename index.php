<?php
// load the title and header
$title = "Home";
require_once "./templates/header.php";
?>
<div class="hero">
    <div class="hero-content text-center">
        <div class="max-w-3xl">
            <h1 class="text-5xl font-bold">Welcome to our Company!</h1>
            <p class="py-6">
                Here, you can access information about our employees, projects, and departments, all in one place.
            </p>
        </div>
    </div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 px-4">
    <!-- Card 1 -->
    <div class="card bg-base-100 max-w-96 shadow-xl">
        <div class="card-body items-center text-center">
            <h2 class="card-title">See All Employees</h2>
            <p>View all current employees, add an employee, or update an employee.</p>
            <div class="card-actions">
                <a role="button" href="./employees.php" class="btn btn-primary">Explore</a>
            </div>
        </div>
    </div>
    <!-- Card 2 -->
    <div class="card bg-base-100 max-w-96 shadow-xl">
        <div class="card-body items-center text-center">
            <h2 class="card-title">See All Projects</h2>
            <p>View all current projects, add a project, or update a project.</p>
            <div class="card-actions">
                <a role="button" href="./projects.php" class="btn btn-primary">Explore</a>
            </div>
        </div>
    </div>
    <!-- Card 3 -->
    <div class="card bg-base-100 max-w-96 shadow-xl">
        <div class="card-body items-center text-center">
            <h2 class="card-title">See All Departments</h2>
            <p>View all current departments, add a department, or update a department.</p>
            <div class="card-actions">
                <a role="button" href="./departments.php" class="btn btn-primary">Explore</a>
            </div>
        </div>
    </div>
</div>
<?php
// load the footer
require_once "./templates/footer.php";
?>