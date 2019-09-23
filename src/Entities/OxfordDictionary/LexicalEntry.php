<?php
declare(strict_types = 1);

namespace CeregoFiller\Entities\OxfordDictionary;

use CeregoFiller\Entities\LexicalEntry as LexicalEntryInterface,
    CeregoFiller\Entities\PronunciationFilesGetterInterface;

class LexicalEntry implements LexicalEntryInterface, PronunciationFilesGetterInterface
{

    private $entryData;

    public function __construct(array $entryData)
    {
        $this->entryData = $entryData;
    }

    public function getLexicalCategory(): string
    {
        return strtolower($this->entryData['lexicalCategory']['text']);
    }

    public function getSentences(): array
    {
        $sentences = array_map(static function (array $sense): array {
            if (isset($sense['examples'])) {
                return array_map(static function (array $example): string {
                    return $example['text'];
                }, $sense['examples']);
            }
            return [];
        }, $this->entryData['entries'][0]['senses']);
        return array_merge(...$sentences);
    }

    public function getDefinitions(): array
    {
        $definitions = array_map(function (array $sense): array {
            return $sense['definitions'] ?? [];
        }, $this->entryData['entries'][0]['senses']);
        return array_merge(...$definitions);
    }

    public function getPronunciationFiles(): array
    {
        return array_map(function (array $pronunciation): string {
            return $pronunciation['audioFile'];
        }, $this->entryData['pronunciations']);
    }
}