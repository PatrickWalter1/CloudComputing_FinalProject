<?php

/**
 * Configuration for database connection to my database hosted at:
 * a4.cxiyvutsdox7.us-east-1.rds.amazonaws.com
 * it's a mariadb database, it has 3 tables
 * the employeetable represents the db of the employer company
 * the insurancetable represents the db of the insurance company
 * the brokertable represents the db of the mortgage broker company (MBR)
 *
 */

$host       = "a4.cxiyvutsdox7.us-east-1.rds.amazonaws.com";
$username   = "pt365049";
$password   = "Dark1010";
$dbname     = "assignment4";
$dsn        = "mysql:host=$host;dbname=$dbname";
$options    = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
              );