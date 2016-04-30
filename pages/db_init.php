<?php
ini_set("error_reporting","E_ALL & ~E_NOTICE");
ini_set("max_execution_time", "1200");
header("Content-Type:text/html; charset=utf-8");
/****************************************************************/
/*                                                              */
/* File Admin                                                   */
/* ----------                                                   */
/*                                                              */
/* File Admin will perform basic functions on files/directories.*/
/* Functions include, List, Open, View, Edit, Create, Upload,   */
/*   Rename and Move.                                           */
/*                                                              */
/*                                                              */
/* Written by Devin Smith, July 1st 2003                        */
/* I wrote this thing before I even knew php                    */
/*   so it'd pretty bad code. but it works.                     */
/* http://www.arzy.net                                          */
/*                                                              */
/****************************************************************/


/****************************************************************/
/* Config Section                                               */
/*                                                              */
/* $adminfile - THIS filename.                                  */
/* $sitetitle - The title at the top of all pages.              */
/* $sqlserver - mySQL server.                                   */
/* $sqluser - mySQL username.                                   */
/* $sqlpass - mySQL password.                                   */
/* $sqldb - mySQL database.                                     */
/* $default_perm - default unix permissions.                    */
/* $defaulttheme - default theme to display.                    */
/* $defaultlang - default language.                             */
/****************************************************************/

$adminfile              = '/backend/pages/upload.php'; // upload.php的相對位置
$sitetitle              = 'Demo Browser';              // 網站名稱(不用更改)
$config['db']['server'] = 'localhost';                 // 資料庫主機
$config['db']['user']   = 'db_user';                   // 資料庫連線帳號
$config['db']['pass']   = 'db_password';               // 資料庫連線密碼
$config['db']['db']     = 'db_name';                   // 資料庫名稱
$config['db']['pref']   = 'osfm_';                     // 使用者表前綴
$default_perm           = '0777';                      // 檔案預設權限
$defaulttheme           = 'classic';                   // 主題(不用更改)
$defaultlang            = 'chinese';                   // 預設語言(不用更改)
$maxuploads             = 100;                         // 同時間最大檔案上傳數量
$config['enable_trash'] = false;                       // 垃圾桶功能(不用更改)


$dbh=mysql_connect ($config['db']['server'], $config['db']['user'], $config['db']['pass']) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_query("SET NAMES 'utf8'");
mysql_select_db ($config['db']['db']);