<?php

namespace Bigfork\SilverstripeLinkset\Model;

use SilverStripe\Assets\File;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\HTML;

class Linkset extends DataObject
{
    private static string $table_name = 'Linkset';

    private static string $singular_name = 'Linkset';

    private static array $db = [
        'Title'      => 'Varchar',
        'LinkType'   => 'Varchar',
        'LinkURL'    => 'Text',
        'LinkTarget' => 'Varchar',
    ];

    private static array $has_one = [
        'LinkedPage' => SiteTree::class,
        'LinkedFile' => File::class,
    ];

    private static array $summary_fields = [
        'Title' => 'Title',
    ];

    private static array $owns = [
        'LinkedFile',
    ];

    private static array $defaults = [
        'LinkType' => 'Page',
    ];

    protected array $extraClasses = [];

    public function forTemplate(): string
    {
        return HTML::createTag('a',
            [
                'href'     => $this->Link(),
                'target'   => $this->LinkTarget,
                'download' => $this->LinkType === 'File' ? 'download' : null,
                'class'    => $this->extraClass()
            ],
            $this->Title
        );
    }

    public function Link(): string
    {
        switch ($this->LinkType) {
            case 'Page':
                $page = $this->LinkedPage();
                if ($page->exists()) {
                    return $page->Link();
                }
                break;
            case 'File':
                $file = $this->LinkedFile();
                if ($file->exists()) {
                    return $file->Link();
                }
                break;
            case 'URL':
                return $this->LinkURL;
        }

        return '';
    }

    public function extraClass(): string
    {
        return implode(' ', $this->extraClasses);
    }

    public function addExtraClass(string $class): self
    {
        $classes = preg_split('/\s+/', $class ?? '');

        foreach ($classes as $class) {
            $this->extraClasses[$class] = $class;
        }

        return $this;
    }

    public function removeExtraClass(string $class): self
    {
        $classes = preg_split('/\s+/', $class ?? '');

        foreach ($classes as $class) {
            unset($this->extraClasses[$class]);
        }

        return $this;
    }
}
