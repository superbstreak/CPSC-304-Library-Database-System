<!-- LIBRARY : Email Borrower Processing -->

<html>

	<head>

		<title> Email Borrower Processing </title>
        <link rel="stylesheet" type= "text/css" href="style.css"/>

	</head>


	<body>


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

if (isset($_POST['emailBorrower'])) {

	if ($db_conn) {

		// information to add to the email template
		//$borrowerType;
		$borrowerName;
		$bookTitle;
		$bookAuthor;
		$bookCallNumber;
		$bookCopyNumber;
		$bookOutDate;
		$bookTimeLimit;
		$bookDueDate;
		$borrowerEmailAddress;

		

		// lookup the information using the borid
		$emailingBORID = $_POST['emailBORID'];

		if ($emailingBORID != "") {

			$result1 = executePlainSQL("SELECT *
										FROM borrowing bo, borrower b, book bk, borrowerType bt
										WHERE bo.bid = b.bid
										and bo.callNumber = bk.callNumber
										and b.type = bt.type
										and bo.borid = $emailingBORID");

			// will return only one result since BORID is unique
			while ($row = OCI_Fetch_Array($result1, OCI_BOTH)) {
				//$borrowerType = $row["TYPE"];
				$borrowerName = $row["BNAME"];
				$bookTitle = $row["TITLE"];
				$bookAuthor = $row["MAINAUTHOR"];
				$bookCallNumber = $row["CALLNUMBER"];
				$bookCopyNumber = $row["COPYNO"];
				$bookOutDate = $row["OUTDATE"];
				$bookTimeLimit = $row["BOOKTIMELIMIT"];
				$borrowerEmailAddress = $row["EMAILADDRESS"];
			}

			// small correction to make our due date exactly x days starting
			// the day of the borrowing (see assumptions)
			$bookTimeLimit = $bookTimeLimit - 1;

			// determine due date using the borrowerType bookTimeLimit
			$bookDueDate = date('Y-m-d', strtotime($bookOutDate . ' + ' . $bookTimeLimit . ' days'));

			// convert due date to more readable format (ie. Monday, January 27, 2014)
			$bookDueDateInWords = date('l, F d, Y', strtotime($bookOutDate));


			/* Email Setup */

			$subject = "Library Item Overdue";

			// email notifaction message
			$message = "<u><b>Library Notification</b></u><br><br>" 
						. $borrowerName . ", you have an overdue library item:<br><br>" 
						. "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp" . $bookTitle . " / " . $bookAuthor . "<br>" 
						. "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp" . $bookCallNumber . " " . $bookCopyNumber . "<br><br>"
						. "This item was due on " . $bookDueDateInWords . ".<br>"
						. "Please return this item immediately to avoid additional late charges.";


			/* IMPORTANT : this (commented out) code sends an email to the user at the 
			   specified email address since we have no installed/working email system, 
			   we will be displaying a popup message instead */
			//mail($borrowerEmailAddress, $subject, $message);

			// dislay what the email would have looked like
			echo "TO : " . $borrowerEmailAddress . "<br>"
				. "FROM : Library Notification System <br>"
				. "SUBJECT : " . $subject . "<br><br>"
				. "MESSAGE BODY : <br><br>"
				. $message;


			// alert librarian that email has been sent
			?>
								<html>
                                <br>
									<p id="success"> Email message sent. Redirecting back to previous page. </p>
								</html>
							<?php

			//echo "<br><br><br>Redirecting....please wait 3 seconds.";

			header('Refresh: 3;  URL=http://www.ugrad.cs.ubc.ca/~'. $undergrad . '/checkOverdueBooks.php' );

		} else {

			?>

			<script type="text/javascript">
				alert("Error : Please enter a BORID.");
			</script>

			<?php

			header('Refresh: 0;  URL=http://www.ugrad.cs.ubc.ca/~'. $undergrad . '/checkOverdueBooks.php' );

		}
	}
}


?>
