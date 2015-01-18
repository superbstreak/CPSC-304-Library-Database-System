<!-- GenerateCheckedOutReport.php -->
<!-- COMPLETED -->

<html>
	<head>
		<title> Report of Checked Out Books </title>
		<meta charset = "utf-8" />
		<link rel="stylesheet" type= "text/css" href="style.css"/>
	</head>
	<body>
		<h1 id="command"> Please enter a subject, or leave field empty for any subject. </h1> <br/>
		<form action="generateCheckedOutReport.php" method="post" id="forms">
			Enter a Subject (optional): <input type="text" name="subject" value="<?php echo $_POST["subject"];?>" id="forms"/>
			<br/> <br/>
			<input type="submit" value="Search" name="generateReport" id="button" />
		</form>
		<form action="librarian.php" method="post" id="forms"> 
			<input type="submit" value="Back to Librarian" name="returnToLibrarianView" id="button" />
		</form>
	
		<?php
		include('globalmethods.php');
		include('OracleConn.php');
		$success = True;
		
		//creating connection to Oracle databas
		$db_conn = ocilogon($oracleUser, $oraclePass, "ug");
		
		function getOverdueItems($array) {
			// Search for items that are overdue among results in given array
			
			// Retrieve the student borrow time limit
			$response1 = executePlainSQL("SELECT * FROM borrowerType WHERE type = 'Student'");
			oci_fetch_all($response1, $array1, null, null, OCI_FETCHSTATEMENT_BY_ROW);
			if (count($array1) != 1) {
				echo "An error has occurred and the student time limit could not be retrieved";
			} else {
				foreach($array1 as $row) {
					$studentTimeLimit = $row["BOOKTIMELIMIT"];
				}
			}
			
			// Retrieve the staff borrow time limit
			$response2 = executePlainSQL("SELECT * FROM borrowerType WHERE type = 'Staff'");
			oci_fetch_all($response2, $array2, null, null, OCI_FETCHSTATEMENT_BY_ROW);
			if (count($array2) != 1) {
				echo "An error has occurred and the staff time limit could not be retrieved";
			} else {
				foreach($array2 as $row) {
					$staffTimeLimit = $row["BOOKTIMELIMIT"];
				}
			}
			
			// Retrieve the faculty borrow time limit
			$response3 = executePlainSQL("SELECT * FROM borrowerType WHERE type = 'Faculty'");
			oci_fetch_all($response3, $array3, null, null, OCI_FETCHSTATEMENT_BY_ROW);
			if (count($array3) != 1) {
				echo "An error has occurred and the faculty time limit could not be retrieved";
			} else {
				foreach($array3 as $row) {
					$facultyTimeLimit = $row["BOOKTIMELIMIT"];
				}
			}
			
			// Determine the difference between the current date and the due dates
			// Any borrowings with an inDate value <= to this date represent late items
			$currentDate = date('Y-m-d');
			$studentMaxCheckoutDate = date('Y-m-d', strtotime($currentDate . ' - ' . $studentTimeLimit . ' days'));
			$staffMaxCheckoutDate = date('Y-m-d', strtotime($currentDate . ' - ' . $staffTimeLimit . ' days'));
			$facultyMaxCheckoutDate = date('Y-m-d', strtotime($currentDate . ' - ' . $staffTimeLimit . ' days'));
			
			// List the borid, bid, and call number of overdue items in the given array
			// Search for all students with overdue items
			$response4 = executePlainSQL("SELECT *
																		FROM borrowing bo, borrower b, book bk
																		WHERE bo.bid = b.bid
																		and bk.callNumber = bo.callNumber
																		and bo.inDate IS NULL
																		and b.type = 'Student'
																		and bo.outDate <= TO_DATE('".$studentMaxCheckoutDate."', 'YYYY-MM-DD')");
			oci_fetch_all($response4, $array4, null, null, OCI_FETCHSTATEMENT_BY_ROW);
			
			// Search for all staff with overdue items
			$response5 = executePlainSQL("SELECT *
																		FROM borrowing bo, borrower b, book bk
																		WHERE bo.bid = b.bid
																		and bk.callNumber = bo.callNumber
																		and bo.inDate IS NULL
																		and b.type = 'Staff'
																		and bo.outDate <= TO_DATE('".$staffMaxCheckoutDate."', 'YYYY-MM-DD')");
			oci_fetch_all($response5, $array5, null, null, OCI_FETCHSTATEMENT_BY_ROW);
			
			// Search for all faculty with overdue items
			$response6 = executePlainSQL("SELECT *
																		FROM borrowing bo, borrower b, book bk
																		WHERE bo.bid = b.bid
																		and bk.callNumber = bo.callNumber
																		and bo.inDate IS NULL
																		and b.type = 'Faculty'
																		and bo.outDate <= TO_DATE('".$facultyMaxCheckoutDate."', 'YYYY-MM-DD')");
			oci_fetch_all($response6, $array6, null, null, OCI_FETCHSTATEMENT_BY_ROW);
			
			return array($array4, $array5, $array6);
			
			/* WE NO LONGER WISH TO DISPLAY A SECOND TABLE. THIS IS KEPT FOR ARCHIVING PURPOSES.
			// Display relevant results
			echo "<br/><b> List of overdue items among those checked out: </b><br/>"; 
			echo "<br/>";
			echo "<table>";
			echo "<tr>";
			echo "<td> <b> Borrowing ID </b> </td>";
			echo "<td> <b> Borrower ID </b> </td>";
			echo "<td> <b> Call Number </b> </td>";
			echo "</tr>";
			// Display overdue student items
			foreach($array4 as $row) {
				foreach($array as $orig) {
					if($row['BORID'] == $orig['BORID']) {
						echo "<tr>";
						echo "<td>".$row['BORID']."</td>";
						echo "<td>".$row['BID']."</td>";
						echo "<td>".$row['CALLNUMBER']."</td>";
						echo "</tr>";
					}
				}
			}
			// Display overdue staff items
			foreach($array5 as $row) {
				foreach($array as $orig) {
					if($row['BORID'] == $orig['BORID']) {
						echo "<tr>";
						echo "<td>".$row['BORID']."</td>";
						echo "<td>".$row['BID']."</td>";
						echo "<td>".$row['CALLNUMBER']."</td>";
						echo "</tr>";
					}
				}
			}
			// Display overdue faculty items
			foreach($array5 as $row) {
				foreach($array as $orig) {
					if($row['BORID'] == $orig['BORID']) {
						echo "<tr>";
						echo "<td>".$row['BORID']."</td>";
						echo "<td>".$row['BID']."</td>";
						echo "<td>".$row['CALLNUMBER']."</td>";
						echo "</tr>";
					}
				}
			}
			echo "</table>";
			*/
		}
		
		if ($db_conn) {
			if ($_POST && $success) {
				// Search for items that are currently borrowed, order by call number
				$sql = "SELECT DISTINCT c.borid, c.callNumber, c.bid, c.copyNo, c.outDate, c.inDate, c.subject
								FROM bookCopy bc
								INNER JOIN
								(SELECT b.borid, b.callNumber, b.bid, b.copyNo, b.outDate, b.inDate, s.subject
								FROM borrowing b INNER JOIN hasSubject s
								ON b.callNumber = s.callNumber
								WHERE REGEXP_LIKE(s.subject, '*".$_POST["subject"]."*', 'i')) c
								ON bc.callNumber = c.callNumber
								WHERE c.inDate IS NULL
								ORDER BY callNumber";
								
				// CHANGED: "WHERE bc.status = 'out'" to "WHERE c.inDate IS NULL"
								
				$responseBORR = executePlainSQL($sql);
				oci_fetch_all($responseBORR, $arrayBORR, null, null, OCI_FETCHSTATEMENT_BY_ROW);
	
				// Display search results in a table
				if ($_POST['subject'] !== '') {
					//echo "<b> Currently borrowed items with subjects containing '".$_POST["subject"]."':</b> <br/>";
					
					?>
						<html>
							<link rel="stylesheet" type= "text/css" href="style.css">
							<p id="tabletitle">  Currently borrowed items with subjects containing  <?php echo " " .$_POST["subject"] ?> : </p>
						</html>	
					<?php
					
				} else {
					//echo "<b> Currently borrowed items (no subject entered):</b> <br/>";
					?>
						<html>
							<link rel="stylesheet" type= "text/css" href="style.css">
							<p id="tabletitle">  Currently borrowed items (no subject entered): </p>
						</html>	
					<?php
				}
				//echo "<br/>";
				
				// Print out message if there are no search results, otherwise figure out which items are overdue
				// and generate a list of them
				if (count($arrayBORR) < 1) {
					?>
						<html>
							<link rel="stylesheet" type= "text/css" href="style.css">
							<p id="error">  No results match your query. </p>
						</html>	
					<?php
				} else {
					// Start table
					echo "<table> <tr> <td><b>Borrowing ID</b></td>
														 <td><b>Call No</b></td>
														 <td><b>Borrower ID</b></td>
														 <td><b>Copy No</b></td>
														 <td><b>Out Date</b></td>
														 <td><b>Subject</b></td>
														 <td><b>Borrow Status</b></td>
												</tr>";
					$arrs = getOverdueItems($arrayBORR);
					$studentarr = $arrs[0];
					$staffarr = $arrs[1];
					$facultyarr = $arrs[2];
					foreach ($arrayBORR as $row) {
						echo "<tr> <td>".$row['BORID']."</td>
											 <td>".$row['CALLNUMBER']."</td>
											 <td>".$row['BID']."</td>
											 <td>".$row['COPYNO']."</td>
											 <td>".$row['OUTDATE']."</td>
											 <td>".$row['SUBJECT']."</td>";
						$found = False;
						foreach ($studentarr as $sarr) {
							if ($sarr["BORID"] == $row["BORID"]) {
								$found = True;
								echo "<td>Overdue</td>";
							}
						}
						foreach ($staffarr as $tarr) {
							if ($tarr["BORID"] == $row["BORID"]) {
								$found = True;
								echo "<td>Overdue</td>";
							}
						}
						foreach ($facultyarr as $farr) {
							if ($farr["BORID"] == $row["BORID"]) {
								$found = True;
								echo "<td>Overdue</td>";
							}
						}
						if (!$found) {
							echo "<td>Lent</td>";
						}
						echo "</tr>";
					}
					// End table
					echo "</table>";
				}
				OCI_free_statement($responseBORR);
			}
		} else {
			echo "Connection Error! DEBUG: DB_CONN";
			$e = OCI_error();
			echo htmlentities($e['message']);
		}
		OCIlogoff($db_conn);
		$success = False;
		?>
	</body>
</html>
	