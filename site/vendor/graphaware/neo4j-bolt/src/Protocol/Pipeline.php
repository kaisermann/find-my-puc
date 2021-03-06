<?php

/*
 * This file is part of the GraphAware Bolt package.
 *
 * (c) GraphAware Ltd <christophe@graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Bolt\Protocol;

use GraphAware\Bolt\Protocol\Message\PullAllMessage;
use GraphAware\Bolt\Protocol\Message\RunMessage;
use GraphAware\Bolt\Protocol\V1\Session;
use GraphAware\Common\Driver\PipelineInterface;
use GraphAware\Common\Result\ResultCollection;

class Pipeline implements PipelineInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var RunMessage[]
     */
    protected $messages = [];

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function push($query, array $parameters = array(), $tag = null)
    {
        $this->messages[] = new RunMessage($query, $parameters, $tag);
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $pullAllMessage = new PullAllMessage();
        $batch = [];
        $resultCollection = new ResultCollection();

        foreach ($this->messages as $message) {
            $batch[] = $message;
            $batch[] = $pullAllMessage;
        }

        $this->session->sendMessages($batch);

        foreach ($this->messages as $message) {
            $resultCollection->add($this->session->recv($message->getStatement(), $message->getParams(), $message->getTag()), $message->getTag());
        }

        return $resultCollection;
    }

    /**
     * @return RunMessage[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->messages);
    }
}
