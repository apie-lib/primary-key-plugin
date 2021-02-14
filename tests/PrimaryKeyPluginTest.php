<?php


namespace Apie\Tests\PrimaryKeyPlugin;

use Apie\CommonApie\DefaultApie;
use PHPUnit\Framework\TestCase;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObjectWithId;

class PrimaryKeyPluginTest extends TestCase
{
    public function testSchemaIsProperlyCreated()
    {
        $apie = DefaultApie::createDefaultApie(
            true,
            [
                new StaticResourcesPlugin([RecursiveObjectWithId::class]),
                new PrimaryKeyPlugin(),
            ],
            null,
            false
        );
        $expected = new Schema(
            [
                'title' => 'RecursiveObjectWithId',
                'description' => 'RecursiveObjectWithId get for groups get, read',
                'type' => 'object',
                'properties' => [
                    'id' => new Schema(
                        [
                            'type' => 'string',
                            'format' => 'uuid',
                        ]
                    ),
                    'child' => new Schema(
                        [
                            'type' => 'string',
                            'format' => 'path',
                            'default' => '/recursive_object_with_id/12345',
                            'nullable' => true,
                            'example' => '/recursive_object_with_id/12345',
                        ]
                    )
                ]
            ]
        );
        $actual = $apie->getSchemaGenerator()->createSchema(
            RecursiveObjectWithId::class,
            'get',
            ['get', 'read']
        );
        $this->assertEquals($expected, $actual);
    }
}
