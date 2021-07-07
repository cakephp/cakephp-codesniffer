<?php
namespace Beakman;

class Foo
{
    /**
     * @param int $test
     * @return void
     * @throws \Exception whenever I want
     */
    public function testValidTagSpacing(int $test)
    {
    }

    /**
     * @param   int $test
     * @return  void
     * @throws  \Exception  whenever  I want
     * and more
     */
    public function testInvalidTagSpacing(int $test)
    {
    }
}
