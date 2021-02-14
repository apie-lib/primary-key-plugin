<?php


namespace Apie\PrimaryKeyPlugin\Normalizers;

use Apie\PrimaryKeyPlugin\ValueObjects\PrimaryKeyReference;
use LogicException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes PrimaryKeyReference instances. These are used to make references to other api resources.
 */
class PrimaryKeyReferenceNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        throw new LogicException('Not implemented yet');
    }


    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === PrimaryKeyReference::class;
    }


    public function normalize($object, $format = null, array $context = [])
    {
        /** @var PrimaryKeyReference $object */
        return $object->getUrl();
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PrimaryKeyReference;
    }
}
