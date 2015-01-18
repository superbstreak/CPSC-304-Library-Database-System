<html>
	<head>
		<title>Place A Hold Request</title>
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
    <h1 id="command"> Please enter details of book you wish to place on hold.</h1>
		
		<form Action = "holdrequest.php" method = "POST" id="forms">
		<br/>
        CallNumber:
		<input type ="text" name = "cnumber" size ="20" id="forms"> <br/><br/>
		
		<input type ="submit" value ="Confirm and Request" name = "reqnow"  id="buttonSide">
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
				if (array_key_exists('reqnow', $_POST))
				{
					$holdrequest = array(
							":bind1" => $_POST['cnumber'],
					);
					$checkcn = false;
					$sqlholds = "SELECT * FROM bookcopy";
					if (!empty($holdrequest[":bind1"]))		// name
					{
						$checkcn = true;
						$responseHOLD = executePlainSQL($sqlholds);
						$nrows = oci_fetch_all($responseHOLD, $arrayHOLD, null, null, OCI_FETCHSTATEMENT_BY_ROW);
						$entercn = $holdrequest[":bind1"];
						$entercn = trim($entercn);
						$flipflopREQUEST = false;
						foreach ($arrayHOLD as $row)
						{
							if ((strcmp($row['CALLNUMBER'], $entercn) == 0) && $flipflopREQUEST == false)	// if suchc call number record exist in the system
							{
								$sql_count_in =  "SELECT count(*) AS i FROM bookcopy WHERE status =  'in' AND callnumber = '$entercn'";
								$responseCNTin = executePlainSQL($sql_count_in);
								$in = oci_fetch_array($responseCNTin, OCI_NUM);
								$numberofIn = $in[0];
								if ($numberofIn == 0)
								{
									$sql_count_hr = "SELECT count(*) AS h FROM holdrequest WHERE bid = '$loginID' AND callnumber = '$entercn'";
									$responseCNThr = executePlainSQL($sql_count_hr);
									$hrbyyou = oci_fetch_array($responseCNThr);
									$alreadyheld = $hrbyyou[0];
									if ($alreadyheld == 0)		// proceed to hold request
									{
										$date = date('Y-m-d');
										$alltuples = array($holdrequest);
										$insertHR = "INSERT INTO holdrequest VALUES (seq_hid.nextval, '$loginID', '$entercn', '$date')";
										executeBoundSQL($insertHR, $alltuples);
										//echo "Hold request for callnumber: '$entercn' placed!";
										
										
										?>
								<html>
									<p id="success"> <br/> <br/> Hold request placed! </p>
								</html>
							<?php
										OCICommit($db_conn);
										$flipflopREQUEST = true;									
									}
									else
									{
										$flipflopREQUEST = true;
										//echo "Request Denied: You've already requested a hold request for this book. We'll notify and hold for you once it becomes available";
										?>
											<script type="text/javascript">
                                               alert("Request Denied: You've already requested a hold for this book. We'll notify you once it becomes available.");
                                         	</script>
                                        <?php
									}
								}
								else if ($flipflopREQUEST  == false)
								{
									//echo "There is still at least one copy in the library. Hurry up and check it out!";
									$flipflopREQUEST = true;
									?>
										<script type="text/javascript">
                                           alert("There is still at least one copy in the library. Hurry up and check it out!");
                                        </script>
                                    <?php
								}
							}
						}
						if(!$flipflopREQUEST)
						{
							//echo "Invalid Call Number. Please double check your information then try again.";
							?>
								<script type="text/javascript">
                                   alert("Invalid Call Number. Please double check your information then try again.");
                               	</script>
                            <?php
							
						}
						
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
