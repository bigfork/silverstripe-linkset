# SilverStripe Linkset
Adds easy to implement link options to the CMS

## Features
- Choose from the following options: None, Page, File, URL
- Option to remove fields that don't apply to your implementation
- Can output link straight to template


## Installation
```sh
$ composer require bigfork/silverstripe-linkset
```
## How to use

You will need to create a $has_one relation on your data object
```
private static array $has_one = [
    'ButtonLink' => LinkSet::class,
];
```

Then add the LinksetField to your fieldlist
```
$fields->addFieldsToTab(
    'Root.Main',
    [
        ...otherFields,
        LinksetField::create($this, 'ButtonLink'),
        ...otherFields,
    ]
);
```

LinkSetField constructor accepts five params:
- DataObject $data - The object the relation is on $this
- string $name - Name of the field should match the relation key
- ?string $title (optional) - Title of the field, will set the heading above the fields
- array $fieldsToRemove (optional) - To remove Title from the list of fields pass in array for example ['Title']. Possible options to remove are:
  - Title
  - LinkType
  - LinkURL
  - LinkTarget
  - LinkedPageID
  - LinkedFile
- array $typesToRemove (optional) - Remove link types from the radio, to remove to option for None pass in array for example ['None']. Possible options to remove are:
  - None
  - Page
  - File
  - URL 

With all the parameters your code may look like:
```
$fields->addFieldsToTab(
    'Root.Main',
    [
        ...otherFields,
        LinksetField::create($this, 'ButtonLink', 'Button link', ['Title'], ['None']),
        ...otherFields,
    ]
);
```

### Using in templates

You can output the link in the template by calling the relation
```
{$ButtonLink.AddExtraClass('button button--primary')}
```
This will output a simple anchor tag with the all the fields that had been set on the LinkSetField group.

Custom anchors can be done by accessing the fields throught the relations as normal
```
<% with $ButtonLink %>
  <a 
    href="{$Link} 
    class="button"
    <% if $LinkTarget %> target="{$LinkTarget}"<% end_if %>
    <% if $LinkType = 'File' %> download<% end_if %>
  >
    {$Title}
  </a>
<% end_with %>
```
