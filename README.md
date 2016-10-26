# DocBuilder

Tool for making config files for documentation or static site generators like [MkDocs](http://www.mkdocs.org/) and help generate the docs from your PHP web apps.

> Current version is in alpha stage, and only supports MkDocs (presuming you have it installed already).

**Requirements:**

- PHP version >= 5.5.9
- Appropriate docs generator programmes installed in the same system that runs this tool, e.g. [MkDocs](http://www.mkdocs.org/).

## Installation

Use [Composer](https://getcomposer.org) to install:

```bash
composer require mogita/docbuilder
```

## Usage

You'll need to prepare all the markdown files before building a Doc. DocBuilder will later support generating markdown files from the data source you provide.

Follow either of these two ways to make configs and build the Docs.

### By passing in a config definition

```php
use Mogita\DocBuilder\MkDocs;

$options = [
    'site_name' => 'A New Docs Site',
    'pages' => [
        ['Home' => 'index.md'],
        ['About' => 'about.md'],
        ['API Docs' => [
            ['User' => 'user.md'],
            ['Data' => 'data.md']
        ]]
    ],
    'extra' => [
        'i18n' => [
            'prev' => '前一页',
            'next' => '后一页'
        ]
    ],
    'theme' => 'material'
];

$mkdocs = new MkDocs(getcwd() . '/docs', $options);

$res = $mkdocs->build();

if ($res === true) {
    echo 'Success!';
}
else {
    var_dump($res);
}
```

### By using the step-by-step setter methods

```php
use Mogita\DocBuilder\MkDocs;

$mkdocs = new MkDocs(getcwd() . '/docs');

$mkdocs->setHeaderLink('http://www.example.com');
$mkdocs->setSiteName('Title new');
$mkdocs->setTheme('material'); // You'll have to install the specific themes to your system first

$mkdocs->addPage('Home', 'index.md');
$mkdocs->addPage('About', 'about.md');

$mkdocs->addPage('API Docs', []); // this creates a secondary level, so that you can add pages to this level
$mkdocs->addPage('User', 'user.md', 'API Docs');
$mkdocs->addPage('Data', 'data.md', 'API Docs');

$mkdocs->setPrev('前一页');
$mkdocs->setNext('后一页');
$mkdocs->setPrimaryColor('purple');
$mkdocs->setAccentColor('teal');

$res = $mkdocs->build();

if ($res === true) {
    echo 'Success!';
}
else {
    var_dump($res);
}
```

## License

The MIT License (MIT). Please see [License File](https://github.com/mogita/docbuilder/blob/master/LICENSE) for 
more information.