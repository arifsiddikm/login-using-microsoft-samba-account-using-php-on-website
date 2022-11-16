<?php
// Initialize Variable
$dnssambaorldap = "domain.com";
$ipsambaorldapserver = "192.168.xxx.xxx";
error_reporting(E_ALL);   
ini_set('display_errors', 'On');  
define('DOMAIN_FQDN', $dnssambaorldap);
define('LDAP_SERVER', $ipsambaorldapserver);
putenv('LDAPTLS_REQCERT=never');
//Basic Login verification
if (isset($_POST['submit'])) {
    $user = strip_tags($_POST['username']) .'@'. DOMAIN_FQDN;
    $pass = stripslashes($_POST['password']);
    // If LDAP no SSL
    $conn = ldap_connect("ldap://". LDAP_SERVER ."/",389);
    // If LDAP with SSL
    // $conn = ldap_connect("ldaps://". LDAP_SERVER ."/",636);
    if (!$conn) {
        $err = 'Could not connect to LDAP server';
    }
    else {
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        $bind = @ldap_bind($conn, $user, $pass);
        ldap_get_option($conn, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error);
        if (!empty($extended_error)) {
            $errno = explode(',', $extended_error);
            $errno = $errno[2];
            $errno = explode(' ', $errno);
            $errno = $errno[2];
            $errno = intval($errno);
            if ($errno == 532) {
                $err = 'Unable to login: Password expired';
                echo "gagal";
            }
        } 
        elseif ($bind) { 
            $base_dn = array("CN=Users,DC=". join(',DC=', explode('.', DOMAIN_FQDN)), "OU=Users,OU=Users,DC=". join(',DC=', explode('.', DOMAIN_FQDN)));
            $result = ldap_search(array($conn,$conn), $base_dn, "(cn=*)");
            if (!count($result))
                $err = 'Result: '. ldap_error($conn);
            else {
                echo "Success";
            }
        }
    }   
    // session OK, redirect to home page
    if (isset($_SESSION['redir'])) {
        header('Location: /');
        exit();
    }
    elseif(!isset($err)) { $err = 'Result: '. ldap_error($conn); }
    ldap_close($conn);
} 
?> 
<!DOCTYPE html>
<head>
<title>PHP LDAP/SAMBA LOGIN</title>
</head>
<body>
<div align="center">
<h3>Login</h3>
<div title="Login" id="loginbox">
    <div>
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" id="login" method="post">
        <table><?php if (isset($err)) echo '<tr><td colspan="2" class="errmsg">'. $err .'</td></tr>'; ?>
            <tr>
                <td>Login:</td>
                <td><input type="text" name="username" autocomplete="off"/></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input type="password" name="password"  autocomplete="off"/></td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input class="button" type="submit" name="submit" value="Login" />
                </td>
            </tr>
        </table>
    </form>
    </div>
</div>
</div>
</body>
</html>
<?php phpinfo(); ?>