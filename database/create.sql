CREATE DATABASE IF NOT EXISTS hoeung_phase3;
USE hoeung_phase3;

-- Creating Department Table
CREATE TABLE IF NOT EXISTS UW_DEPARTMENT (
    Dname VARCHAR(15) NOT NULL UNIQUE,
    Dnumber INT NOT NULL PRIMARY KEY
);

-- Creating Employee Table
CREATE TABLE IF NOT EXISTS UW_EMPLOYEE (
    Fname VARCHAR(15) NOT NULL,
    Minit CHAR(1),
    Lname VARCHAR(15) NOT NULL,
    Ssn CHAR(9) NOT NULL PRIMARY KEY,
    Bdate DATE,
    Address VARCHAR(30),
    Sex CHAR(1),
    Salary DECIMAL(10, 2),
    Super_ssn CHAR(9),
    Dno INT NOT NULL,
    FOREIGN KEY (Dno) REFERENCES UW_DEPARTMENT(Dnumber),
    INDEX idx_employee_dno (Dno), -- For filtering by department
    INDEX idx_super_ssn (Super_ssn) -- For managing supervisor relationships
);

-- Creating Manager Relationship Table
CREATE TABLE IF NOT EXISTS UW_MANAGER (
    Dnumber INT NOT NULL PRIMARY KEY,
    Mgr_ssn CHAR(9) NOT NULL,
    Mgr_start_date DATE,
    FOREIGN KEY (Dnumber) REFERENCES UW_DEPARTMENT(Dnumber),
    FOREIGN KEY (Mgr_ssn) REFERENCES UW_EMPLOYEE(Ssn),
    INDEX idx_manager_mgr_ssn (Mgr_ssn) -- For lookup of manager's Ssn
);

-- Creating Dept_Locations Table
CREATE TABLE IF NOT EXISTS UW_DEPT_LOCATIONS (
    Dnumber INT NOT NULL,
    Dlocation VARCHAR(15) NOT NULL,
    PRIMARY KEY (Dnumber, Dlocation),
    FOREIGN KEY (Dnumber) REFERENCES UW_DEPARTMENT(Dnumber),
    INDEX idx_dept_locations_dnumber (Dnumber) -- For filtering locations by department
);

-- Creating Project Table
CREATE TABLE IF NOT EXISTS UW_PROJECT (
    Pname VARCHAR(15) NOT NULL UNIQUE,
    Pnumber INT NOT NULL PRIMARY KEY,
    Plocation VARCHAR(15),
    Dnum INT NOT NULL,
    FOREIGN KEY (Dnum) REFERENCES UW_DEPARTMENT(Dnumber),
    INDEX idx_project_dnum (Dnum), -- For filtering projects by department
    INDEX idx_project_plocation (Plocation) -- For filtering projects by location
);

-- Creating Works_On Table
CREATE TABLE IF NOT EXISTS UW_WORKS_ON (
    Essn CHAR(9) NOT NULL,
    Pno INT NOT NULL,
    Hours DECIMAL(3,1) NOT NULL,
    PRIMARY KEY (Essn, Pno),
    FOREIGN KEY (Essn) REFERENCES UW_EMPLOYEE(Ssn),
    FOREIGN KEY (Pno) REFERENCES UW_PROJECT(Pnumber),
    INDEX idx_works_on_essn (Essn), -- For filtering by employee
    INDEX idx_works_on_pno (Pno) -- For filtering by project
);

-- Creating Dependent Table
CREATE TABLE IF NOT EXISTS UW_DEPENDENT (
    Essn CHAR(9) NOT NULL,
    Dependent_name VARCHAR(15) NOT NULL,
    Sex CHAR(1),
    Bdate DATE,
    Relationship VARCHAR(8),
    PRIMARY KEY (Essn, Dependent_name),
    FOREIGN KEY (Essn) REFERENCES UW_EMPLOYEE(Ssn),
    INDEX idx_dependent_essn (Essn) -- For filtering dependents by employee
);

-- Altering UW_EMPLOYEE to add Super_ssn foreign key constraint
ALTER TABLE UW_EMPLOYEE
ADD CONSTRAINT fk_super_ssn
FOREIGN KEY (Super_ssn)
REFERENCES UW_EMPLOYEE(Ssn);

-- View for Employee Details
CREATE OR REPLACE VIEW EmployeeDetails AS
SELECT 
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
FROM UW_EMPLOYEE;

-- Procedure to Add an Employee to a Department
DELIMITER $$

CREATE PROCEDURE AddEmployeeToDepartment (
    IN emp_ssn CHAR(9),
    IN dept_number INT
)
BEGIN
    -- Update the employee's department
    UPDATE UW_EMPLOYEE
    SET Dno = dept_number
    WHERE Ssn = emp_ssn;

    -- Check if the update was successful
    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Failed to update employee department. Employee may not exist.';
    END IF;
END$$

DELIMITER ;
