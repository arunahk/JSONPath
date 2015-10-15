<?php

namespace Flow\JSONPath\Test;

require_once __DIR__ . "/../vendor/autoload.php";

use Flow\JSONPath\JSONPath;
use Flow\JSONPath\JSONPathLexer;
use \Peekmo\JsonPath\JsonPath as PeekmoJsonPath;

class JSONPathTest extends \PHPUnit_Framework_TestCase {

    /**
     * $.store.books[0].title
     */
    public function testChildOperators() {
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find('$.store.books[0].title');
        $this->assertEquals('Sayings of the Century', $result[0]);
    }

    /**
     * $['store']['books'][0]['title']
     */
    public function testChildOperatorsAlt() {
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$['store']['books'][0]['title']");
        $this->assertEquals('Sayings of the Century', $result[0]);
    }

    /**
     * $.array[start:end:step]
     */
    public function testFilterSliceA() {
        // Copy all items... similar to a wildcard
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$['store']['books'][:].title");
        $this->assertEquals(array('Sayings of the Century', 'Sword of Honour', 'Moby Dick', 'The Lord of the Rings'), $result->data());
    }

    public function testFilterSliceB() {
        // Fetch every second item starting with the first index (odd items)
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$['store']['books'][1::2].title");
        $this->assertEquals(array('Sword of Honour', 'The Lord of the Rings'), $result->data());
    }

    public function testFilterSliceC() {
        // Fetch up to the second index
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$['store']['books'][0:2:1].title");
        $this->assertEquals(array('Sayings of the Century', 'Sword of Honour', 'Moby Dick'), $result->data());
    }

    public function testFilterSliceD() {
        // Fetch up to the second index
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$['store']['books'][-1:].title");
        $this->assertEquals(array('The Lord of the Rings'), $result->data());
    }

    /**
     * Everything except the last 2 items
     */
    public function testFilterSliceE() {
        // Fetch up to the second index
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$['store']['books'][:-2].title");
        $this->assertEquals(array('Sayings of the Century', 'Sword of Honour'), $result->data());
    }

    /**
     * The Last item
     */
    public function testFilterSliceF() {
        // Fetch up to the second index
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$['store']['books'][-1].title");
        $this->assertEquals(array('The Lord of the Rings'), $result->data());
    }

    /**
     * $.store.books[(@.length-1)].title
     *
     * This notation is only partially implemented eg. hacked in
     */
    public function testChildQuery() {
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$.store.books[(@.length-1)].title");
        $this->assertEquals(array('The Lord of the Rings'), $result->data());
    }

    /**
     * $.store.books[?(@.price < 10)].title
     * Filter books that have a price less than 10
     */
    public function testQueryMatchLessThan() {
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$.store.books[?(@.price < 10)].title");
        $this->assertEquals(array('Sayings of the Century', 'Moby Dick'), $result->data());
    }

    /**
     * $..books[?(@.author == "J. R. R. Tolkien")]
     * Filter books that have a title equal to "..."
     */
    public function testQueryMatchEquals() {
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $results = $jsonPath->find('$..books[?(@.author == "J. R. R. Tolkien")].title');
        $this->assertEquals($results[0], 'The Lord of the Rings');
    }

    /**
     * $.store.books[*].author
     */
    public function testWildcardAltNotation() {
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$.store.books[*].author");
        $this->assertEquals(array('Nigel Rees', 'Evelyn Waugh', 'Herman Melville', 'J. R. R. Tolkien'), $result->data());
    }

    /**
     * $..author
     */
    public function testRecursiveChildSearch() {
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$..author");
        $this->assertEquals(array('Nigel Rees', 'Evelyn Waugh', 'Herman Melville', 'J. R. R. Tolkien'), $result->data());
    }

    /**
     * $.store.*
     * all things in store
     * the structure of the example data makes this test look weird
     */
    public function testWildCard() {
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$.store.*");
        if (is_object($result[0][0])) {
            $this->assertEquals('Sayings of the Century', $result[0][0]->title);
        } else {
            $this->assertEquals('Sayings of the Century', $result[0][0]['title']);
        }

        if (is_object($result[1])) {
            $this->assertEquals('red', $result[1]->color);
        } else {
            $this->assertEquals('red', $result[1]['color']);
        }
    }

    /**
     * $.store..price
     * the price of everything in the store.
     */
    public function testRecursiveChildSearchAlt() {
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$.store..price");
        $this->assertEquals(array(8.95, 12.99, 8.99, 22.99, 19.95), $result->data());
    }

    /**
     * $..books[2]
     * the third book
     */
    public function testRecursiveChildSearchWithChildIndex() {
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$..books[2].title");
        $this->assertEquals(array("Moby Dick"), $result->data());
    }

    /**
     * $..books[(@.length-1)]
     */
    public function testRecursiveChildSearchWithChildQuery() {
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$..books[(@.length-1)].title");
        $this->assertEquals(array("The Lord of the Rings"), $result->data());
    }

    /**
     * $..books[-1:]
     * Resturn the last results
     */
    public function testRecursiveChildSearchWithSliceFilter() {
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$..books[-1:].title");
        $this->assertEquals(array("The Lord of the Rings"), $result->data());
    }

    /**
     * $..books[?(@.isbn)]
     * filter all books with isbn number
     */
    public function testRecursiveWithQueryMatch() {
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$..books[?(@.isbn)].isbn");

        $this->assertEquals(array('0-553-21311-3', '0-395-19395-8'), $result->data());
    }

    /**
     * $..*
     * All members of JSON structure
     */
    public function testRecursiveWithWildcard() {
        $jsonPath = new JSONPath($this->exampleData(rand(0, 1)));
        $result = $jsonPath->find("$..*");
        $result = json_decode(json_encode($result), true);

        $this->assertEquals('Sayings of the Century', $result[0]['books'][0]['title']);
        $this->assertEquals(19.95, $result[26]);
    }

    /**
     * Tests direct key access.
     */
    public function testSimpleArrayAccess() {
        $jsonPath = new JSONPath(array('title' => 'test title'));
        $result = $jsonPath->find('title');

        $this->assertEquals(array('test title'), $result->data());
    }

    public function testFilteringOnNoneArrays() {
        $data = array('foo' => 'asdf');

        $jsonPath = new JSONPath($data);
        $result = $jsonPath->find("$.foo.bar");
        $this->assertEquals(array(), $result->data());
    }

    public function testMagicMethods() {
        $fooClass = new JSONPathTestClass();

        $jsonPath = new JSONPath($fooClass, JSONPath::ALLOW_MAGIC);
        $results = $jsonPath->find('$.foo');

        $this->assertEquals(array('bar'), $results->data());
    }

    public function testMatchWithComplexSquareBrackets() {
        $jsonPath = new JSONPath($this->exampleDataExtra());
        $result = $jsonPath->find("$['http://www.w3.org/2000/01/rdf-schema#label'][?(@['@language']='en')]['@language']");
        $this->assertEquals(array("en"), $result->data());
    }

    public function testQueryMatchWithRecursive() {
        $locations = $this->exampleDataLocations();
        $jsonPath = new JSONPath($locations);
        $result = $jsonPath->find("..[?(@.type == 'suburb')].name");
        $this->assertEquals(array("Rosebank"), $result->data());
    }

    public function testFirst() {
        $jsonPath = new JSONPath($this->exampleDataExtra());
        $result = $jsonPath->find("$['http://www.w3.org/2000/01/rdf-schema#label'].*");

        $this->assertEquals(array("@language" => "en"), $result->first()->data());
    }

    public function testLast() {
        $jsonPath = new JSONPath($this->exampleDataExtra());
        $result = $jsonPath->find("$['http://www.w3.org/2000/01/rdf-schema#label'].*");
        $this->assertEquals(array("@language" => "de"), $result->last()->data());
    }

    public function testSlashesInIndex() {
        $jsonPath = new JSONPath($this->exampleDataWithSlashes());
        $result = $jsonPath->find("$['mediatypes']['image/png']");
        
        $this->assertEquals(
                array(
            "/core/img/filetypes/image.png",
                ), $result->data()
        );
    }

    public function testOffsetUnset() {
        $data = array(
            "route" => array(
                array("name" => "A", "type" => "type of A"),
                array("name" => "B", "type" => "type of B")
            )
        );
        $data = json_encode($data);

        $jsonIterator = new JSONPath(json_decode($data));

        /** @var JSONPath $route */
        $route = $jsonIterator->offsetGet('route');

        $route->offsetUnset(0);

        $first = $route->first();

        $this->assertEquals("B", $first['name']);
    }

    public function testFirstKey() {
        // Array test for array
        $jsonPath = new JSONPath(array('a' => 'A', 'b', 'B'));

        $firstKey = $jsonPath->firstKey();

        $this->assertEquals('a', $firstKey);

        // Array test for object
        $jsonPath = new JSONPath((object) array('a' => 'A', 'b', 'B'));

        $firstKey = $jsonPath->firstKey();

        $this->assertEquals('a', $firstKey);
    }

    public function testLastKey() {
        // Array test for array
        $jsonPath = new JSONPath(array('a' => 'A', 'b' => 'B', 'c' => 'C'));

        $lastKey = $jsonPath->lastKey();

        $this->assertEquals('c', $lastKey);

        // Array test for object
        $jsonPath = new JSONPath((object) array('a' => 'A', 'b' => 'B', 'c' => 'C'));

        $lastKey = $jsonPath->lastKey();

        $this->assertEquals('c', $lastKey);
    }

    public function exampleData($asArray = true) {
        $json = '
        {
          "store":{
            "books":[
              {
                "category":"reference",
                "author":"Nigel Rees",
                "title":"Sayings of the Century",
                "price":8.95
              },
              {
                "category":"fiction",
                "author":"Evelyn Waugh",
                "title":"Sword of Honour",
                "price":12.99
              },
              {
                "category":"fiction",
                "author":"Herman Melville",
                "title":"Moby Dick",
                "isbn":"0-553-21311-3",
                "price":8.99
              },
              {
                "category":"fiction",
                "author":"J. R. R. Tolkien",
                "title":"The Lord of the Rings",
                "isbn":"0-395-19395-8",
                "price":22.99
              }
            ],
            "bicycle":{
              "color":"red",
              "price":19.95
            }
          }
        }';
        return json_decode($json, $asArray);
    }

    public function exampleDataExtra($asArray = true) {
        $json = '
            {
               "http://www.w3.org/2000/01/rdf-schema#label":[
                  {
                     "@language":"en"
                  },
                  {
                     "@language":"de"
                  }
               ]
            }
        ';

        return json_decode($json, $asArray);
    }

    public function exampleDataLocations($asArray = true) {
        $json = '
            {
               "name": "Gauteng",
               "type": "province",
               "child": {
                    "name": "Johannesburg",
                    "type": "city",
                    "child": {
                        "name": "Rosebank",
                        "type": "suburb"
                    }
               }
            }
        ';

        return json_decode($json, $asArray);
    }

    public function exampleDataWithSlashes($asArray = true) {
        $json = '
            {
                "features": [],
                "mediatypes": {
                    "image/png": "/core/img/filetypes/image.png",
                    "image/jpeg": "/core/img/filetypes/image.png",
                    "image/gif": "/core/img/filetypes/image.png",
                    "application/postscript": "/core/img/filetypes/image-vector.png"
                }
            }
        ';

        return json_decode($json, $asArray);
    }

}

class JSONPathTestClass {

    protected $attributes = array(
        'foo' => 'bar'
    );

    public function __get($key) {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
    }

}
