<?php
include 'vendor/autoload.php';

$r = new Gunz\OctaAcl();


function isValid($bit_requested, $bit_required ){
    return (($bit_requested | $bit_required) == $bit_required);
}

function toBitString($integer, $bits = 8){
    return printf("%1$0{$bits}b",$integer);
}

function toInteger($binary_str){
    return bindec($binary_str);
}

var_dump($r);
$user_acl = 19;
$auth_requested = 16;
printf("USE %1$08b\n",$user_acl); // the user defined acl int
printf("REQ %1$08b\n",$auth_requested); // the required acl for the operation
printf("AND %1$08b\n",($user_acl & $auth_requested)); // result from AND bitwise operation
printf("+OR %1$08b\n",($user_acl | $auth_requested)); // restul from OR bitwise operation
printf("XOR %1$08b\n",($user_acl ^ $auth_requested)); // result from XOR bitwise operation

for($i = 0; $i<=255; $i++ ){
   // printf("%03d %1$08b\n",$i,$i); // the user defined acl int

}
print_r([ $auth_requested | $user_acl, $user_acl,(($auth_requested | $user_acl) == $user_acl)]);


print toInteger("0x11");


abstract class BitwiseFlag
{
    protected $flags;
    protected $bits = 8;
    /*
     * Note: these functions are protected to prevent outside code
     * from falsely setting BITS. See how the extending class 'User'
     * handles this.
     *
     */
    protected function isFlagSet($flag)
    {
        return (($this->flags & $flag) == $flag);
    }
    protected function setFlag($flag, $value)
    {
        if($value)
        {
            $this->flags |= $flag;
        }
        else
        {
            $this->flags &= ~$flag;
        }
    }

    public function setBits($bits){
        $this->bits = $bits;
    }

    public function getBits(){
        return $this->bits;
    }

    public function getDecimal(){
        return $this->flags;
    }

    public function setDecimal($int, $value = true){
        $this->setFlag($int, $value);
    }

    public function __toString()
    {
        $bits = $this->bits;
        return sprintf("%1$0{$bits}b",$this->flags);
    }
}

# User.php
class User extends BitwiseFlag
{
    const FLAG_REGISTERED = 1; // BIT #1 of $flags has the value 1
    const FLAG_ACTIVE = 2;     // BIT #2 of $flags has the value 2
    const FLAG_MEMBER = 4;     // BIT #3 of $flags has the value 4
    const FLAG_ADMIN = 8;      // BIT #4 of $flags has the value 8
    public function isRegistered(){
        return $this->isFlagSet(self::FLAG_REGISTERED);
    }
    public function isActive(){
        return $this->isFlagSet(self::FLAG_ACTIVE);
    }
    public function isMember(){
        return $this->isFlagSet(self::FLAG_MEMBER);
    }
    public function isAdmin(){
        return $this->isFlagSet(self::FLAG_ADMIN);
    }
    public function setRegistered($value){
        $this->setFlag(self::FLAG_REGISTERED, $value);
    }
    public function setActive($value){
        $this->setFlag(self::FLAG_ACTIVE, $value);
    }
    public function setMember($value){
        $this->setFlag(self::FLAG_MEMBER, $value);
    }
    public function setAdmin($value){
        $this->setFlag(self::FLAG_ADMIN, $value);
    }
    public function _s_toString(){
        return 'User [' .
            ($this->isRegistered() ? 'REGISTERED' : '') .
            ($this->isActive() ? ' ACTIVE' : '') .
            ($this->isMember() ? ' MEMBER' : '') .
            ($this->isAdmin() ? ' ADMIN' : '') .
            ']';
    }
}


$user = new User();

echo "\n".$user."\n";
$user->setRegistered(false);
echo $user."\n";
$user->setActive(false);
echo $user."\n";
$user->setMember(false);
echo $user."\n";
$user->setAdmin(false);
echo $user."\n";
$user->setDecimal(14|128|43, false);
$user->setDecimal(14, true);
echo $user->getDecimal()."\n";
echo $user."\n";
