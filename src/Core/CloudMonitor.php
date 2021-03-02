<?php

namespace CloudMonitor\Toolkit\Core;

use Closure;
use Exception;
use Throwable;
use InvalidArgumentException;
use CloudMonitor\Toolkit\Core\Segment;
use CloudMonitor\Toolkit\Core\Transaction;
use CloudMonitor\Toolkit\Error\Models\Error;

class CloudMonitor
{
    /**
     * Client version.
     * 
     * @var string
     */
    const VERSION = '1.1.12';

    /**
     * @var Transaction
     */
    public $transaction;

    /**
     * Constructs new CloudMonitor instance.
     */
    public function __construct()
    {
        register_shutdown_function([$this, 'flush']);
    }

    /**
     * Creates new transaction and starts it.
     * 
     * @param $transaction
     * @param string $type Type of transaction.
     */
    public function startTransaction($transaction, string $type): Transaction
    {
        $this->transaction = new Transaction(addslashes($transaction), $type);
        $this->transaction->start();

        return $this->transaction;
    }

    /**
     * Is a transaction currently being monitored.
     * 
     * @return bool
     */
    public function isRecording(): bool
    {
        return isset($this->transaction);
    }

    /**
     * Retrieve current monitored transaction.
     * 
     * @return Transaction
     */
    public function currentTransaction(): Transaction
    {
        return $this->transaction;
    }

    /**
     * Flush the transaction.
     * 
     * @return void
     */
    public function flush(): void
    {
        if (!$this->isRecording()) {
            return;
        }

        if (!$this->transaction->isEnded()) {
            $this->transaction->end();
        }

        unset($this->transaction);
    }

    /**
     * Start a new segment.
     * 
     * @param $type
     * @param $label
     * @return Segment
     */
    public function startSegment($type, $label = null): Segment
    {
        $segment = new Segment($this->transaction, addslashes($type), $label);
        $segment->start();

        return $segment;
    }

    /**
     * Exception reporter.
     * 
     * @param Throwable $exception
     * @param bool handled
     * @return Error
     */
    public function reportException(Throwable $exception, $handled = true): Error
    {
        if (!$exception instanceof Exception && !$exception instanceof Throwable) {
            throw new InvalidArgumentException('$exception need to be an instance of Exception or Throwable.');
        }

        if (!$this->isRecording()) {
            $this->startTransaction($exception->getMessage(), Transaction::TRANSACTION_EXCEPTION);
        }

        $segment = $this->startSegment('exception', substr($exception->getMessage(), 0, 50));
        $error = (new Error($exception, $this->transaction))->setHandled($handled);
        $segment->addContext('Error', $error)->end();

        return $error;
    }
}