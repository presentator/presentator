<?php
namespace presentator\api\base;

use Yii;
use \UnexpectedValueException;

/**
 * Custom Securty component class.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class JWT extends \Firebase\JWT\JWT
{
    /**
     * Parses and returns the `$jwt` payload without secret verification.
     *
     * @param  string $jwt
     * @return object                   The JWT's payload as a PHP object
     * @throws UnexpectedValueException Provided JWT was invalid
     */
    public static function unsafeDecode($jwt)
    {
        $parts = explode('.', $jwt);
        if (count($parts) != 3) {
            throw new UnexpectedValueException('Wrong number of segments');
        }

        $payload = static::jsonDecode(static::urlsafeB64Decode($parts[1]));
        if (null === $payload) {
            throw new UnexpectedValueException('Invalid claims encoding');
        }

        return $payload;
    }

    /**
     * Checks if the provided `$jwt` token is valid.
     *
     * @param  string  $jwt
     * @param  string  $secret
     * @param  array   [$algs]
     * @return boolean
     */
    public static function isValid($jwt, $secret, array $algs = ['HS256']): bool
    {
        try {
            return !empty(static::decode($jwt, $secret, ['HS256']));
        } catch (\Exception | \Throwable $e) {
            Yii::error($e->getMessage());
        }

        return false;
    }
}
