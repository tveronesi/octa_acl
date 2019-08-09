<?php 
namespace Gunz;


/**
 * @author tiago
 * 
 * User control lists class util.
 * each user should have database Users.configuration.acl_octa the 
 * integer reppresenting its roles and access rights.
 * The integer representation in bitcode will give the needed information about 
 * the access rules defined here:
 * 1 AUTH_OPERATOR - is operator  
 * 2 AUTH_ACCOUNTANT - is accountant
 * 3 AUTH_ADMIN  - is admin
 * and so on
 * @example
 * So fo r a user tht has admin operator only powers we should assign to him [1],
 * If the user can perform accountant(2) actions, other that operator(1) it will be a 1 + 2 acl = [3]
 * and so on
 * Checking if the user is authorized:
 * ex. for the operation we need only AUTH_ACCOUNTANT so:
 * if(self::isAuth(AUTH_ACCOUNTANT)){
 *      // doThings()
 * }else{
 *      //USER NOT AUTH
 * }
 * 
 * This way a user can be an admin but cant act as an accountant ( acl [4]).
 * 
 */
//ACL constants definitions
$_acl_definitions = array( // use this array to have auto menus in user edit/create details
    'operator' => array(1),// 0010
    'accountant' => array(2), // 0100
    'admin' => array(4), // 1000
    'manage_cs' => array(8), // 0001

);

foreach($_acl_definitions as $def => $d){ // define constants
    define('AUTH_'.strtoupper($def),$d[0]);
}

/**
 * Class to manage user access control lists
 *
 */
class OctaAcl {
    
    /**
     * returns the current user Acl integer as saved in session
     * @return int
     */
    public static function userAuthAcl(){
        if(!isset($_SESSION['MM']['user_data']['acl'])){
            return 0;
        }
        return $_SESSION['MM']['user_data']['acl'];
    }
    
    /**
     * alias for userAuthAcl, returns the acl for the current logged user as saved in session.
     */
    public static function getUserAcl(){
        return self::userAuthAcl();
    }
    
    /**
     * Returns true if the user is authorized to accomplish the requested acl 
     * @param integer $auth_requested
     * @param integer $usr_acl check acl againts this acl bits value. if not given will use current logged user acl bits
     * @return bolean
     */
    public static function isAuth($auth_requested, $usr_acl = null){
        if(is_null($usr_acl)){ // not given, use current logged user acl
            $user_acl = self::userAuthAcl();
        }else{
            $user_acl = $usr_acl;
        }
        return (($auth_requested | $user_acl) == $user_acl);
    }
    
    /**
     * print html debug information 
     * @param integer $auth_requested
     * @return void
     */
    public static function showAuthBits($auth_requested){
        $user_acl = self::userAuthAcl();
        printf("USE %1$08b<br>",$user_acl); // the user defined acl int
        printf("REQ %1$08b<br>",$auth_requested); // the required acl for the operation
        printf("AND %1$08b<br>",($user_acl & $auth_requested)); // result from AND bitwise operation
        printf("+OR %1$08b<br>",($user_acl | $auth_requested)); // restul from OR bitwise operation
        printf("XOR %1$08b<br>",($user_acl ^ $auth_requested)); // result from XOR bitwise operation
        print("User ACL is $user_acl, Required acl for the operation is : $auth_requested ");
        if(self::isAuth($auth_requested)){ 
            printf("ALLOWED");
        }else{
            printf("ERROR ACCESS NOT ALLOWED");
        }
    }
    
    /**
     * updtes realtime acl data in session without need to re-login!
     * @param $int
     * @return void
     */
    public static function updateAcl($int){
        $_SESSION['MM']['user_data']['acl'] = $int;
    }
    
    /**
     * returns list of acl for the logged user
     * @return array key the acl int , value the acl label as in $_acl_definitions
     */
    public static function getAclUser(){
        $_acl_definitions = self::getDeclaredAcl();
        $lista = array();
        foreach($_acl_definitions as $l=>$d){
            if(self::isAuth($d[0])){
                $lista[$d[0]] = $l;
            }
        }
        return $lista;
    } 
    
    /**
     * returns declared ACL for in the system
     * @return array
     */
    public static function getDeclaredAcl(){
        global $_acl_definitions;
        return $_acl_definitions;
    }
}

//self::updateAcl(AUTH_ADMIN|AUTH_OPERATOR|AUTH_ACCOUNTANT); // until users set acls , i'll set all as AUTH_ADMIN
