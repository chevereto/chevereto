<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Job;

use Chevere\Parameter\Interfaces\ArgumentsInterface;
use Chevereto\Controllers\WorkflowController;
use Ramsey\Uuid\Uuid;

final class Job
{
    private string $id;

    private string $document;

    public function __construct(
        private WorkflowController $controller,
        private ArgumentsInterface $arguments
    ) {
        $this->id = Uuid::uuid4()->toString();
    }
    
    public function withDocument(string $document): self
    {
        $new = clone $this;
        $new->document = $document;
    
        return $new;
    }

    public function id(): string
    {
        return $this->id;
    }
    
    public function document(): string
    {
        return $this->document ??= 'document';
    }
}
