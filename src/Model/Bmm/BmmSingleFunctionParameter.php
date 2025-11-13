<?php
// Paste this code into src/Model/Bmm/BmmSingleFunctionParameter.php

namespace OpenEHR\Tools\CodeGen\Model\Bmm;

use JsonSerializable;
use OpenEHR\Tools\CodeGen\Model\YamlSerializable;

readonly class BmmSingleFunctionParameter extends AbstractBmmFunctionParameter implements JsonSerializable, YamlSerializable
{
    public function __construct(
        string $name,
        public BmmSimpleType|BmmContainerType|BmmGenericType $typeDef,
    )
    {
        parent::__construct($name);
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'name' => $this->name,
            'type_def' => $this->typeDef,
        ]);
    }

    public function yamlSerialize(): array
    {
        return array_filter([
            'name' => $this->name,
            'type_def' => $this->typeDef->yamlSerialize(),
        ]);
    }

    public static function fromArray(array $data): self
    {
        $typeDefData = null;

        // Case 1: The modern 'type_def' object exists.
        if (isset($data['type_def']) && is_array($data['type_def'])) {
            $typeDefData = $data['type_def'];
        }
        // Case 2: The legacy 'type' string exists.
        elseif (isset($data['type'])) {
            $typeDefData = [
                '_type' => 'BMM_SIMPLE_TYPE',
                'type' => $data['type'],
            ];
        }
        // Case 3: Neither exists, so we must default to 'ANY' to prevent a crash.
        else {
            $typeDefData = [
                '_type' => 'BMM_SIMPLE_TYPE',
                'type' => 'ANY',
            ];
        }

        return new self(
            name: $data['name'],
            typeDef: AbstractBmmType::fromArray($typeDefData)
        );
    }
}
