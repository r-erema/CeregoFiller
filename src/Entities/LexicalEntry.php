<?php
declare(strict_types = 1);

namespace CeregoFiller\Entities;

interface LexicalEntry
{

    public function getLexicalCategory(): string;

    public function getSentences(): array;

    public function getDefinitions(): array;

}