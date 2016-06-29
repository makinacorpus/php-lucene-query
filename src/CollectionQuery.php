<?php

namespace MakinaCorpus\Lucene;

class CollectionQuery extends AbstractQuery implements
    \IteratorAggregate,
    \Countable
{
    /**
     * @var AbstractQuery[]
     */
    protected $elements = array();

    /**
     * Can be Query::OP_AND or Query::OP_OR, or null for default (AND)
     *
     * @var string
     */
    protected $operator = null;

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * Adds an element to the internal list
     *
     * @param AbstractQuery $element
     *
     * @return $this
     */
    public function add($element)
    {
        if (!$element instanceof AbstractQuery) {
            throw new \InvalidArgumentException("Provided element is not an AbstractQuery instance");
        }

        $this->elements[] = $element;

        return $this;
    }

    /**
     * Remove element
     *
     * @param AbstractQuery $element
     *
     * @return $this
     */
    protected function removeElement(AbstractQuery $element)
    {
        foreach ($this->elements as $key => $existing) {
            if ($existing === $element) {
                unset($this->elements[$key]);
            }
        }

        return $this;
    }

    /**
     * Set default operator
     *
     * @param string $operator
     *
     * @return $this
     */
    public function setOperator($operator)
    {
        if ($operator !== null && $operator !== Query::OP_AND && $operator !== Query::OP_OR) {
            throw new \InvalidArgumentException("Operator must be Query::OP_AND or Query::OP_OR");
        }

        $this->operator = $operator;

        return $this;
    }

    /**
     * Get current operator.
     *
     * @return string $operator
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return empty($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    protected function toRawString()
    {
        if (empty($this->elements)) {
            return '';
        }
        if (count($this->elements) > 1) {
            $operator = ($this->operator ? (' ' . $this->operator . ' ') : ' ');
            return '(' . implode($operator, $this->elements) . ')';
        } else {
            reset($this->elements);
            return (string)current($this->elements);
        }
    }
}