<h1 align="center">Site Plugin for <a href="https://awilum.github.io/flextype/">Flextype</a></h1>

<p align="center">
<a href="https://github.com/flextype-plugins/site/releases"><img alt="Version" src="https://img.shields.io/github/release/flextype-plugins/site.svg?label=version&color=black"></a> <a href="https://github.com/flextype-plugins/site"><img src="https://img.shields.io/badge/license-MIT-blue.svg?color=black" alt="License"></a> <a href="https://github.com/flextype-plugins/site"><img src="https://img.shields.io/github/downloads/flextype-plugins/site/total.svg?color=black" alt="Total downloads"></a> <a href="https://github.com/flextype/flextype"><img src="https://img.shields.io/badge/Flextype-0.10.0-green.svg" alt="Flextype"></a> <a href=""><img src="https://img.shields.io/discord/423097982498635778.svg?logo=discord&color=black&label=Discord%20Chat" alt="Discord"></a>
</p>

Site plugin to display entries content on the website frontend.

## Dependencies

The following dependencies need to be downloaded and installed for Site Plugin.

| Item | Version | Download |
|---|---|---|
| [flextype](https://github.com/flextype/flextype) | 0.10.0 | [download](https://github.com/flextype/flextype/releases) |

## Installation

1. Download & Install all required dependencies.
2. Create new folder `/project/plugins/site`.
3. Download Site Plugin and unzip plugin content to the folder `/project/plugins/site`.

## Documentation

### Template variables for Site Plugin

| Variable | Description |
|---|---|
| entry | The entry object with all the information about the current page you are currently on. |
| query | The Query Params. |
| uri | The URI string. |

#### Examples

Using PHP View
```php
<?= $entry['title'] ?>
```

Using [Twig Plugin](https://github.com/flextype-plugins/twig)
```twig
{{ entry.title }}
```

## LICENSE
[The MIT License (MIT)](https://github.com/flextype-plugins/site/blob/master/LICENSE.txt)
Copyright (c) 2021 [Sergey Romanenko](https://github.com/Awilum)
