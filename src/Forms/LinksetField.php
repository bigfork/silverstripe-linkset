<?php

namespace Bigfork\SilverstripeLinkset\Forms;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectInterface;
use UncleCheese\DisplayLogic\Forms\Wrapper;

class LinksetField extends CompositeField
{
    protected $linkset;

    protected string $relationName;

    protected array $fields = [
        'Title',
        'LinkType',
        'LinkURL',
        'LinkTarget',
        'LinkedPageID',
        'LinkedFile',
    ];

    protected array $options = [
        'None' => 'No link',
        'Page' => 'Link to a page on this site',
        'File' => 'Link to a file on this site',
        'URL'  => 'Link to another website'
    ];

    public function __construct(
        DataObject $data,
        string $name,
        ?string $title = null,
        array $fieldsToRemove = [],
        array $typesToRemove = []
    ) {
        $this->relationName = $name;
        $this->linkset = $data->{$name}();

        foreach ($typesToRemove as $key => $value) {
            unset($this->options[$value]);
        }

        $children = [
            HeaderField::create("{$name}", $title),
            HiddenField::create($name . '.ID'),

            OptionsetField::create(
                $name . '.LinkType',
                'Link type',
                $this->options
            )->setValue($this->linkset->exists() ? $this->linkset->LinkType : 'None'),

            'Title' => TextField::create($name . '.Title', 'Link text')
                ->displayIf($name . '_LinkType')
                ->isNotEqualTo('None')
                ->end(),

            'LinkedPageID' => Wrapper::create(
                TreeDropdownField::create($name . '.LinkedPageID', 'Linked page', SiteTree::class)
                    ->setTitleField('MenuTitle')
            )->displayIf($name . '_LinkType')
                ->isEqualTo('Page')
                ->end(),

            'LinkedFile' => Wrapper::create(
                UploadField::create($name . '.LinkedFile', 'Linked file')
                    ->setFolderName('Files')
            )->displayIf($name . '_LinkType')
                ->isEqualTo('File')
                ->end(),

            'LinkURL' => Wrapper::create(
                TextField::create($name . '.LinkURL', 'Linked page')
                    ->setDescription('Please include the "https://" prefix')
            )->displayIf($name . '_LinkType')
                ->isEqualTo('URL')
                ->end(),

            'LinkTarget' => Wrapper::create(
                DropdownField::create($name . '.LinkTarget', 'Link target', [
                    '_blank' => 'Open in a new window'
                ])->setEmptyString('Open in the same window')
            )->displayIf($name . '_LinkType')
                ->isNotEqualTo('None')
                ->end(),
        ];

        foreach ($fieldsToRemove as $key => $value) {
            unset($children[$value]);
        }

        parent::__construct($children);
    }

    public function isComposite(): bool
    {
        return false;
    }

    public function hasData(): bool
    {
        return true;
    }

    public function saveInto(DataObjectInterface $record): self
    {

        $relation = $record->{$this->relationName}();
        $children = $this->getChildren();

        foreach ($this->fields as $field) {
            $form_field = $children->dataFieldByName("{$this->relationName}.{$field}");
            $relation->$field = $form_field?->dataValue();
        }

        $relation->write();
        $record->setCastedField("{$this->relationName}ID", $relation->ID);


        return $this;
    }
}
