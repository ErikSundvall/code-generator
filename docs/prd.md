# Product Requirements Document: TypeScript/JavaScript openEHR Library Generator

## 1. Introduction

This document outlines the requirements for a new feature to be added to the openEHR code generator. The feature will enable the simultaneous generation of two versions of openEHR libraries: one in TypeScript and another in JavaScript with extensive JSDoc annotations. The generated libraries will be designed for use with the Deno runtime.

## 2. Problem Statement

Currently, the code generator can produce BMM JSON, BMM YAML, XMI Internal Model, and PlantUML files. There is a need to extend its capabilities to generate code for TypeScript and JavaScript developers who are working with openEHR data. This will enable them to work more efficiently and with greater type safety.

## 3. Goals

*   To create a new generator that can produce openEHR libraries in both TypeScript and JavaScript.
*   The generated JavaScript should be accompanied by extensive JSDoc annotations to provide a good developer experience for JavaScript users.
*   The generated code should be compatible with the Deno runtime.
*   The generated code should follow the specified coding conventions.
*   The generator should be easy to use and well-documented.

## 4. User Stories

*   As a TypeScript developer, I want to be able to generate openEHR libraries from BMM files so that I can work with openEHR data in a type-safe way.
*   As a JavaScript developer, I want to be able to generate openEHR libraries from BMM files with extensive JSDoc annotations so that I can get good autocompletion and type checking in my editor.
*   As a developer using Deno, I want to be able to use the generated openEHR libraries in my projects without having to transpile them or use a compatibility layer.

## 5. Requirements

### 5.1. Functional Requirements

*   The generator shall be implemented as a single writer that can produce both TypeScript and JavaScript output.
*   The generator shall be triggered by a new command in the `bin/generate` script.
*   The generated code shall be compatible with the Deno runtime and its module system.
*   The generator shall produce JSDoc 3 annotations for the JavaScript output, including namepaths and tags for assertions, invariants, preconditions, and postconditions when available from the BMM source files.
*   The generated code shall follow the specified coding conventions:
    *   `snake_case` for methods.
    *   `CAPITALIZED` class names for openEHR types.
    *   `CamelCase` for utility/helper classes and methods that are not part of the openEHR specifications.
*   The generator shall not introduce any external dependencies other than those provided by the Deno runtime or modern browsers.

### 5.2. Non-Functional Requirements

*   The generated code should be well-formatted and easy to read.
*   The generator should be performant and be able to handle large BMM files.
*   The generator should be well-tested to ensure the correctness of the generated code.

## 6. Future Enhancements

*   Support for publishing the generated libraries to a Deno-friendly package registry.
*   Support for generating code for other languages, such as Python or C#.
*   Support for generating code from other input formats, such as Archetype Definition Language (ADL).

## 7. Out of Scope

*   The implementation of a full openEHR validation library. The generated code will only represent the data structures and will not include any validation logic.
*   The implementation of a user interface for the generator. The generator will only be available as a command-line tool.
