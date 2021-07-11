<?php
namespace Beakman;

class Foo
{
    /**
     * @return $this
     */
    public function correct()
    {
    }

    /**
     * @return $this
     */
    public function incorrect(): Foo
    {
    }

    /**
     * @return $this
     */
    public function incorrectSelf(): self
    {
    }
}
