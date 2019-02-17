<?php

declare(strict_types = 1);

use CeregoFiller\Clients\OxfordClient,
    CeregoFiller\Extractors\OxfordExtractor,
    CeregoFiller\GoogleClient,
    CeregoFiller\Utils\Helpers;

require __DIR__ . '/vendor/autoload.php';
$config = require __DIR__ . '/configs/config.php';

/** @noinspection PhpUnhandledExceptionInspection */
$client = GoogleClient::create(__DIR__ . '/configs/credentials.json', __DIR__ . '/configs/token.json');
$service = new Google_Service_Drive($client);

/** @var \GuzzleHttp\Psr7\Response $response */
$response = $service->files->export($config['googleDriveFileId'], 'text/csv');
$content = (string) $response->getBody();
$words = explode("\r\n", $content);

$client = new OxfordClient('en', $config['oxford_app_id'], $config['oxford_app_key']);
$extractor = new OxfordExtractor();

$preparedWords = [];
foreach ($words as $word) {

    try {
        $json = $client->getWordJsonData($word);
    } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        continue;
    }
    $extractor->setWordJsonSourceData($json);

    $word = $extractor->getSourceWord();
    $lexicalEntries = $extractor->getLexicalEntries();
    foreach ($lexicalEntries as $lexicalEntry) {
        $preparedWords[$word][] = [
            'partOfSpeech' => $lexicalEntry->getLexicalCategory(),
            'examples' => $lexicalEntry->getSentences(),
            'definitions' => $lexicalEntry->getDefinitions(),
            'sounds' => $lexicalEntry->getPronunciationFiles()
        ];
    }


}

$maxExamplesCount = Helpers::getRecursiveMaxCountItemsInKey($preparedWords, 'examples');
$maxExamplesCount = $maxExamplesCount > 9 ? 9 : $maxExamplesCount;

$maxSoundsCount = Helpers::getRecursiveMaxCountItemsInKey($preparedWords, 'sounds');
$maxSoundsCount = $maxSoundsCount > 9 ? 9 : $maxSoundsCount;

$toCSV = [
    ['anchor_text', 'association_text']
];

for ($i = 1; $i <= $maxExamplesCount; $i++) {
    $toCSV[0][] = "sentence_{$i}_text";
}

for ($i = 1; $i <= $maxSoundsCount; $i++) {
    $toCSV[0][] = "association_{$i}_sound";
}


foreach ($preparedWords as $word => $data) {

    try {
        $inflections = $client->getLemmaJsonData($word);
    } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        continue;
    }

    foreach ($data as $dataRow) {
        $csvRow = [
            "{$word} [{$dataRow['partOfSpeech']}]",
            Helpers::createListString($dataRow['definitions']),
        ];
        $examples = array_slice($dataRow['examples'], 0, $maxExamplesCount);
        for ($i = 0; $i < $maxExamplesCount; $i++) {
            $csvRow[] = $examples[$i] ?? ' ';
        }

        if ($maxSoundsCount > 0) {
            $sounds = array_slice($dataRow['sounds'], 0, $maxSoundsCount);
            for ($i = 0; $i < $maxSoundsCount; $i++) {
                $csvRow[] = $sounds[$i] ?? ' ';
            }
        }

        $toCSV[] = $csvRow;
    }
}

$fp = fopen('result.csv', 'wb');

foreach ($toCSV as $fields) {
    fputcsv($fp, $fields);
}

fclose($fp);

echo 'Done', PHP_EOL;