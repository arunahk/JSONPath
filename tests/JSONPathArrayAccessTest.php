<?php
namespace Flow\JSONPath\Test;

use Flow\JSONPath\JSONPath;

require_once __DIR__ . "/../vendor/autoload.php";

class JSONPathArrayAccessTest extends \PHPUnit_Framework_TestCase
{
    public function testChaining()
    {
        $data = $this->exampleData(rand(0, 1));
        $jsonPath = new JSONPath($data);
        $conferences = $jsonPath->find('.conferences.*');
        $teams = $conferences->find('..teams.*');

        $this->assertEquals('Dodger', $teams[0]['name']);
        $this->assertEquals('Mets', $teams[1]['name']);

        $teams = $jsonPath->find('.conferences.*')->find('..teams.*');

        $this->assertEquals('Dodger', $teams[0]['name']);
        $this->assertEquals('Mets', $teams[1]['name']);

        $teams = $jsonPath->find('.conferences..teams.*');

        $this->assertEquals('Dodger', $teams[0]['name']);
        $this->assertEquals('Mets', $teams[1]['name']);
    }

    public function testIterating()
    {
        $data = $this->exampleData(rand(0, 1));

        $jsonPath = new JSONPath($data);
        $conferences = $jsonPath->find('.conferences.*');

        $names = array();

        foreach ($conferences as $conference) {
            $players = $conference->find('.teams.*.players[?(@.active=yes)]');

            foreach ($players as $player) {
                $names[] = $player->name;
            }
        }

        $this->assertEquals(['Joe Face', 'something'], $names);
    }

    public function testDifferentStylesOfAccess()
    {
        $data = $this->exampleData(rand(0, 1));

        $league = new JSONPath($data);

        $conferences = $league->conferences;
        $firstConference = $league->conferences[0];

        $this->assertEquals('Western Conference', $firstConference->name);
    }

    public function exampleData($asArray = true)
    {
        $data = array(
            'name'        => 'Major League Baseball',
            'abbr'        => 'MLB',
            'conferences' => array(
                array(
                    'name'  => 'Western Conference',
                    'abbr'  => 'West',
                    'teams' => array(
                        array(
                            'name'     => 'Dodger',
                            'city'     => 'Los Angeles',
                            'whatever' => 'else',
                            'players'  => array(
                                array('name' => 'Bob Smith', 'number' => 22),
                                array('name' => 'Joe Face', 'number' => 23, 'active' => 'yes'),
                            ),
                        )
                    ),
                ),
                array(
                    'name'  => 'Eastern Conference',
                    'abbr'  => 'East',
                    'teams' => array(
                        array(
                            'name'     => 'Mets',
                            'city'     => 'New York',
                            'whatever' => 'else',
                            'players'  => array(
                                array('name' => 'something', 'number' => 14, 'active' => 'yes'),
                                array('name' => 'something', 'number' => 15),
                            )
                        )
                    )
                )
            )
        );

        return $asArray ? $data : json_decode(json_encode($data));

    }
}
