<?php

namespace Quantum\Tests\Libraries\Database\Idiorm\Statements;

use Quantum\Tests\Libraries\Database\Idiorm\IdiormDbalTestCase;
use Quantum\Libraries\Database\Idiorm\IdiormDbal;

class ResultIdiormTest extends IdiormDbalTestCase
{

    public function testIdiormGet()
    {
        $userModel = new IdiormDbal('users');

        $users = $userModel->get();

        $this->assertIsArray($users);

        $this->assertEquals('John', $users[0]->firstname);

        $this->assertEquals('Jane', $users[1]->firstname);
    }

    public function testIdiormFindOne()
    {
        $userModel = new IdiormDbal('users');

        $user = $userModel->findOne(1);

        $this->assertEquals('John', $user->prop('firstname'));

        $this->assertEquals('Doe', $user->prop('lastname'));
    }

    public function testIdiormFindOneBy()
    {
        $userModel = new IdiormDbal('users');

        $user = $userModel->findOneBy('firstname', 'John');

        $this->assertEquals('Doe', $user->prop('lastname'));

        $this->assertEquals('45', $user->prop('age'));
    }

    public function testIdiormFirst()
    {
        $userModel = new IdiormDbal('users');

        $user = $userModel->first();

        $this->assertEquals('Doe', $user->prop('lastname'));

        $this->assertEquals('45', $user->prop('age'));

        $userModel = new IdiormDbal('users');

        $user = $userModel->criteria('age', '<', 50)->first();

        $this->assertEquals('John', $user->prop('firstname'));

        $this->assertEquals('Doe', $user->prop('lastname'));

        $this->assertEquals('45', $user->prop('age'));
    }

    public function testIdiormCount()
    {
        $userModel = new IdiormDbal('users');

        $userCount = $userModel->count();

        $this->assertIsInt($userCount);

        $this->assertEquals(2, $userCount);
    }

    public function testIdiormAsArray()
    {
        $userModel = new IdiormDbal('users');

        $user = $userModel->first();

        $this->assertIsObject($user);

        $this->assertIsArray($user->asArray());
    }

}
