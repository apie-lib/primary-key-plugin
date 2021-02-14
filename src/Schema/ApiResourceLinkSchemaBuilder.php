<?php


namespace Apie\PrimaryKeyPlugin\Schema;

use Apie\Core\PluginInterfaces\DynamicSchemaInterface;
use Apie\Core\PluginInterfaces\FrameworkConnectionInterface;
use Apie\OpenapiSchema\Factories\SchemaFactory;
use W2w\Lib\Apie\OpenApiSchema\OpenApiSchemaGenerator;

class ApiResourceLinkSchemaBuilder implements DynamicSchemaInterface
{
    /**
     * @var FrameworkConnectionInterface
     */
    private $connection;

    public function __construct(FrameworkConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(
        string $resourceClass,
        string $operation,
        array $groups,
        int $recursion,
        OpenApiSchemaGenerator $generator
    ): ?Schema {
        if ($recursion > 0 && $operation === 'get') {
            return SchemaFactory::createStringSchema(
                'path',
                $this->connection->getExampleUrl($resourceClass),
                true
            );
        }
        return $generator->createSchema($resourceClass, $operation, $groups);
    }
}
