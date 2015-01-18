<<!DOCTYPE unspecified PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!-- AUTHORIZATIO.PHP STATUS: FINISHED, NOT REFINED -->

<html>
	<head>
		<title>Account Authorization</title>
		<meta charset = "utf-8">
		<script type ="text/javascript">
			function unsuccessfulLogin()
			{
				//window.location =  "borrower.php";							// HELP!!!
				// REDIRECT TO LOGIN PAGE?
			}
			function successfulLogin()
			{
				window.location = "account.php"								// REDIRECT TO account page for task 2
			}
		</script>
	</head>
	<body>
		<?php
			include ('globalmethods.php');
			$success = True;												// Error Tracker like others
			include ('OracleConn.php');
			$db_conn = ocilogon($oracleUser, $oraclePass, "ug");			// establish connection with username&pw
			$loginid = $_GET['loginID'];
			//echo "DEBUG: BID $loginid !";
			$loginpw = $_GET['loginPWD'];
			//echo "DEBUG: PW $loginpw !";
			
			if ($loginid == NULL || $loginpw == NULL)											// bad request, id is null!
			{
				//echo "DEBUG: AUTH-NULL-ID";
				?>
															<script type="text/javascript">
				                                               alert("Incorrect username or password.");
				                                         	</script>
				                                        <?php
				 header( 'Location: http://www.ugrad.cs.ubc.ca/~' . $undergrad . '/loginFAIL.html' );
			}
			else															// id not null, connect!
			{
				if ($db_conn)												// check connection
				{
					$sql = "SELECT password FROM borrower WHERE bid='$loginid'";
					//echo "DEBUG: $sql !";
					$response = executePlainSQL($sql);
					oci_fetch($response);
					$correctpw = oci_result($response, "PASSWORD");			// CHANGE BASED ON TAB
					$loginpw = (string)$loginpw;
					$correctpw = (string)$correctpw;
					if ($loginpw != $correctpw)								// bad password
					{
						//echo "Incorrect username or password. $loginpw vs $correctpw";
						?>
																	<script type="text/javascript">
						                                               alert("Incorrect username and password");
						                                         	</script>
						                                        <?php
						                                        
						 header( 'Location: http://www.ugrad.cs.ubc.ca/~' . $undergrad . '/loginFAIL.html' );
						//echo "<script> loginRequestDenied(); </script>";
					}
					else													// good password
					{
						setcookie("loginID", "$loginid", time()+60*60*3);	// cookie for 3
						setcookie("loginPW", "$loginpw", time()+60*60*3);	// cookie for 3
						echo "<script> successfulLogin(); </script>";
					}
				}
				else
				{
					echo "Connection Error! DEBUG: DB_CONN";
					$e = oci_error();										// For OCILogon errors pass no handle
					echo htmlentities($e['message']);
				}
			}
			
		?>
	</body>
</html>

