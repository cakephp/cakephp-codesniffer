<?php
namespace Beakman;

class Foo
{
    /**
     * Test void return type with no return statements.
     *
     * @param int $param Some Param.
     * @param bool $otherParam Some Other Param.
     * @return void
     */
    public function void($param, $otherParam)
    {
    }

    /**
     * Test void return type with early void return statement.
     *
     * @return void
     */
    public function voidReturnEarly()
    {
        if ($otherParam) {
            return;
        }
    }

    /**
     * Test mixed return type with mixed return statements.
     *
     * @return mixed
     */
    public function mixed()
    {
        if ($something) {
            return;
        }

        return 'string';
    }

    /**
     * Test mixed again, with a description after the annotation.
     *
     * @return mixed With description.
     */
    public function mixedWithDesc()
    {
        if ($something) {
            return;
        }

        return 'string';
    }

    /**
     * Test multiple return types, with void, and mixed return statements.
     *
     * @return void|string
     */
    public function multiVoidMixed()
    {
        if ($something) {
            return;
        }

        return 'string';
    }

    /**
     * Test multiple return types, with void, and no return statements.
     *
     * @return void|string
     */
    public function multiVoidEmpty()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function inherited($param)
    {
    }
}
