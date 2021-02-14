<?php


namespace Apie\PrimaryKeyPlugin;

use Apie\Core\PluginInterfaces\ApieAwareInterface;
use Apie\Core\PluginInterfaces\ApieAwareTrait;
use Apie\Core\PluginInterfaces\NormalizerProviderInterface;
use Apie\Core\PluginInterfaces\SchemaProviderInterface;
use Apie\Core\Resources\ApiResources;
use Apie\OpenapiSchema\Factories\SchemaFactory;
use Apie\PrimaryKeyPlugin\Normalizers\ApiePrimaryKeyNormalizer;
use Apie\PrimaryKeyPlugin\Normalizers\PrimaryKeyReferenceNormalizer;
use Apie\PrimaryKeyPlugin\Schema\ApiResourceLinkSchemaBuilder;
use Apie\PrimaryKeyPlugin\ValueObjects\PrimaryKeyReference;

/**
 * Core Apie plugin to map api resources to string urls for child objects.
 */
class PrimaryKeyPlugin implements NormalizerProviderInterface, ApieAwareInterface, SchemaProviderInterface
{
    use ApieAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function getNormalizers(): array
    {
        $primaryKeyNormalizer = new ApiePrimaryKeyNormalizer(
            new ApiResources($this->getApie()->getResources()),
            $this->getApie()->getIdentifierExtractor(),
            $this->getApie()->getApiResourceMetadataFactory(),
            $this->getApie()->getClassResourceConverter(),
            $this->getApie()->getFrameworkConnection()
        );
        return [
            new PrimaryKeyReferenceNormalizer(),
            $primaryKeyNormalizer,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getDefinedStaticData(): array
    {
        return [
            PrimaryKeyReference::class => SchemaFactory::createStringSchema('path'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getDynamicSchemaLogic(): array
    {
        $res = [];
        $identifierExtractor = $this->getApie()->getIdentifierExtractor();
        $builder = new ApiResourceLinkSchemaBuilder($this->getApie()->getFrameworkConnection());
        foreach ($this->getApie()->getResources() as $resource) {
            if (null !== $identifierExtractor->getIdentifierKeyOfClass($resource)) {
                $res[$resource] = $builder;
            }
        }
        return $res;
    }
}
