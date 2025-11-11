<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Exceptions;

/**
 * This exception is raised when trying to access root with headless token, or
 * trying to attach a child to a SingleToken
 */
class TokenTraversalException extends AstException {}
