<?php
namespace App\Person\Patient;

class Foo extends Bar
{
    /**
     * @inheritDoc
     */
    public $brain = [];

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function dumpThoughts($thoughts)
    {
    }
}
