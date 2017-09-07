# faker-config
FakerConfig is a [Faker](https://github.com/fzaninotto/Faker) extension to populate entities via a simple JSON configuration file.

FakerConfig provides an easy way to configure the format to use when generating data for a given entity/property combination.

[![Build Status](https://travis-ci.org/clabonte/faker-config.svg?branch=master)]

With this extension, one can create a simple JSON configuration file to describe how to format various Entities and their properties.
FakerConfig will parse Faker's Generator PHPDoc used to identify the list of valid formats that can be used by the configuration file and will validate the configuration file against to to reject any format that won't be understood by Faker 

**Table of Contents**

- [Configuration File](#)
- [Step 1. Create the ConfigGuesser](#)
- [Step 2. Load the Configuration File](#)
	- [Alternate Solution: Configure the Guesser Programmatically](#)
- [Step 3. Populate Your Entity](#)
    - [Populate an Object Entity](#)
	- [Populate an Array Entity](#)
	
## Configuration File
The configuration is done via a very simply JSON file that lists out entities to populate as JSON objects ('*' = wildcard). For each entity, you simply list out the properties you want to populate along with the format to use.

Here is a sample configuration file:
```json
{
  "*": {
    "id": "uuid"
  },

  "Book": {
    "id": "isbn10"
  },

  "Entity": {
    "property1": "url",
    "property2": "numberBetween(0,10)"
  },

  "Package\\Entity": {
    "property1": "city",
    "property2": "words(5, true)"
  }
}
```
Sample configuration files are available in the project:
* Simple file: [example-config.json](examples/example-config.json)
* Configuration with class hierarchy: [guesser-object-config.json](tests/fixtures/guesser-object-config.json)


## Step 1. Create the ConfigGuesser
The first step consists in creating a ConfigGuesser object with the generator to use:
```php
$generator = \Faker\Factory::create();
$guesser = new \FakerConfig\ConfigGuesser($generator);
```

## Step 2. Load the Configuration File
Then, you need to tell the guesser the list of entities/properties that need to be formatted when populating data.

The easiest way to do so is by loading your JSON configuration file:
```php
\FakerConfig\ConfigGuesserLoader::loadFile($guesser, 'path_to_your_config.json');
```

### Alternate Solution: Configure the Guesser Programmatically
Alternatively, you can also configure the guesser programmatically using the FormatParser:
```php
$parser = new \FakerConfig\Parser\FormatParser();
$parser->load($guesser->getGenerator());

// You can use any property defined in the Generator's PhpDoc
$format = $parser->parse("firstName");
$guesser->addFormat('Entity', 'property1', $format);

// Or any method defined in the Generator's PhpDoc
$format = $parser->parse("numberBetween(0,10)");
$guesser->addFormat('Entity', 'property2', $format);

// Wildcard define format to use for a given property for any entity
$format = $parser->parse("uuid");
$guesser->addFormat(\Faker\Guesser\ConfigGuesser::WILDCARD, 'id', $format);

// Specific entity/property format will always take precedence over a wildcard format
$format = $parser->parse("isbn10");
$guesser->addFormat('Book', 'id', $format);
```

## Step 3. Populate Your Entity
Once the ConfigGuesser has been properly configured, you can use it with a populator to fill your entity. FakeConfig provides 2 populators to do so:
* ObjectPopulator: To populate an object entity 
* ArrayPopulator: To populate an associative array entity 

### Populate an Object Entity
The ObjectPopulator can be used to populate any object automatically based on its class hierarchy. The populator will scan the object class and all of its ancestor to identify the list of properties that must be populated and apply the format defined in your configuration.
```php
/* Assuming the Book class has the following properties:
   - id
   - property1
   - property2

  And the ConfigGuesser has been configured with the following JSON:
  { 
    "Book": {
        "id": "isbn10",
        "property1": "words(5, true)"
    }
  }
 */

// The following would populate the Book object as follow:
// - 'id' = random ISBN 
// - 'property1' = random string of 5 words
// - 'property2' = no update

$populator = new \FakerConfig\Populator\ObjectPopulator($generator, $guesser);
$book = new Book();
$populator->populate($book); 
```

## Populate an Array Entity
You can also populate any associative array using a similar approach:
```php
$array = array(
    'id' => null,
    'property1' => null,
    'property2' => null,
    'property3' => null);

/*
  Assuming the ConfigGuesser has been configured with the following JSON:
  { 
    "*": {
      "id": "uuid",
    },
    "Entity": {
        "property1": "name",
        "property2": "numberBetween(0,10)"
     }
  }
 */
 
// The following would populate the array as an 'Entity' entity as follow:
// - 'id' = random UUID 
// - 'property1' = random name
// - 'property2' = random number between 0 and 10
// - 'property3' = no update

$populator = new \FakerConfig\Populator\ArrayPopulator($generator, $guesser);
$populator->populate($array, 'Entity'); 
```

