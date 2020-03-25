<?php declare(strict_types=1);

namespace Symplify\MonorepoBuilder\ComposerJsonDecorator;

use Symplify\MonorepoBuilder\Contract\ComposerJsonDecoratorInterface;
use Symplify\PackageBuilder\Yaml\ParametersMerger;

final class AppenderComposerJsonDecorator implements ComposerJsonDecoratorInterface
{
    /**
     * @var mixed[]
     */
    private $dataToAppend = [];

    /**
     * @var ParametersMerger
     */
    private $parametersMerger;

    /**
     * @param mixed[] $dataToAppend
     */
    public function __construct(array $dataToAppend, ParametersMerger $parametersMerger)
    {
        $this->dataToAppend = $dataToAppend;
        $this->parametersMerger = $parametersMerger;
    }

    /**
     * @param mixed[] $composerJson
     * @return mixed[]
     */
    public function decorate(array $composerJson): array
    {
        foreach (array_keys($composerJson) as $key) {
            if (! isset($this->dataToAppend[$key])) {
                continue;
            }

            $composerJson[$key] = $this->parametersMerger->merge($this->dataToAppend[$key], $composerJson[$key]);

            unset($this->dataToAppend[$key]);

            // fix unique repositories
            if ($key === 'repositories') {
                $composerJson[$key] = array_unique($composerJson[$key], SORT_REGULAR);
            }

            // fix unique scripts
            if ($key === 'scripts') {
                foreach ($composerJson[$key] as $scriptKey => $scriptArray) {
                    if (is_array($scriptArray)) {
                        $composerJson[$key][$scriptKey] = array_unique($composerJson[$key][$scriptKey], SORT_REGULAR);
                    }
                }
            }
        }

        // add what was skipped
        return array_merge($composerJson, $this->dataToAppend);
    }
}
