<?php

namespace fork\alt\connectors\alttextgeneration;

class HuggingFaceBlipLargeAltTextGenerator extends AbstractHuggingFaceAltTextGenerator
{
    protected string $modelPath = '/models/Salesforce/blip-image-captioning-large';
}
