<?php
declare(strict_types = 1);

namespace CeregoFiller\Extractors;

use CeregoFiller\Decorators\OxfordDictionaryCeregoEntry as Decorator,
    CeregoFiller\Entities\OxfordDictionary\LexicalEntry;

class OxfordExtractor implements ExtractorInterface, JsonSourceDataSetter
{

    private $wordData;

    private $lexicalEntries;

    public function setWordJsonSourceData(string $wordJsonSourceData): void
    {
        $this->wordData = json_decode($wordJsonSourceData, true);
        $this->lexicalEntries = [];
        foreach ($this->wordData['results'][0]['lexicalEntries'] as $lexicalEntry) {
            $lexicalEntryInstance = new Decorator(new LexicalEntry($lexicalEntry));
            $this->lexicalEntries[] = $lexicalEntryInstance;
        }
    }

    public function getSourceWord(): string
    {
        return $this->wordData['results'][0]['word'];
    }

    /** @return Decorator[] */
    public function getLexicalEntries(): array
    {
        return $this->lexicalEntries;
    }

}