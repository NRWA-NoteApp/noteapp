<?php

class JwtService
{
    private string $secret;
    private int $expiresIn;

    public function __construct(array $config)
    {
        $this->secret = (string) ($config['secret'] ?? '');
        $this->expiresIn = (int) ($config['expires_in'] ?? 86400);
    }

    public function encode(array $payload): string
    {
        $issuedAt = time();
        $payload = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $issuedAt + $this->expiresIn,
        ]);

        $header = $this->base64UrlEncode(json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256',
        ], JSON_UNESCAPED_SLASHES));
        $body = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $signature = $this->sign("{$header}.{$body}");

        return "{$header}.{$body}.{$signature}";
    }

    public function decode(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$header, $body, $signature] = $parts;
        $expectedSignature = $this->sign("{$header}.{$body}");

        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $headerData = json_decode($this->base64UrlDecode($header), true);
        $payload = json_decode($this->base64UrlDecode($body), true);

        if (!is_array($headerData) || !is_array($payload) || ($headerData['alg'] ?? '') !== 'HS256') {
            return null;
        }

        if (!isset($payload['exp']) || (int) $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    private function sign(string $value): string
    {
        return $this->base64UrlEncode(hash_hmac('sha256', $value, $this->secret, true));
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/')) ?: '';
    }
}
