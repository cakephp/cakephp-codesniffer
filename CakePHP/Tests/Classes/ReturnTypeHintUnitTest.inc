<?php
namespace Beakman;

class Foo
{
    /**
     * @return $this
     */
    public function correct(): static
    {
    }

    /**
     * @return $this
     */
    public function incorrect()
    {
    }

    /**
     * @return $this
     */
    public function incorrectClass(): Foo
    {
    }

    /**
     * @return $this
     */
    public function incorrectSelf(): self
    {
    }
}
