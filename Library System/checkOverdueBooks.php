<!-- LIBRARY : Check Overdue Books -->

<html>
	<head>
		<title> Overdue Books </title>
		<link rel="stylesheet" type= "text/css" href="style.css">
	</head>
	<body>
			<!--
			<form action="" method="post">
				<input type="submit" value="Search for Overdue Items" name="searchOverdueItems" />
			</form>
			-->

			<h1 id="command"> Please enter information to borrower ID.<br></h1>

	</body>
</html>

<?php

include('OracleConn.php');

$success = True;
$db_conn = OCILogon($oracleUser, $oraclePass, "ug");

function executePlainSQL($cmdstr) {
	global $db_conn, $success;
	$statement = OCIParse($db_conn, $cmdstr);

	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn);     
		echo htmlentities($e['message']);
		$success = False;
	}

	$r = OCIExecute($statement, OCI_DEFAULT);
	if (!$r) {
		echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
		$e = oci_error($statement);
		echo htmlentities($e['message']);
		$success = False;
	} else {

	}
	return $statement;

}

function executeBoundSQL($cmdstr, $list) {

	global $db_conn, $success;
	$statement = OCIParse($db_conn, $cmdstr);

	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn);
		echo htmlentities($e['message']);
		$success = False;
	}

	foreach ($list as $tuple) {
		foreach ($tuple as $bind => $val) {
			OCIBindByName($statement, $bind, $val);
			unset ($val); 
		}
		$r = OCIExecute($statement, OCI_DEFAULT);
		if (!$r) {
			echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($statement);
			echo htmlentities($e['message']);
			echo "<br>";
			$success = False;
		}
	}

}

/*  
    List information the follwoing information for overdue items :
   (1) borrowing borid
   (2) book title
   (3) book callNumber
   (4) book copyNo
   (5) book outDate
   (6) borrower bid
   (7) borrower bname
   (8) borrower type
   (9) borrower email (optional : may/may not be displayed)
*/


// print function for listing overdue items
function printResult($result) {
	//echo "<table>";
	?> <table id="table"> <?php

	// print a header for each table (once) only if any rows exist in the table
	$needHeader=1;


	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {	
		if ($needHeader) {
			echo "<table>";
			echo "<tr><th>BORID</th><th>Book Title</th><th>CallNumber</th><th>CopyNo
			</th><th>outDate</th><th>BID</th><th>Borrower Name</th><th>Borrower Type
			</th><th>Borrower Email</th></tr>";
		}
		$needHeader=0;
		echo "<tr><td>" . $row["BORID"] . "</td><td>" . $row["TITLE"] 
		. "</td><td>" . $row["CALLNUMBER"] . "</td><td>" . $row["COPYNO"]
		. "</td><td>". $row["OUTDATE"] . "</td><td>" . $row["BID"]
		. "</td><td>" . $row["BNAME"] . "</td><td>" . $row["TYPE"]
		. "</td><td>". $row["EMAILADDRESS"] . "</td></tr>"; 

	}
	echo "</table>";
	echo "<br>";

}

//if (isset($_POST['searchOverdueItems'])) {

	if ($db_conn) {

		?>

		<form action="emailBorrower.php" method="post" id="forms">
			BORID : <input type="text" name="emailBORID" id="forms" />
            <br/><br/>
			<input type="submit" value="Email Borrower" name="emailBorrower" id="button"/>
		</form>
        <form action="clerk.php" method="post" id="forms">
			<input type="submit" value="Back to Clerk" name="clerkpage" id="button"/>
		</form>


		<?php

		// date of search
		$currentDate = date('Y-m-d');



		// each type of borrower will have a borrowing time limit

		/* students : 2 weeks from current date */
		$result1 = executePlainSQL("SELECT * 
									FROM borrowerType
									WHERE type='Student'");
		$studentTimeLimit;
		while ($row = OCI_Fetch_Array($result1, OCI_BOTH)) {
			$studentTimeLimit = $row["BOOKTIMELIMIT"];
		}
		// determine the date 14 days previous the current date
		// any borrowings with an inDate value <= to this date represent late items
		$studentMaxCheckoutDate = date('Y-m-d', strtotime($currentDate . ' - ' . $studentTimeLimit . ' days'));



		/* staff : 6 weeks from current date */
		$result2 = executePlainSQL("SELECT * 
									FROM borrowerType
									WHERE type='Staff'");
		$staffTimeLimit;
		while ($row = OCI_Fetch_Array($result2, OCI_BOTH)) {
			$staffTimeLimit = $row["BOOKTIMELIMIT"];
		}
		// determine the date 42 days previous the current date
		// any borrowings with an inDate value <= to this date represent late items
		$staffMaxCheckoutDate = date('Y-m-d', strtotime($currentDate . ' - ' . $staffTimeLimit . ' days'));
		


		/* faculty : 12 weeks from current date */
		$result3 = executePlainSQL("SELECT * 
									FROM borrowerType
									WHERE type='Faculty'");
		$facultyTimeLimit;
		while ($row = OCI_Fetch_Array($result3, OCI_BOTH)) {
			$facultyTimeLimit = $row["BOOKTIMELIMIT"];
		}
		// determine the date 84 days previous the current date
		// any borrowings with an inDate value <= to this date represent late items
		$facultyMaxCheckoutDate = date('Y-m-d', strtotime($currentDate . ' - ' . $facultyTimeLimit . ' days'));



		/* 
			List information the follwoing information for overdue items :
		   (1) borrowing borid
		   (2) book title
		   (3) book callNumber
		   (4) book copyNo
		   (5) book outDate
		   (6) borrower bid
		   (7) borrower bname
		   (8) borrower type
		   (9) borrower email (optional : may/may not be displayed)
		*/

		// list all students with overdue items
		$result4 = executePlainSQL("SELECT * 
									FROM borrowing bo, borrower b, book bk
									WHERE bo.bid = b.bid
									and bk.callNumber = bo.callNumber
									and bo.inDate IS NULL
									and b.type = 'Student'
									and bo.outDate <= TO_DATE('$studentMaxCheckoutDate','YYYY-MM-DD')");
		// print header once
		printResult($result4);


		// list all the staff with overdue items
		$result5 = executePlainSQL("SELECT * 
									FROM borrowing bo, borrower b, book bk
									WHERE bo.bid = b.bid
									and bk.callNumber = bo.callNumber
									and bo.inDate IS NULL
									and b.type = 'Staff'
									and bo.outDate <= TO_DATE('$staffMaxCheckoutDate','YYYY-MM-DD')");
		// print header
		printResult($result5);


		// list all the faculty with overdue items
		$result6 = executePlainSQL("SELECT * 
									FROM borrowing bo, borrower b, book bk
									WHERE bo.bid = b.bid
									and bk.callNumber = bo.callNumber
									and bo.inDate IS NULL
									and b.type = 'Faculty'
									and bo.outDate <= TO_DATE('$facultyMaxCheckoutDate','YYYY-MM-DD')");
		// print header
		printResult($result6);
		

	} else {
		echo "cannot connect";
		$e = OCI_Error(); // For OCILogon errors pass no handle
		echo htmlentities($e['message']);
	}	
//}





?>