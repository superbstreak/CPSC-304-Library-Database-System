<!-- LIBRARY : Return Books -->

<html>
	<head>
		<title> Return Books </title>
		<link rel="stylesheet" type= "text/css" href="style.css">
	</head>
	<body>
		<h1 id="command"> Please enter information of book :</h1>
			<form action="" method="post" id="forms">
				Call Number : <input type="text" name="callNumber" id="forms" /></br></br>
				Copy Number : <input type="test" name="copyNumber" id="forms" /></br></br>
				<input type="submit" name="submitCallNumber" id="button" />
			</form>
            <form action="clerk.php" method="post" id="forms">
				<input type="submit" value="Back to Clerk" name="clerkpage" id="button"/>
			</form>

	</body>

</html>

<?php

include('OracleConn.php');

$success = True;
$db_conn = OCILogon($oracleUser, $oraclePass, "ug");

// callNumber and copyNo input into webpage by clerk to locate a borrower
$callNumberSearch;
$copyNumberSearch;

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

function printBorrowerResult($result) {
	//echo "<br>Borrower Information :<br>";
	?>
    	<div id="tabletitle">
			Borrower Information:
   		</div>
    <?php
	//echo "<br>";
	echo "<table>";
	echo "<tr><th>Borrower ID</th><th>Borrower Name</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
		echo "<tr><td>" . $row["BID"] . "</td><td>" . $row["BNAME"] . "</td></tr>"; //or just use "echo $row[0]" 
	}
	echo "</table>";
	echo "<br>";

}

function printBookResult($result) {
	//echo "<br>Book Information :<br>";
	?>
    	<div id="tabletitle">
			<br/><br/>
            Book Information:
   		</div>
    <?php
	//echo "<br>";
	echo "<table>";
	echo "<tr><th>Title</th><th>Call Number</th><th>Copy No</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
		echo "<tr><td>" . $row["TITLE"] . "</td><td>" . $row["CALLNUMBER"] . "</td><td>" . $row["COPYNO"] . "</td></tr>"; //or just use "echo $row[0]" 
	}
	echo "</table>";

}


if (isset($_POST['submitCallNumber'])) {
	if (isset($_POST['callNumber']) && isset($_POST['copyNumber'])) {

		$callNumberSearch = $_POST['callNumber'];
		$copyNumberSearch = $_POST['copyNumber'];

		setcookie("callNumberSearch", $callNumberSearch);
		setCookie("copyNumberSearch", $copyNumberSearch);

		// check for valid entries (no empty strings)
		if ($callNumberSearch == "" || $copyNumberSearch == "") {

			?>

			<script type="text/javascript">
				alert("Error : Please enter both a call number and a copy number to process a return.");
			</script>

			<?php
			
		} else {

			// Connect Oracle...
			if ($db_conn) {

				$result1 = executePlainSQL ("SELECT bk.title, bo.callNumber, bo.copyNo
											FROM book bk, borrowing bo
											WHERE bk.callNumber = bo.callNumber
											and bo.inDate IS NULL
											and bo.callNumber = '$callNumberSearch'
											and bo.copyNo = '$copyNumberSearch'");


				$result2 = executePlainSQL("SELECT b.bid, b.bname
											FROM borrower b, borrowing bo
											WHERE b.bid = bo.bid
											and bo.inDate IS NULL
											and bo.callNumber = '$callNumberSearch'
											and bo.copyNo = '$copyNumberSearch'");

				$tempResult2 = executePlainSQL("SELECT b.bid, b.bname
											FROM borrower b, borrowing bo
											WHERE b.bid = bo.bid
											and bo.inDate IS NULL
											and bo.callNumber = '$callNumberSearch'
											and bo.copyNo = '$copyNumberSearch'");

				$r1 = OCI_Fetch_Array($tempResult2, OCI_BOTH);

				if ($r1) {
					printBookResult($result1);
					echo "<br>";
					printBorrowerResult($result2);

									//Commit to save changes...
					OCICommit($db_conn);
					OCILogoff($db_conn);
       			

					// process return
					?>

					<form action="processReturn.php" method="post">
						<button type="submit" name="processReturnSubmit" id="button" >Process Return</button>
					</form>
				
					<?php

				} else {

					?>

					<script type="text/javascript">
						alert("Error : A borrowing of this book does not exist!");
					</script>

					<?php
				}

			} else {
				echo "cannot connect";
				$e = OCI_Error(); // For OCILogon errors pass no handle
				echo htmlentities($e['message']);

			}
		}
	}
}

?>