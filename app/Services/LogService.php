<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
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
     * @param Request|null $request
     *
     * @return string
     */
    public static function getExceptionTraceAsString(Exception $exception, Request $request = null)
    {
        /** @var User|null $user */
        $user = Auth::user();

        $errorString = '#00 Exception: ' . $exception->getCode() . ' --- ' . $exception->getMessage() . "\n";
        $errorString .= '#01 User: ' . ($user ? $user->id . ' --- ' . $user->name : '') . "\n";

        if ($request) {
            $errorString .= '#02 Request: ' . json_encode($request->all()) . "\n";
        }

        $count = 0;

        foreach ($exception->getTrace() as $frame) {
            $args = '';

            if (isset($frame['args'])) {
                $args = self::getFrameArgs($frame['args']);
            }

            $currentFile = isset($frame['file']) ? $frame['file'] : '[internal function]';
            $currentLine = isset($frame['line']) ? $frame['line'] : '';

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

    /**
     * Get exception args
     *
     * @param $frameArgs
     *
     * @return string
     */
    private static function getFrameArgs($frameArgs)
    {
        $args = array();

        foreach ($frameArgs as $arg) {
            if (is_string($arg)) {
                $args[] = '\'' . $arg . '\'';
            } elseif (is_array($arg)) {
                $args[] = 'Array';
            } elseif (is_null($arg)) {
                $args[] = 'NULL';
            } elseif (is_bool($arg)) {
                $args[] = ($arg) ? 'true' : 'false';
            } elseif (is_object($arg)) {
                $args[] = get_class($arg);
            } elseif (is_resource($arg)) {
                $args[] = get_resource_type($arg);
            } else {
                $args[] = $arg;
            }
        }

        return join(', ', $args);
    }
}
