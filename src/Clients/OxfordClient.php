<?php
declare(strict_types = 1);

namespace CeregoFiller\Clients;

use GuzzleHttp\Client;

class OxfordClient extends Client implements GettingJsonData
{

    private $dictionaryLanguage,
            $appId,
            $appKey;

    public function __construct(string $dictionaryLanguage, string $appId, string $appKey, array $config = [])
    {
        $this->dictionaryLanguage = $dictionaryLanguage;
        $this->appId = $appId;
        $this->appKey = $appKey;
        parent::__construct($config);
    }

    public const WORD_DATA_URL_TEMPLATE = 'https://od-api.oxforddictionaries.com/api/v2/entries/%s/%s',
                 LEMMA_URL_TEMPLATE = 'https://od-api.oxforddictionaries.com/api/v2/lemmas/%s/%s';

    /**
     * @param string $word
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getWordJsonData(string $word): string
    {
        $url = sprintf(self::WORD_DATA_URL_TEMPLATE, $this->dictionaryLanguage, strtolower($word));
        $response = $this->request('GET', $url, [
            'headers' => [
                'Accept' => 'application/json',
                'app_id' => $this->appId,
                'app_key' => $this->appKey,
            ]
        ]);
        return $response->getBody()->getContents();
    }

    /**
     * @param string $word
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getLemmaJsonData(string $word): string
    {
        $url = sprintf(self::LEMMA_URL_TEMPLATE, $this->dictionaryLanguage, strtolower($word));
        $response = $this->request('GET', $url, [
            'headers' => [
                'Accept' => 'application/json',
                'app_id' => $this->appId,
                'app_key' => $this->appKey,
            ]
        ]);
        return $response->getBody()->getContents();
    }

}