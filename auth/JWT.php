<?php
class JWT {
    private static $secret_key = "your_secure_secret_key_here"; // Change this!
    private static $algorithm = 'SHA256';

    // Encode data to Base64URL
    private static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // Decode Base64URL
    private static function base64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    // Generate JWT token
    public static function generate_token($payload) {
        // Create token header
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => self::$algorithm
        ]);

        // Encode Header
        $base64UrlHeader = self::base64url_encode($header);

        // Add issued at and expiration time to payload
        $payload['iat'] = time();
        $payload['exp'] = time() + (60 * 60); // 1 hour expiration

        // Encode Payload
        $base64UrlPayload = self::base64url_encode(json_encode($payload));

        // Create Signature
        $signature = hash_hmac(
            self::$algorithm,
            $base64UrlHeader . "." . $base64UrlPayload,
            self::$secret_key,
            true
        );

        // Encode Signature
        $base64UrlSignature = self::base64url_encode($signature);

        // Create JWT
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    // Validate JWT token
    public static function validate_token($token) {
        try {
            // Split token into parts
            $tokenParts = explode('.', $token);
            if (count($tokenParts) != 3) {
                return false;
            }

            list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $tokenParts;

            // Verify signature
            $signature = self::base64url_decode($base64UrlSignature);
            $expectedSignature = hash_hmac(
                self::$algorithm,
                $base64UrlHeader . "." . $base64UrlPayload,
                self::$secret_key,
                true
            );

            if (!hash_equals($signature, $expectedSignature)) {
                return false;
            }

            // Decode payload
            $payload = json_decode(self::base64url_decode($base64UrlPayload), true);

            // Check if token has expired
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return false;
            }

            return $payload;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>