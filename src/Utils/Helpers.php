<?php
declare(strict_types = 1);

namespace CeregoFiller\Utils;

class Helpers
{

	public static function recursiveFindInArray(array $haystack, $needle): ?\Generator
	{
		$iterator = new \RecursiveArrayIterator($haystack);
		$recursive = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($recursive as $key => $value) {
			if ($key === $needle) {
				yield $value;
			}
		}
	}

	public static function getRecursiveMaxCountItemsInKey(array $array, $needle): int
	{
		$maxCount = 0;
		$iterator = new \RecursiveArrayIterator($array);
		$recursive = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($recursive as $key => $value) {
			if ($key === $needle) {
				$currentCount = is_countable($value) ? count($value) : 1;
				$maxCount = $maxCount < $currentCount ? $currentCount : $maxCount;
			}
		}
		return $maxCount;
	}

	public static function recursiveMergeByKey(array $array, $needle): array
	{
		$toMerge = [];
		$iterator = new \RecursiveArrayIterator($array);
		$recursive = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($recursive as $key => $value) {
			if ($key === $needle) {
				$toMerge[] = $value;
			}
		}
		return array_merge(...$toMerge);
	}

	public static function createListString(array $array, string $rowPostfix = PHP_EOL): string
	{
		$list = '';
		foreach ($array as $i => $item) {
			$number = $i + 1;
			$list .= "{$number}. {$item}{$rowPostfix}";
		}
		return $list;
	}
}