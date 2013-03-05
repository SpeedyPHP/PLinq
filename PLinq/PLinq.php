<?php
namespace PLinq;


use PLinq\Collections as c, PLinq\Exceptions as e;

class PLinq implements \IteratorAggregate {

	const ERROR_NO_ELEMENTS = 'Sequence contains no elements.';
    const ERROR_NO_MATCHES = 'Sequence contains no matching elements.';
    const ERROR_NO_KEY = 'Sequence does not contain the key.';
    const ERROR_MANY_ELEMENTS = 'Sequence contains more than one element.';
    const ERROR_MANY_MATCHES = 'Sequence contains more than one matching element.';
    const ERROR_COUNT_LESS_THAN_ZERO = 'count must be a non-negative value.';
    const ERROR_STEP_NEGATIVE = 'step must be a positive value.';


    private $_iterator;

    /**
     * @internal
     * @param Closure $iterator
     */
    private function __construct ($source)
    {
    	if (is_array($iterator)) {
    		$iterator = new Enumerable($source);
    	} elseif ($source instanceof \ArrayIterator) {
    		$iterator = new Enumerable($source->getArrayCopy());
    	} else {
    		throw new \InvalidArgumentException("Unexpected \$source type");
    	}

        $this->_iterator = $iterator;
    }

    /** {@inheritdoc} */
    public function getIterator ()
    {
        /** @var $it \Iterator */
        $it = $this->_iterator;
        $it->rewind();
        return $it;
    }

	/**
	 * @param array|\Iterator|\IteratorAggregate|\PLinq\PLinq $source
	 * @throws \InvalidArgumentException If source is not array or Traversible or Enumerable.
	 * @return \PLinq\PLinq
	 */
	static function from ($source) {
	    return new PLinq($source);
	}

	/**
     * <p><b>Syntax</b>: firstOrDefault ([default])
     * <p>Returns the first element of a sequence, or a default value if the sequence contains no elements.
     * <p><b>Syntax</b>: firstOrDefault ([default [, predicate {{(v, k) ==> result}]])
     * <p>Returns the first element of the sequence that satisfies a condition or a default value if no such element is found.
     * <p>If obtaining the default value is a costly operation, use {@link firstOrFallback} method to avoid overhead.
     * @param mixed $default A default value.
     * @param callback|null $predicate {(v, k) ==> result} A function to test each element for a condition. Default: true.
     * @return mixed If predicate is null: default value if source is empty; otherwise, the first element in source. If predicate is not null: default value if source is empty or if no element passes the test specified by predicate; otherwise, the first element in source that passes the test specified by predicate.
     */
    public function firstOrDefault ($default = null, $predicate = null)
    {
        $predicate = Utils::createLambda($predicate, 'v,k', Functions::$true);

        foreach ($this as $k => $v) {
            if (call_user_func($predicate, $v, $k))
            	return $v;
        }
        return $default;
    }

    /**
     * <p><b>Syntax</b>: first ()
     * <p>Returns the first element of a sequence.
     * <p>The first method throws an exception if source contains no elements. To instead return a default value when the source sequence is empty, use the {@link firstOrDefault} method.
     * <p><b>Syntax</b>: first (predicate {{(v, k) ==> result})
     * <p>Returns the first element in a sequence that satisfies a specified condition.
     * <p>The first method throws an exception if no matching element is found in source. To instead return a default value when no matching element is found, use the {@link firstOrDefault} method.
     * @param callback|null $predicate {(v, k) ==> result} A function to test each element for a condition. Default: true.
     * @throws \UnexpectedValueException If source contains no matching elements.
     * @return mixed If predicate is null: the first element in the specified sequence. If predicate is not null: The first element in the sequence that passes the test in the specified predicate function.
     */
    public function first ($predicate = null)
    {
        $predicate = Utils::createLambda($predicate, 'v,k', Functions::$true);

        foreach ($this as $k => $v) {
            if (call_user_func($predicate, $v, $k))
                return $v;
        }
        throw new \UnexpectedValueException(self::ERROR_NO_MATCHES);
    }

    /**
     * <p><b>Syntax</b>: where (predicate {{(v, k) ==> result})
     * <p>Filters a sequence of values based on a predicate.
     * @param callback $predicate {(v, k) ==> result} A function to test each element for a condition.
     * @return PLinq A sequence that contains elements from the input sequence that satisfy the condition.
     */
    public function where ($predicate)
    {
        $self = $this;
        $predicate = Utils::createLambda($predicate, 'v,k');

        foreach ($this as $k => $v) {
        	if (!call_user_func($predicate, $v, $k)) {
        		unset($this[$k]);
        	}
        }

        return $this;
    }

}