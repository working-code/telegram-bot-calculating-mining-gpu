<?php

namespace App\Parser;

use App\Exception\ErrorGetPageException;
use GuzzleHttp\Exception\GuzzleException;

trait ParserTrait
{
    /**
     * @throws ErrorGetPageException
     */
    private function getPage(string $url): string
    {
        try {
            $response = $this->httpClient->request('GET', $url);
        } catch (GuzzleException $exception) {
            throw new ErrorGetPageException(sprintf('GuzzleException. message: %s', $exception->getMessage()));
        }

        if ($response->getStatusCode() !== 200) {
            throw new ErrorGetPageException(sprintf('Ошибка получения страницы %', $url));
        }

        return $response->getBody()->getContents();
    }
}
