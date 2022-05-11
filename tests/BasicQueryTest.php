<?php

namespace MakinaCorpus\Lucene\Tests;

use DateTime;
use MakinaCorpus\Lucene\AbstractQuery;
use MakinaCorpus\Lucene\Query;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass  \MakinaCorpus\Lucene\Query
 * @coversDefaultClass  \MakinaCorpus\Lucene\AbstractQuery
 */
class BasicQueryTest extends TestCase
{
    public function testSomeQuery()
    {
        $query = new Query();

        $now = new DateTime("@1467205820");

        $query
            ->setOperator(Query::OP_AND)
            ->createTermCollection(Query::OP_OR)
            ->add("foo")
            ->add("bar");

        $query
            ->createDateRange('my_date_field')
            ->setInclusive()
            ->setRange('1983-03-22', $now);

        $query
            ->matchTerm('some_field', 'some value', null, 0.8)
            ->matchTerm('other_field', 'some_other_value', 1.3);

        // @codingStandardsIgnoreStart
        $this->assertSame(
            '((foo OR bar) AND my_date_field:["1983\-03\-22T00\:00\:00\+0000" TO "2016\-06\-29T13\:10\:20\+0000"] AND some_field:"some value"~0.8 AND other_field:some_other_value^1.3)',
            trim((string)$query)
        );
        // @codingStandardsIgnoreEnd
    }

    /**
     * @covers \MakinaCorpus\Lucene\AbstractQuery::escapeToken
     */
    public function testSpecialCharacterEscaping()
    {
        // @codingStandardsIgnoreStart
        $test_strings = [
            'Use the "~" symbol to add boost to your fields!' => 'Use the \"\~\" symbol to add boost to your fields\!',
            'Some date formats are: \"(30/12/1993), {30-12-1993} & [30\\12\\1993]\"' => 'Some date formats are\: \\\\\"\(30\/12\/1993\), \{30\-12\-1993\} \& \[30\\\12\\\1993\]\\\\\"',
            'one + one = two' => 'one \+ one \= two',
            'if (something || (something_else && another_something))' => 'if \(something \|\| \(something_else \&\& another_something\)\)',
            'Here is a cat: ^*>.<*^!!' => 'Here is a cat\: \^\*\>.\<\*\^\!\!',
            'Couldn\'t you come up with better test Strings? ... NO, sorry!' => 'Couldn\'t you come up with better test Strings\? ... NO, sorry\!',
        ];
        // @codingStandardsIgnoreEnd

        // Assert strings with characters that should be escaped.
        foreach ($test_strings as $input => $expected) {
            $this->assertEquals("\"$expected\"", AbstractQuery::escapeToken($input));
        }
        // Assert single word without characters that should be escaped.
        $this->assertEquals('Word', AbstractQuery::escapeToken('Word'));
        // Assert phrase without characters that should be escaped.
        $this->assertEquals('"Random search phrase."', AbstractQuery::escapeToken('Random search phrase.'));
        /** Assert single word without characters that should be escaped, using @param $force = true. */
        $this->assertEquals('"Word"', AbstractQuery::escapeToken('Word', TRUE));
        // Assert string without spaces, with characters that should be escaped.
        $this->assertEquals('"\!\*Test.php, \*.php"', AbstractQuery::escapeToken('!*Test.php, *.php'));
    }
}
