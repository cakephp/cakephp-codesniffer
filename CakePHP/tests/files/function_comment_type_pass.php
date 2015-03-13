<?php
namespace Beakman;

class Foo
{
    /**
     * Some sentence.
     *
     * @param int $param Some Param.
     * @param bool $otherParam Some Other Param.
     * @return void
     */
    public function bar($param, $otherParam)
    {
    }

    /**
     * Description
     *
     * @return mixed
     */
    public function baz()
    {
        if ($something) {
            return;
        }
        return 'foo';
    }

    /**
     * Description
     *
     * @return void|string
     */
    public function foo()
    {
        if ($something) {
            return;
        }
        return 'foo';
    }

    /**
     * {@inheritDoc}
     */
    public function inherited($param)
    {
    }
}
