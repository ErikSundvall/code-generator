<?php
// Paste this code into src/Model/Bmm/BmmSingleFunctionParameterOpen.php

namespace OpenEHR\Tools\CodeGen\Model\Bmm;

use JsonSerializable;
use OpenEHR\Tools\CodeGen\Model\YamlSerializable;

/**
 * Class representing a BMM single function parameter open
 */
readonly class BmmSingleFunctionParameterOpen extends AbstractBmmFunctionParameter implements JsonSerializable, YamlSerializable
{
    public function __construct(
        string $name,
        public string $type,
    )
    {
        parent::__construct($name);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'name' => $this->name,
            'type' => $this->type,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function yamlSerialize(): array
    {
        return $this->jsonSerialize();
    }

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            type: $data['type'],
        );
    }
}