<!-- addBook.php -->
<!-- COMPLETED -->

<html>
	<head>
		<title> Add Book </title>
		<meta charset = "utf-8" />
		<link rel="stylesheet" type= "text/css" href="style.css"/>
	</head>
	
	<body>
		<h1 id="command"> Please enter details of book. </h1> <br/>
		<form action="addBook.php" method="post" id="forms">
			Call Number: <input type="text" name="callNumber" value="<?php echo $_POST["callNumber"];?>" />
			<br/> <br/>
			ISBN: <input type="text" name="isbn" value="<?php echo $_POST["isbn"];?>" />
			<br/> <br/>
			Title: <input type="text" name="title" value="<?php echo $_POST["title"];?>" />
			<br/> <br/>
			Main Author: <input type="text" name="mainAuthor" value="<?php echo $_POST["mainAuthor"];?>" />
			<br/> <br/>
			Publisher: <input type="text" name="publisher" value="<?php echo $_POST["publisher"];?>" />
			<br/> <br/>
			Year: <input type="text" name="year" value="<?php echo $_POST["year"];?>" />
			<br/> <br/>
			Sub-Authors (separated with commas): <input type="text" name="subAuthors" value="<?php echo $_POST["subAuthors"];?>" />
			<br/> <br/>
			Subjects (separated with commas): <input type="text" name="subjects" value="<?php echo $_POST["subjects"];?>" />
			<br/> <br/>
			<input type="submit" value="Submit" name="addNewBook" id="button">
		</form>
		<form action="librarian.php" method="post" id="forms"> 
			<input type="submit" value="Back to Librarian" name="returnToLibrarianView" id="button" />
		</form>
			
		<?php
		include('globalmethods.php');
		include ('OracleConn.php');
		$success = True;
		
		//creating connection to Oracle databas
		$db_conn = ocilogon($oracleUser, $oraclePass, "ug");

		if ($db_conn) {
			if ($_POST && $success) {
				if (array_key_exists("addNewBook", $_POST)) {

					$subject = $_POST['subjects'];
					$callNumber = $_POST["callNumber"];

					if ($callNumber == "") {

						?>
							<script type="text/javascript">
								alert("Error : A call number must be entered.");
							</script>

						<?php

					} else if ($subject == "") {

						?>
							<script type="text/javascript">
								alert("Error : A subject must be entered.");
							</script>

						<?php

					} else {
					
						$isbn = $_POST["isbn"];
						$title = $_POST["title"];
						$year = $_POST["year"];
						$sql = "INSERT INTO book VALUES
										('".$_POST["callNumber"]."', '".$isbn."', '".$_POST["title"]."', '"
										.$_POST["mainAuthor"]."', '".$_POST["publisher"]."', '".$year."')";
						
						if ($_POST['subAuthors'] !== "") {
							$authsql = "INSERT INTO hasAuthor VALUES
												 ('".$callNumber."', '".$_POST["subAuthors"]."')";
						}
						$subsql = "INSERT INTO hasSubject VALUES
											 ('".$callNumber."', '".$_POST["subjects"]."')";
						$copysql = "SELECT b.callNumber, cnt
												FROM book b
												INNER JOIN
												(SELECT callNumber, count(callNumber) cnt
												FROM bookCopy
												WHERE callNumber = '".$callNumber."'
												GROUP BY callNumber) c
												ON b.callNumber = c.callNumber
												ORDER BY b.callNumber";
						
						// add a book copy to the database
						$responseBORR = executePlainSQL($copysql);
						$nrows = oci_fetch_all($responseBORR, $arrayBORR, null, null, OCI_FETCHSTATEMENT_BY_ROW);
						if (count($arrayBORR) < 1) {
							if ( ((strlen($isbn) == 13) && (is_numeric($isbn))) || (strlen($isbn) == 0) ) {
								if ( ((strlen($year) <= 4) && (is_numeric($year))) || (strlen($year) == 0)) {
									executePlainSQL($sql);
									$firstcopysql = "INSERT INTO bookCopy VALUES
																	 ('".$callNumber."', 'C1', 'in')";
									executePlainSQL($firstcopysql);
									if ( strlen($_POST["subAuthors"]) <= 100) {
										if ( strlen($_POST["subAuthors"]) > 0) {
											executePlainSQL($authsql);
										}
									} else {
										//echo "<p id='error'> Sub-Authors entry is invalid. </p>";
										?>
											<script type="text/javascript">
                                                alert("Error : Sub-Authors entry is invalid.");
                                            </script>        
                                        <?php
										
									}
									
									// Check for valid subjects
									if ( strlen($_POST["subjects"]) <= 100) {
										if ( strlen($_POST["subjects"]) > 0) {
											if ($_POST["subjects"] !== '') {
												executePlainSQL($subsql);
											} else {
												//echo "<p id='error'> Subjects entry cannot be empty. </p>";
												?>
											<script type="text/javascript">
                                                alert("Error : Subjects entry cannot be empty.");
                                            </script>        
                                        <?php
											}
										}
									} else {
										//echo "<p id='error'> Subjects entry is invalid. </p>";
										?>
											<script type="text/javascript">
                                                alert("Error : Invalid subjects entry. Too long.");
                                            </script>        
                                        <?php
									}
									echo "<p id='success'> Successfully added book!</p>";
								} else {
									//echo "<p id='error'> Invalid year. </p>";
									?>
										<script type="text/javascript">
                                            alert("Error : Invalid year.");
                                        </script>        
                                    <?php
								}
							} else {
								//echo "<p id='error'> Invalid ISBN. </p>";
								?>
									<script type="text/javascript">
                                        alert("Error : Invalid ISBN.");
                                    </script>        
                                <?php
							}
						} else {
								foreach($arrayBORR as $row) {
									$nextCopyNum = $row['CNT']+1;
									$subjects = $_POST["subjects"];
									if (is_numeric($year) && strlen($year) < 5 || strlen($year) == 0) {
										if (is_numeric($isbn) && strlen($isbn) == 13 || strlen($isbn) == 0) {
											if ($subjects !== "" && strlen($subjects) < 100) {
												$copyinsertsql = "INSERT INTO bookCopy VALUES
																				 ('".$row['CALLNUMBER']."', 'C".$nextCopyNum."', 'in')";
												executePlainSQL($copyinsertsql);
												echo "<p id='success'> Successfully added book copy ".$nextCopyNum."!</p>";
												//echo "Entered Copy Number : ".$nextCopyNum."<br/>";
											} else {
												//echo "<p id='error'> Invalid subjects entry. The subjects entry cannot be null. </p>";
												?>
													<script type="text/javascript">
                                                        alert("Error : Invalid subjects entry. The subjects entry cannot be null.");
                                                    </script>        
                                                <?php
											}
										} else {
											//echo "<p id='error'> Invalid ISBN.</p>";
											?>
												<script type="text/javascript">
                                                    alert("Error : Invalid ISBN.");
                                                </script>        
                                            <?php
										}
									} else {
										//echo "<p id='error'> Invalid year.</p>";
										?>
												<script type="text/javascript">
                                                    alert("Error : Invalid year.");
                                                </script>        
                                            <?php
									}
							}
						}
					}
				}
				
				oci_free_statement($responseBORR);
				OCICommit($db_conn);
			}
		} else {
			echo "Connection Error! DEBUG: DB_CONN";
			$e = OCI_error();
			echo htmlentities($e['message']);
		}
		OCILogoff($db_conn);
		$success = False;
		?>
	</body>
</html>