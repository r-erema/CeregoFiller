<?php

declare(strict_types = 1);

use CeregoFiller\GoogleClient,
    DataGrabber\DataGrabber,
    DataGrabber\Strategies\CambridgeDictionary,
    GuzzleHttp\Client,
    Symfony\Component\DomCrawler\Crawler;

require __DIR__ . '/vendor/autoload.php';
$config = require __DIR__ . '/configs/config.php';

/** @noinspection PhpUnhandledExceptionInspection */
$client = GoogleClient::create(__DIR__ . '/configs/credentials.json', __DIR__ . '/configs/token.json');
$service = new Google_Service_Drive($client);

/** @var \GuzzleHttp\Psr7\Response $response */
$response = $service->files->export($config['googleDriveFileId'], 'text/csv');
$content = (string) $response->getBody();
$words = explode("\r\n", $content);

$strategy = new CambridgeDictionary('', new Crawler());
$grabber = new DataGrabber($strategy);
$data = [];
foreach ($words as $word) {
    $strategy->setWordToFind($word);
    $data[] = $grabber->grabRemotely(new Client());
}
$f = 1;