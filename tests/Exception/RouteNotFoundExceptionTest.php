<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\Exception;
use Sunrise\Http\Router\Exception\PageNotFoundException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;

/**
 * RouteNotFoundExceptionTest
 */
class RouteNotFoundExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $exception = new RouteNotFoundException();

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertInstanceOf(PageNotFoundException::class, $exception);
    }

    /**
     * @return void
     */
    public function testMessage() : void
    {
        $message = 'blah';

        $exception = new RouteNotFoundException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    /**
     * @return void
     */
    public function testContext() : void
    {
        $context = ['foo' => 'bar'];

        $exception = new RouteNotFoundException('blah', $context);

        $this->assertSame($context, $exception->getContext());
    }

    /**
     * @return void
     */
    public function testCode() : void
    {
        $code = 100;

        $exception = new RouteNotFoundException('blah', [], $code);

        $this->assertSame($code, $exception->getCode());
    }

    /**
     * @return void
     */
    public function testPrevious() : void
    {
        $previous = new \Exception();

        $exception = new RouteNotFoundException('blah', [], 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
