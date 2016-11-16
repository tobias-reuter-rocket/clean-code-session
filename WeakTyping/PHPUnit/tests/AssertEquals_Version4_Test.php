<?php


class AssertEquals_Version4_Test extends \PHPUnit_Framework_TestCase
{

    public function testAssertEquals()
    {
        $result = $this->loadFixture('result');
        $expected = $this->loadFixture('expected');

        $this->assertSame($expected, $result);
    }

    /**
     * Go to
     * @see \PHPUnit_Framework_Constraint_IsIdentical::evaluate
     * and add the code below
     */
    protected function copyMeToExtendAssertSame()
    {
        // this is just a DRAFT and certainly not production ready!!!

        if (is_array($this->value) && is_array($other)) {
            $f = new SebastianBergmann\Comparator\ComparisonFailure(
                $this->value,
                $other,
                $this->exporter->export($this->value),
                $this->exporter->export($other),
                false,
                'Failed asserting that two arrays are identical.'
            );

            throw new PHPUnit_Framework_ExpectationFailedException(
                trim($description . "\n" . $f->getMessage()),
                $f
            );
        }
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