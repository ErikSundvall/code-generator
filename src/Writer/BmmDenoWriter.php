<?php

namespace OpenEHR\Tools\CodeGen\Writer;

use OpenEHR\Tools\CodeGen\Model\Bmm\BmmClass;
use OpenEHR\Tools\CodeGen\Model\Bmm\BmmPackage;
use OpenEHR\Tools\CodeGen\Model\Bmm\BmmSchema;
use OpenEHR\Tools\CodeGen\Model\Bmm\AbstractBmmProperty;
use OpenEHR\Tools\CodeGen\Model\Bmm\BmmContainerProperty;
use OpenEHR\Tools\CodeGen\Model\Bmm\BmmGenericProperty;
use OpenEHR\Tools\CodeGen\Model\Bmm\BmmSingleProperty;
use OpenEHR\Tools\CodeGen\Model\Bmm\BmmSinglePropertyOpen;
use OpenEHR\Tools\CodeGen\Model\Bmm\BmmFunction;
use OpenEHR\Tools\CodeGen\Model\Bmm\BmmContainerType;
use OpenEHR\Tools\CodeGen\Model\Bmm\BmmGenericType;
use OpenEHR\Tools\CodeGen\Model\Bmm\BmmSimpleType;

class BmmDenoWriter extends AbstractWriter
{
    public const string DIR = __WRITER_DIR__ . DIRECTORY_SEPARATOR . 'deno' . DIRECTORY_SEPARATOR;

    public function write(): void
    {
        $this->assureOutputDir();
        /** @var BmmSchema $schema */
        foreach ($this->reader->files as $schema) {
            /** @var BmmPackage $package */
            foreach ($schema->packages as $package) {
                if (count($package->classes)) {
                    $this->createPackage(
                        $package,
                        $schema
                    );
                }
            }
        }
    }

    private function createPackage(
        BmmPackage $package,
        BmmSchema $schema,
        string $namePrefix = ''
    ): void {
        $name = $namePrefix . $package->name;
        $packageDir = self::DIR . str_replace('.', DIRECTORY_SEPARATOR, $name);
        $this->assureOutputDir($packageDir);

        foreach ($package->classes as $className) {
            $class = $schema->classDefinitions->get($className);
            if ($class instanceof BmmClass) {
                $this->createClassFiles(
                    $class,
                    $packageDir
                );
            }
        }

        foreach ($package->packages as $subPackage) {
            $this->createPackage(
                $subPackage,
                $schema,
                $name . '.'
            );
        }
    }

    private function createClassFiles(
        BmmClass $class,
        string $packageDir
    ): void {
        $this->createTsFile(
            $class,
            $packageDir
        );
        $this->createJsFile(
            $class,
            $packageDir
        );
    }

    private function createTsFile(
        BmmClass $class,
        string $packageDir
    ): void {
        $tsContent = $this->generateTsContent($class);
        $tsFilePath = $packageDir . DIRECTORY_SEPARATOR . $this->formatClassName($class->name, false) . '.ts';
        file_put_contents(
            $tsFilePath,
            $tsContent
        );
    }

    private function createJsFile(
        BmmClass $class,
        string $packageDir
    ): void {
        $jsContent = $this->generateJsContent($class);
        $jsFilePath = $packageDir . DIRECTORY_SEPARATOR . $this->formatClassName($class->name, false) . '.js';
        file_put_contents(
            $jsFilePath,
            $jsContent
        );
    }

    private function generateTsContent(BmmClass $class): string
    {
        $imports = [];
        $className = $this->formatClassName($class->name);

        $content = "/**\n * " . $class->documentation . "\n */\n";
        $content .= "class " . $className;

        if (!empty($class->ancestors)) {
            $ancestor = $this->formatClassName($class->ancestors[0]);
            $imports[] = $this->formatClassName($class->ancestors[0], false);
            $content .= " extends " . $ancestor;
        }

        $content .= " {\n";

        foreach ($class->properties as $property) {
            $type = $this->mapBmmPropertyToTsType($property, $imports);
            $content .= "    " . $this->formatPropertyName($property->name) . ": " . $type . ";\n";
        }

        $content .= "\n";
        $content .= "    constructor() {\n";
        if (!empty($class->ancestors)) {
            $content .= "        super();\n";
        }
        $content .= "    }\n";
        $content .= "\n";

        foreach ($class->functions as $function) {
            $content .= $this->generateTsMethod($function, $imports);
        }

        $content .= "}\n";

        $importContent = "";
        foreach (array_unique($imports) as $import) {
            $importContent .= "import { " . $this->formatClassName($import) . " } from './" . $import . ".ts';\n";
        }

        return $importContent . "\n" . $content;
    }

    private function generateJsContent(BmmClass $class): string
    {
        $imports = [];
        $className = $this->formatClassName($class->name);
        $content = "/**\n * @class " . $className . "\n * @description " . $class->documentation . "\n */\n";
        $content .= "class " . $className;

        if (!empty($class->ancestors)) {
            $ancestor = $this->formatClassName($class->ancestors[0]);
            $imports[] = $this->formatClassName($class->ancestors[0], false);
            $content .= " extends " . $ancestor;
        }

        $content .= " {\n";

        foreach ($class->properties as $property) {
            $type = $this->mapBmmPropertyToJsType($property, $imports);
            $content .= "    /**\n";
            $content .= "     * @type {" . $type . "}\n";
            $content .= "     */\n";
            $content .= "    " . $this->formatPropertyName($property->name) . ";\n";
        }

        $content .= "\n";
        $content .= "    constructor() {\n";
        if (!empty($class->ancestors)) {
            $content .= "        super();\n";
        }
        $content .= "    }\n";
        $content .= "\n";

        foreach ($class->functions as $function) {
            $content .= $this->generateJsMethod($function, $imports);
        }

        $content .= "}\n";

        $importContent = "";
        foreach (array_unique($imports) as $import) {
            $importContent .= "import { " . $this->formatClassName($import) . " } from './" . $import . ".js';\n";
        }

        return $importContent . "\n" . $content;
    }

    private function generateTsMethod(BmmFunction $function, array &$imports): string
    {
        $methodName = $this->formatMethodName($function->name);
        $parameters = [];
        foreach ($function->parameters as $parameter) {
            $parameters[] = $this->formatPropertyName($parameter->name) . ": " . $this->mapBmmTypeToTsType($parameter->typeDef, $imports);
        }
        $returnType = $this->mapBmmTypeToTsType($function->result, $imports);

        $content = "    /**\n";
        $content .= "     * " . $function->documentation . "\n";
        $content .= "     */\n";
        $content .= "    " . $methodName . "(" . implode(", ", $parameters) . "): " . $returnType . " {\n";
        $content .= "        // TODO: Implement method\n";
        $content .= "        return null;\n";
        $content .= "    }\n\n";

        return $content;
    }

    private function generateJsMethod(BmmFunction $function, array &$imports): string
    {
        $methodName = $this->formatMethodName($function->name);
        $parameters = [];
        $paramDocs = [];
        foreach ($function->parameters as $parameter) {
            $paramName = $this->formatPropertyName($parameter->name);
            $paramType = $this->mapBmmTypeToJsType($parameter->typeDef, $imports);
            $parameters[] = $paramName;
            $paramDocs[] = "     * @param {" . $paramType . "} " . $paramName;
        }
        $returnType = $this->mapBmmTypeToJsType($function->result, $imports);

        $content = "    /**\n";
        $content .= "     * " . $function->documentation . "\n";
        $content .= implode("\n", $paramDocs) . "\n";
        foreach ($function->preConditions as $preCondition) {
            $content .= "     * @pre " . $preCondition . "\n";
        }
        foreach ($function->postConditions as $postCondition) {
            $content .= "     * @post " . $postCondition . "\n";
        }
        $content .= "     * @returns {" . $returnType . "}\n";
        $content .= "     */\n";
        $content .= "    " . $methodName . "(" . implode(", ", $parameters) . ") {\n";
        $content .= "        // TODO: Implement method\n";
        $content .= "        return null;\n";
        $content .= "    }\n\n";

        return $content;
    }

    private function mapBmmPropertyToTsType(AbstractBmmProperty $property, array &$imports): string
    {
        if ($property instanceof BmmSingleProperty) {
            return $this->mapBmmTypeToTsType($property->typeDef, $imports);
        } elseif ($property instanceof BmmContainerProperty) {
            return "Array<" . $this->mapBmmTypeToTsType($property->typeDef, $imports) . ">";
        } elseif ($property instanceof BmmGenericProperty) {
            return $this->mapBmmTypeToTsType($property->typeDef, $imports);
        } elseif ($property instanceof BmmSinglePropertyOpen) {
            return "any";
        }
        return "any";
    }

    private function mapBmmPropertyToJsType(AbstractBmmProperty $property, array &$imports): string
    {
        if ($property instanceof BmmSingleProperty) {
            return $this->mapBmmTypeToJsType($property->typeDef, $imports);
        } elseif ($property instanceof BmmContainerProperty) {
            return "Array<" . $this->mapBmmTypeToJsType($property->typeDef, $imports) . ">";
        } elseif ($property instanceof BmmGenericProperty) {
            return $this->mapBmmTypeToJsType($property->typeDef, $imports);
        } elseif ($property instanceof BmmSinglePropertyOpen) {
            return "any";
        }
        return "any";
    }

    private function mapBmmTypeToTsType($type, array &$imports): string
    {
        if ($type === null) {
            return "void";
        }

        if ($type instanceof BmmContainerType) {
            return "Array<" . $this->mapBmmTypeToTsType($type->typeDef, $imports) . ">";
        } elseif ($type instanceof BmmGenericType) {
            $typeParameters = [];
            foreach ($type->genericParameterDefs as $param) {
                $typeParameters[] = $this->mapBmmTypeToTsType($param, $imports);
            }
            $rootType = $this->formatClassName($type->rootType);
            if (!in_array($this->formatClassName($type->rootType, false), $imports)) {
                $imports[] = $this->formatClassName($type->rootType, false);
            }
            return $rootType . "<" . implode(", ", $typeParameters) . ">";
        } elseif ($type instanceof BmmSimpleType) {
            $primitiveType = $this->mapBmmPrimitiveToTsType($type->type);
            if ($primitiveType !== null) {
                return $primitiveType;
            }
            $className = $this->formatClassName($type->type, false);
            if (!in_array($className, $imports)) {
                $imports[] = $className;
            }
            return $this->formatClassName($type->type);
        }

        return "any";
    }

    private function mapBmmTypeToJsType($type, array &$imports): string
    {
        if ($type === null) {
            return "void";
        }

        if ($type instanceof BmmContainerType) {
            return "Array<" . $this->mapBmmTypeToJsType($type->typeDef, $imports) . ">";
        } elseif ($type instanceof BmmGenericType) {
            $typeParameters = [];
            foreach ($type->genericParameterDefs as $param) {
                $typeParameters[] = $this->mapBmmTypeToJsType($param, $imports);
            }
            $rootType = $this->formatClassName($type->rootType);
            if (!in_array($this->formatClassName($type->rootType, false), $imports)) {
                $imports[] = $this->formatClassName($type->rootType, false);
            }
            return $rootType . "<" . implode(", ", $typeParameters) . ">";
        } elseif ($type instanceof BmmSimpleType) {
            $primitiveType = $this->mapBmmPrimitiveToJsType($type->type);
            if ($primitiveType !== null) {
                return $primitiveType;
            }
            $className = $this->formatClassName($type->type, false);
            if (!in_array($className, $imports)) {
                $imports[] = $className;
            }
            return $this->formatClassName($type->type);
        }

        return "any";
    }

    private function mapBmmPrimitiveToTsType(string $type): ?string
    {
        return match (strtolower($type)) {
            'string', 'iso8601_date', 'iso8601_date_time', 'iso8601_time', 'iso8601_duration', 'terminology_code' => 'string',
            'integer', 'integer64' => 'number',
            'real' => 'number',
            'boolean' => 'boolean',
            default => null,
        };
    }

    private function mapBmmPrimitiveToJsType(string $type): ?string
    {
        return match (strtolower($type)) {
            'string', 'iso8601_date', 'iso8601_date_time', 'iso8601_time', 'iso8601_duration', 'terminology_code' => 'string',
            'integer', 'integer64' => 'number',
            'real' => 'number',
            'boolean' => 'boolean',
            default => null,
        };
    }

    private function formatClassName(string $name, bool $capitalize = true): string
    {
        if ($capitalize) {
            return strtoupper($name);
        }
        return $name;
    }

    private function formatPropertyName(string $name): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
    }

    private function formatMethodName(string $name): string
    {
        return strtolower(str_replace(' ', '_', str_replace('-', '_', $name)));
    }
}
