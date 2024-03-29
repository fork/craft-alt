<?php

namespace fork\alt\connectors\alttextgeneration;

use Craft;
use craft\elements\Asset;
use craft\fs\Local;
use fork\alt\exception\NotALocalFileException;
use fork\alt\Plugin;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Utils;
use yii\base\InvalidConfigException;

class AbstractHuggingFaceAltTextGenerator implements AltTextGeneratorInterface
{
    protected string $modelPath = '/models/Salesforce/blip-image-captioning-large';

    /**
     * @throws GuzzleException
     * @throws InvalidConfigException
     * @throws NotALocalFileException
     */
    public function generateAltTextForImage(Asset $image): ?string
    {
        $fs = $image->getVolume()->getFs();
        if (!($fs instanceof Local)) {
            throw new NotALocalFileException("Image is not a local file");
        }

        $fsPath = Craft::getAlias($fs->path);
        $absPath = $fsPath . DIRECTORY_SEPARATOR . $image->getPath();

        $client = new Client([
            'base_uri' => "https://api-inference.huggingface.co",
            'headers' => [
                'Authorization' => 'Bearer ' . Plugin::getInstance()->getSettings()->getApiToken()
            ]
        ]);

        $body = Utils::tryFopen($absPath, 'r');
        $response = $client->post(
            $this->modelPath,
            ['body' => $body]
        );

        $body = $response->getBody();
        $decoded = json_decode($body, true);
        $first = !empty($decoded) ? $decoded[0] : null;

        return $first ? $this->filterWords($first['generated_text']) : null;
    }

    protected function filterWords(string $text): string
    {
        foreach (Plugin::getInstance()->getSettings()->wordsBlackList as $word) {
            $text = str_replace($word, '', $text);
        }

        return trim($text);
    }
}
