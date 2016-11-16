<?php


class AssertEquals_Version3_Test extends \PHPUnit_Framework_TestCase
{

    public function testAssertEquals()
    {
        $result = $this->loadFixture('result');
        $expected = $this->loadFixture('expected');
//        $result['something'] = 'which makes it brake';

        // assertEquals is only here to provide convenient diff output,
        // but it doesn't complain about
        // ['pay_later' => false,]
        // vs.
        // ['pay_later' => null,]
        $this->assertEquals($expected, $result);
        foreach ($expected as $key => $expectedValue) {
            $this->assertSame($expectedValue, $result[$key], $key);
        }
//        $this->assertSame($expectedResult, $result);
    }

    /**
     * @param string $fixtureName
     *
     * @return array
     */
    protected function loadFixture($fixtureName)
    {
        return json_decode(file_get_contents(
            sprintf('%s/../fixture/%s.json', __DIR__, $fixtureName)
        ), true);
    }


}