<?php

namespace Enhavo\Bundle\BlockBundle\Model\Column;

use Enhavo\Bundle\BlockBundle\Model\NodeInterface;

class TwoColumnBlock extends Column
{
    /**
     * @var NodeInterface
     */
    private $columnOne;

    /**
     * @var NodeInterface
     */
    private $columnTwo;

    /**
     * @return NodeInterface
     */
    public function getColumnOne()
    {
        return $this->columnOne;
    }

    /**
     * @param NodeInterface $columnOne
     */
    public function setColumnOne($columnOne)
    {
        $columnOne->setParent($this->getNode());
        $columnOne->setProperty('columnOne');
        $columnOne->setType(NodeInterface::TYPE_LIST);
        $this->columnOne = $columnOne;
    }

    /**
     * @return NodeInterface
     */
    public function getColumnTwo()
    {
        return $this->columnTwo;
    }

    /**
     * @param NodeInterface $columnTwo
     */
    public function setColumnTwo($columnTwo)
    {
        $columnTwo->setParent($this->getNode());
        $columnTwo->setProperty('columnTwo');
        $columnTwo->setType(NodeInterface::TYPE_LIST);
        $this->columnTwo = $columnTwo;
    }

    public function setNode(NodeInterface $node = null)
    {
        parent::setNode($node);
        if($this->getColumnOne()) {
            $this->getColumnOne()->setParent($node);
        }
        if($this->getColumnTwo()) {
            $this->getColumnTwo()->setParent($node);
        }
    }
}
