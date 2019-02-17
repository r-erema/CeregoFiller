<?php
declare(strict_types = 1);

namespace CeregoFiller\Extractors;

use CeregoFiller\Entities\LexicalEntry;

interface ExtractorInterface
{

    public function getSourceWord(): string;

    /** @return LexicalEntry[] array */
    public function getLexicalEntries(): array;

}