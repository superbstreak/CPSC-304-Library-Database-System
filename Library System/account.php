<!-- ACCOUNT.PHP STATUS: FINISHED, NOT REFINED -->

<html>
	<head>
		<title>My Account Dashboard</title>
        <link rel="stylesheet" type= "text/css" href="style.css">
		<meta charset = "utf-8" />
		<script type ="text/javascript">
			function payNow()
			{
				window.location = "payfines.php"								// REDIRECT TO account page for task 2
			}
			function holdreqNow()
			{
				window.location = "holdrequest.php"								// REDIRECT TO account page for task 2
			}
			function searchNow()
			{
				window.location = "search.php"								// REDIRECT TO account page for task 2
			}
			function logoutNow()
			{
				window.location = "borrower.php"								// REDIRECT TO account page for task 2
			}
		</script>
	</head>
    <h1 id="title"> Welcome to your profile dashboard! </h1>
    	<body>
		<center>
		<form Action = "account.php" method = "POST">
		<input type ="submit" value ="Pay Fine" name = "paynow" id="buttonSide">
		<input type ="submit" value ="Place Hold Request" name = "holdnow" id="buttonSide">
		<input type ="submit" value ="Search" name = "searchnow" id="buttonSide">
		<input type ="submit" value ="Logout" name = "logoutnow" id="buttonSide">
        <br/> <br/>
		</form></center>
		<?php 
			// AUTHORIZATION SUCCESS -> ACCOUNT
			include ('OracleConn.php');
			include ('globalmethods.php');
			$success = True;
			$loginID = $_COOKIE["loginID"];
			//$db_conn = ocilogon("ora_y4d8", "a42764118", "ug");
			$db_conn = ocilogon($oracleUser, $oraclePass, "ug");
			
			if (array_key_exists('holdnow', $_POST))
			{
				echo "<script> holdreqNow(); </script>";
			}
			if (array_key_exists('searchnow', $_POST))
			{
				echo "<script> searchNow(); </script>";
			}
			if (array_key_exists('logoutnow', $_POST))
			{
				//cookie expire
				setcookie("loginID", "",time()-3600);	
				setcookie("loginPW", "", time()-3600);	
				echo "<script> logoutNow(); </script>";
				//header( 'Location: http://www.ugrad.cs.ubc.ca/~' . $undergrad . '/payfines.php' );
			}
			
			
			if ($db_conn)
			{
				$sql = "SELECT * FROM borrower WHERE bid='$loginID'";
				$response = executePlainSQL($sql);
				$array = oci_fetch_array($response, OCI_NUM);
				
				//=================== PROFILE INFO ===========================
				//echo "<b><center> Welcome to your profile dashboard </center></b> <br/>";
				//echo "<b>_________________________________________________________________________</b><br/>";
				//echo "<b> Account Information: </b> <br/>";
				?>
                    <div id="tabletitle">
                    	<br>
                        -----------------------------------------------------------------------------------
                        <br/>
                    	Account Information:
                        <br/>
                   	</div> 
                
                	 <table cellpadding=5px id="table2"> <tr> <td><b> Borrower </b> </td>
                        									<td> <b>Password </b></td>
                                                            <td> <b>Name</b> </td>
                          							   </tr>
                        
                 <?php
				
				echo "<tr>  <td>". $array[0]. "</td>
								<td>". $array[1]."</td>
								<td>". $array[2]."</td></tr></table>";
				
				
				echo "<table cellpadding = 5px> <tr> <td><center><b>Address</b></center></td></tr>";
				
				echo "<tr>  <td>". $array[3]. "</td></tr></table>";
				
				echo "<table cellpadding = 5px> <tr> <td><center><b>Phone Number</b></center></td>
															 <td><center><b>Email</b></center></td></tr>";
				
				echo "<tr>  <td>". $array[4]. "</td>
								<td>". $array[5]."</td></tr></table>";
				
				echo "<table cellpadding = 5px> <tr> <td><center><b>Expire On</b></center></td>
															 <td><center><b>Account Type</b></center></td>
															 <td><center><b>SIN / Student #</b></center></td></tr>";
				
				echo "<tr>  <td>". $array[7]. "</td>
							<td>". $array[8]."</td>
							<td>". $array[6]."</td></tr></table>";
			
				//echo "<b>_________________________________________________________________________</b><br/>";
				oci_free_statement($response);
				//=================== Currently Borrowed =====================
				$sqlborrowing = "SELECT * FROM borrowing INNER JOIN bookcopy 
								 ON borrowing.callnumber = bookcopy.callnumber AND borrowing.copyno = bookcopy.copyno 
								 WHERE bid = '$loginID' and status = 'out'";
				$responseBORR = executePlainSQL($sqlborrowing);
				$nrows = oci_fetch_all($responseBORR, $arrayBORR, null, null, OCI_FETCHSTATEMENT_BY_ROW);
				//echo "<b> Currently Borrowed (not yet returned):</b> <br/>";
				//echo "<br/>";
				
				?>
                    <div id="tabletitle">
                        	-----------------------------------------------------------------------------------
                            <br/>
                    		Currently Borrowed (not yet returned):
                            <br/>
                   		</div> 
                <?php
				
				echo "<table cellpadding = 5px> <tr> <td><b>Borrower ID</b></td>
													 <td><b>Call Number</b></td>
													 <td><b>Copy Number</b></td>
													 <td><b>Out Date</b></td>
													 <td><b>In Date</b></td></tr>";
				//var_dump($arrayBORR);
				foreach ($arrayBORR as $row)
				{
					echo "<tr>  <td><center>".$row['BORID']. "</center></td>
								<td><center>".$row["CALLNUMBER"]."</center></td>
								<td><center>".$row["COPYNO"]."</center></td>
								<td><center>".$row["OUTDATE"]."</center></td>
								<td><center>".$row["INDATE"]."</center></td></tr>";
				}
				echo "</table>";
				oci_free_statement($responseBORR);
				//=================== Hold Requests ==========================
				$sqlholds = "SELECT * FROM holdRequest WHERE bid='$loginID'";
				$responseHOLD = executePlainSQL($sqlholds);
				$nrows = oci_fetch_all($responseHOLD, $arrayHOLD, null, null, OCI_FETCHSTATEMENT_BY_ROW);
				
				//echo "<b>_________________________________________________________________________</b><br/>";
				//echo "<b> Placed Hold Requests: </b> <br/>";
				?>
                	<div id="tabletitle">
							-----------------------------------------------------------------------------------
                            <br/>
                    		Placed Hold Requests:
                            <br/>
                   		</div>
                <?php	
				
				echo "<table cellpadding = 5px> <tr> <td><b>Hold Request ID</b></td>
													 <td><b>Call Number</b></td>
													 <td><b>Issued Date</b></td>
													 <td><b>Status</b></td></tr>";
				//var_dump($arrayHOLD);
				foreach ($arrayHOLD as $row)
				{
					$notification = "";
					echo "<tr>  <td><center>".$row['HID']. "</center></td>
								<td><center>".$row["CALLNUMBER"]."</center></td>
								<td><center>".$row["ISSUEDDATE"]."</center></td>";
					
					$tempCN = $row["CALLNUMBER"];
					
					// get number of book that is currently on hold with the matching callnumber
					$sqlstatus = "SELECT count(*) AS ho FROM bookcopy WHERE callnumber = '$tempCN' AND status = 'on-hold'";
					$responseSTATUS = executePlainSQL($sqlstatus);
					$statusValidHOLD = oci_fetch_array($responseSTATUS, OCI_NUM);
					$availableHOLD = $statusValidHOLD[0];
					if (strcmp($availableHOLD, "0") != 0)
					{
						// get the bid that requested to hold this book
						$sqlholder = "SELECT bid FROM holdrequest WHERE callnumber = '$tempCN' AND rownum<= $availableHOLD";
						$responseHOLDER = executePlainSQL($sqlholder);
						$nrows = oci_fetch_all($responseHOLDER, $arrayHOLDER, null, null, OCI_FETCHSTATEMENT_BY_ROW);
						foreach ($arrayHOLDER as $rowh)
						{
							if (strcmp($rowh['BID'], $loginID) == 0)
							{
								$notification =  "Ready! Currently on hold for you.";
							}
						}
					}
					if (strcmp($notification, "Ready! Currently on hold for you.") != 0)
						$notification = "Not Ready";
					
					echo "<td>".$notification."</td></tr>";
				}
				echo "</table><";
				echo "If any your requested item's status has changed, you will also receive an email update from 304library@no-reply.com";
				
				oci_free_statement($responseHOLD);				
				//=================== Outstanding Fines ======================
				$sqlfines = "SELECT fine.borid, fine.fid, fine.issueddate, fine.paiddate, fine.amount, borrowing.bid 
									FROM fine INNER JOIN borrowing 
									ON borrowing.borid=fine.borid
									WHERE bid='$loginID' AND paidDate IS NULL";
				
				$responseFINE = executePlainSQL($sqlfines);
				$nrows = oci_fetch_all($responseFINE, $arrayFINE, null, null, OCI_FETCHSTATEMENT_BY_ROW);
				
				//echo "<b>_________________________________________________________________________</b><br/>";
				//echo "<b> Outstanding Fines:</b> <br/>";
				?>
                    	<div id="tabletitle">
                        	-----------------------------------------------------------------------------------
                            <br/>
                    		Outstanding Fines:
                            <br/>
                   		</div> 
                <?php
				
				echo "<table cellpadding = 5px> <tr> <td><b>Fine ID</b></td>
													 <td><b>Issued Date</b></td>
													 <td><b>Amount</b></td></tr>";
				//var_dump($arrayFINE);
				$flipflopFINE = false;
				foreach ($arrayFINE as $row)
				{
					$flipflopFINE = true;
					echo "<tr>  <td><center>".$row['FID']. "</center></td>
								<td><center>".$row["ISSUEDDATE"]."</center></td>
								<td><center> $".$row["AMOUNT"]."</center></td></tr>";
				}
				echo "</table>";
				oci_free_statement($responseFINE);
				
				//=================== PAID FINE HISTORY ======================
				$sqlfinesP = "SELECT fine.borid, fine.fid, fine.issueddate, fine.paiddate, fine.amount, borrowing.bid 
									FROM fine INNER JOIN borrowing 
									ON borrowing.borid=fine.borid
									WHERE bid='$loginID' AND paidDate IS NOT NULL";
				
				$responseFINEP = executePlainSQL($sqlfinesP);
				$nrows = oci_fetch_all($responseFINEP, $arrayFINEP, null, null, OCI_FETCHSTATEMENT_BY_ROW);
				
				
				//echo "<b>_________________________________________________________________________</b><br/>";
				//echo "<b>Payment History (Fines):</b> <br/>";
				?>
                    	<div id="tabletitle">
                        	-----------------------------------------------------------------------------------
                            <br/>
                    		Payment History (Fines):
                            <br/>
                   		</div> 
                <?php
				
				echo "<table cellpadding = 5px> <tr> <td><b>Fine ID</b></td>
													 <td><b>Issued Date</b></td>
													 <td><b>Amount</b></td>
												   	 <td><b>Payment Received On</b></td></tr>";
				//var_dump($arrayFINEP);
				foreach ($arrayFINEP as $row)
				{
					
					echo "<tr>  <td><center>".$row['FID']. "</center></td>
								<td><center>".$row["ISSUEDDATE"]."</center></td>
								<td><center> $".$row["AMOUNT"]."</center>
								<td><center>" .$row["PAIDDATE"]."</center></td></tr>";
				}
				echo "</table>";
				

				if (array_key_exists('paynow', $_POST) && $flipflopFINE == true)
				{
					echo "<script> payNow(); </script>";
					//header( 'Location: http://www.ugrad.cs.ubc.ca/~' . $undergrad . '/payfines.php' );
				}			
				
				
				oci_free_statement($responseFINEP);
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

<?php

?>
