<?php

declare(strict_types = 1);

use CeregoFiller\GoogleClient;
    use CeregoFiller\Utils\Helpers;

    require __DIR__ . '/vendor/autoload.php';
$config = require __DIR__ . '/configs/config.php';

/** @noinspection PhpUnhandledExceptionInspection */
$client = GoogleClient::create(__DIR__ . '/configs/credentials.json', __DIR__ . '/configs/token.json');
$service = new Google_Service_Drive($client);

/** @var \GuzzleHttp\Psr7\Response $response */
$response = $service->files->export($config['googleDriveFileId'], 'text/csv');
$content = (string) $response->getBody();
$words = explode("\r\n", $content);

$extractor = new \CeregoFiller\MerriamWebsterApiExtractor();

$preparedWords = [];
foreach ($words as $word) {

    $json = file_get_contents(sprintf($config['merriam-webster-api-url-tpl'], urlencode($word), urlencode($word)));
    $wordData = json_decode($json, true);

    foreach ($wordData as $data) {

        if (!isset($data['def'])) {
            continue;
        }

        $extractor->setData($data);
        $examples = $extractor->getExamples();
        if (count($examples) > 0) {
            $definitions = $extractor->getDefinitions();
            $partOfSpeech = $extractor->getPartOfSpeech();
            $preparedWords[$data['meta']['stems'][0]][] = [
                'examples' => $examples,
                'definitions' => $definitions,
                'partOfSpeech' => $partOfSpeech
            ];
        }
    }
}

$maxExamplesCount = Helpers::getRecursiveMaxCountItemsInKey($preparedWords, 'examples');
$maxExamplesCount = $maxExamplesCount > 9 ? 9 : $maxExamplesCount;

$toCSV = [
    ['anchor_text', 'association_text']
];

for ($i = 1; $i <= $maxExamplesCount; $i++) {
    $toCSV[0][] = "sentence_{$i}_text";
}

foreach ($preparedWords as $word => $data) {
    foreach ($data as $dataRow) {
        $csvRow = [
            "{$word} [{$dataRow['partOfSpeech']}]",
            Helpers::createListString($dataRow['definitions']),
        ];
        $examples = array_slice($dataRow['examples'], 0, $maxExamplesCount);
        for ($i = 0; $i < $maxExamplesCount; $i++) {
            $csvRow[] = $examples[$i] ?? ' ';
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