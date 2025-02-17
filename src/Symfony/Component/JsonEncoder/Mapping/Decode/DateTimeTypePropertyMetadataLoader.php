<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\JsonEncoder\Mapping\Decode;

use Symfony\Component\JsonEncoder\Decode\Denormalizer\DateTimeDenormalizer;
use Symfony\Component\JsonEncoder\Mapping\PropertyMetadataLoaderInterface;
use Symfony\Component\TypeInfo\Type\ObjectType;

/**
 * Casts DateTime properties to string properties.
 *
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 *
 * @internal
 */
final class DateTimeTypePropertyMetadataLoader implements PropertyMetadataLoaderInterface
{
    public function __construct(
        private PropertyMetadataLoaderInterface $decorated,
    ) {
    }

    public function load(string $className, array $options = [], array $context = []): array
    {
        $result = $this->decorated->load($className, $options, $context);

        foreach ($result as &$metadata) {
            $type = $metadata->getType();

            if ($type instanceof ObjectType && is_a($type->getClassName(), \DateTimeInterface::class, true)) {
                $dateTimeDenormalizer = match ($type->getClassName()) {
                    \DateTimeInterface::class, \DateTimeImmutable::class => 'json_encoder.denormalizer.date_time_immutable',
                    default => 'json_encoder.denormalizer.date_time',
                };
                $metadata = $metadata
                    ->withType(DateTimeDenormalizer::getNormalizedType())
                    ->withAdditionalDenormalizer($dateTimeDenormalizer);
            }
        }

        return $result;
    }
}
