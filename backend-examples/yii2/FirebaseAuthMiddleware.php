<?php

namespace app\middleware;

use Yii;
use yii\base\Behavior;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Firebase Authentication Middleware for Yii2
 * 
 * This middleware verifies Firebase ID tokens sent in the Authorization header
 * Usage in controller:
 * 
 * public function behaviors()
 * {
 *     return [
 *         'firebaseAuth' => [
 *             'class' => FirebaseAuthMiddleware::class,
 *             'only' => ['create', 'update', 'delete'], // Apply only to specific actions
 *         ],
 *     ];
 * }
 */
class FirebaseAuthMiddleware extends Behavior
{
    /**
     * @var array Actions that require authentication
     */
    public $only = [];
    
    /**
     * @var array Actions that don't require authentication
     */
    public $except = [];
    
    /**
     * @var string Firebase project ID
     */
    public $projectId;
    
    /**
     * @var array Firebase public keys cache
     */
    private static $publicKeys = null;
    
    /**
     * @var int Cache duration for public keys (in seconds)
     */
    public $cacheKeyDuration = 3600; // 1 hour

    public function init()
    {
        parent::init();
        
        // Get Firebase project ID from environment or config
        $this->projectId = $this->projectId ?: getenv('FIREBASE_PROJECT_ID') ?: Yii::$app->params['firebaseProjectId'] ?? null;
        
        if (!$this->projectId) {
            throw new \Exception('Firebase Project ID is required for authentication middleware');
        }
    }

    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    /**
     * Check if current action requires authentication
     */
    protected function isActive($action)
    {
        $id = $action->id;
        
        if (empty($this->only) && empty($this->except)) {
            return true;
        }
        
        if (!empty($this->except) && in_array($id, $this->except)) {
            return false;
        }
        
        if (!empty($this->only) && !in_array($id, $this->only)) {
            return false;
        }
        
        return true;
    }

    /**
     * Before action event handler
     */
    public function beforeAction($event)
    {
        if (!$this->isActive($event->action)) {
            return true;
        }

        $token = $this->extractToken();
        
        if (!$token) {
            throw new UnauthorizedHttpException('Authorization token is required');
        }

        try {
            $decodedToken = $this->verifyFirebaseToken($token);
            
            // Store user information in Yii::$app->user or custom component
            Yii::$app->set('firebaseUser', $decodedToken);
            
            return true;
        } catch (\Exception $e) {
            Yii::error('Firebase token verification failed: ' . $e->getMessage());
            throw new UnauthorizedHttpException('Invalid or expired token');
        }
    }

    /**
     * Extract Bearer token from Authorization header
     */
    private function extractToken()
    {
        $authHeader = Yii::$app->request->headers->get('Authorization');
        
        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Verify Firebase ID token
     */
    private function verifyFirebaseToken($token)
    {
        // Get Firebase public keys
        $publicKeys = $this->getFirebasePublicKeys();
        
        // Decode token header to get key ID
        $tokenParts = explode('.', $token);
        if (count($tokenParts) !== 3) {
            throw new \Exception('Invalid token format');
        }
        
        $header = json_decode(base64_decode($tokenParts[0]), true);
        if (!isset($header['kid'])) {
            throw new \Exception('Token header missing key ID');
        }
        
        $keyId = $header['kid'];
        if (!isset($publicKeys[$keyId])) {
            throw new \Exception('Public key not found for key ID: ' . $keyId);
        }
        
        // Verify and decode the token
        $publicKey = $publicKeys[$keyId];
        $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));
        
        // Verify token claims
        $this->verifyTokenClaims($decoded);
        
        return $decoded;
    }

    /**
     * Get Firebase public keys
     */
    private function getFirebasePublicKeys()
    {
        $cacheKey = 'firebase_public_keys';
        $cache = Yii::$app->cache;
        
        // Try to get from cache first
        if ($cache && self::$publicKeys === null) {
            self::$publicKeys = $cache->get($cacheKey);
        }
        
        if (self::$publicKeys === false || self::$publicKeys === null) {
            // Fetch from Google's public key endpoint
            $url = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200 || !$response) {
                throw new \Exception('Failed to fetch Firebase public keys');
            }
            
            self::$publicKeys = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from Firebase public keys endpoint');
            }
            
            // Cache the keys
            if ($cache) {
                $cache->set($cacheKey, self::$publicKeys, $this->cacheKeyDuration);
            }
        }
        
        return self::$publicKeys;
    }

    /**
     * Verify token claims
     */
    private function verifyTokenClaims($decoded)
    {
        $now = time();
        
        // Check expiration
        if (!isset($decoded->exp) || $decoded->exp < $now) {
            throw new \Exception('Token has expired');
        }
        
        // Check issued at time
        if (!isset($decoded->iat) || $decoded->iat > $now) {
            throw new \Exception('Token used before issued');
        }
        
        // Check audience (should be your Firebase project ID)
        if (!isset($decoded->aud) || $decoded->aud !== $this->projectId) {
            throw new \Exception('Token audience mismatch');
        }
        
        // Check issuer
        $expectedIssuer = 'https://securetoken.google.com/' . $this->projectId;
        if (!isset($decoded->iss) || $decoded->iss !== $expectedIssuer) {
            throw new \Exception('Token issuer mismatch');
        }
        
        // Check subject (user ID should exist)
        if (!isset($decoded->sub) || empty($decoded->sub)) {
            throw new \Exception('Token subject missing');
        }
        
        // Check auth time
        if (!isset($decoded->auth_time) || $decoded->auth_time > $now) {
            throw new \Exception('Token auth time invalid');
        }
    }
}
