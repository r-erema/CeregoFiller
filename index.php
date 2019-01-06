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

$strategy = new CambridgeDictionary(new Crawler());
$grabber = new DataGrabber($strategy);
$data = [];
foreach ($words as $word) {
    $strategy->setWordToFind($word);
    $data[] = $grabber->grabRemotely(new Client());
}

$toCSV = [
    ['anchor_text', 'association_text']
];
$maxSentencesCols = 0;
foreach ($data as $row) {

    if (!isset($row['English'])) {
        continue;
    }

    foreach ($row['English'] as $word) {
        $anchorText = "{$word[CambridgeDictionary::DATA_KEY_WORD]} ({$word[CambridgeDictionary::DATA_KEY_TYPE_OF_WORD]})";

        $associationText = '';
        $examples = [];

        foreach ($word[CambridgeDictionary::DATA_KEY_MEANINGS_AND_EXAMPLES] as $i =>$meaningsAndExamples) {
            $number = count($word[CambridgeDictionary::DATA_KEY_MEANINGS_AND_EXAMPLES]) > 1 ? $i + 1 . '. ' : '';
            $associationText .= "{$number}{$meaningsAndExamples[CambridgeDictionary::DATA_KEY_MEANING]}" . PHP_EOL;

            $examples[] = $meaningsAndExamples[CambridgeDictionary::DATA_KEY_EXAMPLES];
        }

        /** @noinspection SlowArrayOperationsInLoopInspection */
        $examples = array_merge(...$examples);
        $examplesCount = count($examples);
        $maxSentencesCols = $examplesCount > $maxSentencesCols ? $examplesCount : $maxSentencesCols;
        $maxSentencesCols = $maxSentencesCols > 9 ? 9 : $maxSentencesCols;

        $examples = array_slice($examples, 0, 9);

        for ($i = 0; $i < 9; $i++) {
            if (!isset($examples[$i])) {
                $examples[$i] = ' ';
            }
        }

        foreach ($examples as &$example) {
            $example = str_replace($word[CambridgeDictionary::DATA_KEY_WORD], "*{$word[CambridgeDictionary::DATA_KEY_WORD]}*", $example);
        }
        unset($example);

        $csvRow = [$anchorText, $associationText];
        foreach ($examples as $example) {
            $csvRow[] = $example;
        }
        $toCSV[] = $csvRow;
    }
}

for ($i = 1; $i <= $maxSentencesCols; $i++) {
    $toCSV[0][] = "sentence_{$i}_text";
}

$fp = fopen('result.csv', 'wb');

foreach ($toCSV as $fields) {
    fputcsv($fp, $fields);
}

fclose($fp);

echo 'Done', PHP_EOL;