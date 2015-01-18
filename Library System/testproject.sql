drop table borrowerType cascade constraints;
drop table borrower cascade constraints;
drop table book cascade constraints;
drop table hasAuthor cascade constraints;
drop table hasSubject cascade constraints;
drop table bookCopy cascade constraints;
drop table holdRequest cascade constraints;
drop table borrowing cascade constraints;
drop table fine cascade constraints;

CREATE TABLE borrowerType
	(type char(7) not null,
	bookTimeLimit smallInt,					
	PRIMARY KEY (type));

CREATE TABLE borrower
	(bid char(10) not null,						
	password varchar2(15) DEFAULT 'letmein',
	bname varchar2(30),
	address varchar2(100),
	phone char(10),
	emailAddress varchar2(30),
	sinOrStNo char(9),
	expiryDate date,
	type char(7) not null,
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
	aname varchar2(30) not null,
	PRIMARY KEY (callNumber, aname),
	FOREIGN KEY (callNumber) references book ON DELETE CASCADE);

CREATE TABLE hasSubject
	(callNumber varchar2(30) not null,
	subject varchar2(30) not null,
	PRIMARY KEY (callNumber, subject),
	FOREIGN KEY (callNumber) references book ON DELETE CASCADE);

CREATE TABLE bookCopy
	(callNumber varchar2(30) not null,
	copyNo char(3) not null,
	status varchar2(7) not null,
	PRIMARY KEY (callNumber, copyNo),
	FOREIGN KEY (callNumber) references book ON DELETE CASCADE);

CREATE TABLE holdRequest
	(hid char(10) not null,			
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
	copyNo char(3),
	outDate date,
	inDate date,
	PRIMARY KEY (borid),
	FOREIGN KEY (bid) references borrower,
	FOREIGN KEY (callNumber, copyNo) references bookCopy);

CREATE TABLE fine
	(fid char(10) not null,
	amount decimal(5,2),
	issuedDate date,
	paidDate date,
	borid char(10) not null,
	PRIMARY KEY (fid),
	FOREIGN KEY (borid) references borrowing);