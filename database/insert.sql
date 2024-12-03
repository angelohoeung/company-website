INSERT INTO UW_DEPARTMENT (Dname, Dnumber) VALUES ('Research', 1);
INSERT INTO UW_DEPARTMENT (Dname, Dnumber) VALUES ('Engineering', 2);
INSERT INTO UW_DEPARTMENT (Dname, Dnumber) VALUES ('Marketing', 3);
INSERT INTO UW_DEPARTMENT (Dname, Dnumber) VALUES ('Sales', 4);

INSERT INTO UW_EMPLOYEE (Fname, Minit, Lname, Ssn, Bdate, Address, Sex, Salary, Super_ssn, Dno)
VALUES ('John', 'A', 'Doe', '123456789', '1970-01-15', '123 Elm St', 'M', 60000.00, NULL, 1);
INSERT INTO UW_EMPLOYEE (Fname, Minit, Lname, Ssn, Bdate, Address, Sex, Salary, Super_ssn, Dno)
VALUES ('Jane', 'B', 'Smith', '987654321', '1985-05-12', '456 Oak St', 'F', 50000.00, '123456789', 1);
INSERT INTO UW_EMPLOYEE (Fname, Minit, Lname, Ssn, Bdate, Address, Sex, Salary, Super_ssn, Dno)
VALUES ('Alice', 'C', 'Johnson', '234567890', '1980-03-14', '789 Pine St', 'F', 45000.00, '987654321', 1);
INSERT INTO UW_EMPLOYEE (Fname, Minit, Lname, Ssn, Bdate, Address, Sex, Salary, Super_ssn, Dno)
VALUES ('Bob', 'D', 'Brown', '345678901', '1992-11-23', '123 Maple St', 'M', 70000.00, NULL, 2);
INSERT INTO UW_EMPLOYEE (Fname, Minit, Lname, Ssn, Bdate, Address, Sex, Salary, Super_ssn, Dno)
VALUES ('Catherine', 'E', 'Davis', '456789012', '1988-06-30', '456 Birch St', 'F', 55000.00, '345678901', 2);
INSERT INTO UW_EMPLOYEE (Fname, Minit, Lname, Ssn, Bdate, Address, Sex, Salary, Super_ssn, Dno)
VALUES ('David', 'F', 'Miller', '567890123', '1990-09-18', '890 Spruce St', 'M', 80000.00, NULL, 3);

INSERT INTO UW_MANAGER (Dnumber, Mgr_ssn, Mgr_start_date) VALUES (1, '123456789', '2020-01-01');
INSERT INTO UW_MANAGER (Dnumber, Mgr_ssn, Mgr_start_date) VALUES (2, '345678901', '2019-07-15');
INSERT INTO UW_MANAGER (Dnumber, Mgr_ssn, Mgr_start_date) VALUES (3, '567890123', '2021-03-20');

INSERT INTO UW_DEPT_LOCATIONS (Dnumber, Dlocation) VALUES (1, 'New York');
INSERT INTO UW_DEPT_LOCATIONS (Dnumber, Dlocation) VALUES (1, 'Boston');
INSERT INTO UW_DEPT_LOCATIONS (Dnumber, Dlocation) VALUES (2, 'San Francisco');
INSERT INTO UW_DEPT_LOCATIONS (Dnumber, Dlocation) VALUES (3, 'Los Angeles');

INSERT INTO UW_PROJECT (Pname, Pnumber, Plocation, Dnum) VALUES ('Project X', 101, 'New York', 1);
INSERT INTO UW_PROJECT (Pname, Pnumber, Plocation, Dnum) VALUES ('Project Y', 102, 'San Francisco', 2);
INSERT INTO UW_PROJECT (Pname, Pnumber, Plocation, Dnum) VALUES ('Project Z', 103, 'Los Angeles', 3);
INSERT INTO UW_PROJECT (Pname, Pnumber, Plocation, Dnum) VALUES ('Project Alpha', 104, 'Boston', 1);

INSERT INTO UW_WORKS_ON (Essn, Pno, Hours) VALUES ('123456789', 101, 20.0);
INSERT INTO UW_WORKS_ON (Essn, Pno, Hours) VALUES ('234567890', 101, 15.0);
INSERT INTO UW_WORKS_ON (Essn, Pno, Hours) VALUES ('345678901', 102, 25.0);
INSERT INTO UW_WORKS_ON (Essn, Pno, Hours) VALUES ('456789012', 103, 30.0);
INSERT INTO UW_WORKS_ON (Essn, Pno, Hours) VALUES ('567890123', 104, 35.0);
INSERT INTO UW_WORKS_ON (Essn, Pno, Hours) VALUES ('123456789', 104, 10.0);

INSERT INTO UW_DEPENDENT (Essn, Dependent_name, Sex, Bdate, Relationship) VALUES ('123456789', 'Anna', 'F', '2010-08-20', 'Daughter');
INSERT INTO UW_DEPENDENT (Essn, Dependent_name, Sex, Bdate, Relationship) VALUES ('234567890', 'Tom', 'M', '2015-04-10', 'Son');
INSERT INTO UW_DEPENDENT (Essn, Dependent_name, Sex, Bdate, Relationship) VALUES ('345678901', 'Emily', 'F', '2018-01-25', 'Daughter');
INSERT INTO UW_DEPENDENT (Essn, Dependent_name, Sex, Bdate, Relationship) VALUES ('456789012', 'Michael', 'M', '2012-12-15', 'Son');
INSERT INTO UW_DEPENDENT (Essn, Dependent_name, Sex, Bdate, Relationship) VALUES ('567890123', 'Olivia', 'F', '2020-07-05', 'Daughter');
INSERT INTO UW_DEPENDENT (Essn, Dependent_name, Sex, Bdate, Relationship) VALUES ('987654321', 'Sophia', 'F', '2013-11-09', 'Daughter');
