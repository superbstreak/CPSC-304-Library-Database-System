<!-- LIBRARY : Process Return -->


<html>

	<head>

		<title> Process Return </title>

	</head>


	<body>
		

	</body>

</html>

<?php

include('OracleConn.php');

$success = True;
$db_conn = OCILogon($oracleUser, $oraclePass, "ug");

function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
	//echo "<br>running ".$cmdstr."<br>";
	global $db_conn, $success;
	$statement = OCIParse($db_conn, $cmdstr); //There is a set of comments at the end of the file that describe some of the OCI specific functions and how they work

	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn); // For OCIParse errors pass the       
		// connection handle
		echo htmlentities($e['message']);
		$success = False;
	}

	$r = OCIExecute($statement, OCI_DEFAULT);
	if (!$r) {
		echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
		$e = oci_error($statement); // For OCIExecute errors pass the statementhandle
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
			//echo $val;
			//echo "<br>".$bind."<br>";
			OCIBindByName($statement, $bind, $val);
			unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype

		}
		$r = OCIExecute($statement, OCI_DEFAULT);
		if (!$r) {
			echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($statement); // For OCIExecute errors pass the statementhandle
			echo htmlentities($e['message']);
			echo "<br>";
			$success = False;
		}
	}

}



if (isset($_POST['processReturnSubmit'])) {

	if ($db_conn) {

		$callNumberSearch = $_COOKIE['callNumberSearch'];
		$copyNumberSearch = $_COOKIE['copyNumberSearch'];

		// check for a hold request for a book with the given call number
		$result3 = executePlainSQL("SELECT h.hid, b.bid, b.bname, b.emailAddress
									FROM holdRequest h, borrower b
									WHERE h.bid = b.bid
									and h.callNumber = '$callNumberSearch'");

		// determine number of hold request for given book
		$holdRequestCount = 0;
		while ($row = OCI_Fetch_Array($result3, OCI_BOTH)) {
			$holdRequestCount++;
		} 

		$result4 = executePlainSQL("SELECT status
									FROM bookCopy
									WHERE status = 'on-hold'
									and callNumber = '$callNumberSearch'
									ORDER BY copyNo DESC");

		// determine number of already existing holds for given book
		$onHoldCount = 0;
		while ($row = OCI_Fetch_Array($result4, OCI_BOTH)) {
			$onHoldCount++;
		}

		// the number of hold requests exceeds the number of books on hold,
		// so put the returned book on hold
		if ($holdRequestCount > $onHoldCount) {
			$result5 = executePlainSQL("UPDATE bookCopy
										SET status='on-hold'
										WHERE callNumber = '$callNumberSearch'
										and copyNo = '$copyNumberSearch'");
		} 

		// the number of hold requests is equal to or exceeds the number of 
		// books available
		else if ($holdRequestCount <= $onHoldCount) {
			$result9 = executePlainSQL("UPDATE bookCopy
										SET status='in'
										WHERE callNumber = '$callNumberSearch'
										and copyNo = '$copyNumberSearch'");

		}

		// update the inDate for the borrowing
		$date = date('Y-m-d');

		$result6 = executePlainSQL("UPDATE borrowing
									SET inDate=TO_DATE('$date','YYYY-MM-DD')
									WHERE callNumber = '$callNumberSearch'
									and copyNo = '$copyNumberSearch'");


		// assess fine at 10 cents/day
		$fineRate = 0.10;

		$result7 = executePlainSQL("SELECT bo.inDate, bo.outDate, bo.borid, bt.bookTimeLimit
			FROM borrowing bo, borrower b, borrowerType bt
			WHERE bo.bid = b.bid
			and b.type = bt.type
			and bo.callNumber = '$callNumberSearch'
			and bo.copyNo = '$copyNumberSearch'");


		while ($row = OCI_Fetch_Array($result7, OCI_BOTH)) {

			$dateIn = strtotime($row["INDATE"]);
			$dateOut = strtotime($row["OUTDATE"]);
			$secondDifference = $dateIn - $dateOut;

			// calculate the number of days since book was checked out
			$dateDifference = floor($secondDifference/3600/24);

			// // borrower type
			// $type = $row["TYPE"];

			// borrower type bookTimeLimit
			$timeLimit = $row["BOOKTIMELIMIT"];

			// borrower id
			$borID = $row["BORID"];

			// fine amount (assessed for overdue items only)
			$fine;

			if ($dateDifference > $timeLimit) {

				// calculate fine
				$fine = ($dateDifference - $timeLimit) * $fineRate;

				// update database with fine
				$result8 = executePlainSQL("INSERT INTO fine
					VALUES (seq_fid.nextval, 
						$fine,
						TO_DATE('$date','YYYY-MM-DD'),
						null,
						$borID)");

			}
		}

		//Commit to save changes...
		OCICommit($db_conn);
		OCILogoff($db_conn);
   		$success = False;


   		header( 'Location: http://www.ugrad.cs.ubc.ca/~' . $undergrad . '/returnBooks.php' );


	} else {
	echo "cannot connect";
	$e = OCI_Error(); // For OCILogon errors pass no handle
	echo htmlentities($e['message']);
	}
}

?>

















