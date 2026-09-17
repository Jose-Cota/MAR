<?php

namespace App\Helpers;

class PasswordHelper
{
    /**
     * Check whether a plain text password matches a stored hashed password.
     */
    public static function checkPassword($password, $stored_hash)
    {
        if (substr($stored_hash, 0, 2) == 'U$') {
            $stored_hash = substr($stored_hash, 1);
            $password = md5($password);
        }

        $type = substr($stored_hash, 0, 3);
        switch ($type) {
            case '$S$':
                // A normal Drupal 7 password using sha512.
                $hash = self::_password_crypt('sha512', $password, $stored_hash);
                break;
            case '$H$':
            case '$P$':
                $hash = self::_password_crypt('md5', $password, $stored_hash);
                break;
            default:
                return false;
        }

        return ($hash && $stored_hash == $hash);
    }

    private static function _password_itoa64()
    {
        return './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    }

    private static function _password_base64_encode($input, $count)
    {
        $output = '';
        $i = 0;
        $itoa64 = self::_password_itoa64();
        do {
            $value = ord($input[$i++]);
            $output .= $itoa64[$value & 0x3f];
            if ($i < $count) {
                $value |= ord($input[$i]) << 8;
            }
            $output .= $itoa64[($value >> 6) & 0x3f];
            if ($i++ >= $count) {
                break;
            }
            if ($i < $count) {
                $value |= ord($input[$i]) << 16;
            }
            $output .= $itoa64[($value >> 12) & 0x3f];
            if ($i++ >= $count) {
                break;
            }
            $output .= $itoa64[($value >> 18) & 0x3f];
        } while ($i < $count);

        return $output;
    }

    private static function _password_get_count_log2($setting)
    {
        $itoa64 = self::_password_itoa64();
        return strpos($itoa64, $setting[3]);
    }

    private static function _password_crypt($algo, $password, $setting)
    {
        $setting = substr($setting, 0, 12);
        if ($setting[0] != '$' || $setting[2] != '$') {
            return false;
        }

        $count_log2 = self::_password_get_count_log2($setting);
        if ($count_log2 < 7 || $count_log2 > 30) {
            return false;
        }

        $salt = substr($setting, 4, 8);
        if (strlen($salt) != 8) {
            return false;
        }

        $count = 1 << $count_log2;
        $hash = hash($algo, $salt . $password, true);
        do {
            $hash = hash($algo, $hash . $password, true);
        } while (--$count);

        $len = strlen($hash);
        $output = $setting . self::_password_base64_encode($hash, $len);
        $expected = 12 + ceil((8 * $len) / 6);

        return (strlen($output) == $expected) ? substr($output, 0, 55) : false;
    }
}
