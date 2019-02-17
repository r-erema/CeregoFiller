<?php

namespace CeregoFiller\Decorators;

use CeregoFiller\Entities\LexicalEntry,
    CeregoFiller\Entities\PronunciationFilesGetterInterface,
    CeregoFiller\Entities\OxfordDictionary\LexicalEntry as OxfordDictionaryLexicalEntry;

class OxfordDictionaryEntry implements LexicalEntry, PronunciationFilesGetterInterface
{
    private $entry;

    public function __construct(OxfordDictionaryLexicalEntry $entry)
    {
        $this->entry = $entry;
    }

    public function getLexicalCategory(): string
    {
        return $this->entry->getLexicalCategory();
    }

    public function getSentences(): array
    {
        return array_map(function (string $sentence) {
            $sentence = strtoupper($sentence);
            return "**{$sentence}";
        }, $this->entry->getSentences());
    }

    public function getDefinitions(): array
    {
        return $this->entry->getDefinitions();
    }


    public function getPronunciationFiles(): array
    {
        //return $this->entry->getPronunciationFiles();
        return [];
    }
}