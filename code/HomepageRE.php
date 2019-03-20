<?php

/**
 *
 * This page is the homepage of the real estate agent.
 * The real estate agent takes your name, msid and mortid.
 * It calls the databases of Insurance and Municipality for records on the MiSID
 * These records are compiled together, shown to the user, and sent to Azure Logic App (Insurance).
 *
 * Final Project
 * CSCI 4145 Cloud Computing Winter 2018
 * Written By: Patrick Walter
 * Dalhousie University
 * Halifax, NS
 *
 * This file contains modified code from this project:
 * https://github.com/taniarascia/pdo  read.php
 *

 */

if (isset($_POST['appraisalrecord']))
{
	
	try 
	{
	// config file for database
	// the database is a mariadb instance running on AWS RDS
		require "config.php";
		require "common.php";
        #######################Insurance#############################################################
		$connection = new PDO($dsn, $username, $password, $options);
		$sql = "SELECT * 
						FROM projectinsinc
						WHERE misid = :misid";
        $misid = $_POST['misid'];
		$statement = $connection->prepare($sql);
		$statement->bindParam(':misid', $misid, PDO::PARAM_STR);
		$statement->execute();
		$result = $statement->fetchAll();
        $insurance->name = $_POST['name'];
        # reprocess the record to be a clean json
        foreach ($result as $row)
        {
            $insurance->misid= $row["misid"];
            $insurance->insuredvalue= $row['insuredvalue'];
            $insurance->deductible= $row['deductible'];
        }
        $insurance = json_encode($insurance);
        #send this record to POST
        $_POST['insurance']=$insurance;
        ###############################Municipality##################################################
        $sql = "SELECT * 
						FROM projectmun
						WHERE misid = :misid";
        $misid = $_POST['misid'];

        $statement = $connection->prepare($sql);
        $statement->bindParam(':misid', $misid, PDO::PARAM_STR);
        $statement->execute();
        $result = $statement->fetchAll();
        # reprocess the record to be a clean json
        foreach ($result as $row) {
            $mun->misid = $row["misid"];
            $mun->code = $row['code'];
        }
        $mun = json_encode($mun);
       # echo $mun;
        $_POST['mun']=$mun;
        #####################################Real Estate ##########################################
        $sql = "SELECT * 
						FROM projectre
						WHERE misid = :misid";
        $misid = $_POST['misid'];
        $statement = $connection->prepare($sql);
        $statement->bindParam(':misid', $misid, PDO::PARAM_STR);
        $statement->execute();
        $result = $statement->fetchAll();
        # reprocess the record to be a clean json
        foreach ($result as $row) {
            $realestate->msid = $row["misid"];
            $realestate->appraisedvalue = $row['appraisedvalue'];
        }
        $realestate= json_encode($realestate);
        # echo $mun;
        $_POST['realestate']=$realestate;



        #echo json_encode($insurance);


	}

	catch(PDOException $error)
	{
		echo $sql . "<br>" . $error->getMessage();
	}

}
?>
<?php require "header.php"; ?>
		
<?php  
if (isset($_POST['appraisalrecord'])) {
    if ($result && $statement->rowCount() > 0) { ?>
        <h3>Results</h3>
        <?php
        $insurance=$_POST['insurance'];
        $insurance = json_decode($insurance, true);
        $mun = $_POST['mun'];
        $mun = json_decode($mun,true);
        $realestate = $_POST['realestate'];
        $realestate = json_decode($realestate,true);
        $mydate=getdate(date("U"));
        foreach ($result as $row) { ?>
            <div style="background-color:lightblue">
                <blockquote> <h4>Real Estate House Appraisal Record:</h4></blockquote>
                <blockquote> Date: <?php echo "$mydate[weekday], $mydate[month] $mydate[mday], $mydate[year]"; ?> </blockquote>
                <blockquote> Name: <?php echo escape($_POST['name']); ?> </blockquote>
                <blockquote> Mortgage ID: <?php echo escape($_POST['mortid']); ?> </blockquote>
                <blockquote> <h5>Insurance Record From INSinc:</h5></blockquote>
                <blockquote> MISID: <?php echo escape($insurance['misid']); ?> </blockquote>
                <blockquote> Insured Value: $<?php echo escape($insurance['insuredvalue']); ?>.00 </blockquote>
                <blockquote> Deductible: $<?php echo escape($insurance['deductible']); ?>.00 </blockquote>
                <blockquote> <h5>Municipality Service Record From MUN:</h5></blockquote>
                <blockquote> MISID: <?php echo escape($mun['misid']); ?> </blockquote>
                <blockquote> Utilities Code: <?php echo escape($mun['code']); ?> </blockquote>
                <blockquote> <h5>Our Real Estate Appraisal:</h5></blockquote>
                <blockquote> MISID: <?php echo escape($realestate['misid']); ?> </blockquote>
                <blockquote> Appraised Value: $<?php echo escape($realestate['appraisedvalue']); ?>.00 </blockquote><br>
            </div>

            <?php
        }
            #build the payload from all three records:
            $payload->name = $_POST['name'];
            $payload->mortid = $_POST['mortid'];
            $payload->misid = $insurance['misid'];
            $payload->insuredvalue = $insurance['insuredvalue'];
            $payload->deductible =$insurance['deductible'];
            $payload->code = $mun['code'];
            $payload->appraisedvalue = $realestate['appraisedvalue'];
            #send it to the logic app:
            #code from https://stackoverflow.com/questions/6213509/send-json-post-using-php below \/
            $payload = json_encode($payload);
            echo $payload;
            #sends it to the mortgage logic app
            $url = "https://prod-03.northcentralus.logic.azure.com:443/workflows/a29e29a725e0413497f983251245f3c3/triggers/manual/paths/invoke?api-version=2016-10-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=SchaPR30-dvZQJzOYl3pTmIrXPttVDi_9rdMosz6HsM";
            $content = $payload;
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
                die("Error: call to URL $content failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
            }
            else{
                $response = json_decode($json_response, true);
               # echo $response;
                ?>
                <blockquote>This record was sent to MBR:</blockquote>
                <blockquote>Here is MBR response: <?php echo $json_response; ?>.</blockquote>
                <?php
            }
            curl_close($curl);

    } else { ?>
        <blockquote>No results found for MIsID:  <?php echo escape($_POST['misid']); ?>.</blockquote>
        <?php
    }

}

if (isset($_POST['appraisal']))
    { ?>


        <form method="post">
            <fieldset>
                <legend>Property Appraisal Form</legend>
                <label for="name">Name: </label>
                <input type="text" name="name" id="name"><br><br>
                <label for="misid">MIsID: </label>
                <input type="text" name="misid" id="misid"><br><br>
                <label for="value">Mortgage ID: </label>
                <input type="text" name="mortid" id="mortid"><br><br>
                <input type="submit" name="appraisalrecord" value=" Submit Appraisal ">
            </fieldset>
        </form>

        <?php

}


?>
    <header class="w3-panel w3-center w3-opacity" style="padding:12px 16px">
<h2>Real Estate RE</h2>
    </header>

<form method = "post">
    <input type = "submit" name ="appraisal" value ="Get an Appraisal" >
</form><br><br>






<a href="index.php">Back to index</a>

<?php require "footer.php"; ?>