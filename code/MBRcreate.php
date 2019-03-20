<?php

/**
 * Mortgage Broker MBR Homepage. To submit a mortgage application.
 * Dependent on AWS instance
 * Dependent on AWS RDS
 *
 *
 * Uses curl to send HTTP POST requests
 * Uses SQL to access database records on AWS RDS
 *
 * Final Project
 * CSCI 4145 Cloud Computing Winter 2018
 * Written By: Patrick Walter
 * Dalhousie University
 * Halifax, NS
 *
 * This file is modified version of this project https://github.com/taniarascia/pdo
 */


if (isset($_POST['submit']))
{
	// config file for database
	// the database is a mariadb instance running on AWS RDS
	require "config.php";
	require "common.php";
// adding the student with info from the form.
	try 
	{
		$connection = new PDO($dsn, $username, $password, $options);
		
		$new_student = array(
			"name" => $_POST['name'],
			"misid"  => $_POST['misid'],
			"mortvalue"  => $_POST['value'],
            "email" =>$_POST['emailaddress']

		);

		$sql = sprintf(
				"INSERT INTO %s (%s) values (%s)",
				"projectmbr",
				implode(", ", array_keys($new_student)),
				":" . implode(", :", array_keys($new_student))
		);
		
		$statement = $connection->prepare($sql);
		$statement->execute($new_student);

        $sql = "SELECT * 
						FROM projectmbr
						WHERE name = :name";

        $name = $_POST['name'];

        $statement = $connection->prepare($sql);
        $statement->bindParam(':name', $name, PDO::PARAM_STR);
        $statement->execute();

        $result = $statement->fetchAll();

        foreach ($result as $row) {
            $application_id = $row["mortid"];
        }
        # PAYLOAD SENT TO APPREPLY LOGIC APP
		#send email to applicant with recieved and application id number!
        $payload->email =  $_POST['emailaddress'];
		$payload->application_id = $application_id;
		$content = json_encode($payload);
        #code from https://stackoverflow.com/questions/6213509/send-json-post-using-php below \
        $url = "https://prod-11.northcentralus.logic.azure.com:443/workflows/7bdab06f87414c79832dfe2f2cc7319c/triggers/manual/paths/invoke?api-version=2016-10-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=CYI0D3UOLlFwZXTWlNYj0uj53xsSlzfrNfUnePHpSKg";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); #insecure
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

        $json_response = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ( $status != 201 ) {
            die("Error: Failed to send content: $content failed with status: $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
        }
        else {
            print($json_response);
        }


        curl_close($curl);


	}

	catch(PDOException $error) 
	{
		echo $sql . "<br>" . $error->getMessage();
	}
	
}
?>

<?php require "header.php"; ?>

<?php 
if (isset($_POST['submit']) && $statement) 
{ ?>

<?php 
} ?>
    <header class="w3-panel w3-center w3-opacity" style="padding:12px 16px">
<h3>MBR Mortgage Broker Homepage</h3>
    </header>

<form method="post">
    <fieldset>
        <legend>Mortgage Application Form</legend>
	<label for="name">Name: </label>
	<input type="text" name="name" id="name"><br><br>
	<label for="misid">MIsID: </label>
	<input type="text" name="misid" id="misid"><br><br>
	<label for="value">Mortgage Value: </label>
	<input type="number" name="value" id="value"><br><br>
    <label for="emailAddress">Email Address: </label>
    <input type="text" name="emailaddress" id="emailaddress"><br><br>
	<input type="submit" name="submit" value=" Submit Application ">
    </fieldset>
</form>

<div>
    <a href="status.php">Check Application Status</a>
</div>

<a href="index.php">Back to Index</a>

<?php require "footer.php"; ?>