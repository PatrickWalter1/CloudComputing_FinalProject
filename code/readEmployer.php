<?php

/**
 * HTML PAGE FOR READING A EMPLOYEE RECORD FROM THE EMPLOYEE TABLE. 
 * This file is modified version of this project https://github.com/taniarascia/pdo
 *

 */

if (isset($_POST['submit'])) 
{
	
	try 
	{
	// config file for database
	// the database is a mariadb instance running on AWS RDS
		require "config.php";
		require "common.php";

		$connection = new PDO($dsn, $username, $password, $options);

		$sql = "SELECT * 
						FROM employeetable
						WHERE employee_id = :employee_id";

		$employeeID = $_POST['employee_id'];

		$statement = $connection->prepare($sql);
		$statement->bindParam(':employee_id', $employeeID, PDO::PARAM_STR);
		$statement->execute();

		$result = $statement->fetchAll();

        #code from https://stackoverflow.com/questions/6213509/send-json-post-using-php below \/
        $payload = json_encode($result);
        $url = "https://prod-31.northcentralus.logic.azure.com:443/workflows/b74d8843c2d04d58a1987f7d58259fef/triggers/manual/paths/invoke?api-version=2016-10-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=Ho27ogQ3vamWGHjp7IdoqDZJmx74wfI7RjWwaL5lWyM";
        $content = json_encode($result);
        $curl = curl_init($url);
         // important line added for SSL cert
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
        $json_response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ( $status != 201 ) {
            die("Error: call to URL $content failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
        }
        curl_close($curl);

        $response = json_decode($json_response, true);

	}
	
	catch(PDOException $error) 
	{
		echo $sql . "<br>" . $error->getMessage();
	}

}
?>
<?php require "header.php"; ?>
		
<?php  
if (isset($_POST['submit'])) 
{
	if ($result && $statement->rowCount() > 0) 
	{ ?>
		<h2>Results</h2>


	<?php 
		foreach ($result as $row) 
		{ ?>
		<blockquote> Employee ID: <?php echo escape($_POST['employee_id']); ?> Authenticated.</blockquote>
            <blockquote>Employee record sent to Mortage Broker!</blockquote>

		<?php 
		} ?>

	<?php 
	}
    else
    { ?>
        <blockquote>No results found for <?php echo escape($_POST['employee_id']); ?>.</blockquote>
        <?php
    }


}?> 

<h2>Enter your Employee ID number and agree to send you information to Mortgage Broker:</h2>

<form method="post">
	<label for="employee_id">Employee ID</label>
	<input type="text" id="employee_id" name="employee_id">
	<input type="submit" name="submit" value="I Agree">
</form>




<a href="index.php">Back to home</a>

<?php require "footer.php"; ?>