<?php

namespace Paperyard;

/**
 * Class ErrorCodes
 *
 * This class servers enum like error codes and an interface to resolve them to text hints. This will also be the place, where error code translations will happen.
 *
 * @package Paperyard
 */
abstract class ErrorCodes {

    const PARAMETER_NULL = 20001;
    const PARAMETER_NOT_NULL = 20002;
    const PARAMETER_FORMAT_MISMATCH = 20003;

    static public function resolve($errorCode) {
        return "ERROR: " . $errorCode;
    }

}