<?php
	// this file includes most of the global function call to dql
	
	function executePlainSQL($input)
	{
		global $db_conn, $success;		//GBval
		$statement = ociparse($db_conn, $input);
		
		if (!$statement)
		{
			echo "<br> PARSE: Cannot parse the following command: " . $input . "<br>";
			$e = oci_error($db_conn);
			echo htmlentities($e['message']);
			$success = False; 	// affects all ops
		}
		$exeOCI = ociexecute($statement, OCI_DEFAULT);
		
		if (!$exeOCI)
		{
			echo "<br> EXECUTE: Cannot execute the following command: " . $input . "<br>";
			$e = oci_error($statement);
			echo htmlentities($e['message']);
			$success = False;
		}
		
		return $statement;
	}
	
	function executeBoundSQL($cmdstr, $list) {
	
		global $db_conn, $success;
		$statement = OCIParse($db_conn, $cmdstr);
	
		if (!$statement) {
			echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($db_conn);
			echo htmlentities($e['message']);
			$success = False;
		}
	
		foreach ($list as $tuple) {
			foreach ($tuple as $bind => $val) {
				"<br>".$bind."<br>";
				OCIBindByName($statement, $bind, $val);
				unset ($val); 
			}
	
			$r = OCIExecute($statement, OCI_DEFAULT);
	
			if (!$r) {
				echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
				$e = OCI_Error($statement);
				echo htmlentities($e['message']);
				echo "<br>";
				$success = False;
			}
		}
	
		return $statement;
	}
	
	function printResult($result) {
		echo "<br>" ?>
	    <html> <link rel="stylesheet" type= "text/css" href="style.css">
	    <div id="tabletitle">
		Borrower's Borrowing List:
	    </div> 
	    </html>
	    <?php
		echo "<table>";
		?> <html> <table border=1px bordercolor="#666666" ></html>
	        <?php
	        echo "<tr><th>Book</th><th>Due Date</th></tr>";
	
	
		while ($row = OCI_FETCH_ARRAY($result, OCI_BOTH)) {
			echo "<tr><td>" .$row["CALLNUMBER"] . "</td><td>" . $row["INDATE"] ."</td></tr>";
		}
		echo "</table>";
	}
	
?>