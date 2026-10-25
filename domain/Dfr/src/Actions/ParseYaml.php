<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\YamlResult;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Parses frontmatter YAML. The YAML must be keys and values, or empty.
 */
class ParseYaml
{
    public function __invoke(string $yaml): YamlResult
    {
        try {
            $values = Yaml::parse($yaml) ?? [];
        } catch (ParseException $exception) {
            return new YamlResult(null, $exception->getMessage());
        }

        if (! is_array($values) || ($values !== [] && array_is_list($values))) {
            return new YamlResult(null, 'The frontmatter must be keys and values.');
        }

        return new YamlResult($values, null);
    }
}
