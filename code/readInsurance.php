<?php

/**
 * HTML PAGE FOR READING A INSURANCE CUSTOMER'S RECORD FROM THE INSURANCETABLE. 
 * This file is modified version of this project https://github.com/taniarascia/pdo
 * 
 */

if (isset($_POST['submit'])) {

    try {
        // config file for database
        // the database is a mariadb instance running on AWS RDS
        require "config.php";
        require "common.php";

        $connection = new PDO($dsn, $username, $password, $options);

        $sql = "SELECT * 
						FROM insurancetable
						WHERE name = :name";

        $name = $_POST['name'];

        $statement = $connection->prepare($sql);
        $statement->bindParam(':name', $name, PDO::PARAM_STR);
        $statement->execute();

        $result = $statement->fetchAll();

        #code from https://stackoverflow.com/questions/6213509/send-json-post-using-php below \
        $url = "https://prod-30.northcentralus.logic.azure.com:443/workflows/0894c1c06eca43f080a6249e0cf20284/triggers/manual/paths/invoke?api-version=2016-10-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=0XCHpQwBxang4F1-2lPrxEco8rLjQX9PY2XX6Nxtt6g";
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
            die("Error: call to URL $result[0] failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
        }
        else
            ?> <blockquote> Sent to logic app! </blockquote> <?php
        curl_close($curl);

        $response = json_decode($json_response, true);

    } catch (PDOException $error) {
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
		<blockquote> Customer: <?php echo escape($_POST['name']); ?> found.</blockquote>
		<blockquote>Record sent to Mortgage Broker:</blockquote>
        <blockquote><table>
        <thead>
        <tr>
            <th>Customer ID</th>
            <th>Name</th>
            <th>Policy Number</th>
            <th>Policy Value ($)</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($result as $row) { ?>
            <tr>
                <td><?php echo escape($row["client_id"]); ?></td>
                <td><?php echo escape($row["name"]); ?></td>
                <td><?php echo escape($row["policynumber"]); ?></td>
                <td><?php echo escape($row["policyvalue"]); ?></td>

            </tr>
            </tbody>
            </table>
            </blockquote>


            <?php
        }
	} 
	else 
	{ ?>
		<blockquote>No results found for <?php echo escape($_POST['name']); ?>.</blockquote>
	<?php
	}

}

        ?>

<h2>Enter your name and agree to send you information to Mortgage Broker:</h2>

<form method="post">
	<label for="name">Name</label>
	<input type="text" id="name" name="name">
	<input type="submit" name="submit" value="I Agree">
</form>




<a href="index.php">Back to home</a>

<?php require "footer.php"; ?>