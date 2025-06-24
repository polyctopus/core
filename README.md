# Polyctopus Core

Polyctopus Core is a lightweight PHP library for managing structured content and content types, inspired by headless CMS concepts. It provides a flexible, type-safe way to define content models, fields, and to manage content instances in your application.

## Features

- Define custom content types with fields and validation
- Create, update, find, and delete content entries
- In-memory repositories for rapid prototyping and testing
- Extensible field type system (e.g. Text fields, custom types)
- Service layer for business logic
- Type-safe content status using PHP enums (requires PHP 8.1+)
- Automatic validation of content data against field definitions
- **Versioning and rollback for content entries**  
  Every create and update operation creates a new version. You can rollback to any previous version at any time.

## Requirements

- PHP 8.3 or higher

## Installation

Install via Composer (assuming you have a `composer.json`):

```bash
composer require polyctopus/core
```

## Usage Example

Simply have a look at the `examples` directory for a complete example of how to use the library. 

## Versioning & Rollback

- **Automatic versioning:**  
  Every time you create or update content using the service, a new version is created and stored in the `ContentVersionRepositoryInterface`.
- **Rollback:**  
  You can access all versions for a content entry and use the `rollback($contentId, $versionId)` method of the service to restore a previous state.

## Validation

When creating or updating content, the service automatically validates the data against the field definitions of the content type. If validation fails (e.g. a string is too long), an `InvalidArgumentException` is thrown.

## Extending

- Add new field types by implementing `FieldTypeInterface`.
- Implement your own repositories for persistent storage by extending `ContentRepositoryInterface` and `ContentVersionRepositoryInterface`.
- Create custom validation rules by implementing `FieldValidationRuleInterface`.

## Contributing
Contributions are welcome! Please create a pull request or open an issue for discussion.

## License
LGPL-3.0-or-later