<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\Exception;
use Sunrise\Http\Router\Exception\RouteAlreadyExistsException;

/**
 * RouteAlreadyExistsExceptionTest
 */
class RouteAlreadyExistsExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $exception = new RouteAlreadyExistsException();

        $this->assertInstanceOf(Exception::class, $exception);
    }

    /**
     * @return void
     */
    public function testMessage() : void
    {
        $message = 'blah';

        $exception = new RouteAlreadyExistsException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    /**
     * @return void
     */
    public function testContext() : void
    {
        $context = ['foo' => 'bar'];

        $exception = new RouteAlreadyExistsException('blah', $context);

        $this->assertSame($context, $exception->getContext());
    }

    /**
     * @return void
     */
    public function testCode() : void
    {
        $code = 100;

        $exception = new RouteAlreadyExistsException('blah', [], $code);

        $this->assertSame($code, $exception->getCode());
    }

    /**
     * @return void
     */
    public function testPrevious() : void
    {
        $previous = new \Exception();

        $exception = new RouteAlreadyExistsException('blah', [], 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
