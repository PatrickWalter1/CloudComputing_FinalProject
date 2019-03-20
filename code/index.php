<?

/**
 * THIS IS THE MAIN INDEX PAGE FOR THE SITE
 * Final Project
 * CSCI 4145 Cloud Computing Winter 2018
 * Written By: Patrick Walter
 * Dalhousie University
 * Halifax, NS
 * This file is modified version of this project https://github.com/taniarascia/pdo
// This file contains template from : https://www.w3schools.com/w3css/tryw3css_templates_photo2.htm
// User: testusr  Password: mypw
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
  $file = '/tmp/sample-app.log';
  $message = file_get_contents('php://input');
  file_put_contents($file, date('Y-m-d H:i:s') . " Received message: " . $message . "\n", FILE_APPEND);
}
else
{
?>


    <?php require "header.php"; ?>

    <div class="w3-content" style="max-width:1500px">

        <!-- Header -->
        <header class="w3-panel w3-center w3-opacity" style="padding:128px 16px">
            <h1 class="w3-xlarge">Cloud Computing Project</h1>
            <h1>Index</h1>

            <div class="w3-padding-32">
                <div class="w3-bar w3-border">
                    <a href="MBRcreate.php" class="w3-bar-item w3-button">MBR</a>
                    <a href="HomepageEMP.php" class="w3-bar-item w3-button ">EMP</a>
                    <a href="INSfront.php" class="w3-bar-item w3-button">INSinc</a>
                    <a href="HomepageRE.php" class="w3-bar-item w3-button ">RE</a>

                </div>
            </div>
        </header>
    </div>




    <?php include "footer.php"; ?>
<? 
} 
?>