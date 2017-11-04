<?php

namespace Paperyard\Controllers\Rule;

use Paperyard\Controllers\BasicController;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;

class SubjectDetails extends BasicController
{
    /** @var \Paperyard\Models\Rule\Subjects */
    private $rule;

    public function __construct(Twig $view, LoggerInterface $logger, Messages $flash)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->flash = $flash;

        $this->registerPlugin('bootstrap-notify.min');
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        // find model from id
        $this->rule = \Paperyard\Models\Rule\Subjects::find((int)$request->getAttribute('ruleId'));

        // if not found, redirect with error
        if ($this->rule === null) {
            $this->flash->addMessage('error', _('Rule not found'));
            return $response->withRedirect('/rules/subjects');
        }

        // show rule details
        $this->view->render($response, 'rule/subject_details.twig', $this->render());
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
