<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core\Type;

/**
 * Number type. Uses bcmath functionality for number calculations.
 *
 * @copyright   copyright (c) 2010-2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Number extends \Octris\Core\Type
{
    /**
     * Value of object.
     *
     * @type    float
     */
    protected $value = '0';

    /**
     * Number of digits after the decimal point for a calculated result.
     *
     * @type    int|null
     */
    protected $scale = null;

    /**
     * Constructor.
     *
     * @param   float       $value      Optional value for number.
     * @param   int         $scale      Number of digits after the decimal point for a calculated result.
     */
    public function __construct($value = 0, $scale = null)
    {
        $this->value = (string)$value;
        $this->scale = (is_null($scale)
                        ? (($scale = ini_get('precision'))
                            ? $scale
                            : null)
                        : $scale);
    }

    /**
     * Method is called, when number object is casted to a string.
     *
     * @return  string                      Value of object.
     */
    public function __toString()
    {
        return (string)$this->get();
    }

    /**
     * Magic caller to implement calculation functionality.
     *
     * @param   string              $func                                       Name of function to perform.
     * @param   array               $args                                       Arbitrary number of arguments of type float, number or money.
     * @return  \Octris\Core\Type\Number|\Octris\Core\Type\Money        Instance of current object.
     */
    public function __call($func, array $args)
    {
        if (($cnt = count($args)) == 0) {
            throw new \Exception('Function must be called with one or multiple operands or an array of operands');
        } elseif ($cnt == 1 && is_array($args[0])) {
            $args = array_shift($args);

            if (count($args) == 0) {
                throw new \Exception('Function must be called with one or multiple operands or an array of operands');
            }
        }

        switch ($func) {
            case 'add':
                array_walk($args, function ($v) {
                    $this->value = bcadd($this->value, (string)$v, $this->scale);
                });
                break;
            case 'sub':
                array_walk($args, function ($v) {
                    $this->value = bcsub($this->value, (string)$v, $this->scale);
                });
                break;
            case 'mul':
                array_walk($args, function ($v) {
                    $this->value = bcmul($this->value, (string)$v, $this->scale);
                });
                break;
            case 'div':
                array_walk($args, function ($v) {
                    $this->value = bcdiv($this->value, (string)$v, $this->scale);
                });
                break;
            case 'mod':
                array_walk($args, function ($v) {
                    $this->value = bcmod($this->value, (string)$v, $this->scale);
                });
                break;
            default:
                throw new \Exception(sprintf('Unknown method "%s"', $func));
                break;
        }

        return $this;
    }

    /**
     * Test if stored number is decimal.
     *
     * @return  bool                                                            Returns true, if number is a decimal.
     */
    public function isDecimal()
    {
        return (strpos($this->value, '.') !== false);
    }

    /**
     * Absolute value.
     *
     * @return  \Octris\Core\Type\Number|\Octris\Core\Type\Money        Instance of current object.
     */
    public function abs()
    {
        $this->value = ltrim($this->value, '-');

        return $this;
    }

    /**
     * Round fractions up.
     *
     * @return  \Octris\Core\Type\Number|\Octris\Core\Type\Money        Instance of current object.
     */
    public function ceil()
    {
        $this->value = (substr($this->value, 0, 1) == '-'
                        ? bcsub($this->value, 0, 0)
                        : bcadd($this->value, 1, 0));

        return $this;
    }

    /**
     * Compare number with another one.
     *
     * @param   mixed               $num    Number to compare with.
     * @return  int                         Returns 0 if the both numbers are equal, 1 if the current number object is larger, -1 if the specified number is larger.
     */
    public function compare($num)
    {
        return bccomp($this->value, (string)$num, $this->scale);
    }

    /**
     * Compare number with another one and return true, if both numbers are equal.
     *
     * @param   mixed               $num    Number to compare with.
     * @return  bool                        Returns true, if numbers are equal.
     */
    public function equals($num)
    {
        return ($this->compare($num) === 0);
    }

    /**
     * Round fractions down.
     *
     * @return  \Octris\Core\Type\Number|\Octris\Core\Type\Money        Instance of current object.
     */
    public function floor()
    {
        $this->value = (substr($this->value, 0, 1) == '-'
                        ? bcsub($this->value, 1, 0)
                        : bcadd($this->value, 0, 0));

        return $this;
    }

    /**
     * Negate value.
     *
     * @return  \Octris\Core\Type\Number|\Octris\Core\Type\Money        Instance of current object.
     */
    public function neg()
    {
        $this->value = (substr($this->value, 0, 1) == '-'
                        ? substr($this->value, 1)
                        : '-' . $this->value);

        return $this;
    }

    /**
     * Exponential expression.
     *
     * @exp     mixed               $exp                The exponent.
     * @return  \Octris\Core\Type\Number|\Octris\Core\Type\Money        Instance of current object.
     */
    public function pow($exp)
    {
        $this->value = bcpow($this->value, (string)$exp, $this->scale);

        return $this;
    }

    /**
     * Rounds the number.
     *
     * @param   int                 $precision          Optional number of decimals to round to.
     * @return  \Octris\Core\Type\Number|\Octris\Core\Type\Money        Instance of current object.
     */
    public function round($precision = 0)
    {
        $this->value = (substr($this->value, 0, 1) == '-'
                        ? bcsub($this->value, '0.' . str_repeat('0', $precision) . '5', $precision)
                        : bcadd($this->value, '0.' . str_repeat('0', $precision) . '5', $precision));

        return $this;
    }

    /**
     * Calculate the square root.
     *
     * @return  \Octris\Core\Type\Number|\Octris\Core\Type\Money        Instance of current object.
     */
    public function sqrt()
    {
        $this->value = bcsqrt($this->value, $this->scale);

        return $this;
    }

    /**
     * Return value of object.
     *
     * @return  float                                   Value.
     */
    public function get()
    {
        return (float)(!(bool)(float)$this->value ? ltrim($this->value, '-') : $this->value); // prevents signed zero, which we do not want for formatting reasons.
    }

    /**
     * Set value of object.
     *
     * @param   float               $amount             Value to set.
     * @return  \Octris\Core\Type\Number|\Octris\Core\Type\Money        Instance of current object.
     */
    public function set($value)
    {
        $this->value = $value;

        return $this;
    }
}
