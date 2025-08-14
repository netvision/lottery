# Firebase Authentication for Yii2 Backend

This directory contains example code for implementing Firebase authentication in your Yii2 backend API.

## Setup Instructions

### 1. Install Firebase JWT Library

Add the Firebase JWT package to your Yii2 project:

```bash
composer require firebase/php-jwt
```

### 2. Configure Firebase Project ID

Add your Firebase project ID to your Yii2 configuration. You can do this in several ways:

#### Option A: Environment Variable
```bash
# Set in your environment
export FIREBASE_PROJECT_ID="your-firebase-project-id"
```

#### Option B: Yii2 Params Configuration
In `config/params.php`:
```php
return [
    'firebaseProjectId' => 'your-firebase-project-id',
    // ... other params
];
```

#### Option C: Direct Configuration
In the middleware usage, set it directly:
```php
'firebaseAuth' => [
    'class' => FirebaseAuthMiddleware::class,
    'projectId' => 'your-firebase-project-id',
    'only' => ['create', 'update'],
],
```

### 3. Copy the Middleware

Copy `FirebaseAuthMiddleware.php` to your Yii2 project directory:
```
your-yii2-project/
├── middleware/
│   └── FirebaseAuthMiddleware.php
```

### 4. Use in Your Controllers

Apply the middleware to your controllers that need protection:

```php
public function behaviors()
{
    $behaviors = parent::behaviors();
    
    $behaviors['firebaseAuth'] = [
        'class' => FirebaseAuthMiddleware::class,
        'only' => ['create', 'update', 'delete'], // Protect these actions
    ];
    
    return $behaviors;
}
```

### 5. Access User Information

After successful authentication, you can access Firebase user data:

```php
public function actionCreate()
{
    $firebaseUser = Yii::$app->get('firebaseUser');
    
    $userId = $firebaseUser->sub;           // Firebase User ID
    $email = $firebaseUser->email ?? null; // User email
    $name = $firebaseUser->name ?? null;   // User display name
    
    // Your logic here...
}
```

## Frontend Integration

Your Vue.js frontend is already configured to send Firebase tokens. The middleware will:

1. Extract the Bearer token from the `Authorization` header
2. Verify the token using Firebase's public keys
3. Validate token claims (expiration, audience, issuer, etc.)
4. Make user information available in your controller actions

## Security Features

- **Token Verification**: Uses Firebase's public keys to verify token authenticity
- **Claim Validation**: Checks expiration, audience, issuer, and other claims
- **Key Caching**: Caches Firebase public keys to reduce API calls
- **Auto Refresh**: Handles token refresh on 401 errors
- **CORS Support**: Includes CORS configuration example

## Error Handling

The middleware handles various error scenarios:

- **Missing Token**: Returns 401 Unauthorized
- **Invalid Token**: Returns 401 Unauthorized  
- **Expired Token**: Returns 401 Unauthorized
- **Network Issues**: Logs errors and returns 401

## Testing

You can test the protected endpoints using curl:

```bash
# Get a token from your Vue.js app's network tab or console
TOKEN="your-firebase-id-token-here"

# Test protected endpoint
curl -X POST "https://superlaxmi.netserve.in/results" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"date":"2025-08-14","time":"10:00:00","slot_id":1,"winning_no":42}'
```

## Configuration Options

### Middleware Options

- `only`: Array of actions that require authentication
- `except`: Array of actions that don't require authentication  
- `projectId`: Firebase project ID
- `cacheKeyDuration`: How long to cache Firebase public keys (default: 3600 seconds)

### Example Configurations

```php
// Protect all actions except 'index' and 'view'
'firebaseAuth' => [
    'class' => FirebaseAuthMiddleware::class,
    'except' => ['index', 'view'],
],

// Protect only specific actions
'firebaseAuth' => [
    'class' => FirebaseAuthMiddleware::class,
    'only' => ['create', 'update', 'delete'],
],

// Custom cache duration
'firebaseAuth' => [
    'class' => FirebaseAuthMiddleware::class,
    'cacheKeyDuration' => 1800, // 30 minutes
],
```

## Troubleshooting

### Common Issues

1. **"Firebase Project ID is required"**
   - Make sure you've set the project ID in environment, params, or middleware config

2. **"Failed to fetch Firebase public keys"**
   - Check your server's internet connection
   - Verify firewall settings allow outbound HTTPS requests

3. **"Token audience mismatch"**
   - Ensure the Firebase project ID matches between frontend and backend

4. **"Invalid or expired token"**
   - Check that the frontend is sending valid Firebase ID tokens
   - Verify token hasn't expired (default: 1 hour)

### Debug Mode

To enable debug logging, add this to your controller:

```php
Yii::info('Firebase user: ' . json_encode($firebaseUser), 'firebase-auth');
```
