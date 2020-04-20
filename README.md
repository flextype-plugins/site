<h1 align="center">Site Plugin for <a href="http://flextype.org/">Flextype</a></h1>

<p align="center">
<a href="https://github.com/flextype-plugins/site/releases"><img alt="Version" src="https://img.shields.io/github/release/flextype-plugins/site.svg?label=version"></a> <a href="https://github.com/flextype-plugins/site"><img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="License"></a> <a href="https://github.com/flextype-plugins/site"><img src="https://img.shields.io/github/downloads/flextype-plugins/site/total.svg?colorB=blue" alt="Total downloads"></a> <a href="https://github.com/flextype-plugins/site"><img src="https://img.shields.io/badge/Flextype-0.9.8-green.svg" alt="Flextype"></a> <a href=""><img src="https://img.shields.io/discord/423097982498635778.svg?logo=discord&colorB=728ADA&label=Discord%20Chat" alt="Discord"></a>
</p>

Site plugin to display entries content on the website frontend.

## Dependencies

The following dependencies need to be downloaded and installed for Site Plugin.

### System

| Item | Version | Download |
|---|---|---|
| [flextype](https://github.com/flextype/flextype) | 0.9.8 | [download](https://github.com/flextype/flextype/releases/download/v0.9.8/flextype-0.9.8.zip) |

### Plugins
| Item | Version | Download |
|---|---|---|
| [twig](https://github.com/flextype-plugins/twig) | 1.0.0 | [download](https://github.com/flextype-plugins/twig/releases/download/v1.0.0/twig-1.0.0.zip) |

### Themes
| Item | Version | Download |
|---|---|---|
| [noir](https://github.com/flextype-plugins/noir) | 1.0.0 | [download](https://github.com/flextype-plugins/noir/releases/download/v1.0.0/noir-1.0.0.zip) |

## Installation

* Download & Install all required dependencies.
* Download Admin Panel Plugin and unzip plugin to the folder /site/plugins/

### Twig variables

| Variable | Description |
|---|---|
| entry | The entry object with all the information about the current page you are currently on. |
| query | The Query Params |
| uri | The URI string |

### Examples

```twig
{{ entry.title }} {# returns the current entry title #}
```

## LICENSE
[The MIT License (MIT)](https://github.com/flextype-plugins/site/blob/master/LICENSE.txt)
Copyright (c) 2018-2020 [Sergey Romanenko](https://github.com/Awilum)
