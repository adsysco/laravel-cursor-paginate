<?php

namespace Bitsnbolts\CursorPaginate\Tests;

use PHPUnit\Framework\Attributes\Test;

class RequestTest extends TestCase
{
    #[Test]
    public function it_will_return_the_next_cursor_url_in_the_response()
    {
        (new TestModelFactory)->count(11)->create();
        $this->get('/')
            ->assertJsonFragment(['next' => 'http://localhost?cursor=WyIyMDIxLTAzLTAzIDAwOjAwOjAwIiwxMF0%3D']);

        $this->get('/?cursor=WyIyMDIxLTAzLTAzIDAwOjAwOjAwIiwxMF0%3D')
            ->assertJsonFragment(['next' => 'http://localhost?cursor=WyIyMDIxLTAzLTAzIDAwOjAwOjAwIiw4XQ%3D%3D']);
    }

    #[Test]
    public function it_will_discover_the_current_cursor_url()
    {
        (new TestModelFactory)->count(11)->create();
        $response = $this->get('/?cursor=WyIyMDIxLTAzLTAzIDAwOjAwOjAwIiwxMF0%3D');

        $response->assertJsonFragment(['self' => 'http://localhost?cursor=WyIyMDIxLTAzLTAzIDAwOjAwOjAwIiwxMF0%3D']);
    }

    #[Test]
    public function the_next_url_will_return_the_next_set_of_results()
    {
        (new TestModelFactory)->count(11)->create();
        $next = json_decode($this->get('/')->getContent())->next;

        $response = $this->get($next);
        $response->assertJsonMissing(['id' => 11]);
        $response->assertJsonMissing(['id' => 10]);
        $response->assertJsonFragment(['id' => 9]);
    }

    #[Test]
    public function the_self_url_contains_the_original_query_parameters()
    {
        (new TestModelFactory)->count(11)->create();
        $self = json_decode($this->get('/?foo=bar&baz=9')->getContent())->self;
        $this->assertStringContainsString('?foo=bar&baz=9', $self);
    }

    #[Test]
    public function the_next_url_contains_the_original_query_parameters()
    {
        (new TestModelFactory)->count(11)->create();
        $next = json_decode($this->get('/?foo=bar&baz=9')->getContent())->next;
        $this->assertStringContainsString('foo=bar&baz=9', $next);
    }

    #[Test]
    public function the_total_is_the_total_number_of_items()
    {
        (new TestModelFactory)->count(23)->create();
        $response = $this->get('/');
        $total = json_decode($response->getContent())->total;
        $this->assertEquals(23, $total);
    }

    #[Test]
    public function the_total_is_calculated_correctly_when_using_the_cursor()
    {
        // It works for the base request
        (new TestModelFactory)->count(23)->create();
        $response = $this->get('/');
        $total = json_decode($response->getContent())->total;
        $this->assertEquals(23, $total);

        // And when using the cursor
        $next = json_decode($response->getContent())->next;

        $response = $this->get($next);
        $total = json_decode($response->getContent())->total;
        $this->assertEquals(23, $total);
    }
}
