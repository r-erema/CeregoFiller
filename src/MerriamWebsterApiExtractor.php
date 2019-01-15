<?php

namespace CeregoFiller;

use CeregoFiller\Utils\Helpers;

class MerriamWebsterApiExtractor
{

	private $data;

	public function getDefinitions(): array
	{
		return $this->data['shortdef'];
	}

	public function getExamples(): array
	{
		$definitions = [];
		foreach (Helpers::recursiveFindInArray($this->data['def'], 't') as $definition) {
			$definitions[] = str_replace(['{wi}', '{/wi}', '{it}', '{/it}', '{phrase}', '{/phrase}'], '*', $definition);
		}
		return $definitions;
	}

	public function getPartOfSpeech(): string
	{
		return $this->data['fl'];
	}

	public function setData(array $data): void
	{
		$this->data = $data;
	}

}