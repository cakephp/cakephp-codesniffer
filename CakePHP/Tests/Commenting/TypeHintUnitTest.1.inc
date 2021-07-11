<?php
namespace Beakman;

class Foo
{
    /** @var int|string[]|int[] */
    protected $testVar;

    /** @var null|string|class-string<\Cake\I18n\Number>|array $testVar2 */
    protected $testVar2;

    /**
     * @param \Test|\Closure|mixed|array<string|int>|string|int|false $test
     * @psalm-param \Test|array<int, string>|array{0: string, 1: int}|string $test2
     * @return string|int|void
     */
    public function testSortedCompound()
    {
    }

    /**
     * @param \Closure|\Test|mixed|int|false|string[]|array<string|int>|string $test
     * @psalm-param string|list<int>|array{0: string, 1: int}|\Test $test2
     * @return false|true|void|int|\Test|array<string>|int[]|null
     * @psalm-return false|true|void|int|\Test|array<string>|int[]|null
     */
    public function testUnsortedCompound()
    {
    }
}

function test()
{
    /** @var class-string<\Cake\I18n\Number>|null */
    $testVar;

    /** @psalm-var null|string|class-string<\Cake\I18n\Number>|array $testVar2 */
    $testVar2;
}
