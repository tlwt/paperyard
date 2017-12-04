<?php

namespace Paperyard\Controllers\Rule;

use Paperyard\Controllers\BasicController;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class ArchiveDetails
 * @package Paperyard\Controllers\Rule
 */
class ArchiveDetails extends BasicController
{
    /** @var \Paperyard\Models\Rule\Archive */
    private $rule;

    /**
     * ArchiveDetails constructor.
     * @param Twig $view
     * @param LoggerInterface $logger
     * @param Messages $flash
     */
    public function __construct(Twig $view, LoggerInterface $logger, Messages $flash)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->flash = $flash;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|static
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        // find model from id
        $this->rule = \Paperyard\Models\Rule\Archive::find((int)$request->getAttribute('ruleId'));

        // if not found, redirect with error
        if ($this->rule === null) {
            $this->flash->addMessage('error', _('Rule not found.'));
            return $response->withRedirect('/rules/archives');
        }

        // show rule details
        $this->view->render($response, 'rule/archive_details.twig', $this->render());
        return $response;
    }

    /**
     * render
     * @return array data to render the view
     */
    public function render()
    {
        return array(
            'plugins' => parent::getPlugins(),
            'rule' => $this->rule,
            'languageFlag' => parent::getLanguageFlag()
        );
    }
}
