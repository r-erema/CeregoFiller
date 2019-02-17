<?php
declare(strict_types = 1);

namespace CeregoFiller\Clients;

interface GettingJsonData
{

    public function getWordJsonData(string $word): string;

}