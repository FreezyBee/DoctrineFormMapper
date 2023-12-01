<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);
    $ecsConfig->fileExtensions(['php', 'phpt']);

    $ecsConfig->sets([
        // run and fix, one by one
        SetList::PSR_12,
        SetList::ARRAY,
        SetList::CLEAN_CODE,
        SetList::STRICT,
        SetList::NAMESPACES,
        SetList::DOCBLOCK,
        SetList::SPACES,
    ]);

    $ecsConfig->skip([
        NotOperatorWithSuccessorSpaceFixer::class,
    ]);
};
