<?php
  require_once("db_init.php");
  function listdir($dir, $level_count = 0) {
    global $content;
    if (!@($thisdir = opendir(mb_convert_encoding($dir, "big5", "UTF-8")))) { return; }
    while ($item = readdir($thisdir)) {
      if (is_dir(mb_convert_encoding($dir, "big5", "UTF-8")."/".$item) && (substr($item, 0, 1) != '.')) {
        listdir($dir."/".mb_convert_encoding($item, "UTF-8", "big5"), $level_count + 1);
      }
    }
    if ($level_count > 0) {
      $dir = ereg_replace("[/][/]", "/", $dir);
      $content[] = $dir;
    }
    return $content;
  }

  function getfilesize($size) {
    if ($size != 0) { 
      if ($size>=1099511627776) $size = round($size / 1024 / 1024 / 1024 / 1024, 2)." TB";
      elseif ($size>=1073741824) $size = round($size / 1024 / 1024 / 1024, 2)." GB";
      elseif ($size>=1048576) $size = round($size / 1024 / 1024, 2)." MB";
      elseif ($size>=1024) $size = round($size / 1024, 2)." KB";
      elseif ($size<1024) $size = round($size / 1024, 2)." B";
    }
    return $size;
  }

  if (!function_exists('mime_content_type')) {
     function mime_content_type($f) {
         $f = escapeshellarg($f);
         return trim( `file -bi $f` );
     }
  }

  function listdircontents($dir, $level_count = 0) {
    global $contenta, $contentb, $userdir;
      $actualdir = getActualDir($userdir);
      $dir = $actualdir.$dir;
      if (!@($thisdir = opendir($actualdir.$dir))) { return; }
      while ($item = readdir($thisdir) ) {
        if (is_dir($actualdir.$dir."/".$item) && (substr($item, 0, 1) != '.')) {
          listdircontents($actualdir.$dir."/".$item, $level_count + 1);
        }
      }
      if ($level_count > 0) {

        $dir = ereg_replace("[/][/]", "/", $dir);
        $handle=opendir($dir);
        while ($file = readdir($handle)) $filelist[] = $file;
        while (list ($key, $file) = each ($filelist)) { 
          if ($file != "." && $file != ".." && !is_dir($dir."/".$file)) {
            $contenta[] = $dir."/".$file;
          }
        }
        $contentb[] = $dir;
      }
  }

  function error_handler ($level, $message, $file, $line, $context) { 
    global $parseerror; 
    $parseerror = 1;
    if ($level == 2) echo "<font class=error>$message</font><br>\n";
    //if ($level == 2) echo "<b>Warning</b>: $message in $file on line $line\n";
  } 

  function permerror($error) { 
    page_header("Permission Error");
    opentable("100%");
    echo "<br><font class=error>$error</font><br><br>\n";
    closetable();
    page_footer();
  } 

  function ismail($str) {
    if(eregi("^[\'+\\./0-9A-Z^_\`a-z{|}~\-]+@[a-zA-Z0-9_\-]+(\.[a-zA-Z0-9_\-]+){1,5}$",$str) || !$str) return true;
    else return false;
  }

  function up($year) {
    global $user, $d, $userdir, $maxuploads;
    $cates = array("地區災害潛勢特性評估", "災害防救體系", "培植災害防救能力", "災時緊急應變處置機制", "災害防救資源");
    echo <<<END
    <div class="note note-info">
      <p>資料上傳須知：</p>
        <ol>
          <li>請確定檔案格式為 .doc, .docx, .ppt, .pptx, .xls, .xlsx, .pdf, .jpg, .jpeg, .png, .gif</li>
          <li>請點選送出按鈕，將上傳檔案提交</li>
        </ol>
    </div>
END;

    if($year == 0) {
        $result = mysql_query("SELECT year from year ORDER BY year DESC limit 0,1") or die(mysql_error());
        $year = mysql_fetch_row($result)[0];
        echo "<script>window.onload = function(){ window.location.href = '?p=up&year={$year}'; };</script>";
    }
    echo <<<END
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown"><font color="white">目前年度：{$year}</font>
        <span class="caret"></span></button>
        <ul class="dropdown-menu">
END;
    $result = mysql_query("SELECT year from year ORDER BY year DESC") or die(mysql_error());
    while($row = mysql_fetch_row($result)) {
        echo "<li><a href='?p=up&year={$row[0]}'>".$row[0]."</a></li>";
    }
    echo <<<END
        </ul>
    </div>
END;

    //page_header("Upload");
    //opentable("100%");
    echo <<<END
    <table class="table table-bordered">
      <thead>
        <tr style="background: #5bc0de;">
          <th class="col-lg-1 text-center valign-middle" style="width: 10%;">繳交</th>
          <th class="valign-middle">工作項目</th>
          <th class="col-lg-5 valign-middle">檔案上傳</th>
        </tr>
      </thead>
      <tbody>
END;

    $subset = "(SELECT * FROM 檔案總管 where year = '{$year}')x";

    foreach($cates as $cate) {
      echo "<tr style=\"background: #A48DDC;\"><td colspan=\"3\"><strong>{$cate}</strong></td></tr>\n";
      $result = getCate("{$cate}");
      while($row = mysql_fetch_array($result)) {
        $count = mysql_query("SELECT count(name) FROM {$subset} WHERE owner = '$user' AND topic = $row[id]") or die(mysql_error());
        $count_result = mysql_fetch_array($count);
        if($count_result[0] != 0) {
          echo "<tr style=\"background: #FFFFFF;\"><td class=\"text-center\"><button type=\"button\" class=\"btn btn-success btn-circle\"><i class=\"fa fa-check\"></i></button></td>\n";
        } else {
          echo "<tr style=\"background: #FFFFFF;\"><td class=\"text-center\"><button type=\"button\" class=\"btn btn-danger btn-circle\"><i class=\"fa fa-times\"></i></button></td>\n";
        }
        echo "<td><p class=\"text-bg no-padding no-margin\">$row[item]</p>";
        $links = mysql_query("SELECT name FROM {$subset} WHERE owner = '$user' AND topic = $row[id]") or die(mysql_error());
        while($link = mysql_fetch_array($links)) {
          $filename = end(split('/', $link[0]));
          echo "<p><span><a href=\"{$userdir}{$link[0]}\">{$filename}</a>&nbsp;<a href=\"upload.php?p=user_delete&d={$cate}/&filesel={$filename}&year={$year}\" class=\"btn btn-outline btn-xs btn-danger\" data-delete=\"\" data-title=\"即將刪除\" data-message=\"{$filename}\">刪除</a>&nbsp;<a href=\"upload.php?p=ren&d={$cate}/&file={$filename}&year={$year}\" class=\"btn btn-outline btn-xs btn-info\">重命名</a></span></p>\n";
        }
        echo "</td>\n";
        echo "<td class=\"valign-middle\">";
        echo "<FORM ENCTYPE=\"multipart/form-data\" ACTION=\"?p=upload\" METHOD=\"POST\">\n";
        echo "<input accept=\"image/*, .pdf, .doc, .docx, .ppt, .pptx, .xls, .xlsx\" style=\"float:left; width:70%;\" type=\"File\" name=\"upfile[]\" size=\"20\" class=\"text\" multiple>\n";
        echo "<progress></progress>\n";
        echo "<input type=\"hidden\" name=ndir value=\"{$cate}/\">\n"
            ."<input type=\"hidden\" name=d value=\"{$cate}/\">\n"
            ."<input type=\"hidden\" name=tid value=\"{$row[id]}\">\n"
            ."<input type=\"hidden\" name=year value=\"{$year}\">\n"
            ."<input type=\"submit\" value=\"Upload\" class=\"btn btn-outline btn-success\">\n"
            ."</form>\n";
        echo "</td></tr>\n";
      }
    }

    /*echo "<br><br>目的地:<br><select name=\"ndir\" size=1>\n";
    if (!$d) echo "<option value=\"/\">/</option>";
    else echo "<option value=\"".$d."\">".$d."</option>";
    $content = listdir($userdir.$d);
    asort($content);
    foreach ($content as $item) echo "<option value=\"".substr($item,strlen($userdir))."/\">".substr($item,strlen($userdir))."/</option>\n";
    echo "</select><br><br>"
        ."<input type=\"hidden\" name=d value=\"$d\">\n"
        ."<input type=\"submit\" value=\"Upload\" class=\"button\">\n"
        ."</form>\n";*/
    echo "</tbody></table>";
    echo "<script src=\"../bower_components/sweetalert/dist/deleter.js\"></script>";
    echo "<script>deleter.init();</script>";
    //closetable();
    //page_footer();
  }

  function upload($upfile, $ndir, $tid, $d, $year) {
    global $user, $userdir, $maxuploads, $default_perm;
    $actualdir = getActualDir($userdir);
    $sql = "";
    $filename = "";
    $x = 0;
    //page_header("Upload");
    //opentable("100%");
    for ($x=0;$x<=$maxuploads;$x++) {
      if(@$upfile['name'][$x]) {
        if (checkdiskspace(filesize($upfile['tmp_name'][$x]))) {
          if (!file_exists(mb_convert_encoding($actualdir.$year.'/'.$ndir, "big5", "UTF-8"))) {
            mkdir(mb_convert_encoding($actualdir.$year.'/'.$ndir, "big5", "UTF-8"), 0777, true);
          }
          if(copy(mb_convert_encoding($upfile['tmp_name'][$x], "big5", "UTF-8"), mb_convert_encoding($actualdir.$year.'/'.$ndir.$upfile['name'][$x], "big5", "UTF-8"))) echo "<b><font color=\"limegreen\">檔案 '/".$year.'/'.$ndir.$upfile['name'][$x]."' 上傳完畢.</font></b><br>\n";
          else echo "<b><font color=\"red\">檔案無法上傳 '/".$year.'/'.$ndir.$upfile['name'][$x]."'.</font></b><br>\n";
          @chmod(mb_convert_encoding($actualdir.$year.$ndir.$upfile['name'][$x], "big5", "UTF-8"), intval($default_perm,8));
          $filename = $year.'/'.$ndir.$upfile['name'][$x];
          $sql = "INSERT INTO 檔案總管 (name, d, owner, topic, year) VALUES ('$filename', '$d', '$user', $tid, $year)";
          mysql_query($sql);
        } else {
          echo "<b><font color=\"red\">空間不夠以致於無法上傳.</font></b><br>\n";
          $space = 1;
          break;
        }
        $uploaded = 1;
      }
    }
    if (!$uploaded && !$space) echo "<b><font color=\"red\">沒有選擇檔案.</font></b>\n";
    /*echo "<br><a href=\"javascript:history.back();\">再次上傳</a>\n"
        ."<br><a href=\"?d=$d\">返回</a>\n";*/
    //closetable();
    //page_footer();
  }

  function edit($fename) {
    global $userdir, $d, $next_action, $message;
    $actualdir = getActualDir($userdir);
    if ($fename && file_exists($actualdir.$d.$fename)) {
      if ($next_action == 2) $sel2 = " checked";
      else $sel1 = " checked";
      page_header("Edit");
      opentable("100%");
      if ($next_action == 1) echo "<font class=ok>The file '".$d.$fename."' was succesfully edited.</font><br>\n";
      else echo "Editing: '".$d.$fename."'<br>\n";
      echo "<form action=\"".$adminfile."?p=save\" method=\"post\">\n"
          ."<textarea cols=\"73\" rows=\"40\" name=\"ncontent\" wrap=off>\n";
      $handle = fopen ($actualdir.$d.$fename, "r");
      $contents = "";
      while ($x<1) {
        $data = @fread ($handle, filesize ($actualdir.$d.$fename));
        if (strlen($data) == 0) break;
        $contents .= $data;
      }
      fclose ($handle);
      echo  ereg_replace ("</textarea>","&lt;/textarea&gt;",$contents)
          ."</textarea>\n"
          ."<br>\n"
          ."<input type=\"hidden\" name=\"d\" value=\"".$d."\">\n"
          ."<input type=\"hidden\" name=\"fename\" value=\"".$fename."\">\n"
          ."<table cellpadding=0 cellpadding=0 width=400>\n"
          ."<tr><td align=left valign=bottom><table cellpadding=1 cellpadding=1>\n"
          ."<tr><td>After saving:\n"
          ."<tr><td><input type=\"radio\" name=\"next_action\" value=\"1\" id=\"act1\"$sel1><label for=\"act1\"> Continue Editing</label>\n"
          ."<input type=\"radio\" name=\"next_action\" value=\"2\" id=\"act2\"$sel2><label for=\"act2\"> Return Home</label>\n"
          ."</table>\n"
          ."<td align=right valign=bottom><input type=\"submit\" value=\"   Save   \" class=\"button\">\n"
          ."</table></form>\n";
    } else {
      page_header("Edit");
      opentable("100%");
      echo "<font class=error>Error opening file.</font><br><a href=\"javascript:history.back();\">Back</a>\n";
    }
    closetable();
    page_footer();
  }

  function save($ncontent, $fename, $d, $next_action) {
    global $userdir, $message;
    $actualdir = getActualDir($userdir);
    if ($fename) {
      $fp = fopen($actualdir.$d.$fename, "w");
      if ($ncontent) {
        if(fwrite($fp, stripslashes($ncontent))) {
          $fp = null;
        } else {
          page_header("Edit");
          opentable("100%");
          echo "<font class=error>編輯此檔案時發生錯誤.</font><br><a href=\"javascript:history.back();\">Back</a>\n";
          closetable();
          page_footer();
        }
      } else {
        // No content. asume correct modification.
      }
    } else {
      page_header("Edit");
      opentable("100%");
      echo "<font class=error>存檔時發生錯誤.</font><br><a href=\"javascript:history.back();\">Back</a>\n";
    }
    if ($next_action == 1) header("Location: ?p=edit&fename=$fename&d=$d&next_action=$next_action");
    else header("Location: ?d=$d");
    die();
  }

  function cr() {
    global $d, $userdir;
    $actualdir = getActualDir($userdir);
    page_header("Create");
    opentable("100%");
    if (!$content == "") { echo "<br><br>請輸入檔名.\n"; }
    echo "<form action=\"".$adminfile."?p=create\" method=\"post\">\n"
        ."檔名: <br><input type=\"text\" size=\"20\" name=\"nfname\" class=\"text\"><br><br>\n"
        ."目的地:<br><select name=ndir size=1>\n";
    if (!$d) echo "<option value=\"/\">/</option>";
    else echo "<option value=\"".$d."\">".$d."</option>";
    $content = listdir($actualdir.$d);
    asort($content);
    foreach ($content as $item) echo "<option value=\"".substr($item,strlen($userdir))."/\">".substr($item,strlen($userdir))."/</option>\n";
    echo "</select><br><br>"
        ."<table cellpadding=0 cellspacing=0 width=100>\n"
        ."<tr><td>目錄 <td><input type=\"radio\" size=\"20\" name=\"isfolder\" value=\"1\" checked>\n"
        ."<tr><td>檔案 <td><input type=\"radio\" size=\"20\" name=\"isfolder\" value=\"0\">\n"
        ."</table><br><br>\n"
        ."<input type=\"hidden\" name=\"d\" value=\"$d\">\n"
        ."<input type=\"submit\" value=\"Create\" class=\"button\">\n"
        ."</form>\n";
    closetable();
    page_footer();
  }

  function create($nfname, $isfolder, $d, $ndir) {
    global $userdir, $default_perm;
    $actualdir = getActualDir($userdir);
    if (!$d) $dis = "/";
    page_header("Create");
    opentable("100%");
    if (!$nfname == "") {
      if (!file_exists($actualdir.$d.$nfname)) {
        if ($isfolder == 1) {
          if(mkdir($actualdir.$d.mb_convert_encoding($nfname, 'big5', 'UTF-8'), $default_perm)) $ok = "Your directory, '".$dis.$d.$ndir.$nfname."', was succesfully created.\n";
          else $error = "目錄, '/".$d.$ndir.$nfname."', 無法被建立. 檢查並確認此目錄的權限被設置為 '0777'.\n";
        } else {
          if(fopen($actualdir.$d.$nfname, "w")) {
            $ok = "Your file, '".$dis.$d.$ndir.$nfname."', was succesfully created.\n";
            @chmod($actualdir.$d.$nfname, intval($default_perm,8));
          } else $error = "檔案, '".$dis.$ndir.$nfname."', 無法被建立. 檢查並確認此目錄的權限被設置為 '0777'.\n";
        }
        if ($ok) echo "<font class=ok>$ok</font><br><a href=\"?d=$d\">Return</a>\n";
        if ($error) echo "<font class=error>$error</font><br><a href=\"javascript:history.back();\">Back</a>\n";
      } else {
        if (is_dir($actualdir.d.$nfname)) echo "<font class=error>A directory by this name already exists. Please choose another.</font><br><a href=\"javascript:history.back();\">Back</a>\n";
        else echo "<font class=error>A file/directory by this name already exists. Please choose another.</font><br><a href=\"javascript:history.back();\">Back</a>\n";
      }
    } else {
      echo "<font class=error>Please enter a filename.</font><br><a href=\"javascript:history.back();\">Back</a>\n";
    }
    closetable();
    page_footer();
  }

  function ren($file, $year) {
    global $d;
    if (!$file == "") {
      page_header("Rename");
      opentable("100%");
      echo "<form action=\"".$adminfile."?p=rename\" method=\"post\">\n"
          ."重命名: '/".$d.$file."'\n"
          ."<br>\n"
          ."<input type=\"hidden\" name=\"rename\" value=\"".$file."\">\n"
          ."<input type=\"hidden\" name=\"d\" value=\"".$d."\">\n"
          ."<input type=\"hidden\" name=\"year\" value=\"".$year."\">\n"
          ."<input class=\"text\" type=\"text\" size=\"40\" width=\"40\" name=\"nrename\" value=\"".$file."\">\n"
          ."<input type=\"Submit\" value=\"Rename\" class=\"button\">\n";
      closetable();
      page_footer();
    } else {
      home();
    }
  }

  function renam($rename, $nrename, $d, $year) {
    global $user, $userdir, $permadmin;
    $actualdir = getActualDir($userdir);
    if ($rename && $nrename) {
      page_header("Rename");
      opentable("100%");

      if($permadmin == 1) {
          $append = strstr($d, "/", true);
          $d = substr(strstr($d, "/"), 1);
          if(rename(mb_convert_encoding($actualdir.$append.'/'.$year.'/'.$d.$rename, "big5", "UTF-8"), mb_convert_encoding($actualdir.$append.'/'.$year.'/'.$d.$nrename, "big5", "UTF-8"))) {
            $back = "?p=complete&d={$d}&year={$year}";
            echo "<font class=ok>The file '".$append.'/'.$year.'/'.$d.$rename."' has been sucessfully changed to '".$append.'/'.$year.'/'.$d.$nrename."'.</font><br><a href=\"{$back}\">Back</a>\n";
            
            mysql_query("UPDATE 檔案總管 SET name = '{$year}/{$d}{$nrename}' WHERE d = '$d' AND name = '{$year}/{$d}{$rename}' AND owner = '$append'") or die(mysql_error());
          } else {
            echo "<font class=error>There was a problem renaming '".$year.'/'.$d.$rename."'</font><br><a href=\"javascript:history.back();\">Back</a>\n";
          }
      } else {
        if(rename(mb_convert_encoding($actualdir.$year.'/'.$d.$rename, "big5", "UTF-8"), mb_convert_encoding($actualdir.$year.'/'.$d.$nrename, "big5", "UTF-8"))) {
            $back = "?p=up&year={$year}";
            echo "<font class=ok>The file '".$year.'/'.$d.$rename."' has been sucessfully changed to '".$year.'/'.$d.$nrename."'.</font><br><a href=\"{$back}\">Back</a>\n";

            mysql_query("UPDATE 檔案總管 SET name = '{$year}/{$d}{$nrename}' WHERE d = '$d' AND name = '{$year}/{$d}{$rename}' AND owner = '$user'") or die(mysql_error());
        }
        else {
            echo "<font class=error>There was a problem renaming '".$year.'/'.$d.$rename."'</font><br><a href=\"javascript:history.back();\">Back</a>\n";
        }
      }
    } else {
      page_header("Rename");
      opentable("100%");
      echo "<font class=error>Please enter a new file name.</font>\n"
          ."<br><a href=\"javascript:history.back();\">Back</a>\n";
    }
    closetable();
    page_footer();
  }

  function bulk_submit($bulk_action,$d) {
    global $_POST, $sqlpref, $d, $tbcolor1, $userdir, $tbcolor2;
    $actualdir = getActualDir($userdir);
    if (!$bulk_action) $error .= "Please select an action.<br>\n";
    if (!$_POST[filesel] && !$_POST[foldersel]) $error .= "Please select at least one file to perform an action on.<br>\n";
    if ($_POST[filesel] && $_POST[foldersel]) $delvar = "files/folders and all of their content";
    elseif ($_POST[filesel] && count($_POST[filesel]) > 1) $delvar = "files";
    elseif ($_POST[foldersel] && count($_POST[foldersel]) > 1) $delvar = "folders and all of their content";
    elseif ($_POST[filesel]) $delvar = "file";
    elseif ($_POST[foldersel]) $delvar = "folder and all of its contents";
    if (!$error && $bulk_action == "delete") {
      page_header("Delete");
      opentable("100%");
      echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=100%>\n"
          ."<form name=bulk_action action=\"?p=bulk_action\" method=post>\n"
          ."<tr><td><font class=error>Are you sure you want to delete the following $delvar?</font><br>\n"
          ."<tr><td bgcolor=$tbcolor1>\n";
      $a=0; $b=0;
      if (is_array($_POST[filesel])) {
        foreach ($_POST[filesel] as $file) {
          echo "$file <input type=hidden name=filesel[$a] value=$file><br>\n";
          $a++;
        }
      }
      if (is_array($_POST[foldersel])) {
        foreach ($_POST[foldersel] as $file) {
          echo "$file<input type=hidden name=foldersel[$b] value=$file><br>\n";
          $b++;
        }
      }
      echo "<tr><td align=center><br><a href=\"javascript:document.bulk_action.submit();\">Yes</a> | \n"
          ."<a href=\"?p=home\"> No </a>\n"
          ."<input type=hidden name=bulk_action value=\"$bulk_action\">\n"
          ."<input type=hidden name=d value=\"$d\">\n"
          ."</td></tr></form></table>\n";
      closetable();
      page_footer();
    } elseif (!$error && $bulk_action == "move") {
      page_header("Move");
      opentable("100%");
      echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=100%>\n"
          ."<form name=bulk_action action=\"?p=bulk_action\" method=post>\n"
          ."<tr><td>Move $delvar:\n"
          ."<tr><td bgcolor=$tbcolor1>\n";

      $a=0; $b=0;
      if (is_array($_POST[filesel])) {
        foreach ($_POST[filesel] as $file) {
          echo "$file <input type=hidden name=filesel[$a] value=$file><br>\n";
          $a++;
        }
      }
      if (is_array($_POST[foldersel])) {
        foreach ($_POST[foldersel] as $file) {
          echo "$file<input type=hidden name=foldersel[$b] value=$file><br>\n";
          $b++;
        }
      }
      echo "<tr><td><select name=ndir size=1>\n"
          ."<option value=\"".substr($item,strlen($userdir.$d))."/\">".substr($item,strlen($userdir.$d))."/</option>";
      $content = listdir($actualdir);
      asort($content);
      foreach ($content as $item) echo "<option value=\"".substr($item,strlen($userdir))."/\">".substr($item,strlen($userdir))."/</option>\n";
      echo "</select> "
          ."<input type=\"Submit\" value=\"Move\" class=\"button\">\n"
          ."<input type=hidden name=bulk_action value=\"$bulk_action\">\n"
          ."<input type=hidden name=d value=\"$d\">\n"
          ."</td></tr></form></table>\n";
      closetable();
      page_footer();
    } elseif (!$error && $bulk_action == "chmod") {
      page_header("Change Permissions");
      opentable("100%");
      echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=100%>\n"
          ."<form name=bulk_action action=\"?p=bulk_action\" method=post>\n"
          ."<tr><td>Change Permissions of $delvar:\n"
          ."<tr><td bgcolor=$tbcolor1>\n";

      $a=0; $b=0;
      if (is_array($_POST[filesel])) {
        foreach ($_POST[filesel] as $file) {
          echo "$file <input type=hidden name=filesel[$a] value=$file><br>\n";
          $a++;
        }
      }
      if (is_array($_POST[foldersel])) {
        foreach ($_POST[foldersel] as $file) {
          echo "$file<input type=hidden name=foldersel[$b] value=$file><br>\n";
          $b++;
        }
      }
      
      if (is_array($_POST[filesel])) {
        $keys = array_keys($_POST[filesel]);
        $chval = substr(sprintf('%o', @fileperms($actualdir.$d.$_POST[filesel][$keys{0}])), -4);
      } else {
        $keys = array_keys($_POST[foldersel]);
        $chval = substr(sprintf('%o', @fileperms($actualdir.$d.$_POST[foldersel][$keys{0}])), -4);
      }
      echo "<tr><td><br><table cellpadding=0 cellspacing=0>\n"
  /* Work in Progess
          ."<tr><td><table cellpadding=0 cellspacing=0 width=120 bgcolor=$tbcolor1>\n"
          ."<tr><td colspan=2>Owner:<tr><td>Read<td><input type=checkbox name=perms00 onMouseUp=\"chmodmake(400);\">\n"
          ."<tr><td>Write<td><input type=checkbox name=perms01 onMouseUp=\"chmodmake(200);\">\n"
          ."<tr><td>Execute<td><input type=checkbox name=perms02 onMouseUp=\"chmodmake(100);\"></table>\n"
          ."<td width=20><img src=../images/pixel.gif width=20 height=1>\n"
          ."<td><table cellpadding=0 cellspacing=0 width=120 bgcolor=$tbcolor1>\n"
          ."<tr><td colspan=2>Group:<tr><td>Read<td><input type=checkbox name=perms10 onMouseUp=\"chmodmake(40);\">\n"
          ."<tr><td>Write<td><input type=checkbox name=perms11 onMouseUp=\"chmodmake(20);\">\n"
          ."<tr><td>Execute<td><input type=checkbox name=perms12 onMouseUp=\"chmodmake(10);\"></table>\n"
          ."<td width=20><img src=../images/pixel.gif width=20 height=1>\n"
          ."<td><table cellpadding=0 cellspacing=0 width=120 bgcolor=$tbcolor1>\n"
          ."<tr><td colspan=2>Pubic:<tr><td>Read<td><input type=checkbox name=perms20 onMouseUp=\"chmodmake(4);\">\n"
          ."<tr><td>Write<td><input type=checkbox name=perms21 onMouseUp=\"chmodmake(2);\">\n"
          ."<tr><td>Execute<td><input type=checkbox name=perms22 onMouseUp=\"chmodmake(1);\"></table>\n"
          ."</table>\n"
  */
          ."<tr><td><input type=text width=20 size=20 name=ndir value=\"$chval\">\n"
          ."<br><br><input type=\"Submit\" value=\"Change\" class=\"button\">\n"
          ."<input type=hidden name=bulk_action value=\"$bulk_action\">\n"
          ."<input type=hidden name=d value=\"$d\">\n"
          ."</td></tr></form></table></table>\n";
      closetable();
      page_footer();
    } else {
      page_header("Action");
      opentable("100%");
      echo "<font class=error>$error</font>\n";
      closetable();
      page_footer();
    }
  }

  function bulk_action($bulk_action,$d,$ndir) {

    global $user, $_POST, $sqlpref, $tbcolor1, $contenta, $contentb, $userdir, $permadmin;
    $actualdir = getActualDir($userdir);
    //set_error_handler ('error_handler');
    if (!$bulk_action) $error .= "Please select an action.<br>\n";
    if (!$_POST[filesel] && !$_POST[foldersel]) $error .= "Please select at least one file to perform an action on.<br>\n";
    if (!$error && $bulk_action == "delete") {
      //page_header("Delete");
      //opentable("100%");
      if (is_array($_POST[filesel])) {
        foreach ($_POST[filesel] as $file) {
          if($permadmin == 1) {
            $append = strstr($d, "/", true);
            if(@unlink(mb_convert_encoding($actualdir.$d.$file, "big5", "UTF-8"))) echo "<font class=ok>" . $file . " 已成功刪除.<br>\n";
            $d = substr(strstr($d, "/"), 1);
            mysql_query("DELETE FROM 檔案總管 WHERE name = '{$d}{$file}' AND owner = '{$append}'") or die(mysql_error());
          } else {
            if(@unlink(mb_convert_encoding($actualdir.$d.$file, "big5", "UTF-8"))) echo "<font class=ok>" . $file . " 已成功刪除.<br>\n";
            mysql_query("DELETE FROM 檔案總管 WHERE name = '{$d}{$file}' AND owner = '{$user}'") or die(mysql_error());
          }
        }
      }
      if (is_array($_POST[foldersel])) {
        foreach ($_POST[foldersel] as $file) {
          listdircontents($userdir.$d);
          foreach ($contenta as $delitem) if(@unlink(mb_convert_encoding($actualdir.$d.$delitem, "big5", "UTF-8"))) echo "<font class=ok>$delitem 已成功刪除.<br>\n";
          foreach ($contentb as $delitem) if(@rmdir(mb_convert_encoding($actualdir.$d.$delitem, "big5", "UTF-8"))) echo "<font class=ok>$delitem 已成功刪除.<br>\n";
        }
      }
      //if (!$parseerror) echo "<a href=\"?d=$d\">Back</a>\n";
      //closetable();
      //page_footer();
    } elseif (!$error && $bulk_action == "move") {
      //page_header("Move");
      //opentable("100%");
      if (is_array($_POST[filesel])) {
        foreach ($_POST[filesel] as $file) {
          if(@rename(mb_convert_encoding($actualdir.$d.$file, "big5", "UTF-8"), mb_convert_encoding($actualdir.$ndir.$file, "big5", "UTF-8"))) echo "<font class=ok>$file 已成功移動.<br>\n";
        }
      }
      if (is_array($_POST[foldersel])) {
        foreach ($_POST[foldersel] as $file) {
          if(@rename(mb_convert_encoding($actualdir.$d.$file, "big5", "UTF-8"), mb_convert_encoding($actualdir.$ndir.$file, "big5", "UTF-8"))) echo "<font class=ok>$file 已成功移動.<br>\n";
        }
      }
      if (!$parseerror) echo "<a href=\"?d=$d\">Back</a>\n";
      //closetable();
      //page_footer();
    } elseif (!$error && $bulk_action == "chmod") {
      //page_header("Change Permissions");
      //opentable("100%");
      if (is_array($_POST[filesel])) {
        foreach ($_POST[filesel] as $file) {
          if(@chmod(mb_convert_encoding($actualdir.$d.$file, "big5", "UTF-8"), intval($ndir,8))) echo "<font class=ok>".$file."'s permissions have been sucessfully chnaged to $ndir.<br>\n";
        }
      }
      if (is_array($_POST[foldersel])) {
        foreach ($_POST[foldersel] as $file) {
          if(@chmod(mb_convert_encoding($userdir.$d.$file, "big5", "UTF-8"), $ndir)) echo "<font class=ok>".$file."'s permissions have been sucessfully chnaged.<br>\n";
        }
      }
      if (!$parseerror) echo "<a href=\"?d=$d\">Back</a>\n";
      //closetable();
      //page_footer();
    } else {
      //page_header("Action");
      //opentable("100%");
      echo "<font class=error>$error</font>\n";
      //closetable();
      //page_footer();
    }
  }

  function viewfile($file,$d) {
    global $userdir;
    $file = $file;
    $filep = $userdir.$d.$file;
    $file = basename(mb_convert_encoding($d.$file, 'big5', 'UTF-8'));
    $len = filesize(mb_convert_encoding($filep, 'big5', 'UTF-8'));
    //$type = mime_content_type($d.$file);
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Content-type: application/force-download");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=".mb_convert_encoding($file, 'UTF-8', 'big5'));
    header("Accept-Ranges: $len"); 
    readfile(mb_convert_encoding($filep, 'big5', 'UTF-8'));
  }

  function getspaceusage($uid) {
    global $sqlpref, $totalbytes;
    $mysql = mysql_query("SELECT folder FROM ".$GLOBALS['config']['db']['pref']."users WHERE id='$uid'");
    list ($folder) = mysql_fetch_row($mysql);
    $totalbytes = "";
    dirusage($folder);
    return $totalbytes;
  }

  function dirusage($dir, $level_count = 0) {
    global $totalbytes;
    if (!@($thisdir = opendir($dir))) return;
    while ($item = readdir($thisdir)) if (is_dir("$dir/$item") && (substr($item, 0, 1) != '.'|'..')) dirusage("$dir/$item", $level_count + 1);
    if ($level_count >= 0) {
      $handle = opendir($dir);
      while ($file = readdir($handle)) if ($file != "." && $file != ".." && !is_dir($dir."/".$file)) $totalbytes = $totalbytes + filesize($dir."/".$file);
    }
  }

  function formatperms($perms) {
    if (($perms & 0xC000) == 0xC000) $info = 's';
    elseif (($perms & 0xA000) == 0xA000) $info = 'l';
    elseif (($perms & 0x8000) == 0x8000) $info = '-';
    elseif (($perms & 0x6000) == 0x6000) $info = 'b';
    elseif (($perms & 0x4000) == 0x4000) $info = 'd';
    elseif (($perms & 0x2000) == 0x2000) $info = 'c';
    elseif (($perms & 0x1000) == 0x1000) $info = 'p';
    else $info = 'u';
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));
    return $info;
  }

  function showdiskspace() {
    global $userid, $limit;
    $width = 208;
    $used = getspaceusage($userid);
    $percent = round(($used  / $limit) * 100);
    if ($percent < 60) $barstyle = "barnormal";
    elseif ($percent < 80) $barstyle = "barwarning";
    else $barstyle = "barerror";
    $barwidth = round(($used  / $limit) * $width);
    $retval = "<table cellpadding=0 cellspacing=0>\n"
             ."<tr><td class=space style=\"COLOR: #000000\" align=center>空間用量: ".getfilesize($used)." / ".getfilesize($limit)." ($percent%)\n"
             ."<tr><td><table cellpadding=1 cellspacing=0 width=$width bgcolor=#FFFFFF height=4>"
             ."<tr><td><table cellpadding=0 cellspacing=0 bgcolor=#555555 width=100% height=4>"
             ."<tr><td><table cellpadding=0 cellspacing=0 class=\"$barstyle\" width=\"".$barwidth."\" height=4 >"
             ."<tr><td class=space><img src=../images/pixel.gif height=4 width=1></td></tr>"
             ."</table></td></tr></table></td></tr></table></td></tr></table>\n";
    return $retval;
  }

  function checkdiskspace($change) {
    global $userid, $limit;
    if ($limit > getspaceusage($userid)+$change) return TRUE;
    else return FALSE;
  }

  function super() {
    page_header("Admin Pannel");
    opentable("100%");
    echo "<a href=?p=users>使用者管理</a><br>\n";
    echo "<a href=?p=items>上傳項目管理</a><br>\n";
    closetable();
    page_footer();
  }

  function usermod() {
    global $tbcolor1, $tbcolor2, $tbcolor3, $issuper, $sqlpref, $bgcolor3, $IMG_DELETE, $IMG_EDIT;
    //page_header("Admin Pannel");
    $result = mysql_query("SELECT id, user, folder, name, permadmin FROM ".$GLOBALS['config']['db']['pref']."users");
    while(list($uid, $uname, $d, $info, $permadmin) = mysql_fetch_row($result)) {
      if ($permadmin) $super = "Yes"; else $super = "No";
      $tcoloring = ($a % 2) ? $tbcolor1 : $tbcolor2;
      $content .= "<tr bgcolor=$tcoloring><td>$uname</a>"
                 ."<td align=center>$d<td align=center>$super"
                 ."<td align=center><a href=\"".$adminfile."?p=deluser&muid=$uid\"><img src=\"$IMG_DELETE\" border=0 alt=\"Delete\" label=\"Delete\" title\"Delete\"></a>\n"
                 ."<td align=center><a href=\"".$adminfile."?p=eduser&muid=$uid\"><img src=\"$IMG_EDIT\" border=0 alt=\"Edit\" label=\"Edit\" title=\"Edit\"></a>\n";
      $a++;
    }
    echo "<tr><td bgcolor=$bgcolor3>\n"
        ."<table border=\"0\" cellpadding=\"0\" cellspacing=\"1\" width=100%>\n"
        ."<tr bgcolor=\"$tbcolor3\" width=20 class=titlebar1 height=25>\n"
        ."<td class=theader>帳號<td class=theader>根目錄<td class=theader width=60 nowrap>管理員<td class=theader colspan=2 width=60 nowrap align=center>動作\n"
        .$content
        ."</table>\n"
        ."<br><br><a href=\"".$adminfile."?p=newuser\">新增使用者</a><br>\n";
    //page_footer();
  }

  function eduser($muid) {
    global $sqlpref, $extraheaders;
    $extraheaders = "<script language=\"javascript\">\n"
                   ."function checkall() {\n"
                   ."  document.user_edit.config_permbrowse.checked = true;\n"
                   ."  document.user_edit.config_permupload.checked = true;\n"
                   ."  document.user_edit.config_permcreate.checked = true;\n"
                   ."  document.user_edit.config_permuser.checked = true;\n"
                   ."  document.user_edit.config_permpass.checked = true;\n"
                   ."  document.user_edit.config_permdelete.checked = true;\n"
                   ."  document.user_edit.config_permmove.checked = true;\n"
                   ."  document.user_edit.config_permchmod.checked = true;\n"
                   ."  document.user_edit.config_permget.checked = true;\n"
                   ."  document.user_edit.config_permadmin.checked = true;\n"
                   ."  document.user_edit.config_permdeleteuser.checked = true;\n"
                   ."  document.user_edit.config_permedituser.checked = true;\n"
                   ."  document.user_edit.config_permmakeuser.checked = true;\n"
                   ."  document.user_edit.config_permedit.checked = true;\n"
                   ."  document.user_edit.config_permrename.checked = true;\n"
                   ."  document.user_edit.config_permsub.checked = true;\n"
                   ."  document.user_edit.config_permprefs.checked = true;\n"
                   ."  try {document.user_edit.config_permrecycle.checked = true;} catch (e) { }\n"
                   ."}\n"
                   ."function uncheckall() {\n"
                   ."  document.user_edit.config_permbrowse.checked = false;\n"
                   ."  document.user_edit.config_permupload.checked = false;\n"
                   ."  document.user_edit.config_permcreate.checked = false;\n"
                   ."  document.user_edit.config_permuser.checked = false;\n"
                   ."  document.user_edit.config_permpass.checked = false;\n"
                   ."  document.user_edit.config_permdelete.checked = false;\n"
                   ."  document.user_edit.config_permmove.checked = false;\n"
                   ."  document.user_edit.config_permchmod.checked = false;\n"
                   ."  document.user_edit.config_permget.checked = false;\n"
                   ."  document.user_edit.config_permadmin.checked = false;\n"
                   ."  document.user_edit.config_permdeleteuser.checked = false;\n"
                   ."  document.user_edit.config_permedituser.checked = false;\n"
                   ."  document.user_edit.config_permmakeuser.checked = false;\n"
                   ."  document.user_edit.config_permedit.checked = false;\n"
                   ."  document.user_edit.config_permrename.checked = false;\n"
                   ."  document.user_edit.config_permsub.checked = false;\n"
                   ."  document.user_edit.config_permprefs.checked = false;\n"
                   ."  try {document.user_edit.config_permrecycle.checked = false;} catch (e) { }\n"
                   ."}\n"
                   ."</script>\n";
    echo $extraheaders;
    $result = mysql_query("SELECT id, user, email, name, folder, http, spacelimit, theme, language, permbrowse, permupload, permcreate, permuser, permadmin, permdelete, permmove, permchmod, permget, permdeleteuser, permedituser, permmakeuser, permpass, permedit, permrename, permsub, formatperm, status, recycle, permrecycle, permprefs FROM ".$GLOBALS['config']['db']['pref']."users WHERE id=$muid");
    list($uid, $uname, $email, $name, $folder, $http, $limit, $theme, $language, $permbrowse, $permupload, $permcreate, $permuser, $permadmin, $permdelete, $permmove, $permchmod, $permget, $permdeleteuser, $permedituser, $permmakeuser, $permpass, $permedit, $permrename, $permsub, $formatperm, $status, $recycle, $permrecycle, $permprefs) = mysql_fetch_row($result);
    page_header("Edit User $uname");
    opentable("100%");
    echo "<table>\n"
        ."<form name=\"user_edit\" action=\"".$adminfile."?p=edituser\" method=\"post\">\n";
    if ($permbrowse == 1) $sel1 = " checked"; else $sel1 = "";
    if ($permupload == 1) $sel2 = " checked"; else $sel2 = "";
    if ($permcreate == 1) $sel3 = " checked"; else $sel3 = "";
    if ($permuser == 1) $sel4 = " checked"; else $sel4 = "";
    if ($permpass == 1) $sel5 = " checked"; else $sel5 = "";
    if ($permdelete == 1) $sel6 = " checked"; else $sel6 = "";
    if ($permmove == 1) $sel7 = " checked"; else $sel7 = "";
    if ($permchmod == 1) $sel8 = " checked"; else $sel8 = "";
    if ($permget == 1) $sel9 = " checked"; else $sel9 = "";
    if ($permadmin == 1) $sel10 = " checked"; else $sel10 = "";
    if ($permdeleteuser == 1) $sel11 = " checked"; else $sel11 = "";
    if ($permedituser == 1) $sel12 = " checked"; else $sel12 = "";
    if ($permmakeuser == 1) $sel13 = " checked"; else $sel13 = "";
    if ($permedit == 1) $sel14 = " checked"; else $sel14 = "";
    if ($permrename == 1) $sel15 = " checked"; else $sel15 = "";
    if ($permsub == 1) $sel16 = " checked"; else $sel16 = "";
    if ($permrecycle == 1) $sel17 = " checked"; else $sel17 = "";
    if ($permrecycle == 1) $sel17 = " checked"; else $sel17 = "";
    if ($permprefs == 1) $sel18 = " checked"; else $sel18 = "";


    if ($formatperm == 0) $perm1 = " checked";
    elseif ($formatperm == 1) $perm2 = " checked"; 

    if ($status == 0) $stat1 = " checked";
    elseif ($status == 1) $stat2 = " checked";

    if ($recycle == 0) $rec1 = " checked";
    elseif ($recycle == 1) $rec2 = " checked";

    echo "<tr><td>帳號: <td><input type=\"text\" name=\"config_user\" size=\"40\" border=\"0\" class=\"txtinput\" value=\"$uname\">\n"
        ."<tr><td>稱號: <td><input type=\"text\" name=\"config_name\" size=\"40\" border=\"0\" class=\"txtinput\" value=\"$name\">\n"
        ."<tr><td>密碼: <td><input type=\"password\" name=\"config_pass\" size=\"40\" border=\"0\" class=\"txtinput\" value=\"\">\n"
        ."<tr><td>信箱: <td><input type=\"text\" name=\"config_email\" size=\"40\" border=\"0\" class=\"txtinput\" value=\"$email\">\n"
        ."<tr><td>根目錄: <td><input type=\"text\" name=\"config_folder\" size=\"40\" border=\"0\" class=\"txtinput\" value=\"$folder\">\n"
        ."<tr><td>HTTP目錄: <td><input type=\"text\" name=\"config_http\" size=\"40\" border=\"0\" class=\"txtinput\" value=\"$http\"> (*)\n"
        ."<tr><td>空間大小: <td><table cellpadding=0 cellspacing=0><td nowrap>".getspaceusage($uid)." (".getfilesize(getspaceusage($uid)).") / <td><input type=\"text\" name=\"config_limit\" size=\"15\" width=\"15\" border=\"0\" class=\"txtinput\" value=\"$limit\"> bytes</table>\n"
        ."<tr><td>語言: <td><select name=\"config_language\">\n";
    $handle = opendir("./language");
    while ($file = readdir($handle)) $filelist[] = $file;
    natcasesort($filelist);
    foreach ($filelist as $file) {
      if ($file != "." && $file != ".." && is_dir("./language/".$file)) {
        @include("./language/".$file."/lng.def.php");
        if ($language == $file) $isel = " selected"; else $isel = "";
        echo "<option value=\"$file\"$isel>$LNG_NAME</option>\n";
      }
    }
    closedir("./language");
    echo "</select><tr><td>主題: <td><select name=\"config_theme\">\n";
    $handle = opendir("./themes");
    while ($file = readdir($handle)) $filelist[] = $file;
    natcasesort($filelist);
    foreach ($filelist as $file) {
      if ($file != "." && $file != ".." && is_dir("./themes/".$file)) {
        @include("./themes/".$file."/theme.def.php");
        if ($theme == $file) $isel = " selected"; else $isel = "";
        echo "<option value=\"$file\"$isel>$THEME_NAME</option>\n";
      }
    }
    closedir("./themes");
    echo "</select>\n"
        ."<tr><td>帳號狀態: <td>\n"
        ."<input type=radio name=\"config_status\" value=\"1\" id=\"stat1\"$stat2><label for=\"stat1\"> 啟用</label>&nbsp;&nbsp;&nbsp;\n"
        ."<input type=radio name=\"config_status\" value=\"0\" id=\"stat2\"$stat1><label for=\"stat2\"> 停用</label>\n";
    if ($GLOBALS['config']['enable_trash']) {
      echo "<tr><td>Trash Bin: <td>\n"
        ."<input type=radio name=\"config_recycle\" id=\"rec1\"$rec2><label for=\"rec1\"> On</label>&nbsp;&nbsp;&nbsp;\n"
        ."<input type=radio name=\"config_recycle\" id=\"rec2\"$rec1><label for=\"rec2\"> Off</label>\n";
    }
    echo "<tr><td>權限表示法: <td>\n"
        ."<input type=radio name=\"config_formatperms\" value=\"0\" id=\"perm1\"$perm1><label for=\"perm1\"> UNIX (0644)</label>&nbsp;&nbsp;&nbsp;\n"
        ."<input type=radio name=\"config_formatperms\" value=\"1\" id=\"perm2\"$perm2><label for=\"perm2\"> Symbolic (-rw-r--r--)</label>\n"


        ."<tr><td valign=top>權限: <td><table cellpadding=1 cellspacing=1>\n"
        ."<td valign=top nowrap><input type=\"checkbox\" name=\"config_permbrowse\" id=\"config_permbrowse\" size=\"40\" border=\"0\" class=\"text\"$sel1><label for=\"config_permbrowse\"> 瀏覽</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permupload\" id=\"config_permupload\" size=\"40\" border=\"0\" class=\"text\"$sel2><label for=\"config_permupload\"> 上傳</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permcreate\" id=\"config_permcreate\" size=\"40\" border=\"0\" class=\"text\"$sel3><label for=\"config_permcreate\"> 新增</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permpass\" id=\"config_permpass\" size=\"40\" border=\"0\" class=\"text\"$sel5><label for=\"config_permpass\"> 變更密碼</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permdelete\" id=\"config_permdelete\" size=\"40\" border=\"0\" class=\"text\"$sel6><label for=\"config_permdelete\"> 刪除</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permmove\" id=\"config_permmove\" size=\"40\" border=\"0\" class=\"text\"$sel7><label for=\"config_permmove\"> 移動</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permedit\" id=\"config_permedit\" size=\"40\" border=\"0\" class=\"text\"$sel14><label for=\"config_permedit\"> 編輯</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permrename\" id=\"config_permrename\" size=\"40\" border=\"0\" class=\"text\"$sel15><label for=\"config_permrename\"> 重命名</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permget\" id=\"config_permget\" size=\"40\" border=\"0\" class=\"text\"$sel9><label for=\"config_permget\"> 下載</label>\n"
        ."<td valign=top nowrap><input type=\"checkbox\" name=\"config_permchmod\" id=\"config_permchmod\" size=\"40\" border=\"0\" class=\"text\"$sel8><label for=\"config_permchmod\"> 變更權限</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permsub\" id=\"config_permsub\" size=\"40\" border=\"0\" class=\"text\"$sel16><label for=\"config_persub\"> 存取子目錄</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permuser\" id=\"config_permuser\" size=\"40\" border=\"0\" class=\"text\"$sel4><label for=\"config_permuser\"> 使用者控制台</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permadmin\" id=\"config_permadmin\" size=\"40\" border=\"0\" class=\"text\"$sel10><label for=\"config_permadmin\"> 管理員控制台</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permdeleteuser\" id=\"config_permdeleteuser\" size=\"40\" border=\"0\" class=\"text\"$sel11><label for=\"config_permdeleteuser\"> 刪除使用者</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permedituser\" id=\"config_permedituser\" size=\"40\" border=\"0\" class=\"text\"$sel12><label for=\"config_permedituser\"> 編輯使用者</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permmakeuser\" id=\"config_permmakeuser\" size=\"40\" border=\"0\" class=\"text\"$sel13><label for=\"config_permmakeuser\"> 新增使用者</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permprefs\" id=\"config_permprefs\" size=\"40\" border=\"0\" class=\"text\"$sel18><label for=\"config_permprefs\"> 編輯前綴</label>\n";

    if ($GLOBALS['config']['enable_trash']) {
      echo "<br><input type=\"checkbox\" name=\"config_permrecycle\" id=\"config_permrecycle\" size=\"40\" border=\"0\" class=\"text\"$sel17><label for=\"config_permrecycle\"> Trash Bin</label>\n";
    }
    echo "</table>\n"
        ."<br><a href=\"javascript:checkall();\">全部選取</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript:uncheckall();\">全部取消</a>\n"
        ."<input type=hidden name=muid value=\"$muid\">\n"
        ."<tr><td colspan=\"2\"><br><input type=\"submit\" name=\"submitButtonName\" value=\"Save\" border=\"0\" class=\"button\">\n"
        ."</td></tr></form></table>\n";
    closetable();
    page_footer();
  }

  function edituser($muid) {
    if ($_REQUEST['config_status']) $_REQUEST['config_status'] = 1; else $_REQUEST['config_status'] = 0;
    if ($_REQUEST['config_recycle']) $_REQUEST['config_recycle'] = 1; else $_REQUEST['config_recycle'] = 0;
    if ($_REQUEST['config_permbrowse']) $_REQUEST['config_permbrowse'] = 1; else $_REQUEST['config_permbrowse'] = 0;
    if ($_REQUEST['config_permupload']) $_REQUEST['config_permupload'] = 1; else $_REQUEST['config_permupload'] = 0;
    if ($_REQUEST['config_permcreate']) $_REQUEST['config_permcreate'] = 1; else $_REQUEST['config_permcreate'] = 0;
    if ($_REQUEST['config_permpass']) $_REQUEST['config_permpass'] = 1; else $_REQUEST['config_permpass'] = 0;
    if ($_REQUEST['config_permdelete']) $_REQUEST['config_permdelete'] = 1; else $_REQUEST['config_permdelete'] = 0;
    if ($_REQUEST['config_permmove']) $_REQUEST['config_permmove'] = 1; else $_REQUEST['config_permmove'] = 0;
    if ($_REQUEST['config_permedit']) $_REQUEST['config_permedit'] = 1; else $_REQUEST['config_permedit'] = 0;
    if ($_REQUEST['config_permrename']) $_REQUEST['config_permrename'] = 1; else $_REQUEST['config_permrename'] = 0;
    if ($_REQUEST['config_permget']) $_REQUEST['config_permget'] = 1; else $_REQUEST['config_permget'] = 0;
    if ($_REQUEST['config_permchmod']) $_REQUEST['config_permchmod'] = 1; else $_REQUEST['config_permchmod'] = 0;
    if ($_REQUEST['config_permsub']) $_REQUEST['config_permsub'] = 1; else $_REQUEST['config_permsub'] = 0;
    if ($_REQUEST['config_permuser']) $_REQUEST['config_permuser'] = 1; else $_REQUEST['config_permuser'] = 0;
    if ($_REQUEST['config_permadmin']) $_REQUEST['config_permadmin'] = 1; else $_REQUEST['config_permadmin'] = 0;
    if ($_REQUEST['config_permdeleteuser']) $_REQUEST['config_permdeleteuser'] = 1; else $_REQUEST['config_permdeleteuser'] = 0;
    if ($_REQUEST['config_permedituser']) $_REQUEST['config_permedituser'] = 1; else $_REQUEST['config_permedituser'] = 0;
    if ($_REQUEST['config_permmakeuser']) $_REQUEST['config_permmakeuser'] = 1; else $_REQUEST['config_permmakeuser'] = 0;
    if ($_REQUEST['config_permuser']) $_REQUEST['config_permuser'] = 1; else $_REQUEST['config_permuser'] = 0;
    if ($_REQUEST['config_permrecycle']) $_REQUEST['config_permrecycle'] = 1; else $_REQUEST['config_permrecycle'] = 0;
    if ($_REQUEST['config_permprefs']) $_REQUEST['config_permprefs'] = 1; else $_REQUEST['config_permprefs'] = 0;

    page_header("Edit User $muname");
    opentable("100%");

    $query = '
      UPDATE '.$GLOBALS['config']['db']['pref'].'users 
      SET user="'.$_REQUEST['config_user'].'", ';
    if ($_REQUEST['config_pass']) $query .= 'pass="'.md5($_REQUEST['config_pass']).'",';
    $query .= '
      email="'.$_REQUEST['config_email'].'",
      name="'.$_REQUEST['config_name'].'",
      folder="'.$_REQUEST['config_folder'].'",
      http="'.$_REQUEST['config_http'].'",
      spacelimit="'.$_REQUEST['config_limit'].'",
      theme="'.$_REQUEST['config_theme'].'",
      language="'.$_REQUEST['config_language'].'",
      permbrowse="'.$_REQUEST['config_permbrowse'].'",
      permupload="'.$_REQUEST['config_permupload'].'",
      permcreate="'.$_REQUEST['config_permcreate'].'",
      permuser="'.$_REQUEST['config_permuser'].'",
      permadmin="'.$_REQUEST['config_permadmin'].'",
      permdelete="'.$_REQUEST['config_permdelete'].'",
      permmove="'.$_REQUEST['config_permmove'].'",
      permchmod="'.$_REQUEST['config_permchmod'].'",
      permget="'.$_REQUEST['config_permget'].'",
      permdeleteuser="'.$_REQUEST['config_permdeleteuser'].'",
      permedituser="'.$_REQUEST['config_permedituser'].'",
      permmakeuser="'.$_REQUEST['config_permmakeuser'].'",
      permpass="'.$_REQUEST['config_permmakeuser'].'",
      permedit="'.$_REQUEST['config_permedit'].'",
      permrename="'.$_REQUEST['config_permrename'].'",
      permsub="'.$_REQUEST['config_permsub'].'",
      permrecycle="'.$_REQUEST['config_permrecycle'].'",
      permprefs="'.$_REQUEST['config_permprefs'].'",
      formatperm="'.$_REQUEST['config_formatperms'].'",
      status="'.$_REQUEST['config_status'].'",
      recycle="0"
      WHERE id='.$muid;
    $msql = mysql_query($query) or die(mysql_error());
    echo "User info succesfully updated<br>\n";
    closetable();
    page_footer();
  }

  function deluser($muid) {
    global $issuper, $userid;

    $mresult = mysql_query("SELECT user FROM ".$GLOBALS['config']['db']['pref']."users WHERE id=$muid");
    list ($muname) = mysql_fetch_row($mresult);
    page_header("Delete user $muname");
    opentable("100%");
    if ($muid == $userid) {
      echo "<font class=error>**ERROR: You can not delete yourself!**</font><br>\n";
    } elseif ($msuper == "Code57") {
      echo "<font class=error>**ERROR: You can not delete an admin!**</font><br>\n";
    } else {
      echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n"
          ."<font class=error>**WARNING: This will permanatly delete $muname. This action is irreversable.**</font><br><br>\n"
          ."Are you sure you want to delete ".$muname."?<br><br>\n"
          ."<a href=\"".$adminfile."?p=deleteuser&muid=$muid\">Yes</a> | \n"
          ."<a href=\"".$adminfile."?p=home\"> No </a>\n"
          ."</table>\n";
    }
    closetable();
    page_footer();
  }

  function deleteuser($muid) {
    global $issuper, $userid;
    $mresult = mysql_query("SELECT user FROM ".$GLOBALS['config']['db']['pref']."users WHERE id=$muid");
    list ($uname) = mysql_fetch_row($mresult);
    page_header("Delete user $uname");
    opentable("100%");
    if ($muid == $userid) {
      echo "<font class=error>**ERROR: You can not delete yourself!**</font><br>\n";
    } elseif ($msuper == "Code57") {
      echo "<font class=error>**ERROR: You can not delete an admin!**</font><br>\n";
    } else {
      $mysql = mysql_query("DELETE FROM ".$GLOBALS['config']['db']['pref']."users WHERE id=$muid");
      echo "User $uname successfully deleted<br>\n";
    }
    closetable();
    page_footer();
  }

  function newuser() {
    global $sqlpref, $extraheaders;
    $extraheaders = "<script language=\"javascript\">\n"
                   ."function checkall() {\n"
                   ."  document.user_edit.config_permbrowse.checked = true;\n"
                   ."  document.user_edit.config_permupload.checked = true;\n"
                   ."  document.user_edit.config_permcreate.checked = true;\n"
                   ."  document.user_edit.config_permuser.checked = true;\n"
                   ."  document.user_edit.config_permpass.checked = true;\n"
                   ."  document.user_edit.config_permdelete.checked = true;\n"
                   ."  document.user_edit.config_permmove.checked = true;\n"
                   ."  document.user_edit.config_permchmod.checked = true;\n"
                   ."  document.user_edit.config_permget.checked = true;\n"
                   ."  document.user_edit.config_permadmin.checked = true;\n"
                   ."  document.user_edit.config_permdeleteuser.checked = true;\n"
                   ."  document.user_edit.config_permedituser.checked = true;\n"
                   ."  document.user_edit.config_permmakeuser.checked = true;\n"
                   ."  document.user_edit.config_permedit.checked = true;\n"
                   ."  document.user_edit.config_permrename.checked = true;\n"
                   ."  document.user_edit.config_permsub.checked = true;\n"
                   ."  document.user_edit.config_permprefs.checked = true;\n"
                   ."  try {document.user_edit.config_permrecycle.checked = true;} catch (e) { }\n"
                   ."}\n"
                   ."function uncheckall() {\n"
                   ."  document.user_edit.config_permbrowse.checked = false;\n"
                   ."  document.user_edit.config_permupload.checked = false;\n"
                   ."  document.user_edit.config_permcreate.checked = false;\n"
                   ."  document.user_edit.config_permuser.checked = false;\n"
                   ."  document.user_edit.config_permpass.checked = false;\n"
                   ."  document.user_edit.config_permdelete.checked = false;\n"
                   ."  document.user_edit.config_permmove.checked = false;\n"
                   ."  document.user_edit.config_permchmod.checked = false;\n"
                   ."  document.user_edit.config_permget.checked = false;\n"
                   ."  document.user_edit.config_permadmin.checked = false;\n"
                   ."  document.user_edit.config_permdeleteuser.checked = false;\n"
                   ."  document.user_edit.config_permedituser.checked = false;\n"
                   ."  document.user_edit.config_permmakeuser.checked = false;\n"
                   ."  document.user_edit.config_permedit.checked = false;\n"
                   ."  document.user_edit.config_permrename.checked = false;\n"
                   ."  document.user_edit.config_permsub.checked = false;\n"
                   ."  document.user_edit.config_permprefs.checked = false;\n"
                   ."  try {document.user_edit.config_permrecycle.checked = false;} catch (e) { }\n"
                   ."}\n"
                   ."</script>\n";
    echo $extraheaders;
    page_header("Create a new user");
    opentable("100%");
    echo "<table>\n"
        ."<form name=\"user_edit\" action=\"".$adminfile."?p=saveuser\" method=\"post\">\n";

    echo "<tr><td>帳號: <td><input type=\"text\" name=\"config_user\" size=\"40\" border=\"0\" class=\"txtinput\" value=\"$uname\">\n"
        ."<tr><td>稱號: <td><input type=\"text\" name=\"config_name\" size=\"40\" border=\"0\" class=\"txtinput\" value=\"$name\">\n"
        ."<tr><td>密碼: <td><input type=\"password\" name=\"config_pass\" size=\"40\" border=\"0\" class=\"txtinput\" value=\"\">\n"
        ."<tr><td>信箱: <td><input type=\"text\" name=\"config_email\" size=\"40\" border=\"0\" class=\"txtinput\" value=\"$email\">\n"
        ."<tr><td>根目錄: <td><input type=\"text\" name=\"config_folder\" size=\"40\" border=\"0\" class=\"txtinput\" value=\"$folder\">\n"
        ."<tr><td>HTTP目錄: <td><input type=\"text\" name=\"config_http\" size=\"40\" border=\"0\" class=\"txtinput\" value=\"$http\"> (*)\n"
        ."<tr><td>空間大小: <td><table cellpadding=0 cellspacing=0><td nowrap>".getspaceusage($uid)." (".getfilesize(getspaceusage($uid)).") / <td><input type=\"text\" name=\"config_limit\" size=\"15\" width=\"15\" border=\"0\" class=\"txtinput\" value=\"$limit\"> bytes</table>\n"
        ."<tr><td>語言: <td><select name=\"config_language\">\n";
    $handle = opendir("./language");
    while ($file = readdir($handle)) $filelist[] = $file;
    natcasesort($filelist);
    foreach ($filelist as $file) {
      if ($file != "." && $file != ".." && is_dir("./language/".$file)) {
        @include("./language/".$file."/lng.def.php");
        if ($language == $file) $isel = " selected"; else $isel = "";
        echo "<option value=\"$file\"$isel>$LNG_NAME</option>\n";
      }
    }
    closedir("./language");
    echo "</select><tr><td>主題: <td><select name=\"config_theme\">\n";
    $handle = opendir("./themes");
    while ($file = readdir($handle)) $filelist[] = $file;
    natcasesort($filelist);
    foreach ($filelist as $file) {
      if ($file != "." && $file != ".." && is_dir("./themes/".$file)) {
        @include("./themes/".$file."/theme.def.php");
        if ($theme == $file) $isel = " selected"; else $isel = "";
        echo "<option value=\"$file\"$isel>$THEME_NAME</option>\n";
      }
    }
    closedir("./themes");
    echo "</select>\n"
        ."<tr><td>帳號狀態: <td>\n"
        ."<input type=radio name=\"config_status\" value=\"1\" id=\"stat1\"$stat2><label for=\"stat1\"> 啟用</label>&nbsp;&nbsp;&nbsp;\n"
        ."<input type=radio name=\"config_status\" value=\"0\" id=\"stat2\"$stat1><label for=\"stat2\"> 停用</label>\n";
    if ($GLOBALS['config']['enable_trash']) {
      echo "<tr><td>Trash Bin: <td>\n"
        ."<input type=radio name=\"config_recycle\" id=\"rec1\"$rec2><label for=\"rec1\"> On</label>&nbsp;&nbsp;&nbsp;\n"
        ."<input type=radio name=\"config_recycle\" id=\"rec2\"$rec1><label for=\"rec2\"> Off</label>\n";
    }
    echo "<tr><td>權限表示法: <td>\n"
        ."<input type=radio name=\"config_formatperms\" value=\"0\" id=\"perm1\"$perm1><label for=\"perm1\"> UNIX (0644)</label>&nbsp;&nbsp;&nbsp;\n"
        ."<input type=radio name=\"config_formatperms\" value=\"1\" id=\"perm2\"$perm2><label for=\"perm2\"> Symbolic (-rw-r--r--)</label>\n"


        ."<tr><td valign=top>權限: <td><table cellpadding=1 cellspacing=1>\n"
        ."<td valign=top nowrap><input type=\"checkbox\" name=\"config_permbrowse\" id=\"config_permbrowse\" size=\"40\" border=\"0\" class=\"text\"$sel1><label for=\"config_permbrowse\"> 瀏覽</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permupload\" id=\"config_permupload\" size=\"40\" border=\"0\" class=\"text\"$sel2><label for=\"config_permupload\"> 上傳</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permcreate\" id=\"config_permcreate\" size=\"40\" border=\"0\" class=\"text\"$sel3><label for=\"config_permcreate\"> 新增</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permpass\" id=\"config_permpass\" size=\"40\" border=\"0\" class=\"text\"$sel5><label for=\"config_permpass\"> 變更密碼</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permdelete\" id=\"config_permdelete\" size=\"40\" border=\"0\" class=\"text\"$sel6><label for=\"config_permdelete\"> 刪除</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permmove\" id=\"config_permmove\" size=\"40\" border=\"0\" class=\"text\"$sel7><label for=\"config_permmove\"> 移動</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permedit\" id=\"config_permedit\" size=\"40\" border=\"0\" class=\"text\"$sel14><label for=\"config_permedit\"> 編輯</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permrename\" id=\"config_permrename\" size=\"40\" border=\"0\" class=\"text\"$sel15><label for=\"config_permrename\"> 重命名</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permget\" id=\"config_permget\" size=\"40\" border=\"0\" class=\"text\"$sel9><label for=\"config_permget\"> 下載</label>\n"
        ."<td valign=top nowrap><input type=\"checkbox\" name=\"config_permchmod\" id=\"config_permchmod\" size=\"40\" border=\"0\" class=\"text\"$sel8><label for=\"config_permchmod\"> 變更權限</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permsub\" id=\"config_permsub\" size=\"40\" border=\"0\" class=\"text\"$sel16><label for=\"config_persub\"> 存取子目錄</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permuser\" id=\"config_permuser\" size=\"40\" border=\"0\" class=\"text\"$sel4><label for=\"config_permuser\"> 使用者控制台</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permadmin\" id=\"config_permadmin\" size=\"40\" border=\"0\" class=\"text\"$sel10><label for=\"config_permadmin\"> 管理員控制台</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permdeleteuser\" id=\"config_permdeleteuser\" size=\"40\" border=\"0\" class=\"text\"$sel11><label for=\"config_permdeleteuser\"> 刪除使用者</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permedituser\" id=\"config_permedituser\" size=\"40\" border=\"0\" class=\"text\"$sel12><label for=\"config_permedituser\"> 編輯使用者</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permmakeuser\" id=\"config_permmakeuser\" size=\"40\" border=\"0\" class=\"text\"$sel13><label for=\"config_permmakeuser\"> 新增使用者</label>\n"
        ."<br><input type=\"checkbox\" name=\"config_permprefs\" id=\"config_permprefs\" size=\"40\" border=\"0\" class=\"text\"$sel18><label for=\"config_permprefs\"> 編輯偏好設定</label>\n";

    if ($GLOBALS['config']['enable_trash']) {
      echo "<br><input type=\"checkbox\" name=\"config_permrecycle\" id=\"config_permrecycle\" size=\"40\" border=\"0\" class=\"text\"$sel17><label for=\"config_permrecycle\"> Trash Bin</label>\n";
    }
    echo "</table>\n"
        ."<br><a href=\"javascript:checkall();\">全部選取</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript:uncheckall();\">全部取消</a>\n"
        ."<input type=hidden name=muid value=\"$muid\">\n"
        ."<tr><td colspan=\"2\"><br><input type=\"submit\" name=\"submitButtonName\" value=\"Save\" border=\"0\" class=\"button\">\n"
        ."</td></tr></form></table>\n";
    closetable();
    page_footer();
  }

  function saveuser() {
    if ($_REQUEST['config_status']) $_REQUEST['config_status'] = 1; else $_REQUEST['config_status'] = 0;
    if ($_REQUEST['config_recycle']) $_REQUEST['config_recycle'] = 1; else $_REQUEST['config_recycle'] = 0;
    if ($_REQUEST['config_permbrowse']) $_REQUEST['config_permbrowse'] = 1; else $_REQUEST['config_permbrowse'] = 0;
    if ($_REQUEST['config_permupload']) $_REQUEST['config_permupload'] = 1; else $_REQUEST['config_permupload'] = 0;
    if ($_REQUEST['config_permcreate']) $_REQUEST['config_permcreate'] = 1; else $_REQUEST['config_permcreate'] = 0;
    if ($_REQUEST['config_permpass']) $_REQUEST['config_permpass'] = 1; else $_REQUEST['config_permpass'] = 0;
    if ($_REQUEST['config_permdelete']) $_REQUEST['config_permdelete'] = 1; else $_REQUEST['config_permdelete'] = 0;
    if ($_REQUEST['config_permmove']) $_REQUEST['config_permmove'] = 1; else $_REQUEST['config_permmove'] = 0;
    if ($_REQUEST['config_permedit']) $_REQUEST['config_permedit'] = 1; else $_REQUEST['config_permedit'] = 0;
    if ($_REQUEST['config_permrename']) $_REQUEST['config_permrename'] = 1; else $_REQUEST['config_permrename'] = 0;
    if ($_REQUEST['config_permget']) $_REQUEST['config_permget'] = 1; else $_REQUEST['config_permget'] = 0;
    if ($_REQUEST['config_permchmod']) $_REQUEST['config_permchmod'] = 1; else $_REQUEST['config_permchmod'] = 0;
    if ($_REQUEST['config_permsub']) $_REQUEST['config_permsub'] = 1; else $_REQUEST['config_permsub'] = 0;
    if ($_REQUEST['config_permuser']) $_REQUEST['config_permuser'] = 1; else $_REQUEST['config_permuser'] = 0;
    if ($_REQUEST['config_permadmin']) $_REQUEST['config_permadmin'] = 1; else $_REQUEST['config_permadmin'] = 0;
    if ($_REQUEST['config_permdeleteuser']) $_REQUEST['config_permdeleteuser'] = 1; else $_REQUEST['config_permdeleteuser'] = 0;
    if ($_REQUEST['config_permedituser']) $_REQUEST['config_permedituser'] = 1; else $_REQUEST['config_permedituser'] = 0;
    if ($_REQUEST['config_permmakeuser']) $_REQUEST['config_permmakeuser'] = 1; else $_REQUEST['config_permmakeuser'] = 0;
    if ($_REQUEST['config_permuser']) $_REQUEST['config_permuser'] = 1; else $_REQUEST['config_permuser'] = 0;
    if ($_REQUEST['config_permrecycle']) $_REQUEST['config_permrecycle'] = 1; else $_REQUEST['config_permrecycle'] = 0;
    if ($_REQUEST['config_permprefs']) $_REQUEST['config_permprefs'] = 1; else $_REQUEST['config_permprefs'] = 0;

    page_header("New user");
    opentable("100%");

    $query = '
      INSERT INTO '.$GLOBALS['config']['db']['pref'].'users 
      (user, pass, email, name, folder, http, spacelimit, theme, language, permbrowse, permupload, permcreate, permuser, permadmin, permdelete, permmove, permchmod, permget, permdeleteuser, permedituser, permmakeuser, permpass, permedit, permrename, permsub, permrecycle, permprefs, formatperm, status, recycle)
      VALUES(
        "'.$_REQUEST['config_user'].'", 
        "'.md5($_REQUEST['config_pass']).'",
        "'.$_REQUEST['config_email'].'",
        "'.$_REQUEST['config_name'].'",
        "'.$_REQUEST['config_folder'].'",
        "'.$_REQUEST['config_http'].'",
        "'.$_REQUEST['config_limit'].'",
        "'.$_REQUEST['config_theme'].'",
        "'.$_REQUEST['config_language'].'",
        "'.$_REQUEST['config_permbrowse'].'",
        "'.$_REQUEST['config_permupload'].'",
        "'.$_REQUEST['config_permcreate'].'",
        "'.$_REQUEST['config_permuser'].'",
        "'.$_REQUEST['config_permadmin'].'",
        "'.$_REQUEST['config_permdelete'].'",
        "'.$_REQUEST['config_permmove'].'",
        "'.$_REQUEST['config_permchmod'].'",
        "'.$_REQUEST['config_permget'].'",
        "'.$_REQUEST['config_permdeleteuser'].'",
        "'.$_REQUEST['config_permedituser'].'",
        "'.$_REQUEST['config_permmakeuser'].'",
        "'.$_REQUEST['config_permmakeuser'].'",
        "'.$_REQUEST['config_permedit'].'",
        "'.$_REQUEST['config_permrename'].'",
        "'.$_REQUEST['config_permsub'].'",
        "'.$_REQUEST['config_permrecycle'].'",
        "'.$_REQUEST['config_permprefs'].'",
        "'.$_REQUEST['config_formatperms'].'",
        "'.$_REQUEST['config_status'].'",
        "0"
      );';
    $msql = mysql_query($query) or die(mysql_error());
    echo "New user succesfully created<br>\n";
    closetable();
    page_footer();
  }

  function user() {
    page_header("Control Pannel");
    opentable("100%");
    echo "<a href=\"?p=pass\">變更密碼</a><br>\n"
        ."<a href=\"?p=prefs\">變更偏好 </a><br>\n";
    closetable();
    page_footer();
  }

  function pass() {
    global $content, $extraheaders;
    $extraheaders = "<script language=javascript>\n"
                   ."  <!--\n"
                     .md5return()
                   ."  function encpass() {\n"
                   ."    if (md5_vm_test() && valid_js()) { \n"
                   ."      var hasho;\n"
                   ."      var hash;\n"
                   ."      var hashb;\n"
                   ."      var passizeo;\n"
                   ."      var passize;\n"
                   ."      var passizeb;\n"
                   ."      var showpasso = \"\";\n"
                   ."      var showpass = \"\";\n"
                   ."      var showpassb = \"\";\n"
                   ."      hasho = hex_md5(document.prefmod.passwordo.value);\n"
                   ."      hash = hex_md5(document.prefmod.password.value);\n"
                   ."      hashb = hex_md5(document.prefmod.password2.value);\n"
                   ."      passizeo = document.prefmod.passwordo.value.length;\n"
                   ."      passize = document.prefmod.password.value.length;\n"
                   ."      passizeb = document.prefmod.password2.value.length;\n"
                   ."      for (x=0; x<passizeo; x++ ) {\n"
                   ."        showpasso = showpasso + \"x\";\n"
                   ."      }\n"
                   ."      for (x=0; x<passize; x++ ) {\n"
                   ."        showpass = showpass + \"x\";\n"
                   ."      }\n"
                   ."      for (x=0; x<passizeb; x++ ) {\n"
                   ."        showpassb = showpassb + \"x\";\n"
                   ."      }\n"
                   ."      document.prefmod.passwordo.value = showpasso;\n"
                   ."      document.prefmod.password.value = showpass;\n"
                   ."      document.prefmod.password2.value = showpassb;\n"
                   ."      document.prefmod.encpaso.value = hasho;\n"
                   ."      document.prefmod.encpas1.value = hash;\n"
                   ."      document.prefmod.encpas2.value = hashb;\n"
                   ."      return true;\n"
                   ."    } else {\n"
                   ."      form.onsubmit=null;\n"
                   ."      return false;\n"
                   ."    }\n"
                   ."  } \n"
                   ."  -->\n"
                   ."</script>\n";
    echo $extraheaders;
    page_header("Change Password");
    opentable("100%");
    echo "<font class=error>**WARNING: Do not lose your password. It will not be replaced.**</font><br><br>\n"
        ."<form action=\"?p=password\" method=\"post\" name=prefmod onsubmit=\"return encpass(1);\">\n"
        ."<table>\n"
        ."<tr><td>舊密碼: <td><input type=password name=passwordo value=\"\">\n"
        ."<tr><td>新密碼: <td><input type=password name=password value=\"\">\n"
        ."<tr><td>確認新密碼: <td><input type=password name=password2 value=\"\">\n"
        ."<input type=hidden name=\"encpaso\">\n"
        ."<input type=hidden name=\"encpas1\">\n"
        ."<input type=hidden name=\"encpas2\">\n"
        ."<tr><td colspan=\"2\"><br><input type=\"submit\" name=\"secure\" value=\"變更\" border=\"0\" class=\"button\">\n"
        ."</table>\n"
        ."</form>\n";
    closetable();
    page_footer();
  }

  function password($oldpass, $newpass, $cnewpass) {

    global $uid, $user, $content, $sqlpref;
    $flag=0; $content="";
    $mresult = mysql_query("SELECT pass FROM ".$GLOBALS['config']['db']['pref']."users WHERE user='$user'");
    list ($password) = mysql_fetch_row($mresult);
    if ($oldpass != $password) $error .= "<font class=error>Incorrect Old Password.</font><br>\n";
    if ($newpass != $cnewpass) $error .= "<font class=error>Passwords do not match.</font><br>\n";
    if ($newpass == "d41d8cd98f00b204e9800998ecf8427e") $error .= "<font class=error>You must enter a password.</font><br>\n";
    if (!$error) {
      $msql = mysql_query("UPDATE ".$GLOBALS['config']['db']['pref']."users SET pass='$newpass' WHERE user='$user'") or die (mysql_error());
    setcookie('pass',md5($newpass.$_COOKIE['sess']),time()+60*60*24*1);
      page_header("Change Password");
      opentable("100%");
      echo "<font class=ok>Your password has been succesfully changed.</font><br>\n";
    } else {
      page_header("Change Password");
      opentable("100%");
      echo $error;
    }
    closetable();
    page_footer();
  }

  function prefs() {
    global $user, $d, $error, $sqlpref, $extraheaders, $config_email, $config_name, $config_theme, $config_language, $config_recycle, $config_formatperm;
    $result = mysql_query("SELECT id, user, email, name, theme, language, recycle, formatperm, permrecycle FROM ".$GLOBALS['config']['db']['pref']."users WHERE user='".$user."'");
    list($uid, $uname, $email, $name, $theme, $language, $recycle, $formatperm, $permrecycle) = mysql_fetch_row($result);
    if ($error){
      $email = $config_email;
      $name = $config_name;
      $theme = $config_theme;
      $language = $config_language;
      $recycle = $config_recycle;
      $formatperm = $config_formatperm;
    }
    page_header("User Preferences");
    opentable("100%");
    echo "<table>\n"
        ."<form name=\"prefs\" action=\"?p=saveprefs\" method=\"post\">\n";
    if ($formatperm == '0') $perm1 = " checked";
    elseif ($formatperm == '1') $perm2 = " checked"; 
    if ($recycle == 0) $rec1 = " checked";
    elseif ($recycle == 1) $rec2 = " checked";
    echo "<tr><td>帳號: <td><input disabled type=\"text\" name=\"config_user\" size=\"40\" border=\"0\" class=\"txtinput\" value=\"$uname\">\n";

    if($error && !$config_name) echo "<tr><td valign=bottom>Name: <td><font class=error>Invalid Name</font><br><table cellpadding=0 cellspacing=0 class=errorbox><tr><td><table cellpadding=1 cellspacing=1><tr><td><input type=\"text\" name=\"config_name\" size=\"40\" border=\"0\" class=\"txtinput\" value=\"$name\"></table></table>\n";
    else echo "<tr><td>名稱: <td><input type=\"text\" name=\"config_name\" size=\"40\" border=\"0\" class=\"txtinput\" value=\"$name\">\n";
    if($error && !$config_email|!ismail($config_email)) echo"<tr><td valign=bottom>Email: <td><font class=error>Invalid Email</font><br><table cellpadding=0 cellspacing=0 class=errorbox><tr><td><table cellpadding=1 cellspacing=1><tr><td><input type=\"text\" name=\"config_email\" size=\"40\" border=\"0\" class=\"txtinput\" value=\"$email\"></table></table>\n";
    else echo"<tr><td>信箱: <td><input type=\"text\" name=\"config_email\" size=\"40\" border=\"0\" class=\"txtinput\" value=\"$email\">\n";

    echo "<tr><td>語言: <td><select name=\"config_language\">\n";
    $handle = opendir("./language");
    while ($file = readdir($handle)) $filelist[] = $file;
    natcasesort($filelist);
    foreach ($filelist as $file) {
      if ($file != "." && $file != ".." && is_dir("./language/".$file)) {
        @include("./language/".$file."/lng.def.php");
        if ($language == $file) $isel = " selected"; else $isel = "";
        echo "<option value=\"$file\"$isel>$LNG_NAME</option>\n";
      }
    }
    closedir("./language");
    echo "</select><tr><td>主題: <td><select name=\"config_theme\">\n";
    $handle = opendir("./themes");
    while ($file = readdir($handle)) $filelist[] = $file;
    natcasesort($filelist);
    foreach ($filelist as $file) {
      if ($file != "." && $file != ".." && is_dir("./themes/".$file)) {
        @include("./themes/".$file."/theme.def.php");
        if ($theme == $file) $isel = " selected"; else $isel = "";
        echo "<option value=\"$file\"$isel>$THEME_NAME</option>\n";
      }
    }
    closedir("./themes");
    echo "</select>\n";
    if ($permrecycle == 1 && $GLOBALS['config']['enable_trash']) {
      echo "<tr><td>Trash Bin: <td>\n"
          ."<input type=radio name=\"config_recycle\" id=\"rec1\"$rec2><label for=\"rec1\"> On</label>&nbsp;&nbsp;&nbsp;\n"
          ."<input type=radio name=\"config_recycle\" id=\"rec2\"$rec1><label for=\"rec2\"> Off</label>\n";
    }
    echo "<tr><td>權限表示法: <td>\n"
        ."<input type=\"radio\" name=\"config_formatperm\" id=\"perm1\" value=\"0\" $perm1><label for=\"perm1\"> UNIX (0644)</label>&nbsp;&nbsp;&nbsp;\n"
        ."<input type=\"radio\" name=\"config_formatperm\" id=\"perm2\" value=\"1\" checked><label for=\"perm2\"> Symbolic (-rw-r--r--)</label>\n"
        ."<input type=hidden name=d value=\"$d\">\n"
        ."<tr><td colspan=\"2\"><br><input type=\"submit\" name=\"submitButtonName\" value=\"存檔\" border=\"0\" class=\"button\">\n"
        ."</td></tr></form></table>\n";
    closetable();
    page_footer();
  }

  function preferences($config_email, $config_name, $config_theme, $config_language, $config_recycle, $config_formatperm) {
    global $d, $userid, $error;
    if (!$config_email || !ismail($config_email)) $error = TRUE;
    if (!$config_name) $error = TRUE;
    if ($error) prefs();
    else {
      page_header("User Preferences");
      opentable("100%");
      $msql = mysql_query("UPDATE ".$GLOBALS['config']['db']['pref']."users SET email='$config_email', name='$config_name', theme='$config_theme', language='$config_language', recycle='$config_recycle', formatperm='$config_formatperm' WHERE id='$userid'") or die(mysql_error());
      echo "<font class=ok>您的偏好設已存檔.</font><br><br><a href=\"?d=$d\">返回</a>\n";
      closetable();
      page_footer();
    }
  }

  /****************************************************************************/
  /*                                                                          */
  /* A JavaScript implementation of the RSA Data Security, Inc. MD5 Message   */
  /* Digest Algorithm, as defined in RFC 1321.                                */
  /* Version 2.1 Copyright (C) Paul Johnston 1999 - 2002.                     */
  /* Other contributors: Greg Holt, Andrew Kepert, Ydnar, Lostinet            */
  /* Distributed under the BSD License                                        */
  /* See http://pajhome.org.uk/crypt/md5 for more info.                       */
  /*                                                                          */
  /****************************************************************************/

  function md5return() {
    return "
      var hexcase = 0;  /* hex output format. 0 - lowercase; 1 - uppercase        */
      var chrsz   = 8;  /* bits per input character. 8 - ASCII; 16 - Unicode      */

      function hex_md5(s){ return binl2hex(core_md5(str2binl(s), s.length * chrsz));}

      function md5_vm_test() {
        return hex_md5(\"abc\") == \"900150983cd24fb0d6963f7d28e17f72\";
      }


      function core_md5(x, len) {
        x[len >> 5] |= 0x80 << ((len) % 32);
        x[(((len + 64) >>> 9) << 4) + 14] = len;

        var a =  1732584193;
        var b = -271733879;
        var c = -1732584194;
        var d =  271733878;

        for(var i = 0; i < x.length; i += 16) {
          var olda = a;
          var oldb = b;
          var oldc = c;
          var oldd = d;

          a = md5_ff(a, b, c, d, x[i+ 0], 7 , -680876936);
          d = md5_ff(d, a, b, c, x[i+ 1], 12, -389564586);
          c = md5_ff(c, d, a, b, x[i+ 2], 17,  606105819);
          b = md5_ff(b, c, d, a, x[i+ 3], 22, -1044525330);
          a = md5_ff(a, b, c, d, x[i+ 4], 7 , -176418897);
          d = md5_ff(d, a, b, c, x[i+ 5], 12,  1200080426);
          c = md5_ff(c, d, a, b, x[i+ 6], 17, -1473231341);
          b = md5_ff(b, c, d, a, x[i+ 7], 22, -45705983);
          a = md5_ff(a, b, c, d, x[i+ 8], 7 ,  1770035416);
          d = md5_ff(d, a, b, c, x[i+ 9], 12, -1958414417);
          c = md5_ff(c, d, a, b, x[i+10], 17, -42063);
          b = md5_ff(b, c, d, a, x[i+11], 22, -1990404162);
          a = md5_ff(a, b, c, d, x[i+12], 7 ,  1804603682);
          d = md5_ff(d, a, b, c, x[i+13], 12, -40341101);
          c = md5_ff(c, d, a, b, x[i+14], 17, -1502002290);
          b = md5_ff(b, c, d, a, x[i+15], 22,  1236535329);

          a = md5_gg(a, b, c, d, x[i+ 1], 5 , -165796510);
          d = md5_gg(d, a, b, c, x[i+ 6], 9 , -1069501632);
          c = md5_gg(c, d, a, b, x[i+11], 14,  643717713);
          b = md5_gg(b, c, d, a, x[i+ 0], 20, -373897302);
          a = md5_gg(a, b, c, d, x[i+ 5], 5 , -701558691);
          d = md5_gg(d, a, b, c, x[i+10], 9 ,  38016083);
          c = md5_gg(c, d, a, b, x[i+15], 14, -660478335);
          b = md5_gg(b, c, d, a, x[i+ 4], 20, -405537848);
          a = md5_gg(a, b, c, d, x[i+ 9], 5 ,  568446438);
          d = md5_gg(d, a, b, c, x[i+14], 9 , -1019803690);
          c = md5_gg(c, d, a, b, x[i+ 3], 14, -187363961);
          b = md5_gg(b, c, d, a, x[i+ 8], 20,  1163531501);
          a = md5_gg(a, b, c, d, x[i+13], 5 , -1444681467);
          d = md5_gg(d, a, b, c, x[i+ 2], 9 , -51403784);
          c = md5_gg(c, d, a, b, x[i+ 7], 14,  1735328473);
          b = md5_gg(b, c, d, a, x[i+12], 20, -1926607734);

          a = md5_hh(a, b, c, d, x[i+ 5], 4 , -378558);
          d = md5_hh(d, a, b, c, x[i+ 8], 11, -2022574463);
          c = md5_hh(c, d, a, b, x[i+11], 16,  1839030562);
          b = md5_hh(b, c, d, a, x[i+14], 23, -35309556);
          a = md5_hh(a, b, c, d, x[i+ 1], 4 , -1530992060);
          d = md5_hh(d, a, b, c, x[i+ 4], 11,  1272893353);
          c = md5_hh(c, d, a, b, x[i+ 7], 16, -155497632);
          b = md5_hh(b, c, d, a, x[i+10], 23, -1094730640);
          a = md5_hh(a, b, c, d, x[i+13], 4 ,  681279174);
          d = md5_hh(d, a, b, c, x[i+ 0], 11, -358537222);
          c = md5_hh(c, d, a, b, x[i+ 3], 16, -722521979);
          b = md5_hh(b, c, d, a, x[i+ 6], 23,  76029189);
          a = md5_hh(a, b, c, d, x[i+ 9], 4 , -640364487);
          d = md5_hh(d, a, b, c, x[i+12], 11, -421815835);
          c = md5_hh(c, d, a, b, x[i+15], 16,  530742520);
          b = md5_hh(b, c, d, a, x[i+ 2], 23, -995338651);

          a = md5_ii(a, b, c, d, x[i+ 0], 6 , -198630844);
          d = md5_ii(d, a, b, c, x[i+ 7], 10,  1126891415);
          c = md5_ii(c, d, a, b, x[i+14], 15, -1416354905);
          b = md5_ii(b, c, d, a, x[i+ 5], 21, -57434055);
          a = md5_ii(a, b, c, d, x[i+12], 6 ,  1700485571);
          d = md5_ii(d, a, b, c, x[i+ 3], 10, -1894986606);
          c = md5_ii(c, d, a, b, x[i+10], 15, -1051523);
          b = md5_ii(b, c, d, a, x[i+ 1], 21, -2054922799);
          a = md5_ii(a, b, c, d, x[i+ 8], 6 ,  1873313359);
          d = md5_ii(d, a, b, c, x[i+15], 10, -30611744);
          c = md5_ii(c, d, a, b, x[i+ 6], 15, -1560198380);
          b = md5_ii(b, c, d, a, x[i+13], 21,  1309151649);
          a = md5_ii(a, b, c, d, x[i+ 4], 6 , -145523070);
          d = md5_ii(d, a, b, c, x[i+11], 10, -1120210379);
          c = md5_ii(c, d, a, b, x[i+ 2], 15,  718787259);
          b = md5_ii(b, c, d, a, x[i+ 9], 21, -343485551);

          a = safe_add(a, olda);
          b = safe_add(b, oldb);
          c = safe_add(c, oldc);
          d = safe_add(d, oldd);
        }
        return Array(a, b, c, d);

      }

      function md5_cmn(q, a, b, x, s, t) {
        return safe_add(bit_rol(safe_add(safe_add(a, q), safe_add(x, t)), s),b);
      }
      function md5_ff(a, b, c, d, x, s, t) {
        return md5_cmn((b & c) | ((~b) & d), a, b, x, s, t);
      }
      function md5_gg(a, b, c, d, x, s, t) {
        return md5_cmn((b & d) | (c & (~d)), a, b, x, s, t);
      }
      function md5_hh(a, b, c, d, x, s, t) {
        return md5_cmn(b ^ c ^ d, a, b, x, s, t);
      }
      function md5_ii(a, b, c, d, x, s, t) {
        return md5_cmn(c ^ (b | (~d)), a, b, x, s, t);
      }

      function safe_add(x, y) {
        var lsw = (x & 0xFFFF) + (y & 0xFFFF);
        var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
        return (msw << 16) | (lsw & 0xFFFF);
      }

      function bit_rol(num, cnt) {
        return (num << cnt) | (num >>> (32 - cnt));
      }

      function str2binl(str) {
        var bin = Array();
        var mask = (1 << chrsz) - 1;
        for(var i = 0; i < str.length * chrsz; i += chrsz)
          bin[i>>5] |= (str.charCodeAt(i / chrsz) & mask) << (i%32);
        return bin;
      }

      function binl2hex(binarray) {
        var hex_tab = hexcase ? \"0123456789ABCDEF\" : \"0123456789abcdef\";
        var str = \"\";
        for(var i = 0; i < binarray.length * 4; i++) {
          str += hex_tab.charAt((binarray[i>>2] >> ((i%4)*8+4)) & 0xF) +
                 hex_tab.charAt((binarray[i>>2] >> ((i%4)*8  )) & 0xF);
        }
        return str;
      }

      function valid_js() {
        if (navigator.userAgent.indexOf(\"Mozilla/\") == 0) {
          return (parseInt(navigator.appVersion) >= 4);
        }
        return false;
      }
    ";
  }

  /* User defined functions */

  function home($userdir, $d, $bgcolor3, $formatperm, $totalsize, $permadmin, $permrename, $permmove, $permdelete, $permchmod, $permsub, $permget, $tcoloring, $tbcolor1, $tbcolor2, $tbcolor3, $tbcolor4, $adminfile, $p, $pdir, $IMG_CHECK, $IMG_RENAME, $IMG_GET, $IMG_EDIT, $IMG_OPEN, $IMG_RENAME_NULL, $IMG_EDIT_NULL, $IMG_OPEN_NULL, $IMG_GET_NULL, $IMG_MIME_FOLDER, $IMG_MIME_BINARY, $IMG_MIME_AUDIO, $IMG_MIME_VIDEO, $IMG_MIME_IMAGE, $IMG_MIME_TEXT, $IMG_MIME_UNKNOWN, $year) {
    global $userdir;
    $actualdir = getActualDir($userdir);

    if($year == 0) {
        $result = mysql_query("SELECT year from year ORDER BY year DESC limit 0,1") or die(mysql_error());
        $year = mysql_fetch_row($result)[0];
        echo "<script>window.onload = function(){ window.location.href = '?year={$year}'; };</script>";
    }

    echo <<<END
    <div class="dropdown" style="float: right;">
        <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown"><font color="white">目前年度：{$year}</font>
        <span class="caret"></span></button>
        <ul class="dropdown-menu">
END;
    $result = mysql_query("SELECT year from year ORDER BY year DESC") or die(mysql_error());
    while($row = mysql_fetch_row($result)) {
        echo "<li><a href='?year={$row[0]}'>".$row[0]."</a></li>";
    }
    echo <<<END
        </ul>
    </div>
END;

    $ud = "/".$d;
    echo "正在瀏覽: $ud";
    echo "<form name=bulk_submit action=\"?p=bulk_submit\" method=post id=\"bulk_submit\">\n";
    page_header("Browse");
    echo "<tr><td bgcolor=$bgcolor3><table border=\"0\" cellpadding=\"0\" cellspacing=\"1\" width=100%>\n"
            ."<input type=hidden name=d value=\"$d\">\n"
            ."<input type=hidden name=year value=\"$year\">\n"
            ."<div class=\"pull-right\">\n"
            ."<div class=\"btn-group\">\n";

    $count = "0";
    $a=0; $b=0; $content1 = ""; $content2 = "";
    $handle=opendir(mb_convert_encoding($actualdir, 'big5', 'UTF-8').mb_convert_encoding($d, 'big5', 'UTF-8'));
    while ($fileinfo = readdir($handle)) $filelist[] = $fileinfo;
    natcasesort($filelist); 
    while (list ($key, $fileinfo) = each ($filelist)) {
        if (strlen($fileinfo)>40) {
            $fileinfoa = substr($fileinfo,0,40)."...";
        }
        else $fileinfoa = $fileinfo;
        if ($fileinfo[0] != "." && $fileinfo[0] != ".." ) {
            if (is_dir(mb_convert_encoding($actualdir.$d, 'big5', 'UTF-8').$fileinfo) && is_readable(mb_convert_encoding($actualdir.$d, 'big5', 'UTF-8').$fileinfo)) { 
                if($fileinfo == $year || (($permadmin == 1) && (substr_count($d, '/') != 1)) || (($permadmin == 0) && !empty($d))) {
                    if ($formatperm == 1) $perms = formatperms(@fileperms($actualdir.$d.$fileinfo));
                    else $perms = substr(sprintf('%o', @fileperms($actualdir.$d.$fileinfo)), -4);
                    if ($permrename == 1) $lnk_rename = "<a href=\"".$adminfile."?p=ren&file=".mb_convert_encoding($fileinfo, 'UTF-8', 'big5')."&d=$d&year={$year}\"><img src=\"$IMG_RENAME\" border=0 onclick=\"itemsel(this,1,'foldersel_$a',3,'#CCCCFF','$tcoloring','#FFCC99');\"></a>\n";
                    else $lnk_rename = "<img src=\"$IMG_RENAME_NULL\" border=0>\n";
                    if ($permsub == 1) $lnk_open = "<a href=\"".$adminfile."?d=".$d.mb_convert_encoding($fileinfo, 'UTF-8', 'big5')."/&year={$year}\">".mb_convert_encoding($fileinfo, 'UTF-8', 'big5')."</a>\n";
                    else $lnk_open = $fileinfoa;

                    $content1[$a] ="<td><input type=checkbox name=\"foldersel[$a]\" id=\"foldersel_$a\" value=\"".mb_convert_encoding($fileinfo, 'UTF-8', 'big5')."\" onclick=\"itemsel(this,1,'foldersel_$a',3,'#CCCCFF','$tcoloring','#FFCC99');\"> \n"
                          ."<td valign=bottom><img src=\"$IMG_MIME_FOLDER\"> ".$lnk_open."</td>\n"
                          ."<td align=\"center\">\n"
                          ."<td align=\"center\">".$lnk_rename."\n"
                          ."<td> <td align=\"center\"> <td align=\"center\">$perms\n";
                    $a++;
                }
            } elseif (!is_dir(mb_convert_encoding($actualdir.$d, 'big5', 'UTF-8').$fileinfo) && is_readable(mb_convert_encoding($actualdir.$d, 'big5', 'UTF-8').$fileinfo)) { 
                if($permadmin == 1) {
                    if ($formatperm == 1) $perms = formatperms(@fileperms(mb_convert_encoding($actualdir.$d, 'big5', 'UTF-8').$fileinfo));
                    else $perms = substr(sprintf('%o', @fileperms($actualdir.$d.$fileinfo)), -4);
                    $size = filesize(mb_convert_encoding($actualdir.$d, 'big5', 'UTF-8').$fileinfo);
                    $totalsize = $totalsize + $size;
                    $type = mime_content_type(mb_convert_encoding($actualdir.$d, 'big5', 'UTF-8').$fileinfo);
                    if (substr($type,0,4) == "text") $mimeimage = "<img src=\"$IMG_MIME_TEXT\">";
                    elseif (substr($type,0,5) == "image") $mimeimage = "<img src=\"$IMG_MIME_IMAGE\">";
                    elseif (substr($type,0,11) == "application") $mimeimage = "<img src=\"$IMG_MIME_BINARY\">";
                    elseif (substr($type,0,5) == "audio") $mimeimage = "<img src=\"$IMG_MIME_AUDIO\">";
                    elseif (substr($type,0,5) == "video") $mimeimage = "<img src=\"$IMG_MIME_VIDEO\">";
                    elseif (substr($type,0,5) == "model") $mimeimage = "<img src=\"$IMG_MIME_IMAGE\">";
                    elseif (substr($type,0,7) == "message") $mimeimage = "<img src=\"$IMG_MIME_TEXT\">";
                    elseif (substr($type,0,9) == "multipart") $mimeimage = "<img src=\"$IMG_MIME_TEXT\">";
                    else $mimeimage = "<img src=\"$IMG_MIME_UNKNOWN\">";
                    if ((substr($type,0,4) == "text" || $size == 0) && $permedit == 1) $edit = "<a href=\"".$adminfile."?p=edit&fename=".mb_convert_encoding($fileinfo, 'UTF-8', 'big5')."&d=$d\"><img src=\"$IMG_EDIT\" border=0 onclick=\"itemsel(this,1,'filesel_$b',3,'#CCCCFF','$tcoloring','#FFCC99');\"></a>\n";
                    elseif (substr($type,0,4) == "text" || $size == 0) $edit = "<a href=\"".$adminfile."?p=edit&fename=".$fileinfo."&d=$d\"><img src=\"$IMG_EDIT_NULL\" border=0>\n"; 
                    else $edit = "";
                    if ($permrename == 1) $rename = "<a href=\"".$adminfile."?p=ren&file=".mb_convert_encoding($fileinfo, 'UTF-8', 'big5')."&d=".str_replace($year.'/', '', $d)."&year={$year}\"><img src=\"$IMG_RENAME\" border=0 onclick=\"itemsel(this,1,'filesel_$b',3,'#CCCCFF','$tcoloring','#FFCC99');\"></a>\n";
                    else $rename = "<img src=\"$IMG_RENAME_NULL\" border=0>\n";
                    if ($permget == 1) $get = "<a href=\"".$adminfile."?p=view&file=".mb_convert_encoding($fileinfo, 'UTF-8', 'big5')."&d=$d\"><img src=\"$IMG_GET\" border=0 onclick=\"itemsel(this,1,'filesel_$b',3,'#CCCCFF','$tcoloring','#FFCC99');\"></a>\n";
                    else $get = "<img src=\"$IMG_GET_NULL\" border=0>\n";
                    if ($permget == 1) $filefile = "<a href=\"{$userdir}".$http.$d.mb_convert_encoding($fileinfo, 'UTF-8', 'big5')."\" onclick=\"itemsel(this,1,'filesel_$b',3,'#CCCCFF','$tcoloring','#FFCC99');\">".mb_convert_encoding($fileinfoa, 'UTF-8', 'big5')."</a>\n";
                    else $filefile = "$fileinfoa\n";

                    $content2[$b] ="<td><input type=checkbox name=\"filesel[$b]\" id=\"filesel_$b\" value=\"".mb_convert_encoding($fileinfo, 'UTF-8', 'big5')."\" onclick=\"itemsel(this,1,'filesel_$b',3,'#CCCCFF','$tcoloring','#FFCC99');\">\n"
                                                             ."<td>$mimeimage $filefile</td>\n"
                                                             ."<td align=\"center\" width=20> $edit\n"
                          ."<td align=\"center\" width=20> $rename\n"
                          ."<td align=\"center\" width=20> $get\n"
                          ."<td align=\"left\" nowrap>".getfilesize($size)."\n"
                          ."<td align=\"center\">$perms\n";
                    $b++;
                } else {
                    if ($formatperm == 1) $perms = formatperms(@fileperms(mb_convert_encoding($actualdir.$d, 'big5', 'UTF-8').$fileinfo));
                    else $perms = substr(sprintf('%o', @fileperms($actualdir.$d.$fileinfo)), -4);
                    $size = filesize(mb_convert_encoding($actualdir.$d, 'big5', 'UTF-8').$fileinfo);
                    $totalsize = $totalsize + $size;
                    $type = mime_content_type(mb_convert_encoding($actualdir.$d, 'big5', 'UTF-8').$fileinfo);
                    if (substr($type,0,4) == "text") $mimeimage = "<img src=\"$IMG_MIME_TEXT\">";
                    elseif (substr($type,0,5) == "image") $mimeimage = "<img src=\"$IMG_MIME_IMAGE\">";
                    elseif (substr($type,0,11) == "application") $mimeimage = "<img src=\"$IMG_MIME_BINARY\">";
                    elseif (substr($type,0,5) == "audio") $mimeimage = "<img src=\"$IMG_MIME_AUDIO\">";
                    elseif (substr($type,0,5) == "video") $mimeimage = "<img src=\"$IMG_MIME_VIDEO\">";
                    elseif (substr($type,0,5) == "model") $mimeimage = "<img src=\"$IMG_MIME_IMAGE\">";
                    elseif (substr($type,0,7) == "message") $mimeimage = "<img src=\"$IMG_MIME_TEXT\">";
                    elseif (substr($type,0,9) == "multipart") $mimeimage = "<img src=\"$IMG_MIME_TEXT\">";
                    else $mimeimage = "<img src=\"$IMG_MIME_UNKNOWN\">";
                    if ((substr($type,0,4) == "text" || $size == 0) && $permedit == 1) $edit = "<a href=\"".$adminfile."?p=edit&fename=".mb_convert_encoding($fileinfo, 'UTF-8', 'big5')."&d=$d\"><img src=\"$IMG_EDIT\" border=0 onclick=\"itemsel(this,1,'filesel_$b',3,'#CCCCFF','$tcoloring','#FFCC99');\"></a>\n";
                    elseif (substr($type,0,4) == "text" || $size == 0) $edit = "<a href=\"".$adminfile."?p=edit&fename=".$fileinfo."&d=$d\"><img src=\"$IMG_EDIT_NULL\" border=0>\n"; 
                    else $edit = "";
                    if ($permrename == 1) $rename = "<a href=\"".$adminfile."?p=ren&file=".mb_convert_encoding($fileinfo, 'UTF-8', 'big5')."&d=".substr(strstr($d, '/'), 1)."&year={$year}\"><img src=\"$IMG_RENAME\" border=0 onclick=\"itemsel(this,1,'filesel_$b',3,'#CCCCFF','$tcoloring','#FFCC99');\"></a>\n";
                    else $rename = "<img src=\"$IMG_RENAME_NULL\" border=0>\n";
                    if ($permget == 1) $get = "<a href=\"".$adminfile."?p=view&file=".mb_convert_encoding($fileinfo, 'UTF-8', 'big5')."&d=$d\"><img src=\"$IMG_GET\" border=0 onclick=\"itemsel(this,1,'filesel_$b',3,'#CCCCFF','$tcoloring','#FFCC99');\"></a>\n";
                    else $get = "<img src=\"$IMG_GET_NULL\" border=0>\n";
                    if ($permget == 1) $filefile = "<a href=\"{$userdir}".$http.$d.mb_convert_encoding($fileinfo, 'UTF-8', 'big5')."\" onclick=\"itemsel(this,1,'filesel_$b',3,'#CCCCFF','$tcoloring','#FFCC99');\">".mb_convert_encoding($fileinfoa, 'UTF-8', 'big5')."</a>\n";
                    else $filefile = "$fileinfoa\n";

                    $content2[$b] ="<td><input type=checkbox name=\"filesel[$b]\" id=\"filesel_$b\" value=\"".mb_convert_encoding($fileinfo, 'UTF-8', 'big5')."\" onclick=\"itemsel(this,1,'filesel_$b',3,'#CCCCFF','$tcoloring','#FFCC99');\">\n"
                                                             ."<td>$mimeimage $filefile</td>\n"
                                                             ."<td align=\"center\" width=20> $edit\n"
                          ."<td align=\"center\" width=20> $rename\n"
                          ."<td align=\"center\" width=20> $get\n"
                          ."<td align=\"left\" nowrap>".getfilesize($size)."\n"
                          ."<td align=\"center\">$perms\n";
                    $b++;
                }
            } else {
                echo "<font class=error>Directory '$fileinfo' is unreadable.</font><br>\n";
            }
        $count++;
        }
    }
    @closedir($actualdir.$d);
    $filetotal = $b;
    $foldertotal = $a;
    echo "<tr bgcolor=\"$tbcolor3\" width=20 class=titlebar1 height=25><td width=10 align=left valign=bottom><a href=\"javascript:selectall();\"><img src=\"$IMG_CHECK\" border=0></a> "
        ."<td class=theader width=420>檔名\n"
        ."<td align=\"center\" width=80 class=theader colspan=3>動作<font size=1>\n"
        ."<td width=70 class=theader align=left>大小<font size=1>\n"
        ."<td align=\"center\" width=60 class=theader>權限\n";
    if ($d) {
        $p=1;
        $tcoloring   = ($p % 2) ? $tbcolor1 : $tbcolor2;
        if (substr($d,0,strrpos(substr($d,0,-1),"/")) != "") $pdir = substr($d,0,strrpos(substr($d,0,-1),"/"))."/";
        echo "<tr height=20 id=p_sel bgcolor=".$tcoloring." width=100% height=22 onmouseover=\"itemsel(this,1,'p_sel',1,'$tbcolor4','$tcoloring','$tbcolor3');\" onmouseout=\"itemsel(this,1,'p_sel',2,'$tbcolor4','$tcoloring','$tbcolor3');\">\n"
            ."<td><td><img src=\"$IMG_MIME_FOLDER\"> <a href=\"".$adminfile."?d=".$pdir."\">../</a></td>\n"
            ."<input type=hidden name=\"p_sel\" value=\"$tcoloring\" id=\"p_sel\">\n"
            ."<td align=\"center\" width=20></a>\n"
            ."<td align=\"center\" width=20>\n"
            ."<td align=\"center\" width=20>\n"
            ."<td align=\"left\" nowrap>\n"
            ."<td align=\"center\">\n";
    }

    if ($content1) {
        for ($a=0; $a<count($content1);$a++) {
        $tcoloring = (($a+$p) % 2) ? $tbcolor1 : $tbcolor2;
        echo "<tr height=20 id=\"folderbg_$a\" bgcolor=".$tcoloring." width=100% height=22 onmouseover=\"itemsel(this,1,'foldersel_$a',1,'$tbcolor4','$tcoloring','$tbcolor3');\" onmouseout=\"itemsel(this,1,'foldersel_$a',2,'$tbcolor4','$tcoloring','$tbcolor3');\" onclick=\"itemsel(this,1,'foldersel_$a',3,'$tbcolor4','$tcoloring','$tbcolor3');\">"
            ."<input type=hidden name=\"foldercolor_$a\" value=\"$tcoloring\" id=\"foldercolor_$a\">\n"
            .$content1[$a]
            ."</td></tr>\n";
        }
    }
    if ($content2) {
        for ($b=0; $b<count($content2);$b++) {
        $tcoloring   = (($a++ + $p) % 2) ? $tbcolor1 : $tbcolor2;
        echo "<tr id=\"filebg_$b\" bgcolor=".$tcoloring." width=100% height=22 onmouseover=\"itemsel(this,1,'filesel_$b',1,'$tbcolor4','$tcoloring','$tbcolor3');\" onmouseout=\"itemsel(this,1,'filesel_$b',2,'$tbcolor4','$tcoloring','$tbcolor3');\" onclick=\"itemsel(this,1,'filesel_$b',3,'$tbcolor4','$tcoloring','$tbcolor3');\">"
            ."<input type=hidden name=\"filecolor_$b\" value=\"$tcoloring\" id=\"filecolor_$b\">\n"
            .$content2[$b]
            ."</td></tr>\n";
        }
    }
    if ($formatperm == 1) $perm = formatperms(@fileperms($actualdir.$d));
    else $perm = substr(sprintf('%o', @fileperms($actualdir.$d)), -4);
    echo "<tr bgcolor=\"$tbcolor3\" width=100% class=titlebar2 height=20><td class=titlebar2 colspan=5>\n"
        ."<img src=../images/pixel.gif width=25 height=1>檔案數: $count\n"
        ."<input type=hidden name=filetotal value=\"$filetotal\">\n"
        ."<input type=hidden name=foldertotal value=\"$foldertotal\">\n"
        ."<td align=\"left\" class=titlebar2 nowrap>".getfilesize($totalsize)."\n"
        ."<td align=\"center\" class=titlebar2>".$perm."\n";
    if ($permmove == 1 || $permdelete == 1 || $permchmod == 1) {
        echo "<tr class=titlebar2><td colspan=2 class=titlebar2><img src=../images/pixel.gif width=25 height=1>批次處理: <select name=bulk_action class=\"action\"\">\n"
            ."<option value=\"\" class=action>[ Select Action ]</option>\n";
        if ($permdelete) echo "<option value=delete class=\"actiondelete\">刪除</option>\n";
        if ($permmove) echo "<option value=move class=\"actionmove\">移動</option>\n";
        if ($permchmod) echo "<option value=chmod class=\"actionchmod\">變更權限</option>\n";
        $tmp = split('/', $d)[0];
        echo "</select>\n"
            ."<input type=hidden name=area value=\"{$tmp}\">\n"
            ."<input type=submit value=\"  Go  \" class=button data-delete=\"\">\n";
    }
    echo "</td><td colspan=5 align=center>".showdiskspace()."</td></tr></form></table>";
    $nobar = 1;
    page_footer();
  }

  function getCate($Cate) {
    $sql = "SELECT a.item, b.id FROM $Cate as a, 對應表 as b WHERE a.item = b.topic_name";
    $mysql = mysql_query($sql);
    if($mysql === FALSE) die(mysql_error());
    return $mysql;
  }

  function user_delete($d, $filesel, $year) {
    global $sqlpref, $tbcolor1, $contenta, $contentb, $userdir, $user, $permadmin;
    $actualdir = getActualDir($userdir);
    //set_error_handler ('error_handler');
    if (!$filesel) $error .= "Please select at least one file to perform an action on.<br>\n";
    if (@!$error) {
      if($permadmin == 1) {
        $append = strstr($d, "/", true);
        $d = substr(strstr($d, "/"), 1);
        if(@unlink(mb_convert_encoding($actualdir.$append.'/'.$year.'/'.$d.$filesel, "big5", "UTF-8"))) echo "<font class=ok>" . $filesel . " 已成功刪除.<br>\n";
        mysql_query("DELETE FROM 檔案總管 WHERE name = '{$year}/{$d}{$filesel}' AND owner = '{$append}'") or die(mysql_error());
      } else {
        if(@unlink(mb_convert_encoding($actualdir.$year.'/'.$d.$filesel, "big5", "UTF-8"))) echo "<font class=ok>" . $filesel . " 已成功刪除.<br>\n";
        mysql_query("DELETE FROM 檔案總管 WHERE name = '{$year}/{$d}{$filesel}' AND owner = '{$user}'") or die(mysql_error());
      }
    }
  }

  function complete($d, $year) {
    global $userdir;

    if($year == 0) {
        $result = mysql_query("SELECT year from year ORDER BY year DESC limit 0,1") or die(mysql_error());
        $year = mysql_fetch_row($result)[0];
        echo "<script>window.onload = function(){ window.location.href = '?p=complete&d={$d}&year={$year}'; };</script>";
    }

    echo "<div>";
    echo <<<END
    <div class="dropdown" style="float: right;">
        <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown"><font color="white">目前年度：{$year}</font>
        <span class="caret"></span></button>
        <ul class="dropdown-menu">
END;
    $result = mysql_query("SELECT year from year ORDER BY year DESC") or die(mysql_error());
    while($row = mysql_fetch_row($result)) {
        echo "<li><a href='?p=complete&d={$d}&year={$row[0]}'>".$row[0]."</a></li>";
    }
    echo <<<END
        </ul>
    </div>
END;
    echo "<input id=\"toggle-event\" type=\"checkbox\" data-toggle=\"toggle\">";
    echo "</div>";

    $d = substr($d, 0, -1);

    echo "<div class=\"panel-group\" id=\"accordion\">";
    $sets = mysql_query("SELECT name FROM zone");
    $subset = "(SELECT * FROM 檔案總管 where year = '{$year}')x";
    while($set = mysql_fetch_array($sets)) {
      echo "<div class=\"panel panel-default\">\n"
          ."<div class=\"panel-heading\">\n"
          ."<h4 class=\"panel-title\">\n"
          ."<a data-toggle=\"collapse\" href=\"#collapse{$set[0]}\">$set[0]</a>\n"
          ."</h4>\n"
          ."</div>\n"
          ."<div id=\"collapse{$set[0]}\" class=\"panel-collapse collapse\">\n"
          ."<div class=\"panel-body\">\n";

      echo <<<END
        <table class="table table-bordered">
          <thead>
            <tr style="background: #5bc0de;">
              <th class="col-lg-1 text-center valign-middle" style="width: 10%;">繳交</th>
              <th class="valign-middle">工作項目</th>
            </tr>
          </thead>
          <tbody>
END;

      echo "<tr style=\"background: #A48DDC;\"><td colspan=\"3\"><strong>{$d}</strong></td></tr>\n";
      $result = getCate("{$d}");
      while($row = mysql_fetch_array($result)) {
        $count = mysql_query("SELECT count(name) FROM {$subset} WHERE owner = '$set[0]' AND topic = (SELECT id FROM 對應表 WHERE topic_name = '$row[0]')") or die(mysql_error());
        $count_result = mysql_fetch_array($count);
        if($count_result[0] != 0) {
          echo "<tr style=\"background: #FFFFFF;\"><td class=\"text-center\"><button type=\"button\" class=\"btn btn-success btn-circle\"><i class=\"fa fa-check\"></i></button></td>\n";
        } else {
          echo "<tr style=\"background: #FFFFFF;\"><td class=\"text-center\"><button type=\"button\" class=\"btn btn-danger btn-circle\"><i class=\"fa fa-times\"></i></button></td>\n";
        }
        echo "<td><p class=\"text-bg no-padding no-margin\">$row[item]</p>";
        $links = mysql_query("SELECT name, owner FROM {$subset} WHERE owner = '$set[0]' AND topic = (SELECT id FROM 對應表 WHERE topic_name = '$row[0]')") or die(mysql_error());
        while($link = mysql_fetch_array($links)) {
          $filename = end(split('/', $link[0]));
          echo "<p><span><a href=\"{$userdir}{$link[1]}/{$link[0]}\">{$filename}</a>&nbsp;<a href=\"upload.php?p=user_delete&d={$link[1]}/{$d}/&filesel={$filename}&year={$year}\" class=\"btn btn-outline btn-xs btn-danger\" data-delete=\"\" data-title=\"即將刪除\" data-message=\"{$filename}\">刪除</a>&nbsp;<a href=\"upload.php?p=ren&d={$link[1]}/{$d}/&file={$filename}&year={$year}\" class=\"btn btn-outline btn-xs btn-info\">重命名</a></span></p>\n";
        }
        echo "</td>\n";
        echo "</tr>\n";
        $tid = $tid+1;
      }
      echo "</tbody></table>";

      echo "</div>\n"
          ."</div>\n"
          ."</div>\n";
    }
    echo "</div>";
    echo "<script src=\"../bower_components/sweetalert/dist/deleter.js\"></script>";
    echo "<script>deleter.init();</script>";
    echo <<<END
      <script>
        var active = true;
        $(function() {
          $('#toggle-event').change(function() {
            if ( $(this).prop('checked') ) {
              active = false;
              $('.panel-collapse').collapse('show');
            }
            else {
              active = true;
              $('.panel-collapse').collapse('hide');;
            }
          })
        })

        $('#accordion').on('show.bs.collapse', function () {
          if (active) $('#accordion .in').collapse('hide');
        });
      </script>
END;
  }

  function yearSetting() {
    $count = mysql_query("SELECT count(year) FROM year ORDER BY year DESC");
    $count_result = mysql_fetch_array($count);
    if($count_result[0] == 0) {
      echo '<b>您還尚未建立任何年度！</b>';
    } else {
      echo '<b>您已建立以下年度：</b>';
      echo <<<END
        <table class="table table-bordered">
          <thead>
            <tr style="background: #5bc0de;">
              <th class="col-lg-1 text-center valign-middle" style="width: 10%;">年度</th>
              <th class="text-center valign-middle">是否可更動檔案</th>
              <th class="text-center valign-middle">功能</th>
            </tr>
          </thead>
          <tbody>
END;
      
      $years = mysql_query("SELECT year, action FROM year ORDER BY year DESC");
      while($year = mysql_fetch_array($years)) {
        echo "<tr style=\"background: #FFFFFF;\"><td class=\"text-center\">".sprintf("%04s", $year[0])."</td>";
        $re = ($year[1] == '0') ? '不可' : '可';
        echo "<td class='text-center'>{$re}</td>";
        echo "<td class=\"text-center\"><form method='post' action='upload.php?p=changeYearAction'><input type='hidden' name='speYear' value='{$year[0]}'><input type='hidden' name='check' value='{$year[1]}'><button type='submit'>切換</button></form></td></tr>\n";
      }
      echo "</tbody></table>";
    }

    echo "<form method='post' action='upload.php?p=doYearSetting'>\n";
    echo "<input name='name'\>\n";
    echo "<button type='submit'>建立年度</button>\n";
  }

  function doYearSetting($name) {
    echo "已經成功建立年度<br>";
    echo "<a href=\"upload.php?p=yearSetting\">返回</a>\n";
    $sql = "INSERT INTO year (year) VALUES ('$name')";
    mysql_query($sql);
  }

  function changeYearAction($year, $check) {
    echo "已經成功變更年度更動限制<br>";
    echo "<a href=\"upload.php?p=yearSetting\">返回</a>\n";
    $check = intval($check) ^ 1;
    $sql = "UPDATE year SET action = {$check} WHERE year = {$year}";
    mysql_query($sql);
  }

  function backup() {
  	$mutex = 0;
    $count = mysql_query("SELECT count(*) FROM backup");
    $count_result = mysql_fetch_array($count);
    if($count_result[0] == 0) {
      echo '<b>您還尚未建立任何備份！</b>';
    } else {
      echo '<b>您已建立以下備份：</b>';
      echo <<<END
        <table class="table table-bordered">
          <thead>
            <tr style="background: #5bc0de;">
              <th class="col-lg-1 text-center valign-middle" style="width: 10%;">年度</th>
              <th class="valign-middle">備份檔路徑</th>
              <th class="valign-middle">功能</th>
            </tr>
          </thead>
          <tbody>
END;
	  $check = "SELECT count(year) FROM backup where year='0000'";
      $has = mysql_fetch_array(mysql_query($check));
	  if($has[0] == '0') {
	    $mutex = 1;
	  }
	  
      $links = mysql_query("SELECT * FROM backup");
      while($link = mysql_fetch_array($links)) {
        echo "<tr style=\"background: #FFFFFF;\"><td class=\"text-center\">".sprintf("%04s", $link[1])."</td>";
        echo "<td class=\"text-center\">{$link[2]}</td>";
        echo "<td class=\"text-center\"><form method='post' action='upload.php?p=changeyear'><input type='hidden' name='year' value='{$link[2]}'><input type='hidden' name='check' value='{$link[1]}'>".(($mutex == 1 || $link[1] == '0') ? "<button type='submit'>切換</button>" : "<button type='submit' disabled>請先切換回年度0000</button>")."</form></td></tr>\n";
      }
      echo "</tbody></table>";
    }

    echo "<form method='post' action='upload.php?p=dobackup'>\n";
    echo "<input name='name'\>\n";
    echo "<button type='submit'>建立備份</button>\n";
  }

  function dobackup($name) {
  	global $config;
    if (checkdiskspace(filesize($upfile['tmp_name'][$x]))) {
      if (!file_exists(mb_convert_encoding('C:/xampp/htdocs/backend/files/'."backup/", "big5", "UTF-8"))) {
        mkdir(mb_convert_encoding('C:/xampp/htdocs/backend/files/'."backup/", "big5", "UTF-8"), 0777, true);
      }
      if (!file_exists(mb_convert_encoding('D:/'."backend_files_backup/", "big5", "UTF-8"))) {
        mkdir(mb_convert_encoding('D:/'."backend_files_backup/", "big5", "UTF-8"), 0777, true);
      }
      $backup_file = 'C:/xampp/htdocs/backend/files/'."backup/".$name;
      
      if($name != '0000') {
      	$cd_sql = "UPDATE 檔案總管 set name=concat('../../backend_files_backup/".$name."/', owner, '/', name), d=concat('../../backend_files_backup/".$name."/', owner, '/', d)";
      	if (!file_exists(mb_convert_encoding('D:/backend_files_backup/'.$name."/", "big5", "UTF-8"))) {
          mkdir(mb_convert_encoding('D:/backend_files_backup/'.$name."/", "big5", "UTF-8"), 0777, true);
        }
        mysql_query($cd_sql) or die('Error!');
        system("C:\\xampp\\mysql\\bin\\mysqldump.exe -u".$config['db']['user']." -p".$config['db']['pass']." ".$config['db']['db']." --ignore-table=".$config['db']['db'].".osfm_users --ignore-table=".$config['db']['db'].".backup --ignore-table=".$config['db']['db'].".zone > $backup_file");
        system("robocopy C:\\xampp\\htdocs\\backend\\files\\ D:\\backend_files_backup\\".$name."\\ /mov /e /xd \"backup\" > NUL");
        echo "已經成功建立備份<br>";
        echo "<a href=\"upload.php\">返回</a>\n";
        $sql = "INSERT INTO backup (year,path) VALUES ('$name', '$backup_file')";
	  	mysql_query($sql);
      } else {
      	system("C:\\xampp\\mysql\\bin\\mysqldump.exe -u".$config['db']['user']." -p".$config['db']['pass']." ".$config['db']['db']." --ignore-table=".$config['db']['db'].".osfm_users --ignore-table=".$config['db']['db'].".backup --ignore-table=".$config['db']['db'].".zone > $backup_file");
      	$check = "SELECT count(year) FROM backup where year='0000'";
        $has = mysql_fetch_array(mysql_query($check));
	    if($has[0] == '0') {
	      $sql = "INSERT INTO backup (year,path) VALUES ('$name', '$backup_file')";
	      mysql_query($sql);
	    }
	  }
	  
      mysql_query("truncate 檔案總管");
    } else {
      echo "<b><font color=\"red\">空間不夠以致於無法建立備份！</font></b><br>\n";
      $space = 1;
      break;
    }
  }
  
  function cleartmp() {
  	  mysql_query("DELETE FROM backup WHERE year = '0000'") or die(mysql_error());
  }

  function changeyear($path, $check) {
  	global $config;
    if($check != '0')
      dobackup("0000");
    else
      cleartmp();

    system("C:\\xampp\\mysql\\bin\\mysql -u".$config['db']['user']." -p".$config['db']['pass']." ".$config['db']['db']." < $path");
    echo "已經成功導入備份<br>";
    echo "<a href=\"upload.php\">返回</a>\n";
  }

  function itemmod() {
    $cates = array("地區災害潛勢特性評估", "災害防救體系", "培植災害防救能力", "災時緊急應變處置機制", "災害防救資源");
    echo <<<END
    <table class="table table-bordered">
      <thead>
        <tr style="background: #5bc0de;">
          <th class="col-lg-1 text-center valign-middle" style="width: 10%;">繳交</th>
          <th class="valign-middle">工作項目</th>
          <th class="col-lg-5 valign-middle">檔案上傳</th>
        </tr>
      </thead>
      <tbody>
END;

    foreach($cates as $cate) {
      echo "<tr style=\"background: #A48DDC;\"><td colspan=\"3\"><strong>{$cate}</strong><a href=\"upload.php?p=edititem&cate={$cate}\" class=\"btn btn-xs btn-danger\" style=\"float: right;\">管理</a></td></tr>\n";
      $result = getCate("{$cate}");
      while($row = mysql_fetch_array($result)) {
        echo "<tr style=\"background: #FFFFFF;\"><td class=\"text-center\"><button type=\"button\" class=\"btn btn-danger btn-circle\"><i class=\"fa fa-times\"></i></button></td>\n";
        echo "<td><p class=\"text-bg no-padding no-margin\">$row[item]</p>";
        echo "</td>\n";
        echo "<td class=\"valign-middle\">";
        echo "<FORM ENCTYPE=\"multipart/form-data\" ACTION=\"?p=upload\" METHOD=\"POST\">\n";
        echo "<input accept=\"image/*, .pdf, .doc, .docx, .ppt, .pptx, .xls, .xlsx\" style=\"float:left; width:70%;\" type=\"File\" name=\"upfile[]\" size=\"20\" class=\"text\" multiple>\n";
        echo "<progress></progress>\n";
        echo "<input type=\"hidden\" name=ndir value=\"{$cate}/\">\n"
            ."<input type=\"hidden\" name=d value=\"{$cate}/\">\n"
            ."<input type=\"hidden\" name=tid value=\"{$row[id]}\">\n"
            ."<input type=\"submit\" value=\"Upload\" class=\"btn btn-outline btn-success\" disabled>\n"
            ."</form>\n";
        echo "</td></tr>\n";
      }
    }

    /*echo "<br><br>目的地:<br><select name=\"ndir\" size=1>\n";
    if (!$d) echo "<option value=\"/\">/</option>";
    else echo "<option value=\"".$d."\">".$d."</option>";
    $content = listdir($userdir.$d);
    asort($content);
    foreach ($content as $item) echo "<option value=\"".substr($item,strlen($userdir))."/\">".substr($item,strlen($userdir))."/</option>\n";
    echo "</select><br><br>"
        ."<input type=\"hidden\" name=d value=\"$d\">\n"
        ."<input type=\"submit\" value=\"Upload\" class=\"button\">\n"
        ."</form>\n";*/
    echo "</tbody></table>";
    echo "<script src=\"../bower_components/sweetalert/dist/deleter.js\"></script>";
    echo "<script>deleter.init();</script>";
    //closetable();
    //page_footer();
  }

  function edititem($cate) {
    echo <<<END
        <table class="table table-bordered">
          <thead>
            <tr style="background: #5bc0de;">
              <th class="col-lg-1 text-center valign-middle" style="width: 10%;">id</th>
              <th class="valign-middle" style="text-align: center;">項目</th>
              <th class="valign-middle" style="text-align: center;">功能</th>
            </tr>
          </thead>
          <tbody>
END;
    $rows = mysql_query("SELECT * FROM $cate");
    while($row = mysql_fetch_array($rows)) {
        echo "<tr style=\"background: #FFFFFF;\"><td class=\"text-center\">{$row[id]}</td>";
        echo "<td class=\"text-center\">{$row[item]}</td>";
        echo "<td class=\"text-center\"><a class=\"btn btn-danger btn-sm\" href=\"upload.php?p=itemfunc&action=delete&desc={$row[item]}&cate={$cate}\">刪除</a></td></tr>\n";
    }
    echo "</tbody></table>";

    echo "<form method='post' action='upload.php?p=itemfunc&action=add&cate={$cate}'>\n";
    echo "<input name='desc'\>\n";
    echo "<button type='submit'>新增項目</button>\n";
  }

  function itemfunc() {
    if($_GET['action'] == "delete") {
        mysql_query("DELETE FROM $_GET[cate] WHERE item = '$_GET[desc]'") or die(mysql_error());
        mysql_query("DELETE FROM 對應表 WHERE topic_name = '$_GET[desc]'") or die(mysql_error());
    } else {
        mysql_query("INSERT INTO $_GET[cate] (item) VALUE ('$_POST[desc]')") or die(mysql_error());
        mysql_query("INSERT INTO 對應表 (topic_name) VALUE ('$_POST[desc]')") or die(mysql_error());
    }
    echo "指令執行成功<br>";
    echo "<a href=\"upload.php?p=items\">返回</a>\n";
  }

  function getActualDir($dir) {
    $linkdir = preg_replace('@^.*\00([A-Z]:)(?:[\00\\\\]|\\\\.*?\\\\\\\\.*?\00)([^\00]+?)\00.*$@s', '$1\\\\$2', file_get_contents(substr($dir, 0, 8).".lnk"));
    $actualdir = $linkdir.substr(strstr($dir, '/'), 6);
    return str_replace('\\', '/', $actualdir);;
  }