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
						FROM projectemp
						WHERE empid = :empid";

		$employeeID = $_POST['employee_id'];

		$statement = $connection->prepare($sql);
		$statement->bindParam(':empid', $employeeID, PDO::PARAM_STR);
		$statement->execute();
        $result = $statement->fetchAll();

        # reprocess the record to be a clean json
        foreach ($result as $row)
        {
                $payload->empid= $row["empid"];
                $payload->name= $row['name'];
                $payload->salary= $row['salary'];
                $payload->startdate = $row['startdate'];
        }

        ##echo json_encode($payload);



            #echo json_encode($result);
        #PAYLOAD SENT TO EMP LOGIC APP!
        #code from https://stackoverflow.com/questions/6213509/send-json-post-using-php below to set up curl post connection
        $payload = json_encode($payload);
        $url = "https://prod-12.northcentralus.logic.azure.com:443/workflows/ad89dd0132724dbd98c52be389e54b88/triggers/manual/paths/invoke?api-version=2016-10-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=qlE9mqrN5NR1rLz8b9WCK76rMKYx7-AIB7OAmk6BhAU";
        $content = json_encode($result);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // important line added for SSL cert
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
        $json_response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ( $status != 201 ) {
            die("Error: Content: $content failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
        }
        else{

            }


        curl_close($curl);

        $response = json_decode($json_response, true);
        $message = $json_response;
       # $message = json_encode($message);
        $_POST['message']= $message;

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
            <blockquote>Response from Mortage Broker Server: <?php echo escape($_POST['message']); ?> </blockquote>

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

<?php require 'auth.php'; ?>
    <header class="w3-panel w3-center w3-opacity" style="padding:12px 16px">
        <h2> EMP Employer HomePage</h2>
    </header>

<?php

if (isset($_POST['Auth'])) {
    $userid = authenticate();

    if ($userid != false) {

        ?>
        <h4> Enter your Employee ID:</h4>
        <form method="post">
            <label for="employee_id">Employee ID: </label>
            <input type="text" id="employee_id" name="employee_id">
            <input type="submit" name="submit" value="I Agree">
        </form>
        <?php

    }

    }
    else{
    ?>
        <form method="post">
            <label for="user">user: </label>
            <input type="text" id="usr" name="usr">
            <label for="user">pass: </label>
            <input type="text" id="pw" name="pw">
            <input type="submit" name="Auth" value="Auth">
        </form>

        <?php
    }
?>




<br><br><br>
<a href="index.php">Back to Index</a>

<?php require "footer.php"; ?>