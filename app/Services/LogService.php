<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;

/**
 * Class LogService
 *
 * @package App\Services
 */
class LogService
{
    /**
     * Get full trace of an exception.
     *
     * @param Exception $exception
     *
     * @return string
     */
    public static function getExceptionTraceAsString(Exception $exception)
    {
        $errorString = "#00 User: ";

        /** @var User $user */
        $user = Auth::user();

        if ($user) {
            $errorString .= $user->id . ' ' . $user->name;
        }

        $errorString .= "\n";
        $count = 0;

        foreach ($exception->getTrace() as $frame) {
            $args = "";

            if (isset($frame['args'])) {
                $args = array();

                foreach ($frame['args'] as $arg) {
                    if (is_string($arg)) {
                        $args[] = "'" . $arg . "'";
                    } elseif (is_array($arg)) {
                        $args[] = "Array";
                    } elseif (is_null($arg)) {
                        $args[] = 'NULL';
                    } elseif (is_bool($arg)) {
                        $args[] = ($arg) ? "true" : "false";
                    } elseif (is_object($arg)) {
                        $args[] = get_class($arg);
                    } elseif (is_resource($arg)) {
                        $args[] = get_resource_type($arg);
                    } else {
                        $args[] = $arg;
                    }
                }

                $args = join(", ", $args);
            }

            $currentFile = "[internal function]";

            if (isset($frame['file'])) {
                $currentFile = $frame['file'];
            }

            $currentLine = "";

            if (isset($frame['line'])) {
                $currentLine = $frame['line'];
            }

            $errorString .= sprintf("#%s %s(%s): %s(%s)\n",
                $count,
                $currentFile,
                $currentLine,
                $frame['function'],
                $args);
            $count++;
        }

        return $errorString . "\n";
    }
}
