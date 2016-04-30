<!DOCTYPE html>
<?php
    include_once("db_init.php");

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

    $yay = 0; $user = ''; $pass = ''; $sess = '';

    if ($_REQUEST['login']) $user = $_REQUEST['login'];
    elseif ($_COOKIE['user']) $user = $_COOKIE['user'];

    if ($_REQUEST['encpas']) $pass = $_REQUEST['encpas'];
    elseif ($_COOKIE['pass']) $pass = $_COOKIE['pass'];

    if ($_REQUEST['randsess']) $sess = $_REQUEST['randsess'];
    elseif ($_COOKIE['sess']) $sess= $_COOKIE['sess'];

    if ($user && $pass) {
      $sql = "SELECT pass, user, id, folder, http, spacelimit, language, theme, permbrowse, permupload, permcreate, permuser, permadmin, permdelete, permmove, permchmod, permget, permdeleteuser, permedituser, permmakeuser, permpass, permrename, permedit, permsub, formatperm, status, recycle, permprefs FROM ".$GLOBALS['config']['db']['pref']."users WHERE user='".mysql_escape_string($user)."'";
      $mysql = mysql_query($sql);
        list ($dbpass, $dbuser, $userid, $userdir, $http, $limit, $language, $theme, $permbrowse, $permupload, $permcreate, $permuser, $permadmin, $permdelete, $permmove, $permchmod, $permget, $permdeleteuser, $permedituser, $permmakeuser, $permpass, $permrename, $permedit, $permsub, $formatperm, $status, $recycle, $permprefs) = mysql_fetch_row($mysql);

        if ($userid && $pass == md5($dbpass.$sess)) {
            $yay = 1;
        }
    }

    if ($yay) {

        $user = $dbuser;
        $activesess = date("YmdHis");
        $mysql = mysql_query("UPDATE ".$GLOBALS['config']['db']['pref']."users SET currsess='$activesess' WHERE id='$userid'") or die (mysql_error());

        setcookie('user','',time()-60*60*24*1);
        setcookie('pass','',time()-60*60*24*1);
        setcookie('sess','',time()-60*60*24*1);

        setcookie('user',$dbuser,time()+60*60*24*1);
        setcookie('pass',$pass,time()+60*60*24*1);
        setcookie('sess',$sess,time()+60*60*24*1);

        header("location:./index.php");
        exit();

    } else {
        setcookie('user','',time()-60*60*24*1);
        setcookie('pass','',time()-60*60*24*1);
        setcookie('sess','',time()-60*60*24*1);
        $user = '';
        $pass = '';

        global $configindex, $extraheaders, $REQUEST_URI, $sqlpref, $lastpage, $bgcolor1, $bgcolor2,$bgcolor3, $tbcolor1, $tbcolor2, $fail, $login, $password, $user, $pass;
        $randsess = md5(md5(rand(1,25419876)).md5(date("DMjygia")));
        $extraheaders = "<script language=javascript>\n"
                       ."  <!--\n"
                       .md5return()
                       ."  function encpass() {\n"
                       ."    if (md5_vm_test() && valid_js()) { \n"
                       ."      document.login.encpas.value = hex_md5(hex_md5(document.login.password.value) + document.login.randsess.value);\n"
                       ."      document.login.password.value = \"\";\n"
                       ."      document.login.submit();\n"
                       ."      return true;\n"
                       ."    } else {\n"
                       ."      form.onsubmit=null;\n"
                       ."      return false;\n"
                       ."    }\n"
                       ."  }\n"
                       ."  -->\n"
                       ."</script>\n";
        echo <<<END
        <html lang="en">

            <head>

                <meta charset="utf-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <meta name="description" content="">
                <meta name="author" content="">

                <title>深耕計畫後台</title>

                <!-- Bootstrap Core CSS -->
                <link href="../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

                <!-- MetisMenu CSS -->
                <link href="../bower_components/metisMenu/dist/metisMenu.min.css" rel="stylesheet">

                <!-- Custom CSS -->
                <link href="../dist/css/sb-admin-2.css" rel="stylesheet">

                <!-- Custom Fonts -->
                <link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

                <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
                <!-- WARNING: Respond.js doesn\'t work if you view the page via file:// -->
                <!--[if lt IE 9]>
                    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
                    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
                <![endif]-->

            </head>

            <body>
END;
            echo $extraheaders;
            echo <<<END
                <div class="container">
                    <div class="row">
                        <div class="col-md-4 col-md-offset-4">
                            <div class="login-panel panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">請登入</h3>
                                </div>
                                <div class="panel-body">
                                    <form role="form" action="" method="POST" name="login">
                                        <fieldset>
                                            <div class="form-group">
                                                <input class="form-control" placeholder="帳號" name="login" type="text" autofocus>
                                            </div>
                                            <div class="form-group">
                                                <input class="form-control" placeholder="密碼" name="password" type="password" value="">
                                            </div>
                                            <div class="checkbox">
                                                <label>
                                                    <input name="remember" type="checkbox" value="Remember Me">記住我
                                                </label>
                                            </div>
                                            <input type=hidden name="lastpage" value="$lastpage">
                                            <input type=hidden name="encpas"><br>
                                            <input type=hidden name="randsess" value="$randsess"><br>
                                            <input type=hidden name="loging" value="1">
                                            <!-- Change this to a button or input when using this as a form -->
                                            <input type="submit" onclick="encpass();" value="登入" class="btn btn-lg btn-success btn-block">
                                        </fieldset>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- jQuery -->
                <script src="../bower_components/jquery/dist/jquery.min.js"></script>

                <!-- Bootstrap Core JavaScript -->
                <script src="../bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

                <!-- Metis Menu Plugin JavaScript -->
                <script src="../bower_components/metisMenu/dist/metisMenu.min.js"></script>

                <!-- Custom Theme JavaScript -->
                <script src="../dist/js/sb-admin-2.js"></script>

            </body>

        </html>
END;
    }

?>
