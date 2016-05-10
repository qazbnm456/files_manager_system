## 檔案說明

- [/backend.gz](https://github.com/qazbnm456/files_manager_system/blob/master/backend.gz) - 測試DB檔，可直接匯入至本地資料庫進行測試
- [/index.html](https://github.com/qazbnm456/files_manager_system/blob/master/index.html) - 跳轉頁面至[/pages/index.php](https://github.com/qazbnm456/files_manager_system/blob/master/pages/index.php)
- [/pages/index.php](https://github.com/qazbnm456/files_manager_system/blob/master/pages/index.php) - 跳轉頁面至[/pages/upload.php](https://github.com/qazbnm456/files_manager_system/blob/master/pages/upload.php)
- [/pages/login.php](https://github.com/qazbnm456/files_manager_system/blob/master/pages/login.php) - 登入頁面
- [/pages/logout.php](https://github.com/qazbnm456/files_manager_system/blob/master/pages/logout.php) - 登出頁面
- [/pages/db_init.php](https://github.com/qazbnm456/files_manager_system/blob/master/pages/db_init.php) - 資料庫及一些環境變數設定
- [/pages/upload.php](https://github.com/qazbnm456/files_manager_system/blob/master/pages/upload.php) - 主要頁面
- [/pages/functions.php](https://github.com/qazbnm456/files_manager_system/blob/master/pages/functions.php) - 所有功能函數定義

## db_init.php

設定資料庫及各種環境變數：

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

## upload.php

主要頁面，若未登入會重導向至[login.php](#)，若已登入則會根據參數`p`去執行相對應的功能函數，所有功能皆已定義在[functions.php](https://github.com/qazbnm456/files_manager_system/blob/master/pages/functions.php)中；沒有參數`p`則會預設執行[home()](https://github.com/qazbnm456/files_manager_system/blob/master/pages/functions.php#L1524)功能函數。

這些`p`參數需要在頁面渲染前動作：

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

這些`p`參數則是需要在頁面渲染後動作：

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

## functions.php

定義所有功能函數的檔案。

## Copyright and License

Copyright 2015-2016 NPTU, Dept of COM.
