<?php
  if (!isset($_COOKIE['user'])) {
    header("location:./login.php");
    exit();
  }
  else {
    require_once("db_init.php");

    $user = $_COOKIE['user'];
    $pass = $_COOKIE['pass'];
    $sess = $_COOKIE['sess'];
    $sql = "SELECT name, pass, user, id, folder, http, spacelimit, language, theme, permbrowse, permupload, permcreate, permuser, permadmin, permdelete, permmove, permchmod, permget, permdeleteuser, permedituser, permmakeuser, permpass, permrename, permedit, permsub, formatperm, status, recycle, permprefs FROM ".$GLOBALS['config']['db']['pref']."users WHERE user='".mysql_escape_string($user)."'";
    $mysql = mysql_query($sql);
        list ($user, $dbpass, $dbuser, $userid, $userdir, $http, $limit, $language, $theme, $permbrowse, $permupload, $permcreate, $permuser, $permadmin, $permdelete, $permmove, $permchmod, $permget, $permdeleteuser, $permedituser, $permmakeuser, $permpass, $permrename, $permedit, $permsub, $formatperm, $status, $recycle, $permprefs) = mysql_fetch_row($mysql);

    $check = "SELECT count(year) FROM backup where year='0000'";
    $has = mysql_fetch_array(mysql_query($check));
	if($has[0] != '0' && $permadmin != 1) {
	    die('伺服器維修中！');
	}

    $d = @$_REQUEST['d'];
    $year = @$_REQUEST['year'];
    if ($d) {
      while (preg_match('/\\\/',$d)) $d = preg_replace('/\\\/','/',$d);
      while (preg_match('/\/\//',$d)) $d = preg_replace('/\/\//','/',$d);
      while (preg_match('/\.\.\//',$d)) $d = preg_replace('/\.\.\//','/',$d);
      if ($d[strlen($d)-1] != '/') $d = $d.'/';
      if ($d == '/') $d = '';
    }
    if (!$userdir) $userdir = "./";
    if ($permsub != 1) $d = "";

    if ($userid && $pass != md5($dbpass.$sess)) {
        header("location:./logout.php");
        exit();
    }

    if (file_exists("../themes/$theme/theme.php")) require_once("../themes/$theme/theme.php");
    else {
      require_once("../themes/$defaulttheme/theme.php");
      $theme = $defaulttheme;
    }

    if (file_exists("../language/$language/lng.php")) require_once("../language/chinese/lng.php");
    else {
      require_once("../language/$defaultlang/lng.php");
    }
  }

  global $nobar, $d, $bgcolor3, $tbcolor1, $tbcolor2, $tbcolor3, $tbcolor4, $userdir, $HTTP_HOST, $theme, $http, $extraheaders, $IMG_CHECK, $IMG_RENAME, $IMG_GET, $IMG_EDIT, $IMG_OPEN, $IMG_RENAME_NULL, $IMG_EDIT_NULL, $IMG_OPEN_NULL, $IMG_GET_NULL, $IMG_MIME_FOLDER, $IMG_MIME_BINARY, $IMG_MIME_AUDIO, $IMG_MIME_VIDEO, $IMG_MIME_IMAGE, $IMG_MIME_TEXT, $IMG_MIME_UNKNOWN, $permget, $permedit, $permrename, $permsub, $formatperm, $permmove, $permdelete, $permchmod;
  $extraheaders = "<script language=javascript>\n"
                 ."function itemsel(item,ff,check,action,overcolor,outcolor,clickcolor) {\n"
                 ."  if (action == 1) {\n"
                 ."    item.bgColor=overcolor;\n"
                 ."  }\n"
                 ."  if (action == 2) {\n"
                 ."    if (document.getElementById(check).checked == false) item.bgColor=outcolor;\n"
                 ."    else item.bgColor=clickcolor;\n"
                 ."  }\n"
                 ."  if (action == 3) {\n"
                 ."    document.getElementById(check).checked = (document.getElementById(check).checked ? false : true);\n"
                 ."    item.bgColor=clickcolor;\n"
                 ."  }\n"
                 ."}\n"
                 ."function selectall() {\n"
                 ."  var holder;\n"
                 ."  for (x=0;x<document.bulk_submit.filetotal.value;x++) {\n"
                 ."    document.getElementById(\"filesel_\" + x).checked = (document.getElementById(\"filesel_\" + x).checked ? false : true);\n"
                 ."    document.getElementById(\"filebg_\" + x).bgColor = (document.getElementById(\"filesel_\" + x).checked ? \"$tbcolor3\" : document.getElementById(\"filecolor_\" + x).value);\n"
                 ."  }\n"
                 ."  for (x=0;x<document.bulk_submit.foldertotal.value;x++) {\n"
                 ."    document.getElementById(\"foldersel_\" + x).checked = (document.getElementById(\"foldersel_\" + x).checked ? false : true);\n"
                 ."    document.getElementById(\"folderbg_\" + x).bgColor = (document.getElementById(\"foldersel_\" + x).checked ? \"$tbcolor3\" : document.getElementById(\"foldercolor_\" + x).value);\n"
                 ."  }\n"

                 ."}\n"
                 ."function getall() {\n"
                 ."  var holder = \"\";\n"
                 ."  for (x=0;x<document.bulk_submit.filetotal.value;x++) {\n"
                 ."    holder += (document.getElementById(\"filesel_\" + x).checked ? document.getElementById(\"filesel_\" + x).value+\"\\n\" : \"\");"
                 ."  }\n"
                 ."  for (x=0;x<document.bulk_submit.foldertotal.value;x++) {\n"
                 ."    holder += (document.getElementById(\"foldersel_\" + x).checked ? document.getElementById(\"filesel_\" + x).value+\"\\n\" : \"\");"
                 ."  }\n"
                 ."  return holder;\n"
                 ."}\n"
                 ."</script>\n";

    require_once("functions.php");
    switch(@$_REQUEST['p']) {
      case "bulk_action":
        if($permadmin == 0) checkYearAction($year);
        if ($permmove != 1 && $_REQUEST['bulk_action'] == "move") permerror("You do not currently have permission to move files/folders.\n");
        elseif ($permdelete != 1 && $_REQUEST['bulk_action'] == "delete") permerror("You do not currently have permission to delete files/folders.\n");
        elseif ($permchmod != 1 && $_REQUEST['bulk_action'] == "chmod") permerror("You do not currently have permission to change file/folders permissions.\n");
        else bulk_action($_REQUEST['bulk_action'], $d, $_REQUEST['ndir']);
        exit();
        break;
      case "user_delete":
        if($permadmin == 0) checkYearAction($year);
        user_delete($_GET['d'], $_GET['filesel'], $year);
        exit();
        break;
      case "upload":
        if($permadmin == 0) checkYearAction($year);
        if ($permupload == 1) upload($_FILES['upfile'], $_REQUEST['ndir'], $_REQUEST['tid'], $_REQUEST['d'], $year);
        else permerror("You do not currently have permission to upload.\n");
        exit();
        break;
      case "view":
        if ($permget == 1) viewfile($_REQUEST['file'],$d);
        exit();
        break;
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="token" content="csrf_token()">

    <title>深耕計畫後台</title>

    <!-- Bootstrap Core CSS -->
    <link href="../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="../bower_components/metisMenu/dist/metisMenu.min.css" rel="stylesheet">

    <!-- Timeline CSS -->
    <link href="../dist/css/timeline.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">

    <!-- Morris Charts CSS -->
    <link href="../bower_components/morrisjs/morris.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- jQuery -->
    <script src="../bower_components/jquery/dist/jquery.min.js"></script>

    <!-- jQuery fixed version of clone() -->
    <script src="../bower_components/jquery/dist/jquery.fix.clone.js"></script>

    <!-- sweetAlert -->
    <script src="../bower_components/sweetalert/dist/sweetalert.min.js"></script>
    <link rel="stylesheet" href="../bower_components/sweetalert/dist/sweetalert.css">

    <!-- bootstrap-toggle -->
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.0/css/bootstrap-toggle.min.css" rel="stylesheet">
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.0/js/bootstrap-toggle.min.js"></script>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>
    <?= $extraheaders; ?>
    <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="<?= "index.php?year={$year}" ?>">深耕計畫</a>
            </div>
            <!-- /.navbar-header -->

            <ul class="nav navbar-top-links navbar-right">
                <!-- /.dropdown -->
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <?php
                            if ($permadmin == 1) {
                                echo "<li><a href=\"upload.php?p=super\"><i class=\"fa fa-user fa-fw\"></i> 管理員控制面板</a></li>";
                            }
                        ?>
                        <li><a href="upload.php?p=user"><i class="fa fa-gear fa-fw"></i> 使用者控制面板</a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="logout.php"><i class="fa fa-sign-out fa-fw"></i> 登出</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->
            <!-- /.navbar-static-side -->
        </nav>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">歡迎您，<?= $user ?>！</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="page-header">Upload</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <?php
                if ($permadmin == 1) {
                    echo<<<END
            <div class="row">
                <div class="col-lg-2 col-md-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-tasks fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">33</div>
                                    <div>鄉鎮</div>
                                </div>
                            </div>
                        </div>
                        <a href="upload.php?p=complete&d=地區災害潛勢特性評估/&year={$year}">
                            <div class="panel-footer">
                                <span class="pull-left">地區災害潛勢特性評估</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="panel panel-purple">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-tasks fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">33</div>
                                    <div>鄉鎮</div>
                                </div>
                            </div>
                        </div>
                        <a href="upload.php?p=complete&d=災害防救體系/&year={$year}">
                            <div class="panel-footer">
                                <span class="pull-left">災害防救體系</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="panel panel-red">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-tasks fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">33</div>
                                    <div>鄉鎮</div>
                                </div>
                            </div>
                        </div>
                        <a href="upload.php?p=complete&d=培植災害防救能力/&year={$year}">
                            <div class="panel-footer">
                                <span class="pull-left">培植災害防救能力</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="panel panel-green">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-tasks fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">33</div>
                                    <div>鄉鎮</div>
                                </div>
                            </div>
                        </div>
                        <a href="upload.php?p=complete&d=災時緊急應變處置機制/&year={$year}">
                            <div class="panel-footer">
                                <span class="pull-left">災時緊急應變處置機制</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="panel panel-yellow">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-tasks fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">33</div>
                                    <div>鄉鎮</div>
                                </div>
                            </div>
                        </div>
                        <a href="upload.php?p=complete&d=災害防救資源/&year={$year}">
                            <div class="panel-footer">
                                <span class="pull-left">災害防救資源</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-tasks fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">33</div>
                                    <div>鄉鎮</div>
                                </div>
                            </div>
                        </div>
                        <a href="upload.php?p=yearSetting">
                            <div class="panel-footer">
                                <span class="pull-left">年度設定</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /.row -->
END;
                }
            ?>
            <div class="row">
                <div class="col-lg-<?php echo ($permadmin != 1) ? "8" : "12" ?>">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?php
                                function checkYearAction($year) {
                                    $sql = "SELECT action FROM year where year = {$year}";
                                    $result = mysql_query($sql);
                                    if(mysql_fetch_array($result)[0] == '0') {
                                        die("{$year}年度已不再接受更動，如有需求請洽管理員！");
                                    }
                                }

                                switch(@$_REQUEST['p']) {
                                    case 'yearSetting':
                                        if ($permadmin == 1) yearSetting();
                                        else permerror("You do not currently have permission to setup year.\n");
                                        break;
                                    case 'doYearSetting':
                                        if ($permadmin != 1) permerror("You do not currently have permission to do setup year.\n");
                                        else doYearSetting($_POST['name']);
                                        break;
                                    case 'changeYearAction':
                                        if ($permadmin != 1) permerror("You do not currently have permission to do changeYearAction.\n");
                                        else changeYearAction($_POST['speYear'], $_POST['check']);
                                        break;
                                    case "backup":
                                        if ($permadmin == 1) backup();
                                        else permerror("You do not currently have permission to backup.\n");
                                        break;
                                    case "dobackup":
                                        if ($permadmin != 1) permerror("You do not currently have permission to do backup.\n");
                                        else dobackup($_POST['name']);
                                        break;
                                    case "changeyear":
                                        if ($permadmin != 1) permerror("You do not currently have permission to do changeyear.\n");
                                        else changeyear($_POST['year'], $_POST['check']);
                                        break;
                                    case "up":
                                        if ($permupload == 1) up($year);
                                        else permerror("You do not currently have permission to upload.\n");
                                        break;
                                    case "complete":
                                        if ($permadmin == 1) complete($_REQUEST['d'], $year);
                                        else permerror("You do not currently have permission to access this site.\n");
                                        break;
                                    case "edit":
                                        if($permadmin == 0) checkYearAction($year);
                                        if ($permedit == 1) edit($_REQUEST['fename']);
                                        else permerror("You do not currently have permission to edit.\n");
                                        break;
                                    case "save":
                                        if($permadmin == 0) checkYearAction($year);
                                        if ($permedit == 1) save($_REQUEST['ncontent'], $_REQUEST['fename'], $d, $_REQUEST['next_action']);
                                        else permerror("You do not currently have permission to edit.\n");
                                        break;
                                    case "cr":
                                        if($permadmin == 0) checkYearAction($year);
                                        if ($permcreate == 1) cr();
                                        else permerror("You do not currently have permission to create.\n");
                                        break;
                                    case "create":
                                        if($permadmin == 0) checkYearAction($year);
                                        if ($permcreate == 1) create($_REQUEST['nfname'], $_REQUEST['isfolder'], $d, $_REQUEST['ndir']);
                                        else permerror("You do not currently have permission to create.\n");
                                        break;
                                    case "ren":
                                        if($permadmin == 0) checkYearAction($year);
                                        if ($permrename == 1) ren($_REQUEST['file'], $year);
                                        else permerror("You do not currently have permission to rename.\n");
                                        break;
                                    case "rename":
                                        if($permadmin == 0) checkYearAction($year);
                                        if ($permrename == 1) renam($_REQUEST['rename'], $_REQUEST['nrename'], $d, $year);
                                        else permerror("You do not currently have permission to rename.\n");
                                        break;
                                    case "user":
                                        if ($permuser == 1) user();
                                        else permerror("You do not currently have permission to access the user control pannel.\n");
                                        break;
                                    case "pass":
                                        if ($permpass == 1) pass();
                                        else permerror("You do not currently have permission to change your password.\n");
                                        break;
                                    case "password":
                                        if ($permpass == 1) password($_REQUEST['encpaso'], $_REQUEST['encpas1'], $_REQUEST['encpas2']);
                                        else permerror("You do not currently have permission to change your password.\n");
                                        break;
                                    case "prefs":
                                        if ($permprefs == 1) prefs();
                                        else permerror("You do not currently have permission to change your preferences.\n");
                                        break;
                                    case "saveprefs":
                                        if ($permpass == 1) preferences($_REQUEST['config_email'], $_REQUEST['config_name'], $_REQUEST['config_theme'], $_REQUEST['config_language'], $_REQUEST['config_recycle'], $_REQUEST['config_formatperm']);
                                        else permerror("You do not currently have permission to change your preferences.\n");
                                        break;
                                    case "super":
                                        if ($permadmin == 1) super();
                                        else permerror("You do not currently have permission to access the admin pannel.\n");
                                        break;
                                    case "newuser":
                                        if ($permmakeuser == 1) newuser();
                                        else permerror("You do not currently have permission to create new users.\n");
                                        break;
                                    case "saveuser":
                                        if ($permmakeuser == 1) saveuser();
                                        else permerror("You do not currently have permission to create new users.\n");
                                        break;
                                    case "eduser":
                                        if ($permedituser == 1) eduser($_REQUEST['muid']);
                                        else permerror("You do not currently have permission to edit users.\n");
                                        break;
                                    case "edituser":
                                        if ($permedituser == 1) edituser($_REQUEST['muid']);
                                        else permerror("You do not currently have permission to edit users.\n");
                                        break;
                                    case "deluser":
                                        if ($permdeleteuser == 1) deluser($_REQUEST['muid']);
                                        else permerror("You do not currently have permission to delete users.\n");
                                        break;
                                    case "deleteuser":
                                        if ($permdeleteuser == 1) deleteuser($_REQUEST['muid']);
                                        else permerror("You do not currently have permission to delete users.\n");
                                        break;
                                    case "printerror":
                                        printerror($error);
                                        break;
                                    case "bulk_submit":
                                        if($permadmin == 0) checkYearAction($year);
                                        if ($permmove != 1 && $_REQUEST['bulk_action'] == "move") permerror("You do not currently have permission to move files/folders.\n");
                                        elseif ($permdelete != 1 && $_REQUEST['bulk_action'] == "delete") permerror("You do not currently have permission to delete files/folders.\n");
                                        elseif ($permchmod != 1 && $_REQUEST['bulk_action'] == "chmod") permerror("You do not currently have permission to change file/folders permissions.\n");
                                        else bulk_submit($_REQUEST['bulk_action'], $d);
                                        break;
                                    case "users":
                                        if ($permedituser == "1" || $permedituser == "1") usermod();
                                        else permerror("You do not currently have permission to modify users.\n");
                                        break;
                                    case "items":
                                        if($permadmin == 0) checkYearAction($year);
                                        if ($permadmin == 1) itemmod();
                                        else permerror("You do not currently have permission to modify items.\n");
                                        break;
                                    case "edititem":
                                        if($permadmin == 0) checkYearAction($year);
                                        if ($permadmin == 1) edititem($_GET['cate']);
                                        else permerror("You do not currently have permission to modify items.\n");
                                        break;
                                    case "itemfunc":
                                        if($permadmin == 0) checkYearAction($year);
                                        if ($permadmin == 1) itemfunc();
                                        else permerror("You do not currently have permission to modify items.\n");
                                        break;
                                    default:
                                        home($userdir, $d, $bgcolor3, $formatperm, $totalsize, $permadmin, $permrename, $permmove, $permdelete, $permchmod, $permsub, $permget, $tcoloring, $tbcolor1, $tbcolor2, $tbcolor3, $tbcolor4, $adminfile, $p, $pdir, $IMG_CHECK, $IMG_RENAME, $IMG_GET, $IMG_EDIT, $IMG_OPEN, $IMG_RENAME_NULL, $IMG_EDIT_NULL, $IMG_OPEN_NULL, $IMG_GET_NULL, $IMG_MIME_FOLDER, $IMG_MIME_BINARY, $IMG_MIME_AUDIO, $IMG_MIME_VIDEO, $IMG_MIME_IMAGE, $IMG_MIME_TEXT, $IMG_MIME_UNKNOWN, $year);
                                }
                            ?>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div></div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-8 -->
                <?php
                    if ($permadmin != 1) {
                        echo<<<END
                <div class="col-lg-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-tasks fa-fw"></i> 工具箱
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="list-group">
                                <a href="upload.php?p=up&year={$year}" class="list-group-item">
                                    <i class="fa fa-upload fa-fw"></i> 上傳
                                </a
                            </div>
                            <!-- /.list-group -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-4 -->
END;
                    }
                ?>
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

    <!-- Bootstrap Core JavaScript -->
    <script src="../bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../bower_components/metisMenu/dist/metisMenu.min.js"></script>

    <!-- Morris Charts JavaScript -->
    <!-- <script src="../bower_components/raphael/raphael-min.js"></script> -->
    <!-- <script src="../bower_components/morrisjs/morris.min.js"></script> -->
    <!-- <script src="../js/morris-data.js"></script> -->

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>
    <!-- Custom deleter of sweetAlert -->
    <script src="../bower_components/sweetalert/dist/deleter.js"></script>

    <script>
        function progressHandlingFunction(e){
            if(e.lengthComputable){
                $('progress').attr({value:e.loaded,max:e.total});
            }
        }
    </script>

    <script>
        $(document).ready(function() {

            $('.remoteService').hide();
                                            
            $('.table-bordered tbody p span').hover(function(){
                $(this).find('.remoteService').show();
            }, function(){
                $(this).find('.remoteService').hide();
            });

            $('#bulk_submit').submit(function(event) {
                var error = "";
                var delvar = "";
                var values = {};
                $.each($('#bulk_submit').serializeArray(), function(i, field) {
                    values[field.name] = field.value;
                });
                if (getall() == "") {
                    error += "請至少選擇一個檔案以進行操作！\n";
                    swal({
                        title: "錯誤",
                        text: error,
                        timer: 1000,
                        showConfirmButton: false
                    });
                    return false;
                }
                else {
                    delvar += "所有已選取的檔案及目錄底下的所有檔案！";
                }

                if (values['bulk_action'] == "delete") {     
                    $('#bulk_submit input[type="submit"]').attr("data-title", "即將刪除\n"+delvar)
                    $('#bulk_submit input[type="submit"]').attr("data-message", getall())
                    deleter.init();
                    $('#bulk_submit input[type="submit"]').trigger("click");
                    deleter.Destroy();
                    event.preventDefault();
                }
                else if (values['bulk_action'] == "move") {
                    return true;
                }
                else if (values['bulk_action'] == "chmod") {
                    return true;
                }
                else {
                    error += "請選取任一操作！\n";
                    swal({
                        title: "錯誤",
                        text: error,
                        timer: 1000,
                        showConfirmButton: false
                    });
                    return false;
                }
            });

            $('form[action="?p=upload"]').each(function() {
                $(this).on("submit", function(event) {
                    var self = $(this);
                    var error = "";
                    event.preventDefault();
                    var formData = new FormData(this);
                    $.ajax({
                        url: 'upload.php?p=upload',  //Server script to process data
                        type: 'POST',
                        xhr: function() {  // Custom XMLHttpRequest
                            var myXhr = $.ajaxSettings.xhr();
                            if(myXhr.upload){ // Check if upload property exists
                                myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
                            }
                            return myXhr;
                        },
                        //Ajax events
                        success: function(output) {
                            swal({
                                    title: "處理完畢！",
                                    text: output,
                                    type: "warning",
                                    html: true
                            }, function() {
                                if(/檔案.*上傳完畢\./.test(output)) {
                                    var filename = output.match(/檔案(.*?)上傳完畢/);
                                    var tmp = self.parent().parent().find('td[class="text-center"]');
                                    tmp.html('<button type="button" class="btn btn-success btn-circle"><i class="fa fa-check"></i></button>');
                                    location.reload()
                                } else {
                                    self.parent().parent().find('td[class="text-center"]').html('<button type="button" class="btn btn-danger btn-circle"><i class="fa fa-times"></i></button>');
                                }
                            });
                        },
                        error: function() {
                            swal("錯誤", "上傳過程發生錯誤", "error");
                        },
                        // Form data
                        data: formData,
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                })
            });
        });
    </script>
    <link rel="stylesheet" href="../bower_components/jquery/dist/jquery-ui.min.css">
    <script src="../bower_components/jquery/dist/jquery-ui.min.js"></script>
</body>

</html>
