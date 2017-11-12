<?php


namespace Paperyard\Helpers;

use Slim\Flash\Messages;

class PaperyardMassages extends Messages
{
    /**
     * Adds an array of messages to a single key.
     *
     * @param $key string
     * @param $messages array
     */
    public function addMessages($key, $messages)
    {
        foreach ($messages as $message) {
            $this->addMessage($key, $message[0]);
        }
    }
}