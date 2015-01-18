<!--LIBRARY-->
<html>
	<head>
		<title>Add Borrower</title>
        <link rel="stylesheet" type= "text/css" href="style.css">
	</head>

	<body>   
    <h1 id= "command"> Please enter new borrower's information.</h1>
    
    <div id= "body">
    	<form action="addBorrower.php" method="post" id="forms">
        <br/>
        Full Name: <input type="text" name="bname" value="<?php echo $_POST["bname"];?>" id="forms" />
        <br/> <br/>
        Address: <input type="text" name="address" value="<?php echo $_POST["address"];?>" id="forms" />
        <br/> <br/>
        Phone: <input type="text" placeholder="eg. xxx-xxx-xxxx" name="phone" value="<?php echo $_POST["phone"];?>" id="forms"/>
        <br/> <br/>
        Email: <input type="text" name="emailAddress" value="<?php echo $_POST["emailAddress"];?>" id="forms" />
        <br/> <br/>
        SIN or Student Number: <input type="text" name="sinOrStNo" value="<?php echo $_POST["sinOrStNo"];?>" id="forms" />
        <br/> <br/>
        Type of Borrower: <select name="type" id="forms">
        	<option value = "<?php echo "Student";?>" id="forms"> Student </option>
        	<option value = "<?php echo "Faculty";?>" id="forms"> Faculty </option>
            <option value = "<?php echo "Staff";?>" id="forms"> Staff </option> </select>
        <br/> <br/>
        <input type="submit" value="Submit" name="addNewBorrower" id="button">
        </form>
        <form action="clerk.php" method="post" id="forms">
        	<input type="submit" value="Back to Clerk" name="backToClerk" id="button">
        </form>
        </div>
        
<?php
$success = True;
$badchar = '/[]{}!@$%^&*()_+=|:;<>?.';
$numslets = '123456789abcdefghijklmnopqrstuvwqyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
$randomNo = rand(1,15);

//creating connection to Oracle database
include ('OracleConn.php');
$db_connect = ocilogon($oracleUser, $oraclePass, "ug");
//$db_conn = ocilogon($oracleUser, $oraclePass, "ug");

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
		echo "Cannot execute given SQL command.";
		$error = OCI_Error($parsedSQL);
		$success = False;
	}
	return $parsedSQL;
}

function borrowerExists ($sinOrSt) {
	$check = executeSQLCommand ("SELECT * FROM borrower WHERE sinOrStNo='$sinOrSt'");
	$row = OCI_FETCH_ARRAY($check, OCI_BOTH);
	return $row;
}

if ($db_connect) {
	if ($_POST && $success) {
		if (array_key_exists("addNewBorrower", $_POST)) {
			
			$bid = substr(str_shuffle(str_repeat($numslets, 10)), 0, 10);
			$pswd = substr(str_shuffle(str_repeat($numslets, $randomNo)), 0, $randomNo);;
			$bname = $_POST["bname"];
			$address = $_POST["address"];
			$phone = str_replace ("-", "", $_POST["phone"]);
			$emailAddress = $_POST["emailAddress"];
			$sinOrStNo = $_POST["sinOrStNo"];
			$expiryDate = date('Y-m-d', strtotime('+1 year'));
			$type = $_POST["type"];
			
			if ( (strlen($phone) == 10) && (is_numeric($phone)) ) {
				
				if ( (strpos($emailAddress, '@')) ) {
							
					if ( (($type == 'Student') && (strlen($sinOrStNo) == 8)) || (($type != 'Student') && (strlen($sinOrStNo) == 9)) ) {
						
						if (!borrowerExists($sinOrStNo)) {
								
							executeSQLCommand("INSERT INTO borrower VALUES ('$bid', '$pswd', '$bname', '$address', '$phone', '$emailAddress', '$sinOrStNo', '$expiryDate', '$type')");
							
							?>
								<html>
									<p id="success"> Successfully added borrower! Redirecting to Clerk page. </p>
								</html>
							<?php
							header('Refresh: 2; URL=http://www.ugrad.cs.ubc.ca/~' . $undergrad . '/clerk.php');
						}
						else {
							?>
								<script type="text/javascript">
								   alert("Error : Borrower with same SIN or Student Number already exists.");
							 </script>
							<?php
						}
					}
					else {
						?>
							<script type="text/javascript">
                         	   alert("Error : Please enter a valid SIN or Student Number.");
                       	 </script>
						<?php
					}
				}
				else {
					?>
						<script type="text/javascript">
							alert("Error : Please enter a valid email address.");
						</script>
					<?php
				}
			}
			else {
				?>
					<script type="text/javascript">
                        alert("Error : Please enter a valid Phone number.");
                    </script>
				<?php
			}
			OCICommit($db_connect);
		}
	}
	OCILogoff($db_connect);
	$success = False;
}
?>

</body>
</html>