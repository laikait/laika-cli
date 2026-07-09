<?php
/**
 * Laika MVC Framework
 * Author: Showket Ahmed
 * Email: riyadhtayf@gmail.com
 * License: MIT
 * This file is part of the Laika MMC Framework.
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Laika\Cli\Command;

class Message
{
    /*========================== COLOR METHODS ==========================*/
    /**
     * Black Text
     * @return string
     */
    public static function txt_black(string $text): string
    {
        return "\e[30m{$text}\e[0m";
    }

    /**
     * Red Text
     * @return string
     */
    public static function txt_red(string $text): string
    {
        return "\e[31m{$text}\e[0m";
    }

    /**
     * Green Text
     * @return string
     */
    public static function txt_green(string $text): string
    {
        return "\e[32m{$text}\e[0m";
    }

    /**
     * Yellow Text
     * @return string
     */
    public static function txt_yellow(string $text): string
    {
        return "\e[33m{$text}\e[0m";
    }

    /**
     * Blue Text
     * @return string
     */
    public static function txt_blue(string $text): string
    {
        return "\e[34m{$text}\e[0m";
    }

    /**
     * Magenta Text
     * @return string
     */
    public static function txt_magenta(string $text): string
    {
        return "\e[35m{$text}\e[0m";
    }

    /**
     * Cyan Text
     * @return string
     */
    public static function txt_cyan(string $text): string
    {
        return "\e[36m{$text}\e[0m";
    }

    /**
     * White Text
     * @return string
     */
    public static function txt_white(string $text): string
    {
        return "\e[37m{$text}\e[0m";
    }

    /*========================== BACKGROUND COLOR METHODS ==========================*/
    /**
     * Black Background
     * @return string
     */
    public static function bg_black(string $text): string
    {
        return "\e[40m{$text}\e[0m";
    }

    /**
     * Red Background
     * @return string
     */
    public static function bg_red(string $text): string
    {
        return "\e[41m{$text}\e[0m";
    }

    /**
     * Green Background
     * @return string
     */
    public static function bg_green(string $text): string
    {
        return "\e[42m{$text} \e[0m";
    }

    /**
     * Yellow Background
     * @return string
     */
    public static function bg_yellow(string $text): string
    {
        return "\e[43m{$text}\e[0m";
    }

    /**
     * Blue Text
     * @return string
     */
    public static function bg_blue(string $text): string
    {
        return "\e[44m{$text}\e[0m";
    }

    /**
     * Magenta Text
     * @return string
     */
    public static function bg_magenta(string $text): string
    {
        return "\e[45m{$text}\e[0m";
    }

    /**
     * Cyan Text
     * @return string
     */
    public static function bg_cyan(string $text): string
    {
        return "\e[46m{$text}\e[0m";
    }

    /**
     * White Text
     * @return string
     */
    public static function bg_white(string $text): string
    {
        return "\e[47m{$text}\e[0m";
    }

    /*=========================== MESSAGE COLOR METHODS ===========================*/
    /**
     * @param string $message
     * This method is used to print informational messages to the console.
     * @return void
     */
    public static function success(string $message): void
    {
        echo "\n" . self::txt_green('[SUCCESS]') . " {$message}\n";
    }

        /**
     * @param string $message
     * This method is used to print informational messages to the console.
     * @return void
     */
    public static function error(string $message): void
    {
        echo "\n" . self::txt_red('[ERROR]') . " {$message}\n";
    }

    /**
     * @param string $message
     * This method is used to print informational messages to the console.
     * @return void
     */
    public static function info(string $message): void
    {
        echo "\n" . self::txt_blue('[INFO]') . " {$message}\n";
    }

    /**
     * @param string $message
     * This method is used to print informational messages to the console.
     * @return void
     */
    public static function warning(string $message): void
    {
        echo "\n" . self::txt_yellow('[WARNING]') . " {$message}\n";
    }

    /**
     * @param string $message
     * This method is used to print informational messages to the console.
     * @return void
     */
    public static function suggestion(string $message): void
    {
        echo "\n" . self::txt_cyan('[SUGGESTION]') . " {$message}\n";
    }
}