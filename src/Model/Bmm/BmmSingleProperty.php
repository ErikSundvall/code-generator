<?php

namespace OpenEHR\Tools\CodeGen\Model\Bmm;

use JsonSerializable;
use OpenEHR\Tools\CodeGen\Model\YamlSerializable;

/**
 * Class representing a BMM single property
 */
readonly class BmmSingleProperty extends AbstractBmmProperty implements JsonSerializable, YamlSerializable
{

    public function __construct(
        string $name,
        public BmmSimpleType|BmmContainerType|BmmGenericType $typeDef,
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
            'type_def' => $this->typeDef,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function yamlSerialize(): array
    {
        return array_filter([
            'name' => $this->name,
            'type_def' => $this->typeDef->yamlSerialize(),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            typeDef: AbstractBmmType::fromArray($data['type_def']),
        );
    }
}
