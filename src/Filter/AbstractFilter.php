<?php

namespace MonterHealth\ApiFilterBundle\Filter;

abstract class AbstractFilter implements Filter
{
	protected $joins = [];

	/**
	 * @param string $targetTableAlias
	 * @param string $property
	 * @param array $embedded
	 * @return string
	 */
	protected function determineTarget(string $targetTableAlias, string $property, $embedded = []): string
	{

		if ($this->isPropertyNested($property) && !$this->isPropertyEmbedded($property, $embedded)) {

			$target = $this->extractTargetFromProperty($property);

			$this->extractJoinsFromProperty($property);

		} else {

			$target = $targetTableAlias . '.' . $property;
		}

		return $target;
	}

	/**
	 * @param $property
	 * @return bool
	 */
	protected function isPropertyNested($property): bool
	{
		$pos = strpos($property, '.');
		return (false !== $pos);
	}

	private function isPropertyEmbedded(string $property, array $embedded)
	{
		if (!strpos($property, '.')) {
			return false;
		}

		return in_array(explode('.', $property)[0], $embedded);
	}

	/**
	 * Correct for nested joins: target must only be the last part
	 * rootAlias.nestedAlias.parameterName >> nestedAlias.parameterName
	 * @param string $property
	 * @return string
	 */
	protected function extractTargetFromProperty(string $property)
	{
		if (!strpos($property, '.')) {
			return $property;
		}
		$parts = array_reverse(explode('.', $property));
		return $parts[1] . '.' . $parts[0];
	}

	protected function extractJoinsFromProperty(string $property): void
	{
		if (!strpos($property, '.')) {
			return;
		}
		$joins = explode('.', $property);
		// remove last = property
		array_pop($joins);

		$previous = null;
		foreach ($joins as $join) {
			if ($previous) {
				$this->joins[$previous] = $join;
			} else {
				$this->joins[] = $join;
			}
			$previous = $join;
		}
	}
}