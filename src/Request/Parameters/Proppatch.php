<?php

declare(strict_types=1);

namespace Ngmy\WebDav\Request\Parameters;

use DOMNode;
use InvalidArgumentException;

class Proppatch
{
    /** @var list<DOMNode> */
    private $propertiesToSet = [];
    /** @var list<DOMNode> */
    private $propertiesToRemove = [];

    /**
     * @param list<DOMNode> $propertiesToSet
     * @param list<DOMNode> $propertiesToRemove
     */
    public function __construct($propertiesToSet = [], $propertiesToRemove = [])
    {
        $this->propertiesToSet = $propertiesToSet;
        $this->propertiesToRemove = $propertiesToRemove;
        $this->validate();
    }

    /**
     * @return list<DOMNode>
     */
    public function getPropertiesToSet(): array
    {
        return $this->propertiesToSet;
    }

    /**
     * @return list<DOMNode>
     */
    public function getPropertiesToRemove(): array
    {
        return $this->propertiesToRemove;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function validate(): void
    {
        if (empty($this->propertiesToSet) && empty($this->propertiesToRemove)) {
            throw new InvalidArgumentException(
                'PROPPATCH parameters must add properties to set and/or remove.'
            );
        }
    }
}
