<?php

namespace App\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\LogRecord;

class Utf8StreamHandler extends StreamHandler
{
    protected ?string $errorMessage = null;

    protected function write(LogRecord $record): void
    {
        if (!is_resource($this->stream)) {
            if (null === $this->url || '' === $this->url) {
                throw new \LogicException('Missing stream url, the stream can not be opened. This may be caused by a premature call to close().');
            }
            
            // Create directory if it doesn't exist
            $dir = dirname($this->url);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            $this->errorMessage = null;
            set_error_handler($this->streamExceptionHandler(...));
            $stream = fopen($this->url, 'a');
            if ($this->filePermission !== null) {
                @chmod($this->url, $this->filePermission);
            }
            restore_error_handler();
            if (!is_resource($stream)) {
                $this->stream = null;

                throw new \UnexpectedValueException(sprintf('The stream or file "%s" could not be opened: ' . $this->errorMessage, $this->url));
            }
            stream_set_chunk_size($stream, $this->streamChunkSize);
            $this->stream = $stream;
        }

        $line = $this->formatter->format($record);
        
        // Ensure UTF-8 encoding
        if (!mb_check_encoding($line, 'UTF-8')) {
            $line = mb_convert_encoding($line, 'UTF-8', 'auto');
        }
        
        fwrite($this->stream, $line);
    }

    /**
     * Handle stream exceptions
     */
    protected function streamExceptionHandler(int $code, string $message): bool
    {
        $this->errorMessage = $message;
        return true;
    }
}
