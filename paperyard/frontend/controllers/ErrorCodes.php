<?php

namespace Paperyard;

/**
 * Class ErrorCodes
 *
 * This class servers enum like error codes and an interface to resolve them to text hints. This will also be the place, where error code translations will happen.
 *
 * Code assembly:
 * 1. overall set (e.g. 2 = Rules)
 * 2. subset (e.g. 1 = Senders)
 * 3./4. counter
 * 5. special case (0: general, 1: null, 2: not null, 3: format, 4: type, ...)
 *
 * @package Paperyard
 */
abstract class ErrorCodes {

    const FOUND_WORDS_NULL = 21011;
    const FOUND_WORLD_TYPE = 21014;
    const FILE_COMPANY_NULL = 21021;
    const FILE_COMPANY_TYPE = 21024;
    const COMPANY_SCORE_NULL = 21031;
    const COMPANY_SCORE_TYPE = 21034;
    const TAGS_NULL = 21041;
    const TAGS_TYPE = 21044;
    const IS_ACTIVE_NULL = 21051;
    const IS_ACTIVE_TYPE = 21054;

    static public function resolve($errorCode) {
        return "ERROR: " . $errorCode;
    }

}