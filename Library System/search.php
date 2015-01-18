<!-- SEARCH.PHP STATUS: FINISHED, NOT REFINED -->
<html>
	<head>
		<title>Search</title>
        <link rel="stylesheet" type= "text/css" href="style.css">
		<meta charset = "utf-8" />
		<script type ="text/javascript">
			function backtoAccount()
			{
				window.location = "account.php"								// REDIRECT TO account page for task 2
			}
			function backtoBorrower()
			{
				window.location = "borrower.php"								// REDIRECT TO account page for task 2
			}
		</script>
        <!--
        <style>
		table,th,td {
			border:1px;
			border-color:#B8CCE4;
			border-style:solid;
			font-family:Verdana, Geneva, sans-serif;
			font-size:13px;
			color:#666;
		}
		
		</style>
        -->
	</head>	
	<body>
        <h1 id= "command"> Please enter search details.</h1>
		
		<form Action = "search.php" method = "POST" id="forms">
		<br/>
        Title Keywords:
		<input type ="text" name = "titlekeys" size ="20" id="forms" > <br/><br/>
		Author Keywords: 
		<input type ="text" name = "authorkeys" size ="20" id="forms"> <br/><br/>
		Subject Keywords: 
		<input type ="text" name = "subjectkeys" size ="20" id="forms"> <br/><br/>
		
		<input type ="submit" value ="Search" name = "keywordsubmit" id="buttonside">
		<input type ="submit" value ="Back To Account" name = "backtoAcc" id="buttonside">
		</form>
	
	<?php
		$success = True; 	
		include ('globalmethods.php');	
		include ('OracleConn.php');									// keep track of errors. REDIRECT IF NONE
		$db_conn = ocilogon($oracleUser, $oraclePass, "ug");			// establish connection with username&pw
		
		if (array_key_exists('backtoAcc', $_POST))
				{
					if(isset($_COOKIE['loginID']))
						echo "<script> backtoAccount(); </script>";
					else
						echo "<script> backtoBorrower(); </script>";
				}
		
		
		// Connect Oracle...	
		if ($db_conn)	
		{
			if (array_key_exists('keywordsubmit', $_POST))
			{
				$searchrequest = array(
						":bind1" => $_POST['titlekeys'],
						":bind2" => $_POST['authorkeys'],
						":bind3" => $_POST['subjectkeys']
				);
				$checkone = false;
				$checktwo = false;
				$checkthree = false;
				$titlewhere = '';
				$authorwhere = '';
				$subjectwhere = '';
				
				$title_statement = "SELECT * FROM book
							INNER JOIN hasauthor ON book.callnumber = hasauthor.callnumber
							INNER JOIN hassubject ON book.callnumber = hassubject.callnumber
							WHERE ";
				
				if (!empty($searchrequest[":bind1"]))		// title
				{
					$checkone = true;
					$trimRequestone = trim($searchrequest[":bind1"]);
					if (trimRequestone != "")
					{
						$furthertrimone = preg_replace('/[[:space:]]+/', ' ', $trimRequestone);
						$titleKeywords = explode(' ', $furthertrimone);
						foreach($titleKeywords as $titlekeyword)
						{
							$titlekeyword = strtolower($titlekeyword);
							$titlewhere .= 'LOWER(title) LIKE \'%' . $titlekeyword . '%\' OR ';
						}
						$title_statement = $title_statement. "(" .rtrim($titlewhere, ' OR').")";
					}
				}
				if (!empty($searchrequest[":bind2"]))		// author
				{
					$checktwo = true;
					$trimRequesttwo = trim($searchrequest[":bind2"]);
					if (trimRequesttwo != "")
					{
						$furthertrimtwo = preg_replace('/[[:space:]]+/', ' ', $trimRequesttwo);
						$authorKeywords = explode(' ', $furthertrimtwo);
						foreach($authorKeywords as $authorkeyword)
						{
							$authorkeyword = strtolower($authorkeyword);
							$authorwhere .= 'LOWER(ANAME) LIKE \'%' . $authorkeyword . '%\' OR ';
						}
						if ($checkone)
							$title_statement = $title_statement. " AND (" .rtrim($authorwhere, ' OR').")";
						else
							$title_statement = $title_statement. " (" .rtrim($authorwhere, ' OR').")";
					}
					
				}
				if (!empty($searchrequest[":bind3"]))		// subject
				{
					$checkthree = true;
					$trimRequestthree = trim($searchrequest[":bind3"]);
					if (trimRequestthree != "")
					{
						$furthertrimthree = preg_replace('/[[:space:]]+/', ' ', $trimRequestthree);
						$subjectKeywords = explode(' ', $furthertrimthree);
						foreach($subjectKeywords as $subjectkeyword)
						{
							$subjectkeyword = strtolower($subjectkeyword);
							$subjectwhere .= 'LOWER(SUBJECT) LIKE \'%' . $subjectkeyword . '%\' OR ';
						}
						if ($checkone || $checktwo)
							$title_statement = $title_statement. " AND (" .rtrim($subjectwhere, ' OR'). ")";
						else
							$title_statement = $title_statement. " (" .rtrim($subjectwhere, ' OR'). ")";
					}
				}
				
				// ========= send req
				
				if ($checkone || $checktwo || $checkthree)
				{
					
					$responseBOOK = executePlainSQL($title_statement);
					$nrows = oci_fetch_all($responseBOOK, $arrayBOOK, null, null, OCI_FETCHSTATEMENT_BY_ROW);		
					
					if (!empty($arrayBOOK))
					{
						echo "<br/><br/>";
						/*
						echo "<center><table cellpadding = 5px> <tr> <td><b>Calls Number</b></td>
															 <td><b>ISBN</b></td>
															 <td> <b>Title</b></td>
															 <td> <b>Main Author</b></td>
															 <td> <b>Publisher</b></td>
															 <td> <b>Year</b></td>
															 <td> <b>Author(s)</b></td>
															 <td> <b>Subject</b></td>
															 <td> <b>In</b></td>
															 <td> <b>Out</b></td></tr>";
															 */
															 
						?>
                        <center> <table cellpadding=5px id="table"> <tr> <td><b> Call Number </b> </td>
                        									<td> <b>ISBN </b></td>
                                                            <td> <b>Title</b> </td>
                                                            <td><b> Main Author </b></td>
                                                            <td><b> Publisher </b></td>
                                                            <td><b> Year</b> </td>
                                                            <td><b> Author(s)</b> </td>
                                                            <td><b> Subject </b></td>
                                                            <td> <b>In </b></td>
                                                            <td> <b>Out </b></td>
                          							   </tr>
                        
                        <?php
						
						
						
						$tempForCount= '';
						foreach ($arrayBOOK as $row)
						{
							$calln = $row['CALLNUMBER'];
							$sql_count_in =  "SELECT count(*) AS i FROM bookcopy WHERE status =  'in' AND callnumber = '$calln'";
							$sql_count_out = "SELECT count(*) AS o FROM bookcopy WHERE status = 'out' AND callnumber = '$calln'";
							$responseCNTin = executePlainSQL($sql_count_in);
							$in = oci_fetch_array($responseCNTin, OCI_NUM);
							$responseCNTout = executePlainSQL($sql_count_out);
							$out = oci_fetch_array($responseCNTout, OCI_NUM);
							echo "<tr>  <td>".$row['CALLNUMBER']. "</td>
									<td>".$row['ISBN']."</td>
									<td>".$row['TITLE']."</td>
									<td>".$row['MAINAUTHOR']."</td>
									<td>".$row['PUBLISHER']."</td>
									<td>".$row['YEAR']."</td>
									<td>".$row['ANAME']."</td>
									<td>".$row['SUBJECT']."</td>
									<td>".$in[0]."</td>
									<td>".$out[0]."</td></tr>";
							oci_free_statement($responseCNTin);
							oci_free_statement($responseCNTout);
						}
						echo "</table></center>";
					}
					else
					{
						//echo "</table></center>";
						//echo "<b>Sorry no match found in our system with the given keywords :(</b>";
						?>
						<html>
							<link rel="stylesheet" type= "text/css" href="style.css">
                            <br>
							<p id="error">  No match found in our system with the given keywords :( </p>
						</html>	
					<?php
					}
					oci_free_statement($responseBOOK);
				}
				else
				{
					//echo "Please enter some keywords first.";
					?>
							<script type="text/javascript">
                         	   alert("Please enter some keywords first.");
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










