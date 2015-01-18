<html>
	<head>
		<title>Make a Payment</title>
        <link rel="stylesheet" type= "text/css" href="style.css">
		<meta charset = "utf-8" />
		<script type ="text/javascript">
			function backtoAccount()
			{
				window.location = "account.php"								// REDIRECT TO account page for task 2
			}
		</script>
	</head>
	<body>
    <h1 id="command"> Please enter details.</h1>
		
		<form Action = "payfines.php" method = "POST" id="forms">
		<br/>
        Name:
		<input type ="text" name = "nameF" size ="20"> <br/><br/>
		Fine ID (FID):
		<input type ="number" name = "fidF" size ="20" maxlength="10" id="forms"> <br/><br/>
		Credit Card Number:
		<input type ="number" name = "ccnF" size ="20" maxlength="16" id="forms"> <br/><br/>
		CVV:
		<input type ="number" name = "cvvF" size ="20" maxlength="3" id="forms"> <br/><br/>

		<input type ="submit" value ="Confirm and Pay" name = "paynow" id="buttonSide">
		<input type ="submit" value ="Back To Account" name = "backtoAcc" id="buttonSide">
		</form>
		
		<?php 
			$success = True;
			include ('globalmethods.php');										// keep track of errors. REDIRECT IF NONE
			include ('OracleConn.php');
			$db_conn = ocilogon($oracleUser, $oraclePass, "ug");			// establish connection with username&pw
			$loginID = $_COOKIE["loginID"];
			// Connect Oracle...	
			if ($db_conn)	
			{
				if (array_key_exists('backtoAcc', $_POST))
				{
					echo "<script> backtoAccount(); </script>";
				}
				if (array_key_exists('paynow', $_POST))
				{
					$payrequest = array(
							":bind1" => $_POST['nameF'],
							":bind2" => $_POST['fidF'],
							":bind3" => $_POST['ccnF'],
							":bind4" => $_POST['cvvF']
					);
					$checkname = false;
					$checkfid = false;
					$checkccn = false;
					$checkcvv = false;
					$namewhere = '';
					$fidwhere = '';
					$ccnwhere = '';
					$cvvwhere = '';
				
					$sqlfines = "SELECT fine.borid, fine.fid, fine.issueddate, fine.paiddate, fine.amount, borrowing.bid 
									FROM fine INNER JOIN borrowing ON borrowing.borid=fine.borid
									WHERE bid='$loginID'";
				
					if (!empty($payrequest[":bind1"]))		// name
					{
						$checkname = true;
					}
					if (!empty($payrequest[":bind2"]))		// fid
					{
						$checkfid = true;
					}
					if (!empty($payrequest[":bind3"]))		// ccn
					{
							$checkccn = true;
					}
					if (!empty($payrequest[":bind4"]))		// cvv
					{
							$checkcvv = true;
					}
				
					// ========= send req
					if ($checkname && $checkfid && $checkccn && $checkcvv)
					{
						$enterfid = $payrequest[":bind2"];					
						$responseFINE = executePlainSQL($sqlfines);
						$nrows = oci_fetch_all($responseFINE, $arrayFINE, null, null, OCI_FETCHSTATEMENT_BY_ROW);
						
						$onrecordFID = "";
						$onrecordAMM = "";
						$onrecordPAID = "";
						$fineNOTpaid = false;
						foreach ($arrayFINE as $row)
						{
							if (strcmp($row['FID'], $enterfid) == 0)
							{
								$onrecordAMM = $row['AMOUNT'];
								$onrecordFID = $row['FID'];
								$onrecordPAID = $row['PAIDDATE'];
								if (empty($onrecordPAID))
								{
									$fineNOTpaid = true;
								}
							}
						}
						if ($fineNOTpaid == false)
						{
							//echo "Payment NOT authorized. Please double check you information and try again.";
							?>
							<script type="text/javascript">
                         	   alert("Payment NOT authorized. Please double check you information and try again.");
                       	 </script>
						<?php
						}
						else
						{
							$date = date('Y-m-d');
							$alltuples = array($payrequest);
							executeBoundSQL("update fine set paiddate = '$date' where fid = '$enterfid'", $alltuples);
							OCICommit($db_conn);
							//echo "Payment successful!";
							?>
								<html>
									<p id="success"> <br/> <br/> Payment successful! </p>
								</html>
							<?php
						}						
					}
					else
					{
						//echo "One or multiple fields are empty or invalid.";
						?>
							<script type="text/javascript">
                         	   alert("One or multiple fields are empty or invalid.");
                       	 </script>
						<?php
					}
					
				}
				
				
				if ($_POST && success)
				{
					//POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
					// if post is not empty + no error
				}
				else
				{
					// Select data...if no entry
						 
				}
				// Commit to save changes
				ocilogoff($db_conn);
			}
			else
			{
				echo "Connection Error! DEBUG: DB_CONN";
				$e = oci_error();										// For OCILogon errors pass no handle
				echo htmlentities($e['message']);
			}
			?>
		</body>
	</html>