<?php
/********************************************************************
 * This file is where you set the data related to your server.  In  *
 * order to get these FX.php examples to work on your system, you   *
 * need to set $serverIP to the IP address of your server.  Also,   *
 * be sure to set $webCompanionPort to match the port configured    *
 * for Web Companion on your FileMaker Pro Unlimited machine.       *
 ********************************************************************/

$serverIP = '192.38.112.25';
$webCompanionPort = 80;         // for FM7SA, this should we the web server port
$dataSourceType = 'FMPro7';
$webUN = 'webuser';               // defaults for Book_List in FM7; both should be blank for Book_List in FM5/6
$webPW = 'webpass';
$scheme = 'http';               // generally this will be 'http'; 'https' for SSL connections to FileMaker
?>
