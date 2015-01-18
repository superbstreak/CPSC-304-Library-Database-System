<!-- generatePopularReport.php -->
<!-- COMPLETED -->

<html>
	<head>
		<title> Report of Popular Books </title>
		<meta charset = "utf-8" />
		<link rel="stylesheet" type= "text/css" href="style.css"/>
	</head>
	<body>
		<h1 id="command"> Please enter a year and the maximum amount of results you want. </h1> <br/>
		<form action="generatePopularReport.php" method="post" id="forms">
			Enter a year: <input type="text" name="year" value="<?php echo $_POST["year"];?>" id="forms" />
			<br/> <br/>
			Enter a number (list top <i> n </i> books): <input type="text" name="num" value="<?php echo $_POST["num"];?>" id="forms" />
			<br/> <br/>
			<input type="submit" value="Search" name="generateReport" id="button" />
		</form>
		<form action="librarian.php" method="post" id="forms"> 
			<input type="submit" value="Back to Librarian"
			name="returnToLibrarianView" id="button" />
		</form>
	
		<?php
		include('globalmethods.php');
		include('OracleConn.php');
		$success = True;
		
		//creating connection to Oracle databas
		$db_conn = ocilogon($oracleUser, $oraclePass, "ug");
		
		if ($db_conn) {
			if ($_POST && $success) {
				// n is the number of search results to display
				$n = $_POST["num"];
				$year = $_POST["year"];
				if (!is_numeric($year) || $year < -4713 || $year > 9999){
					?>
                    <script type="text/javascript">
						alert("Error : Year invalid. Year must be a number between -4713 and 9999.");
					</script>
					<?php
				} else {
					if (!is_numeric($n) || $n < 0){
						?>
						<script type="text/javascript">
							alert("Error : n has to be a number greater than 0.");
						</script>
						<?php
					} else {
						/*
						// KEPT THIS FOR ARCHIVING PURPOSES, BUT I HAVE DECIDED AGAINST ALLOWING
						// EMPTY N.
						// Deal with empty n, in this case display all results (up to maxint)
						if($n == '') {
							$n = PHP_INT_MAX;
							?>
							<p id=success> No n specified. Displaying all results. </p>
							<?php
						}
						*/
						
						// Search for the most commonly borrowed book in a given year
						$sql = "SELECT b.callNumber, b.isbn, b.title, b.mainAuthor, b.publisher, cnt
										FROM book b
										INNER JOIN
										(SELECT callNumber, count(callNumber) cnt
										FROM borrowing
										WHERE outDate BETWEEN '".$_POST["year"]."-01-01'
										AND '".$_POST["year"]."-12-31'
										GROUP BY callNumber) c
										ON b.callNumber = c.callNumber
										ORDER BY cnt DESC, b.callNumber";
						$responseBORR = executePlainSQL($sql);
						$nrows = oci_fetch_all($responseBORR, $arrayBORR, null, null, OCI_FETCHSTATEMENT_BY_ROW);
						
						if (count($arrayBORR) >= 1 && $_POST["num"] != 0) {
							// Display the search results
							echo "<table cellpadding=5px>
											<tr><td><b>Call Number</b></td>
													<td><b>Times Borrowed</b></td>
													<td><b>ISBN</b></td>
													<td><b>Title</b></td>
													<td><b>Main Author</b></td>
													<td><b>Publisher</b></td>
											</tr>";
						}
										
						// Display only up to n of the search results
						foreach ($arrayBORR as $row) {
							if ( $n > 0) {
								echo "<tr> <td>".$row['CALLNUMBER']."</td>
													 <td>".$row['CNT']."</td>
													 <td>".$row['ISBN']."</td>
													 <td>".$row['TITLE']."</td>
													 <td>".$row['MAINAUTHOR']."</td>
													 <td>".$row['PUBLISHER']."</td>
											</tr>";
								$n--;
							} else {
								break;
							}
						}
						if (count($arrayBORR) >= 1 && $_POST["num"] != 0) {
							echo "</table>";
						}
						
						// Print a message if the query yields no results
						if (count($arrayBORR) < 1 || $_POST["num"] == 0) {
							?>
								<html>
									<link rel="stylesheet" type= "text/css" href="style.css">
									<p id="error">  No results match your query. </p>
								</html>	
							<?php
						}
		
						oci_free_statement($responseBORR);
					}
				}
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
	