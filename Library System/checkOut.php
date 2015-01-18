<!--LIBRARY-->
<html>
	<head>
		<title>Check-Out Book</title>
        <link rel="stylesheet" type= "text/css" href="style.css">
	</head>

	<body>
    <h1 id="command"> Please enter information to check-out book.</h1>
    <div id= "body">
    	<form action="checkOut.php" method="post" id="forms">
        <br/>
        Borrower ID: <input type="text" name="bid" value="<?php echo $_POST["bid"];?>" id="forms" />
        <br/> <br/>
        Book Call Numbers: 
       	<textarea rows="4" cols="50" name="callNumbers"
       	placeholder="callNumber#1, callNumber#2, callNumber#3, ..." id="forms" >
</textarea>        
       <br/><br/>
        <input type="submit" value="Submit" name="checkOutBooks" id="buttonSide">
        </form><br/>
        <form action="clerk.php" method="post" id="forms">
        	<input type="submit" value="Back to Clerk" name="backToClerk" id="buttonSide">
        </form>
     </div>
        
<?php
include('OracleConn.php');
$success = True;
$validBorrower;
$numslets = '0123456789abcdefghijklmnopqrstuvwqyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

//creating connection to Oracle database
$db_connect = OCILogon($oracleUser, $oraclePass, "ug");

if (!$db_connect) {
	echo "An error occured when trying to connect to database.";
	//$error = OCI_Error($db_connect);
}

//parse and execute SQL command
function executeSQLCommand($sqlCommand) {
	global $db_connect, $success;
	
	//first parse the given SQL command
	$parsedSQL = OCIParse($db_connect, $sqlCommand);
	//check to see if parsed
	if (!$parsedSQL) {
		echo "Cannot parse given SQL command.";
		$error = OCI_Error($db_connect);
		$success = False;
	}
	
	//now execute the parsed SQL command
	$executedSQL = OCIExecute($parsedSQL, OCI_DEFAULT);
	//check to see if there are errors from execute
	if (!$executedSQL) {
		echo "Cannot execute given SQL command!!";
		$error = OCI_Error($parsedSQL);
		$success = False;
	}
	return $parsedSQL;
}

function executeBoundSQL($sqlCommand, $list) {
	global $db_connect, $success;
	
	//first parse the given SQL command
	$parsedSQL = OCIParse($db_connect, $sqlCommand);
	//check to see if parsed
	if (!$parsedSQL) {
		echo "Cannot parse given SQL command.";
		$error = OCI_Error($db_connect);
		$success = False;
	}
	
	foreach ($list as $tuple) {
		foreach($tuple as $bind => $val) {
			OCIBindByName($parsedSQL, $bind, $val);
			unset($val);
		}
		$executedSQL = OCIExecute($parsedSQL, OCI_DEFAULT);
		if (!$executedSQL) {
			echo "Cannot execute given SQL command....!";
			$error = OCI_Error($parsedSQL);
			$success = False;
		}
	}
}

function printResult($result) {
	echo "<br><br>" ?>
    <html> <link rel="stylesheet" type= "text/css" href="style.css">
    <div id="tabletitle">
	Borrower's Borrowing List:
    </div> 
    </html>
    
    <?php
	echo "<table cellpadding=5px>";
	?> <html> <table cellpadding=5px id="table" ></html>
        <?php
        echo "<tr><th>Book</th><th>Due Date</th></tr>";
		
	
	while ($row = OCI_FETCH_ARRAY($result, OCI_BOTH)) {
		
		$takeOutDate = $row[4];				
		$dueDate = date('Y-m-d', (strtotime("+42 days",strtotime($takeOutDate))));
				
		
		echo "<tr><td>" .$row["CALLNUMBER"] . "</td><td>" . $dueDate ."</td></tr>";
	}
	echo "</table>";
}

function getOutDate ($adate) {
	
	if ($bType == 'Staff') {
		$outDate = date('Y-m-d', strtotime('+42 days'));
	}
	if ($bType == 'Student') {
		$outDate = date('Y-m-d', strtotime('+14 days'));
	}						
	if ($bType == 'Faculty') {
		$outDate = date('Y-m-d', strtotime('+84 days'));
	}
	
}


if ($db_connect) {
	if ($_POST && $success) {
		if (array_key_exists("checkOutBooks", $_POST)) {
			
			//.....
			$bid = $_POST["bid"];
			
			$getBorrowerInfo = executeSQLCommand("SELECT * FROM borrower WHERE bid='$bid'");
			$borrowerInfo = OCI_Fetch_Array($getBorrowerInfo, OCI_BOTH);
			
			$bExpireDate = $borrowerInfo[7];		//borrower expiry date
			$todaysDate = date('Y-m-d');			//today's date
			
			$today = strtotime($todaysDate);		//change form to compare
			$expiry = strtotime($bExpireDate);		//change form to compare
						
			if ($expiry > $today) {					
				//parsing the textbox to array of callNumbers
				$textEntry = $_POST["callNumbers"];
				$cNumArray = explode(', ', $textEntry);
				
				foreach ($cNumArray as &$value) {
					
					$borrowHistory = executeSQLCommand("SELECT * FROM borrowing WHERE bid='$bid' and callNumber='$value' and inDate IS NULL");
					$history = OCI_FETCH_ARRAY ($borrowHistory, OCI_BOTH);
					
					if (!$history) {
							//echo "Okay to proceed with checking out process. <br/>";
							
							
							$bookInformation = executeSQLCommand("SELECT * FROM bookCopy WHERE callNumber='$value'");					
							
							while ($information = OCI_FETCH_ARRAY ($bookInformation, OCI_BOTH)) {
								//echo $information[2] . "<br/>";
								
								$borrowHistory2 = executeSQLCommand("SELECT * FROM borrowing WHERE bid='$bid' and callNumber='$value' and inDate IS NULL");
								$history2 = OCI_FETCH_ARRAY ($borrowHistory2, OCI_BOTH);
								if (!$history2) {
									
									$getHoldRequests=executeSQLCommand("SELECT * FROM holdRequest WHERE bid='$bid' and callNumber ='$value'");
									$holdRequests = OCI_Fetch_Array($getHoldRequests, OCI_BOTH);
									
										if (($information[2] == 'in') || (($information[2] == 'on-hold') && $holdRequests)) {
										//echo "Book is in and ready for check-out. <br/>";
										
										$borid = substr(str_shuffle(str_repeat($numslets, 10)), 0, 10);
										$outDate = date('Y-m-d', strtotime('+0 year'));
										
										$bType = $borrowerInfo[8];
										if ($bType == 'Staff') {
											$outDate = date('Y-m-d', strtotime('+42 days'));
										}
										if ($bType == 'Student') {
											$outDate = date('Y-m-d', strtotime('+14 days'));
										}						
										if ($bType == 'Faculty') {
											$outDate = date('Y-m-d', strtotime('+84 days'));
										}
																
										$borrowingInfo = array (
											":bind0" => $borid,				//borid
											":bind1" => $bid,					//bid
											":bind2" => $value,				//call number
											":bind3" => $information[1],		//copy number
											":bind4" => $todaysDate,			//out date
										);
										
										$data = array (
											$borrowingInfo );
											
										executeBoundSQL("INSERT INTO borrowing VALUES (:bind0, :bind1, :bind2, :bind3, :bind4, null)", $data);
										executeBoundSQL("UPDATE bookCopy SET status='out' WHERE callNumber=:bind2 and copyNo=:bind3", $data);
										
										if ($holdRequests) {
											executeSQLCommand("DELETE FROM holdRequest WHERE bid='$bid' and callNumber='$value'");
										}
										
										//echo "Successfully checked-out: " .$value . "<br/>";
										
										?>
											<html>
											 <link rel="stylesheet" type= "text/css" href="style.css">
											 <br>
												<p id="success">  Successfully checked-out:  <?php echo " " .$value ?></p>
											</html>	
										<?php
									
									}//endof if status
									else {
										//echo "Unable to check-out: " . $value . " as it is not currently available. <br/>";
										
										if (!$information) {
										?>
											<script type="text/javascript">
												alert("Error : Unable to check-out as book is not currently available.");
                                            </script>
										<?php
										}
										
									}
								}//end if history
							}//end while ifnormation
					}
					else {
						//echo "Unable to check-out: " . $value . " as the borrower already has another copy of it. <br/>";						
						?>
						<script type="text/javascript">
							alert("Error : Unable to check-out. Borrower already has book taken out.");
						</script>
						<?php
					}
					

			} //close for
				
			 	$result = executeSQLCommand("SELECT * FROM borrowing WHERE bid='$bid' and inDate IS NULL");			
				printResult($result);
						
				
		} //close if expiry
		else {
			//echo "Borrower is invalid - expired.";
			?>
				<script type="text/javascript">
					alert("Error : Borrow is no longer valid.");
				</script>
			<?php
		}
			
		
			OCICommit($db_connect);
			//header('Refresh: 2; URL=http://www.ugrad.cs.ubc.ca/~z7o8/clerk.php');
			
		}
	}
	OCILogoff($db_connect);
	$success = False;
}


?>

</body>
</html>