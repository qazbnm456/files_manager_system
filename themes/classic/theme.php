<?php
$theme = "classic";
$tbcolor1 = "#E5E5E5";
$tbcolor2 = "#EEEEEE";
$tbcolor3 = "#FFCC99";
$tbcolor4 = "#CCCCFF";
$bgcolor1 = "#E8ECF5";
$bgcolor2 = "#a6a6a6";
$bgcolor3 = "#ffffff";
$txtcolor1 = "#000000";
$txtcolor2 = "#003399";

// Image Map
$IMG_CHECK = "../themes/$theme/images/icons/accept.png";
$IMG_RENAME = "../themes/$theme/images/icons/font.png";
$IMG_GET = "../themes/$theme/images/icons/drive_go.png";
$IMG_EDIT = "../themes/$theme/images/icons/page_edit.png";
$IMG_DELETE = "../themes/$theme/images/icons/delete.png";
$IMG_MOVE = "../themes/$theme/images/icons/folder_go.png";
$IMG_CHMOD = "../themes/$theme/images/icons/lock.png";
$IMG_ACTION = "../themes/$theme/images/icons/bullet_arrow_down.png";

$IMG_MIME_FOLDER = "../themes/$theme/images/mime/folder.png";
$IMG_MIME_BINARY = "../themes/$theme/images/mime/page_white_gear.png";
$IMG_MIME_AUDIO = "../themes/$theme/images/mime/page_white_cd.png";
$IMG_MIME_VIDEO = "../themes/$theme/images/mime/page_white_camera.png";
$IMG_MIME_IMAGE = "../themes/$theme/images/mime/page_white_picture.png";
$IMG_MIME_TEXT = "../themes/$theme/images/mime/page_white_text.png";
$IMG_MIME_UNKNOWN = "../themes/$theme/images/mime/page_white.png";


function page_header($title,$show = true) {
  global $HEADER_CHARACTERSET, $permmakeuser, $permedituser, $permdeleteuser, $permbrowse, $permupload, $permcreate, $permuser, $permadmin, $d, $darkgrey, $lkcolor1, $lkcolor2, $lkcolor3, $lkcolor4, $background, $lightgrey, $incolor2, $incolor1, $black, $white, $user, $pass, $extraheaders, $sitetitle, $bgcolor1, $bgcolor2, $bgcolor3, $txtcolor1, $txtcolor2, $tbcolor4, $IMG_ACTION, $IMG_CHMOD, $IMG_DELETE, $IMG_MOVE;
  global $extraheaders, $sitetitle, $lastsess, $login, $viewing, $iftop, $bgcolor1, $bgcolor2, $bgcolor3, $txtcolor1, $txtcolor2, $user, $pass, $password, $debug, $issuper;

  echo "<table cellpadding=2 cellspacing=2 align=center><tr><td>\n"
      ."<table cellpadding=1 cellspacing=1><tr><td>\n"
      ."<table cellpadding=0 cellspacing=0>\n";
}

function page_footer() {
  global $nobar, $user;
  /* keep this footer or you are just a big meaning :( */
  if ($nobar != 1) echo "<tr class=titlebar2 align=right><td class=titlebar2 align=right height=15>\n"; if ($user) showdiskspace();
  echo "</table></table><tr><td align=right><font class=copyright>Powerd by <a href=http://www.osfilemanager.com/>osFileManager</a><br>&copy; 2003-".date("Y")." <a href=http://www.arzy.net/>Arzy LLC</a></font></table>\n";
}

function opensubtitle($title) {
  global $bgcolor3, $black;
  echo "<table cellpadding=1 cellspacing=0 height=20 bgcolor=$bgcolor3>\n"
      ."<tr><td colspan=2 bgcolor=$black> </td></tr>\n"
      ."<tr><td width=16> <td width=100%>\n"
      ."<font class=subtitle2>$title</font>"
      ."<tr><td colspan=2 bgcolor=$black> </td></tr>\n"
      ."</table>\n";
}

function opentable($width) {
  global $bgcolor3, $white;
  echo "<table cellpadding=1 cellspacing=1 width=$width bgcolor=$bgcolor3><tr><td>\n"
      ."<table cellpadding=0 cellspacing=0 width=$width bgcolor=$bgcolor3><tr><td>\n";
}

function closetable() {
  echo "</table></table>\n";
}
?>