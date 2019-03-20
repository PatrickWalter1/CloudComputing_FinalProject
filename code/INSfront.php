<?php
/**
 * Created by PhpStorm.
 * User: Patrick
 * Date: 2018-04-04
 * Time: 3:11 PM
 */

?>

<!DOCTYPE html>
<html>
<title>INSInc Insurance Homepage</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
<style>
    body,h1 {font-family: "Raleway", sans-serif}
    body, html {height: 100%}
    .bgimg {
        background-image: url('/IMG_6174.jpg');
        min-height: 100%;
        background-position: center;
        background-size: cover;
    }
</style>
<body>

<div class="bgimg w3-display-container w3-animate-opacity w3-text-white">
    <div class="w3-display-topleft w3-padding-large w3-xlarge">
        RE Real Estate
    </div>
    <div class="w3-display-middle">
        <h1 class="w3-jumbo w3-animate-top">
            <?php if (isset($_POST['appraisalrecord']))
                { ?>
                    Appraisal received and sent to MBR
            <?php
                }
                else {
                    ?>
                    Get Real Estate to Submit Appraisal

                    <?php
                }
            ?>
       </h1>
        <hr class="w3-border-grey" style="margin:auto;width:40%">
    </div>
    <div class="w3-display-bottomleft w3-padding-large">
        <a href="https://www.w3schools.com/w3css/default.asp" target="_blank"></a>
    </div>
</div>

</body>
</html>

