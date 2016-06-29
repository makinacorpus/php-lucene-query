<?php

namespace MakinaCorpus\Lucene;

/**
 * Applies fuzziness or romaing operator over an expression
 */
abstract class AbstractFuzzyQuery extends AbstractQuery
{
    /**
     * @var int
     */
    protected $fuzzyness;

    /**
     * Set fuzzyness or roaming value, both uses the same operator, only the
     * type of data (phrase or term) on which you apply it matters
     * 
     * @param int $fuzzyness
     *   Positive integer or null to unset
     *
     * @return $this
     */
    public function setFuzzyness($fuzzyness)
    {
        if ($fuzzyness != NULL && (! is_numeric($fuzzyness) || $fuzzyness < 0)) {
            throw new \InvalidArgumentException("Fuzyness/roaming value must be a positive integer, " . print_r($fuzzyness, TRUE) . " given");
        }

        $this->fuzzyness = $fuzzyness;

        return $this;
    }

    /**
     * Alias for setFuzzyness() method
     * 
     * @param int $roaming
     *   Positive integer
     *
     * @return $this
     */
    public function setRoaming($roaming)
    {
        $this->setFuzzyness($roaming);

        return $this;
    }

    /**
     * Escape and apply boost and operators surrounding the given string
     *
     * @return string
     */
    public function __toString()
    {
        $raw = trim($this->toRawString());

        if (!isset($raw) || ('' === $raw)) {
            return '';
        }

        if ($this->field) {
            $raw = self::escapeToken($this->field) . ':' . $raw;
        }

        if (!empty($this->exclusion)) {
            $raw = $this->exclusion . $raw;
        } else {
            if (!empty($this->fuzzyness)) {
                $raw .= '~' . $this->fuzzyness;
            }
            if (!empty($this->boost)) {
               $raw .= '^' . $this->boost;
            }
        }

        return $raw;
    }
}