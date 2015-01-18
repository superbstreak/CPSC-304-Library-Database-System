<!-- LIBRARY : main page -->
<html>

		<head>
                <title> Library </title>
                <link rel="stylesheet" type= "text/css" href="styleAuthorisationPage.css">
        </head> 
        
        <body id="tablestyle"> 

		<h2 id="title"> Welcome!  You are now using the Library Database System! </h2>
        
            <img border="0" src="bookPicture.png" alt="Pulpit rock" width="155" height="200">

			<!--Form for user to select their authorization level : Librarian, Clerk or Borrower-->
		<form action="" method="post" id="forms">

			<h3 id="command"> Please select your authorization level: </h3>
			<select name="authorizationLevel" id="dropdown" >
				<option value="Librarian" id="dropdown"> Librarian </option>
				<option value="Clerk" id="dropdown"> Clerk </option>
				<option value="Borrower" id="dropdown"> Borrower </option>
			</select>
            <br/> <br/>
			<input type="submit" name="submit" id="button" />
		</form>
        </h1>

	</body>
</html>

<?php

include('OracleConn.php');

// Authorization level for the user : Librarian, Clerk or Borrower
$AuthorizationLevel;

if (isset ($_POST['submit'])) {
	$AuthorizationLevel = $_POST['authorizationLevel'];
	if ($AuthorizationLevel ==  "Librarian") {
		header( 'Location: http://www.ugrad.cs.ubc.ca/~' . $undergrad . '/librarian.php' );
	} else if ($AuthorizationLevel == "Clerk") {
		header( 'Location: http://www.ugrad.cs.ubc.ca/~' . $undergrad . '/clerk.php' );
	} else if ($AuthorizationLevel == "Borrower") {
		header( 'Location: http://www.ugrad.cs.ubc.ca/~' . $undergrad . '/borrower.php' );
	}
}

?>