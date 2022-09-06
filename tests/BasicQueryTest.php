<?php

namespace MakinaCorpus\Lucene\Tests;

use MakinaCorpus\Lucene\AbstractQuery;
use MakinaCorpus\Lucene\Query;
use PHPUnit\Framework\TestCase;
use DateTime;

/**
 * @coversDefaultClass \MakinaCorpus\Lucene\Query
 * @coversDefaultClass \MakinaCorpus\Lucene\AbstractQuery
 */
class BasicQueryTest extends TestCase
{
    public function testSomeQuery()
    {
        $query = new Query();

        // Avoid runtime timezone conversion given false negative.
        $from = new DateTime("1983-03-22", new \DateTimeZone("UTC"));
        $now = new DateTime("@1467205820", new \DateTimeZone("UTC"));

        $query
            ->setOperator(Query::OP_AND)
            ->createTermCollection(Query::OP_OR)
            ->add("foo")
            ->add("bar");

        $query
            ->createDateRange('my_date_field')
            ->setInclusive()
            ->setRange($from, $now);

        $query
            ->matchTerm('some_field', 'some value', null, 0.8)
            ->matchTerm('other_field', 'some_other_value', 1.3);

        // @codingStandardsIgnoreStart
        self::assertSame(
            '((foo OR bar) AND my_date_field:["1983\-03\-22T00\:00\:00\+0000" TO "2016\-06\-29T13\:10\:20\+0000"] AND some_field:"some value"~0.8 AND other_field:some_other_value^1.3)',
            trim((string)$query)
        );
        // @codingStandardsIgnoreEnd
    }

    public function dataSpecialCharacterEscaping()
    {
        return [
            [
                'Use the "~" symbol to add boost to your fields!',
                'Use the \"\~\" symbol to add boost to your fields\!',
            ],
            [
                'Some date formats are: \"(30/12/1993), {30-12-1993} & [30\\12\\1993]\"',
                'Some date formats are\: \\\\\"\(30\/12\/1993\), \{30\-12\-1993\} \& \[30\\\12\\\1993\]\\\\\"',
            ],
            [
                'one + one = two',
                'one \+ one \= two',
            ],
            [
                'if (something || (something_else && another_something))',
                'if \(something \|\| \(something_else \&\& another_something\)\)',
            ],
            [
                'Here is a cat: ^*>.<*^!!',
                'Here is a cat\: \^\*\>.\<\*\^\!\!',
            ],
            [
                'Couldn\'t you come up with better test Strings? ... NO, sorry!',
                'Couldn\'t you come up with better test Strings\? ... NO, sorry\!',
            ],
        ];
    }

    /**
     * @dataProvider dataSpecialCharacterEscaping
     */
    public function testSpecialCharacterEscaping(string $raw, string $expected)
    {
        self::assertSame('"' . $expected . '"', AbstractQuery::escapeToken($raw));
    }

    public function testEscapeSingleWordNoQuotes()
    {
        self::assertSame('Word', AbstractQuery::escapeToken('Word'));
    }

    public function testEscapePhraseAddQuotes()
    {
        self::assertSame('"Random search phrase."', AbstractQuery::escapeToken('Random search phrase.'));
    }

    public function testEscapeSingleWordAddQuoteWithForceParam()
    {
        self::assertSame('"Word"', AbstractQuery::escapeToken('Word', true));
    }

    public function testEscapeWithSpecialCharsAddQuotes()
    {
        self::assertSame('"\!\*Test.php, \*.php"', AbstractQuery::escapeToken('!*Test.php, *.php'));
    }
}
