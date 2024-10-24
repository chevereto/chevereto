<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use PhpCsFixer\Fixer\Alias\NoAliasFunctionsFixer;
use PhpCsFixer\Fixer\Alias\NoAliasLanguageConstructCallFixer;
use PhpCsFixer\Fixer\Alias\NoMixedEchoPrintFixer;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoMultilineWhitespaceAroundDoubleArrowFixer;
use PhpCsFixer\Fixer\ArrayNotation\NormalizeIndexBraceFixer;
use PhpCsFixer\Fixer\Casing\IntegerLiteralCaseFixer;
use PhpCsFixer\Fixer\Casing\LowercaseStaticReferenceFixer;
use PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer;
use PhpCsFixer\Fixer\Casing\MagicMethodCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionTypeDeclarationCasingFixer;
use PhpCsFixer\Fixer\CastNotation\NoShortBoolCastFixer;
use PhpCsFixer\Fixer\CastNotation\NoUnsetCastFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentStyleFixer;
use PhpCsFixer\Fixer\ControlStructure\IncludeFixer;
use PhpCsFixer\Fixer\ControlStructure\NoAlternativeSyntaxFixer;
use PhpCsFixer\Fixer\FunctionNotation\LambdaNotUsedImportFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Import\SingleImportPerStatementFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveUnsetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\SingleSpaceAroundConstructFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use PhpCsFixer\Fixer\NamespaceNotation\CleanNamespaceFixer;
use PhpCsFixer\Fixer\Operator\NoSpaceAroundDoubleColonFixer;
use PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\StandardizeNotEqualsFixer;
use PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer;
use PhpCsFixer\Fixer\ReturnNotation\NoUselessReturnFixer;
use PhpCsFixer\Fixer\ReturnNotation\ReturnAssignmentFixer;
use PhpCsFixer\Fixer\Semicolon\MultilineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Whitespace\CompactNullableTypehintFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
use PhpCsFixer\Fixer\Whitespace\TypesSpacesFixer;
use Symplify\CodingStandard\Fixer\Commenting\ParamReturnAndVarTagMalformsFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->parallel();
    $headerFile = __DIR__ . '/.header';
    $ecsConfig->sets([SetList::PSR_12, SetList::COMMON]);
    if (file_exists($headerFile)) {
        $ecsConfig->ruleWithConfiguration(HeaderCommentFixer::class, [
            'header' => file_get_contents($headerFile),
            'location' => 'after_open',
        ]);
    }
    $ecsConfig->rule(TypesSpacesFixer::class);
    $ecsConfig->rule(NoUselessReturnFixer::class);
    $ecsConfig->rule(LinebreakAfterOpeningTagFixer::class);
    $ecsConfig->rule(StandardizeNotEqualsFixer::class);
    $ecsConfig->rule(NoSpaceAroundDoubleColonFixer::class);
    $ecsConfig->rule(CleanNamespaceFixer::class);
    $ecsConfig->rule(ListSyntaxFixer::class);
    $ecsConfig->rule(SingleSpaceAroundConstructFixer::class);
    $ecsConfig->rule(LambdaNotUsedImportFixer::class);
    $ecsConfig->rule(NoAlternativeSyntaxFixer::class);
    $ecsConfig->rule(NoUnsetCastFixer::class);
    $ecsConfig->rule(NoShortBoolCastFixer::class);
    $ecsConfig->rule(NativeFunctionTypeDeclarationCasingFixer::class);
    $ecsConfig->rule(NativeFunctionCasingFixer::class);
    $ecsConfig->rule(MagicMethodCasingFixer::class);
    $ecsConfig->rule(MagicConstantCasingFixer::class);
    $ecsConfig->rule(LowercaseStaticReferenceFixer::class);
    $ecsConfig->rule(IntegerLiteralCaseFixer::class);
    $ecsConfig->rule(NormalizeIndexBraceFixer::class);
    $ecsConfig->rule(NoMultilineWhitespaceAroundDoubleArrowFixer::class);
    $ecsConfig->rule(BlankLineBeforeStatementFixer::class);
    $ecsConfig->rule(CombineConsecutiveUnsetsFixer::class);
    $ecsConfig->rule(CompactNullableTypehintFixer::class);
    $ecsConfig->rule(DeclareStrictTypesFixer::class);
    $ecsConfig->rule(IncludeFixer::class);
    $ecsConfig->rule(MultilineWhitespaceBeforeSemicolonsFixer::class);
    $ecsConfig->rule(NoAliasFunctionsFixer::class);
    $ecsConfig->rule(NoAliasLanguageConstructCallFixer::class);
    $ecsConfig->rule(NoEmptyStatementFixer::class);
    $ecsConfig->rule(NoMixedEchoPrintFixer::class);
    $ecsConfig->rule(ObjectOperatorWithoutWhitespaceFixer::class);
    $ecsConfig->rule(ParamReturnAndVarTagMalformsFixer::class);
    $ecsConfig->rule(ReturnAssignmentFixer::class);
    $ecsConfig->ruleWithConfiguration(SingleLineCommentStyleFixer::class, [
        'comment_types' => ['hash'],
    ]);
    $ecsConfig->rule(SingleQuoteFixer::class);
    $ecsConfig->ruleWithConfiguration(OrderedImportsFixer::class, [
        'imports_order' => ['class', 'function', 'const'],
    ]);
    $ecsConfig->ruleWithConfiguration(ArraySyntaxFixer::class, [
        'syntax' => 'short',
    ]);
    $ecsConfig->ruleWithConfiguration(NoExtraBlankLinesFixer::class, [
        'tokens' => [
            'curly_brace_block',
            'extra',
            'parenthesis_brace_block',
            'square_brace_block',
            'throw',
            'use',
        ],
    ]);
    $ecsConfig->skip([
        SingleImportPerStatementFixer::class => null,
    ]);
};
