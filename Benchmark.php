<?php
/**
 * Simple class to benchmark code
 * Copyright (C) 2012 Hosh Sadiq
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA
 */

/**
 * Simple class to benchmark code
 * 
 * @author Hosh Sadiq
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version 1
 * @package Benchmark
 * 
 * @example
 * $benchmark = new Benchmark();
 * $benchmark->time( 'Closure test', function () {
 *     $fn = function ( $item ) { return $item; };
 *     $fn( 'test' );
 * }, true );
 *
 * var_dump( $benchmark->get_results() );
 */
class Benchmark
{
    /**
     * This will contain the results of the benchmarks.
     * There is no distinction between averages and just one runs
     */
    private $_results = array();

    /**
     * Disable PHP's time limit and optionally PHP's memory limit!
     * These benchmarks may take some resources
     */
    public function __construct($memory = null)
    {
        set_time_limit( 0 );
        if($memory !== null) {
            $memory = preg_replace('/[^0-9]/', '', ( string ) $memory).'M';
            if($memory != 'M') {
                ini_set( 'memory_limit', $memory );
            }
        }
    }

    /**
     * The function that times a piece of code
     * @param string $name Name of the test. Must not have been used before
     * @param callable|closure $callback A callback for the code to run.
     * @param boolean|integer $multiple optional How many times should the code be run,
     * if false, only once, else run it $multiple times, and store the average as the benchmark
     * @return Benchmark $this
     */
    public function time( $name, $callback, $multiple = false )
    {
        if(function_exists('is_closure')) {
            if(!is_callable($callback) && !is_closure($callback)) {
                throw new Exception(__CLASS__.'::'.__FUNCTION__.' requires argument $callback to be callable or closure.');
            }
        } else {
            if(!is_callable($callback)) {
                throw new Exception(__CLASS__.'::'.__FUNCTION__.' requires argument $callback to be callable.');
            }
        }

        if( $multiple === false ) {
            // run and time the test
            $start = microtime( true );
            $callback();
            $end = microtime( true );

            // add the results to the results array
            $this->_results[] = array(
                'test' => $name,
                'iterations' => 1,
                'time_e' => $end - $start,
                'time' => sprintf( '%.10f', $end - $start )
            );
        } else {
            // set a default if $multiple is set to true
            if( $multiple === true ) {
                $multiple = 10000;
            }

            // run the test $multiple times and time it every time
            $total_time = 0;
            for( $i = 1; $i <= $multiple; $i++ ) {
                $start = microtime( true );
                $callback();
                $end = microtime( true );
                $total_time += $end - $start;
            }
            // calculate the average and add it to the results
            $this->_results[] = array(
                'test' => $name,
                'iterations' => $multiple,
                'time_e' => $total_time / $multiple,
                'time' => sprintf( '%.10f', $total_time / $multiple )
            );
        }
        return $this; //chainability
    }

    /**
     * Returns all the results
     * @return array $results
     */
    public function get_results()
    {
        return $this->_results;
    }
}
