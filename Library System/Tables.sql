drop table borrowerType cascade constraints;
drop table borrower cascade constraints;
drop table book cascade constraints;
drop table hasAuthor cascade constraints;
drop table hasSubject cascade constraints;
drop table bookCopy cascade constraints;
drop sequence seq_hid;
drop table holdRequest cascade constraints;
drop table borrowing cascade constraints;
drop table fine cascade constraints;
drop sequence seq_fid;

CREATE TABLE borrowerType
	(type varchar(7) not null,
	bookTimeLimit smallInt,					
	PRIMARY KEY (type));

CREATE TABLE borrower
	(bid char(10) not null,						
	password varchar2(15),
	bname varchar2(30),
	address varchar2(100),
	phone char(10),
	emailAddress varchar2(30),
	sinOrStNo char(9),
	expiryDate date,
	type varchar(7) not null,
	PRIMARY KEY (bid),
	FOREIGN KEY (type) references borrowerType);

CREATE TABLE book
	(callNumber varchar2(30) not null,
	isbn char(13),
	title varchar2(80),
	mainAuthor varchar2(30),
	publisher varchar2(30),
	year smallInt,
	PRIMARY KEY (callNumber));

CREATE TABLE hasAuthor
	(callNumber varchar2(30) not null,
	aname varchar2(100) not null,
	PRIMARY KEY (callNumber, aname),
	FOREIGN KEY (callNumber) references book ON DELETE CASCADE);

CREATE TABLE hasSubject
	(callNumber varchar2(30) not null,
	subject varchar2(100) not null,
	PRIMARY KEY (callNumber, subject),
	FOREIGN KEY (callNumber) references book ON DELETE CASCADE);
	
CREATE TABLE bookCopy
	(callNumber varchar2(30) not null,
	copyNo varchar2(3) not null,
	status varchar2(7) not null,
	PRIMARY KEY (callNumber, copyNo),
	FOREIGN KEY (callNumber) references book ON DELETE CASCADE);

CREATE SEQUENCE seq_hid MINVALUE 1 START WITH 1 INCREMENT BY 1 CACHE 10;

CREATE TABLE holdRequest
	(hid int not NULL,			
	bid char(10) not null,
	callNumber varchar2(30) not null,
	issuedDate date,
	PRIMARY KEY (hid),
	FOREIGN KEY (bid) references borrower ON DELETE CASCADE,
	FOREIGN KEY (callNumber) references book ON DELETE CASCADE);

CREATE TABLE borrowing
	(borid char(10) not null,
	bid char(10) not null,
	callNumber varchar2(30) not null,
	copyNo varchar2(3) not null,
	outDate date,
	inDate date,
	PRIMARY KEY (borid),
	FOREIGN KEY (bid) references borrower,
	FOREIGN KEY (callNumber, copyNo) references bookCopy);

CREATE TABLE fine
	(fid int not null,
	amount decimal(5,2),
	issuedDate date,
	paidDate date,
	borid char(10) not null,
	PRIMARY KEY (fid),
	FOREIGN KEY (borid) references borrowing);

CREATE SEQUENCE seq_fid MINVALUE 1 START WITH 1 INCREMENT BY 1 CACHE 10;


/* borrowerType(type, bookTimeLimit) : 3 unique types */

--student : 2 weeks
INSERT INTO borrowerType
VALUES ('Student', 14);

--faculty : 12 weeks
INSERT INTO borrowerType
VALUES ('Faculty', 84);

--staff : 6 weeks
INSERT INTO borrowerType
VALUES ('Staff', 42);


/* borrower(bid, password, bname, address, phone, emailAddress, sinOrStNo, expiryDate, type) */

-- --student : student #
INSERT INTO borrower
VALUES (3414311864, '1N1dBs7Ym04', 'James Dagenious', 
	'1003 - 2205 Lower Mall, Vancouver, BC, V6T 2G9', 71411120, 
	'jamesdagenious@gmail.com', 920654327, TO_DATE('2014-04-30','YYYY-MM-DD'), 
	'Student');

INSERT INTO borrower
VALUES (1209082663, 'qX60s1b9ul318k', 'Bugsy Peanuckle', 
	'902 - 6335 Thunderbird Crescent, Vancouver, BC, VP2 1MZ', 65430130, 
	'bugsypeanuckle@gmail.com', 864356877, TO_DATE('2014-04-30','YYYY-MM-DD'), 
	'Student');

--faculty : sin #
INSERT INTO borrower
VALUES (5406073649, 'Xc1L92389574', 'Navi Lalalala', 
	'4625 103 Street, Edmonton, Alberta, T5A 2G9', 7804251876, 
	'navilalalala@hotmail.com', 654773456, TO_DATE('2020-01-01','YYYY-MM-DD'), 
	'Faculty');

--staff : sin #
INSERT INTO borrower
VALUES (3834271354, 'r15T0206iooT1', 'Bourbon BonBon', 
	'1734 - 1234 Main Street, Vancovuer, BC, V9H 7P4', 6045239665, 
	'bourbonbonbon@yahoo.ca', 734567298, TO_DATE('2015-08-31','YYYY-MM-DD'), 
	'Staff');

INSERT INTO borrower
VALUES (6123650021, 'o5pGZ8tI850yQv7', 'Brittany Thebomb', 
	'1735 Broadway, Vancouver, BC, V7H 2H9', 7782298746, 
	'brittanythebomb@gmail.com', 638444863, TO_DATE('2015-08-31','YYYY-MM-DD'), 
	'Staff');


/* book(callNumber, isbn, title, mainAuthor, publisher, year) */

INSERT INTO book
VALUES ('QA74.79 J12 2001', 9827436328749, 'Database Engineering', 
'Jana Studiesalot', 'Skyrim Publishing', 2001);

INSERT INTO book
VALUES ('QB72.59 J74 2013', 7820193406337, 'Road Design and Construction', 
'Jim Mugababones', 'Oreo Inc.', 2013);

INSERT INTO book
VALUES ('QZ52.78 J14 2011', 8430291759508, 'Dream a Little Dream', 
'Whoami Gonnabe', 'Daydream Co.', 2011);

INSERT INTO book
VALUES ('RS21.67 A22 1976', 8734561209356, 'Learning PHP',
'Jeremy Jugo', 'Life is Simple Publishing Co.', 1976);

INSERT INTO book
VALUES ('TM16.35 C76 1956', 1874320158351, 'Drawing the Perfect Circle',
'Jason Decirclo', 'Shapes Co.', 1956);

INSERT INTO book
VALUES ('ZZ44.87 C66 2020', 999999999999, 'Throw a Ball Here, Throw a Ball There',
'Rob Wu', 'I am Super Smart Co.', 2020);



/* hasAuthor(callNumber, aname) */

--author Jim Mugababone is main author of one book; additional author of two others
--author Whoami Gonnabe is main author of one book; additional author of one other

INSERT INTO hasAuthor
VALUES ('QA74.79 J12 2001', 'Jana Studiesalot, Whoami Gonnabe');

INSERT INTO hasAuthor
VALUES ('QB72.59 J74 2013', 'Jim Mugababone, Jamse Persuasive');

INSERT INTO hasAuthor
VALUES ('QZ52.78 J14 2011', 'Whoami Gonnabe');

INSERT INTO hasAuthor
VALUES ('RS21.67 A22 1976', 'Jeremy Jugo, Jim Mugababone');

INSERT INTO hasAuthor
VALUES ('TM16.35 C76 1956', 'Jason Decirclo, Jim Mugababone, Whoami Gonnabe');

INSERT INTO hasAuthor
VALUES ('ZZ44.87 C66 2020', 'Rob Wu');


/* hasSubject(callNumber, subject) */

INSERT INTO hasSubject
VALUES ('QA74.79 J12 2001', 'Science, Engineering, Database');

INSERT INTO hasSubject
VALUES ('QB72.59 J74 2013', 'Road, Design, Construction, Engineering');

INSERT INTO hasSubject
VALUES ('QZ52.78 J14 2011', 'Dream');

INSERT INTO hasSubject
VALUES ('RS21.67 A22 1976', 'PHP, Computer Science');

INSERT INTO hasSubject
VALUES ('TM16.35 C76 1956', 'Geometry, Circles, Mathematics');

INSERT INTO hasSubject
VALUES ('ZZ44.87 C66 2020', 'Efficiency, Studying, Problem Solving');

/* bookCopy(callNumber, copyNo, status) */

INSERT INTO bookCopy
VALUES ('QA74.79 J12 2001', 'C2', 'out');

INSERT INTO bookCopy
VALUES ('RS21.67 A22 1976', 'C2', 'in');

INSERT INTO bookCopy
VALUES ('RS21.67 A22 1976', 'C3', 'in');

INSERT INTO bookCopy
VALUES ('QA74.79 J12 2001', 'C3', 'on-hold');

--the follwoing books are in borrowing (out status)
INSERT INTO bookCopy
VALUES ('QZ52.78 J14 2011', 'C1', 'out');

INSERT INTO bookCopy
VALUES ('QZ52.78 J14 2011', 'C2', 'out');

INSERT INTO bookCopy
VALUES ('RS21.67 A22 1976', 'C1', 'out');

INSERT INTO bookCopy
VALUES ('TM16.35 C76 1956', 'C1', 'out');

INSERT INTO bookCopy
VALUES ('QA74.79 J12 2001', 'C1', 'out');

INSERT INTO bookCopy
VALUES ('QB72.59 J74 2013', 'C2', 'out');

--two more copies of the same book (different status)

-- INSERT INTO bookCopy
-- VALUES ('QB72.59 J74 2013', '1', 'in');

INSERT INTO bookCopy
VALUES ('QB72.59 J74 2013', 'C3', 'on-hold');

INSERT INTO bookCopy
VALUES ('ZZ44.87 C66 2020', 'C1', 'in');


/* holdRequest(hid, bid, callNumber, issuedDate) */

--bid 3414311864 has put two books on hold (student)
--bid 5406073649 has put two books on hold (faculty)
--bid 3834271354 has put one book on hold (staff)

--callNumber 'QB72.59 J74 2013' has two hold requests by different borrowers (on two different dates)

--PROBLEM: this one is not entering into the database - why??
INSERT INTO holdRequest
VALUES (seq_hid.nextval, 3834271354, 'QA74.79 J12 2001',
	TO_DATE('2014-03-27','YYYY-MM-DD'));      

INSERT INTO holdRequest
VALUES (seq_hid.nextval, 3414311864, 'QB72.59 J74 2013', 
	TO_DATE('2014-03-27','YYYY-MM-DD'));

INSERT INTO holdRequest
VALUES (seq_hid.nextval, 5406073649, 'QA74.79 J12 2001',
	TO_DATE('2014-03-22','YYYY-MM-DD'));

INSERT INTO holdRequest
VALUES (seq_hid.nextval, 5406073649, 'QB72.59 J74 2013', 
	TO_DATE('2014-03-21','YYYY-MM-DD'));

INSERT INTO holdRequest
VALUES (seq_hid.nextval, 3414311864, 'TM16.35 C76 1956', 
	TO_DATE('2014-03-20','YYYY-MM-DD'));




/* borrowing(borid, bid, callNumber, copyNo, outDate, inDate) */

--bid 3414311864 (student) has borrowed two books for 2 weeks
--bid 5406073649 (faculty) has borrowed one book for 12 weeks
--bid 6123650021 (staff) has borrowed two books for 4 and 6 weeks, respectively

INSERT INTO borrowing
VALUES(1000000000, 3414311864, 'QA74.79 J12 2001', 'C1', 
	TO_DATE('2014-01-01','YYYY-MM-DD'), null);
	
INSERT INTO borrowing
VALUES(1000000006, 3414311864, 'QA74.79 J12 2001', 'C2', 
	TO_DATE('2014-01-01','YYYY-MM-DD'), null);

INSERT INTO borrowing
VALUES(1000000001, 3414311864, 'QZ52.78 J14 2011', 'C1', 
	TO_DATE('2014-03-27','YYYY-MM-DD'), null);

INSERT INTO borrowing
VALUES(1000000002, 5406073649, 'QZ52.78 J14 2011', 'C2', 
	TO_DATE('2014-01-01','YYYY-MM-DD'), null);

INSERT INTO borrowing
VALUES(1000000003, 6123650021, 'TM16.35 C76 1956', 'C1', 
	TO_DATE('2014-03-27','YYYY-MM-DD'), null);

INSERT INTO borrowing
VALUES(1000000004, 6123650021, 'RS21.67 A22 1976', 'C1', 
	TO_DATE('2014-03-27','YYYY-MM-DD'), null);

INSERT INTO borrowing
VALUES(1000000005, 6123650021, 'QB72.59 J74 2013', 'C2', 
	TO_DATE('2014-03-27','YYYY-MM-DD'), null);


/* fine(fid, amount, issuedDate, paidDate, borid) */

INSERT INTO fine
VALUES (seq_fid.nextval, 1.20, TO_DATE('2014-03-27','YYYY-MM-DD'), 
	null, 1000000000);

INSERT INTO fine
VALUES (seq_fid.nextval, 2.40, TO_DATE('2014-03-22','YYYY-MM-DD'), 
	TO_DATE('2014-03-27','YYYY-MM-DD'), 1000000001);

INSERT INTO fine
VALUES (seq_fid.nextval, 17.36, TO_DATE('2014-03-25','YYYY-MM-DD'), 
	null, 1000000002);

INSERT INTO fine
VALUES (seq_fid.nextval, 105.62, TO_DATE('2014-04-01','YYYY-MM-DD'), 
	null, 1000000003);

INSERT INTO fine
VALUES (seq_fid.nextval, 2.22, TO_DATE('2014-03-17','YYYY-MM-DD'), 
	null, 1000000004);









