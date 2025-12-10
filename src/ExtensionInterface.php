<?php
declare(strict_types=1);
namespace FediE2EE\PKD\Extensions;

interface ExtensionInterface
{
    /** @api */
    public function getAuxDataType(): string;

    /** @api */
    public function getRejectionReason(): string;

    /** @api */
    public function isValid(string $auxData): bool;
}
