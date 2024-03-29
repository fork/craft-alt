<?php

namespace fork\alt\services;

use Craft;
use craft\base\Element;
use craft\elements\Asset;
use craft\errors\ElementNotFoundException;
use fork\alt\exception\ImageNotSavedException;
use fork\alt\exception\NotAnImageException;
use fork\alt\Plugin;
use Throwable;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Alt Text Generation service
 */
class AltTextGeneration extends Component
{
    /**
     * @param int $assetId
     * @throws ElementNotFoundException
     * @throws ImageNotSavedException
     * @throws InvalidConfigException
     * @throws NotAnImageException
     * @throws Throwable
     * @throws Exception
     */
    public function generateAltTextForImage(int $assetId): void
    {
        $image = Craft::$app->elements->getElementById($assetId);
        self::validateImage($image);
        $image->alt = $this->generateAltText($image);

        if (!Craft::$app->elements->saveElement($image)) {
            throw new ImageNotSavedException(
                "Image could not be saved, reasons: " . json_encode($image->getErrors(), JSON_PRETTY_PRINT)
            );
        }
    }

    /**
     * @throws InvalidConfigException
     */
    public function generateAltText(Asset $image): string
    {
        return Plugin::getInstance()->getSettings()->getAltTextGenerator()->generateAltTextForImage($image);
    }

    /**
     * @param ?Element $image
     * @return void
     * @throws ElementNotFoundException
     * @throws NotAnImageException
     */
    public static function validateImage(?Element $image): void
    {
        if (!$image) {
            throw new ElementNotFoundException("Image doesn't exist");
        }
        if (!($image instanceof Asset)) {
            throw new NotAnImageException("Element is not an asset");
        }
        if ($image->kind !== Asset::KIND_IMAGE) {
            throw new NotAnImageException("Asset is not an image");
        }
    }
}
