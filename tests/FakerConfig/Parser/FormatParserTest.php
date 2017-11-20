<?php
namespace FakerConfig\Parser;


use Faker\Factory;
use PHPUnit\Framework\TestCase;

class FormatParserTest extends TestCase
{
    /** @var FormatParser */
    private $parser;

    protected function setUp()
    {
        parent::setUp();
        $generator = Factory::create();
        $generator->seed(1234);
        $this->parser = new FormatParser();
        $this->parser->load($generator);
    }

    /**
     * Validates that parse properly identify known Faker properties
     *
     * @dataProvider parsePropertiesDataProvider
     * @param string $value Format to parse
     */
    public function testParseProperties($value)
    {
        $format = $this->parser->parse($value);
        self::assertNotNull($format, 'parse returned null for value: '.$value);
        self::assertEquals($value, $format->getName(), 'invalid format name generated');
        self::assertTrue($format->isProperty(), 'format not marked as a property');
        self::assertEmpty($format->getArguments(), 'parsed property should not have arguments. Found: '.count($format->getArguments()). ' arguments');
    }

    public function parsePropertiesDataProvider()
    {
        return array(
            // Valid Faker Properties
            array('name'),
            array('address'),
            array('firstName'),
            array('firstNameMale'),
            // en_US properties (default locale)
            array('state'),
            array('stateAbbr'),

        );
    }


    /**
     * Validates that parse properly identify known Faker properties using the fr-FR provider
     *
     * @dataProvider parseFrenchPropertiesDataProvider
     * @param string $value Format to parse
     */
    public function testParseFrenchProperties($value)
    {
        $generator = Factory::create('fr_FR');
        $generator->seed(1234);
        $this->parser = new FormatParser();
        $this->parser->load($generator);
        $format = $this->parser->parse($value);
        self::assertNotNull($format, 'parse returned null for value: '.$value);
        self::assertEquals($value, $format->getName(), 'invalid format name generated');
        self::assertTrue($format->isProperty(), 'format not marked as a property');
        self::assertEmpty($format->getArguments(), 'parsed property should not have arguments. Found: '.count($format->getArguments()). ' arguments');
    }

    public function parseFrenchPropertiesDataProvider()
    {
        return array(
            // Valid Faker Properties
            array('name'),
            array('address'),
            array('firstName'),
            array('firstNameMale'),
            array('region'),
            array('department'),
            array('departmentName'),
        );
    }

    /**
     * Validates that parse properly identify known Faker methods
     *
     * @dataProvider parseMethodsDataProvider
     * @param string $value Format to parse
     */
    public function testParseMethods($value, $expectedName, $expectedArgs)
    {
        $format = $this->parser->parse($value);
        self::assertNotNull($format, 'parse returned null for value: '.$value);
        self::assertEquals($expectedName, $format->getName(), 'invalid format name generated');
        self::assertFalse($format->isProperty(), 'format not marked as a property');
        self::assertEquals(count($expectedArgs), count($format->getArguments()), 'invalid number of arguments for parsed method');
        self::assertEquals($expectedArgs, $format->getArguments(), 'invalid arguments parsed');
    }

    public function parseMethodsDataProvider()
    {
        return array(
            // Valid Faker Methods
            array("name()", 'name', array('')),
            array("name('male')", 'name', array('male')),
            array("numberBetween(0)", 'numberBetween', array(0)),
            array("numberBetween(0,10)", 'numberBetween', array(0,10)),
            array("randomElements(['a', 'b', 'c'], 1, false)", 'randomElements', array(array('a', 'b', 'c'), 1, false)),
        );
    }

    /**
     * Validates that parse throws an exception when passed an invalid Faker property or method
     *
     * @dataProvider parseExceptionDataProvider
     * @param string $value Format to parse
     * @expectedException \Exception
     */
    public function testParseShouldThrowException($value)
    {
        $this->parser->parse($value);
    }

    public function parseExceptionDataProvider()
    {
        return array(
            // Unknown Faker Properties
            array('unknownName'),
            array('first name'),

            // fr_FR only properties
            array('region'),
            array('department'),
            array('departmentName'),

            // Invalid Faker Method calls
            array("name("),
            array("unknownMethod(0)"),
            array("numberBetween(0,10,12)"),
        );
    }

}
