<?php

namespace Tests;


use Gunz\OctaAcl;

class ATest extends \PHPUnit\Framework\TestCase
{

    protected static $_acl_definitions = array( // use this array to have auto menus in user edit/create details
        'level1' => array(1),// 0010
        'level2' => array(2), // 0100
        'level3' => array(4), // 1000
        'level4' => array(8), // 0001

    );

    public function testAcls()
    {


        $user_acl = self::$_acl_definitions['level1'] & self::$_acl_definitions['level3'];
        echo $user_acl;
        $this->assertTrue(\Gunz\OctaAcl::isAuth(4,$user_acl));
        $this->assertTrue(\Gunz\OctaAcl::isAuth(1,$user_acl));
        $this->assertFalse(\Gunz\OctaAcl::isAuth(2,$user_acl));
    }

}
