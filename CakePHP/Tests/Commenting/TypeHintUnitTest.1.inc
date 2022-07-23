<?php
namespace Beakman;

class Foo
{
    /** @var string[] */
    protected $convertToGeneric;

    /** @var null|string[]|class-string<\Cake\I18n\Number>|\Generator<int> $testVar2 */
    protected $sortGenerics;

    /** @var string|array|array<int, string>|array{0: string, 1: int} */
    protected $sortArrayShape

    /** @var \Stringable|\Countable|\Traversable|\ArrayObject|\ArrayAccess|\Closure|\Generator|\CustomClass|string */
    protected $sortSpecificClassesLast

    /**
     * @param \Test|\Closure|mixed|array<string|int>|string|int|false $test
     * @return string|int|void
     */
    public function testFunctionAnotations()
    {
    }
}

function test()
{
    /** @var null|string|int|float */
    $testInlineVar;
}

/**
 * @param null|(Type1&Type2) $param
 * @return Type1&Type2
 */
function intersection($param)
{
}
