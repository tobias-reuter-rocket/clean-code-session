<?php


class AssertEquals_Version1_Test extends \PHPUnit_Framework_TestCase
{

    public function testAssertEquals()
    {
        $result = $this->loadFixture('result');
        $expected = $this->loadFixture('expected');

        $result['something'] = 'which makes it brake';
        $this->assertEquals($expected, $result);
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