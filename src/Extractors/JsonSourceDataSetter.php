<?php
declare(strict_types = 1);

namespace CeregoFiller\Extractors;

interface JsonSourceDataSetter
{

    public function setWordJsonSourceData(string $wordJsonSourceData): void;

}