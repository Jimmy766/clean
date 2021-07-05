<?php

namespace App\Core\Base\Traits;
use Aws\Exception\AwsException;
use Aws\SecretsManager\SecretsManagerClient;
use Illuminate\Support\Facades\Cache;

/**
 * Trait SecretHelper
 * @package App\Traits
 */
trait SecretHelper
{

    public static function getSecret($secret)
    {
        $cacheKey = "database-config-" . $secret;
        $expiresIn = now()->addSeconds(30);

        $client = new SecretsManagerClient([
            'version' => '2017-10-17',
            'region' => 'eu-central-1',
        ]);

        $secretName = config('app.env').'/'.$secret;

        try {
            //dd($client);
            $result = $client->getSecretValue([
                'SecretId' => $secretName,
            ]);

        } catch (AwsException $e) {
                $error = $e->getAwsErrorCode();
            if ($error == 'DecryptionFailureException') {
                // Secrets Manager can't decrypt the protected secret text using the provided AWS KMS key.
                // Handle the exception here, and/or rethrow as needed.
                if (!Cache::has('Notification-sent-vault' . $secret)) {
//                    $this->LogToSlack('{' . config('app.env') . '} Secret Manager Error ' . $e->getMessage());
                    Cache::put('Notification-sent-vault' . $secret, 'sent', 10);
                }
                throw $e;
            }
            if ($error == 'InternalServiceErrorException') {
                // An error occurred on the server side.
                // Handle the exception here, and/or rethrow as needed.
                if (!Cache::has('Notification-sent-vault' . $secret)) {
//                    $this->LogToSlack('{' . config('app.env') . '} Secret Manager Error ' . $e->getMessage());
                    Cache::put('Notification-sent-vault' . $secret, 'sent', 10);
                }
                throw $e;
            }
            if ($error == 'InvalidParameterException') {
                // You provided an invalid value for a parameter.
                // Handle the exception here, and/or rethrow as needed.
                if (!Cache::has('Notification-sent-vault' . $secret)) {
//                    $this->LogToSlack('{' . config('app.env') . '} Secret Manager Error ' . $e->getMessage());
                    Cache::put('Notification-sent-vault' . $secret, 'sent', 10);
                }
                throw $e;
            }
            if ($error == 'InvalidRequestException') {
                // You provided a parameter value that is not valid for the current state of the resource.
                // Handle the exception here, and/or rethrow as needed.
                if (!Cache::has('Notification-sent-vault' . $secret)) {
//                    $this->LogToSlack('{' . config('app.env') . '} Secret Manager Error ' . $e->getMessage());
                    Cache::put('Notification-sent-vault' . $secret, 'sent', 10);
                }
                throw $e;
            }
            if ($error == 'ResourceNotFoundException') {
                // We can't find the resource that you asked for.
                // Handle the exception here, and/or rethrow as needed.

                throw $e;
            }
             throw $e;
        }
        // Decrypts secret using the associated KMS CMK.
        // Depending on whether the secret is a string or binary, one of these fields will be populated.
        if (isset($result['SecretString'])) {
            $secret = $result['SecretString'];
            $configs = json_decode($result['SecretString'], true);

            return $configs;
        }
    }
}
