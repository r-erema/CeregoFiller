<?php

namespace CeregoFiller;

class GoogleClient
{

    /**
     * @param string $authConfig
     * @param string $tokenPathDestination
     * @return \Google_Client
     * @throws \Google_Exception
     */
    public static function create(string $authConfig, string $tokenPathDestination): \Google_Client
    {
        $client = new \Google_Client();
        $client->setApplicationName('Google Drive API PHP Quickstart');
        $client->setScopes(\Google_Service_Drive::DRIVE);
        $client->setAuthConfig($authConfig);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        if (file_exists($tokenPathDestination)) {
            $accessToken = json_decode(file_get_contents($tokenPathDestination), true);
            $client->setAccessToken($accessToken);
        }

        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                if (array_key_exists('error', $accessToken)) {
                    throw new \RuntimeException(implode(', ', $accessToken));
                }
            }

            if (!file_exists(dirname($tokenPathDestination)) && !mkdir($concurrentDirectory = dirname($tokenPathDestination), 0700, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
            file_put_contents($tokenPathDestination, json_encode($client->getAccessToken()));
        }
        return $client;
    }
}