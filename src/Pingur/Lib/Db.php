<?php
namespace Pingur\Lib;

use InfluxDB\Point;
use InfluxDB\Client;
use InfluxDB\Driver\Guzzle;
use InfluxDB\Exception;
use InfluxDB\Database;
use InfluxDB\ResultSet;

class Db
{
    public function publish($config, $measurement) {


        $client = new Client($config['url'], $config['port']);
        $database = $client->selectDB($config['db']);

        $points = [
            new Point(
                $measurement['tag'], // name of the measurement
                $measurement['domain'], // the measurement value
                ['host' => $measurement['domain']], // optional tags
                [
                    'status' => $measurement['info']['status'],
                    'expiration' => $measurement['info']['expiration'],
                    'issuer' => $measurement['info']['issuer'],
                    'created' => $measurement['info']['created'],
                ],
                time()
            ),
        ];
        $result = $database->writePoints($points, Database::PRECISION_SECONDS);
    }
}
