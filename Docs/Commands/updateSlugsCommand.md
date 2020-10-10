# TYPO3 extension: tw_base

> Collection of building blocks and viewhelpers for TYPO3 projects by tollwerk

## UpdateSlugsCommand

Finds all records of a given table and updates one or multiple slug fields 
by using the TCA configuration for those fields. Can be executed by using the
command line or by the scheduler inside the TYPO3 backend.

### Command line usage

```
update:slugs [options] [--] [<table> [<fields>]]
```

**Basic example** assuming we want to update the `slug` field for all records of the `pages` table:
```
update:slugs pages slug
```

**Advanced example** assuming we want to change multiple slug fields for all records of a custom table. 
Multiple field names are comma separated. Please not that you have to use quotation marks then.
```
update:slugs tx_myextension_domain_model_myrecord "slug,my_custom_slug" 
```

### Arguments

| Argument| Description
|:----------|:--------|
| `table` | The tablename like `pages` or `tx_myextension_domain_model_myrecord`. |
| `fields` |  One or multiple, comma separated, fieldnames, like `slug` or `"slug,another_slug"`. Please note the quotation marks when using multiple fieldnames.|

### Options

| Options| Description
|:----------|:--------|
|  `-f`, `--force-update` |  Force update of existing slug values. [default: false] |
| `-h`, `--help`   | Display this help message |
| `-q`, `--quiet` | Do not output any message |
| `-V` , `--version` | Display this application version |
| `--ansi` | Force ANSI output |
| `--no-ansi` | Disable ANSI output |
| `-n`, `--no-interaction` | Do not ask any interactive question |
| `-v`/`-vv`/`-vvv`, `--verbose` | Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug |
