<?php

namespace App\Extentions;

use \Illuminate\Database\DatabaseManager as BaseDatabaseManager;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Arr;

use Aws\SecretsManager\SecretsManagerClient;
use Aws\Exception\AwsException;

class DatabaseManager extends BaseDatabaseManager
{
    protected function configuration($name)
    {

        $dbconf = Config::get("database.connections." . $name);
        //if db driver is vault we get it from rest api / cache
        if ($dbconf['driver'] == 'vault') {
            $config = $this->getSecret($name);
            if (is_null($config)) {
                //check if config is in cache
                $cacheKeyBackUp = "database-config-" . $name;
                if (Cache::has($cacheKeyBackUp)) {
                    //get from cache
                    $config =  Cache::get($cacheKeyBackUp);
                }
                if (is_null($config)) {
                    throw new InvalidArgumentException("Database [$name] not configured.");
                }
            }
        } else {
            $name = $name ?: $this->getDefaultConnection();
            // To get the database connection configuration, we will just pull each of the
            // connection configurations and get the configurations for the given name.
            // If the configuration doesn't exist, we'll throw an exception and bail.
            $connections = $this->app['config']['database.connections'];
            if (is_null($config = Arr::get($connections, $name))) {

                throw new InvalidArgumentException("Database [$name] not configured.");
            }
        }
        return $config;
    }





    public static function LogToSlack($message)
    {
        try {
            //slack url
            $url = env('LOG_SLACK_WEBHOOK_URL_TESTS');


            $headers = [
                'Content-Type' => 'application/json',
            ];
            $client = new Client([
                'headers' => $headers
            ]);
            $response = $client->post($url, [
                RequestOptions::JSON => ['text' => $message]
            ])->getBody()->getContents();
        } catch (\Exception $e) {
            //log error to slack

        }
    }
    public function getSecret($secret)
    {
        $cacheKey = "database-config-" . $secret;
        $cacheKeyBackUp = "database-config-" . $secret;
        $expiresIn = now()->addSeconds(30);
        $backupExpiresIn = now()->addHours(12);

        //check if config is in cache
        if (Cache::has($cacheKey)) {
            //get from cache
            return Cache::get($cacheKey);
        }

        $client = new SecretsManagerClient([
            'version' => '2017-10-17',
            'region' => 'eu-central-1',
        ]);

        $secretName = config('app.env') . '/' . $secret;

        try {
            $result = $client->getSecretValue([
                'SecretId' => $secretName,
            ]);
        } catch (AwsException $e) {
            $error = $e->getAwsErrorCode();
            if ($error == 'DecryptionFailureException') {
                // Secrets Manager can't decrypt the protected secret text using the provided AWS KMS key.
                // Handle the exception here, and/or rethrow as needed.
                if (!Cache::has('Notification-sent-vault' . $secret)) {
                    $this->LogToSlack('{' . config('app.env') . '} Secret Manager Error ' . $e->getMessage());
                    Cache::put('Notification-sent-vault' . $secret, 'sent', 10);
                }
                throw $e;
            }
            if ($error == 'InternalServiceErrorException') {
                // An error occurred on the server side.
                // Handle the exception here, and/or rethrow as needed.
                if (!Cache::has('Notification-sent-vault' . $secret)) {
                    $this->LogToSlack('{' . config('app.env') . '} Secret Manager Error ' . $e->getMessage());
                    Cache::put('Notification-sent-vault' . $secret, 'sent', 10);
                }
                throw $e;
            }
            if ($error == 'InvalidParameterException') {
                // You provided an invalid value for a parameter.
                // Handle the exception here, and/or rethrow as needed.
                if (!Cache::has('Notification-sent-vault' . $secret)) {
                    $this->LogToSlack('{' . config('app.env') . '} Secret Manager Error ' . $e->getMessage());
                    Cache::put('Notification-sent-vault' . $secret, 'sent', 10);
                }
                throw $e;
            }
            if ($error == 'InvalidRequestException') {
                // You provided a parameter value that is not valid for the current state of the resource.
                // Handle the exception here, and/or rethrow as needed.
                if (!Cache::has('Notification-sent-vault' . $secret)) {
                    $this->LogToSlack('{' . config('app.env') . '} Secret Manager Error ' . $e->getMessage());
                    Cache::put('Notification-sent-vault' . $secret, 'sent', 10);
                }
                throw $e;
            }
            if ($error == 'ResourceNotFoundException') {
                // We can't find the resource that you asked for.
                // Handle the exception here, and/or rethrow as needed.
                if (!Cache::has('Notification-sent-vault' . $secret)) {
                    $this->LogToSlack('{' . config('app.env') . '} Secret Manager Error ' . $e->getMessage());
                    Cache::put('Notification-sent-vault' . $secret, 'sent', 10);
                }
                throw $e;
            }
            throw $e;
        }
        // Decrypts secret using the associated KMS CMK.
        // Depending on whether the secret is a string or binary, one of these fields will be populated.
        if (isset($result['SecretString'])) {
            $secret = $result['SecretString'];
            $configs = json_decode($result['SecretString'], true);

            if (isset($expiresIn)) {
                Cache::put($cacheKey, $configs, $expiresIn);
                //save backup
                Cache::put($cacheKeyBackUp, $configs, $backupExpiresIn);
            }
            return $configs;
        }
    }
}
