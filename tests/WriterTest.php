<?php

use cebe\openapi\spec\SecurityRequirement;

class WriterTest extends \PHPUnit\Framework\TestCase
{
    private function createOpenAPI($merge = [])
    {
        return new \cebe\openapi\spec\OpenApi(array_merge([
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Test API',
                'version' => '1.0.0',
            ],
            'paths' => [],
        ], $merge));
    }

    /**
     * Normalize YAML output to account for Symfony 8 removing whitespace in empty braces.
     * Symfony 8 changed how it formats empty objects in YAML, so we normalize both
     * expected and actual output to handle both formats consistently.
     */
    private function normalize($str)
    {
        return preg_replace('~\{[\s]+\}~', '{}', $str);
    }

    public function testWriteJson()
    {
        $openapi = $this->createOpenAPI();

        $json = \cebe\openapi\Writer::writeToJson($openapi);

        $this->assertEquals(preg_replace('~\R~', "\n", <<<JSON
{
    "openapi": "3.0.0",
    "info": {
        "title": "Test API",
        "version": "1.0.0"
    },
    "paths": {}
}
JSON
),
            $json
        );
    }

    public function testWriteJsonMofify()
    {
        $openapi = $this->createOpenAPI();

        $openapi->paths['/test'] = new \cebe\openapi\spec\PathItem([
            'description' => 'something'
        ]);

        $json = \cebe\openapi\Writer::writeToJson($openapi);

        $this->assertEquals(preg_replace('~\R~', "\n", <<<JSON
{
    "openapi": "3.0.0",
    "info": {
        "title": "Test API",
        "version": "1.0.0"
    },
    "paths": {
        "\/test": {
            "description": "something"
        }
    }
}
JSON
),
            $json
        );
    }

    public function testWriteYaml()
    {
        $openapi = $this->createOpenAPI();

        $yaml = \cebe\openapi\Writer::writeToYaml($openapi);

        $this->assertEquals(
            $this->normalize(preg_replace('~\R~', "\n", <<<YAML
openapi: 3.0.0
info:
  title: 'Test API'
  version: 1.0.0
paths: {  }

YAML
        )),
            $this->normalize($yaml)
        );
    }

    public function testWriteEmptySecurityJson()
    {
        $openapi = $this->createOpenAPI([
            'security' => [],
        ]);

        $json = \cebe\openapi\Writer::writeToJson($openapi);

        $this->assertEquals(preg_replace('~\R~', "\n", <<<JSON
{
    "openapi": "3.0.0",
    "info": {
        "title": "Test API",
        "version": "1.0.0"
    },
    "paths": {},
    "security": []
}
JSON
        ),
            $json
        );
    }


    public function testWriteEmptySecurityYaml()
    {
        $openapi = $this->createOpenAPI([
            'security' => [],
        ]);

        $yaml = \cebe\openapi\Writer::writeToYaml($openapi);

        $this->assertEquals(
            $this->normalize(preg_replace('~\R~', "\n", <<<YAML
openapi: 3.0.0
info:
  title: 'Test API'
  version: 1.0.0
paths: {  }
security: []

YAML
        )),
            $this->normalize($yaml)
        );
    }

    public function testWriteEmptySecurityPartJson()
    {
        $openapi = $this->createOpenAPI([
            'security' => [new SecurityRequirement(['Bearer' => []])],
        ]);

        $json = \cebe\openapi\Writer::writeToJson($openapi);

        $this->assertEquals(preg_replace('~\R~', "\n", <<<JSON
{
    "openapi": "3.0.0",
    "info": {
        "title": "Test API",
        "version": "1.0.0"
    },
    "paths": {},
    "security": [
        {
            "Bearer": []
        }
    ]
}
JSON
        ),
            $json
        );
    }


    public function testWriteEmptySecurityPartYaml()
    {
        $openapi = $this->createOpenAPI([
            'security' => [new SecurityRequirement(['Bearer' => []])],
        ]);

        $yaml = \cebe\openapi\Writer::writeToYaml($openapi);

        $this->assertEquals(
            $this->normalize(preg_replace('~\R~', "\n", <<<YAML
openapi: 3.0.0
info:
  title: 'Test API'
  version: 1.0.0
paths: {  }
security:
  -
    Bearer: []

YAML
        )),
            $this->normalize($yaml)
        );
    }
}
