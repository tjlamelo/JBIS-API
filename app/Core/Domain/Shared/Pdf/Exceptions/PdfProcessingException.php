<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Pdf\Exceptions;

use Throwable;

final class PdfProcessingException extends PdfException
{
    public static function fromTool(string $tool, Throwable $previous): self
    {
        return new self(
            sprintf('iLovePDF "%s" task failed: %s', $tool, $previous->getMessage()),
            (int) $previous->getCode(),
            $previous
        );
    }

    public static function fileNotFound(string $path): self
    {
        return new self(sprintf('PDF input file not found: %s', $path));
    }

    public static function emptyFileList(string $tool): self
    {
        return new self(sprintf('At least one file is required for the "%s" task.', $tool));
    }

    public static function invalidOutputDirectory(string $path): self
    {
        return new self(sprintf('Unable to create or write to output directory: %s', $path));
    }
}
