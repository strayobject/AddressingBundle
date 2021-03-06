<?php

namespace Markup\AddressingBundle\Tests\Validator;

use Markup\AddressingBundle\Validator\RegionConstraint;
use Markup\AddressingBundle\Validator\RegionValidator;
use Mockery as m;

class RegionValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->useStrictRegions = true;
        $this->validator = new RegionValidator($this->useStrictRegions);
        $this->context = m::mock('Symfony\Component\Validator\ExecutionContextInterface');
        $this->validator->initialize($this->context);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testIsValidator()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\ConstraintValidatorInterface', $this->validator);
    }

    public function testThrowInvalidArgumentExceptionWhenInvalid()
    {
        $invalid = 'not an address';
        $this->setExpectedException('InvalidArgumentException');
        $this->validator->validate($invalid, new RegionConstraint());
    }

    /**
     * @dataProvider cases
     */
    public function testValidationCases($country, $region, $expectedPass)
    {
        $address = new TestAddress();
        $address->setRegion($region);
        $address->setCountry($country);
        $expectation = $this->context->shouldReceive('addViolation');
        if ($expectedPass) {
            $expectation->never();
        } else {
            $expectation->once();
        }
        $this->validator->validate($address, new RegionConstraint());
    }

    public function cases()
    {
        return array(
            array('FR', 'Île de France', true),
            array('US', 'NY', true),
            array('US', 'New York', false),
            array('CA', 'ON', true),
            array('US', 'Ontario', false),
        );
    }

    public function testNonAbbreviationsPassWhenNotStrict()
    {
        $validator = new RegionValidator(false);
        $validator->initialize($this->context);
        $address = new TestAddress();
        $address->setRegion('California');
        $address->setCountry('US');
        $this->context
            ->shouldReceive('addViolation')
            ->never();
        $validator->validate($address, new RegionConstraint());
    }
}
