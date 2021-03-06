<?php

namespace Apie\PrimaryKeyPlugin\Normalizers;

use Apie\Core\ApiResourceMetadataFactory;
use Apie\Core\ClassResourceConverter;
use Apie\Core\IdentifierExtractor;
use Apie\Core\PluginInterfaces\FrameworkConnectionInterface;
use Apie\Core\Resources\ApiResourcesInterface;
use Apie\CorePlugin\ResourceSerializers\SymfonySerializerAdapter;
use Apie\PrimaryKeyPlugin\ValueObjects\PrimaryKeyReference;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * If a resource has an other api resource as a child, this class will map it as a url and not try to hydrate it as an url.
 */
class ApiePrimaryKeyNormalizer implements ContextAwareNormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    /**
     * @var ApiResourcesInterface
     */
    private $apiResources;

    /**
     * @var IdentifierExtractor
     */
    private $identifierExtractor;

    /**
     * @var ApiResourceMetadataFactory
     */
    private $metadataFactory;

    /**
     * @var ClassResourceConverter
     */
    private $converter;

    /**
     * @var FrameworkConnectionInterface
     */
    private $frameworkConnection;

    public function __construct(
        ApiResourcesInterface $apiResources,
        IdentifierExtractor $identifierExtractor,
        ApiResourceMetadataFactory $metadataFactory,
        ClassResourceConverter $converter,
        FrameworkConnectionInterface $frameworkConnection
    ) {
        $this->apiResources = $apiResources;
        $this->identifierExtractor = $identifierExtractor;
        $this->metadataFactory = $metadataFactory;
        $this->converter = $converter;
        $this->frameworkConnection = $frameworkConnection;
    }
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = [])
    {
        if ($format === SymfonySerializerAdapter::INTERNAL_FOR_DATALAYER || empty($context['object_hierarchy']) || !empty($context['disable_pk_normalize'])) {
            return false;
        }
        foreach ($this->apiResources->getApiResources() as $apiResource) {
            // if someone is really stupid to add this as an API resource....
            if ($apiResource === PrimaryKeyReference::class) {
                continue;
            }
            $resourceContext = $this->metadataFactory->getMetadata($apiResource)->getContext();
            $identifier = $this->identifierExtractor->getIdentifierKeyOfClass($apiResource, $resourceContext);
            if (null !== $identifier && is_a($data, $apiResource)) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $metadata = $this->metadataFactory->getMetadata($object);
        $resourceContext = $this->metadataFactory->getMetadata($object)->getContext();
        $identifierValue = $this->identifierExtractor->getIdentifierValue($object, $resourceContext);
        return $this->serializer->normalize(
            new PrimaryKeyReference(
                $metadata,
                $this->frameworkConnection->getUrlForResource($object),
                $identifierValue
            ),
            $format,
            $context
        );
    }
}
