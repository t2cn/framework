<?php
/**
 * This file is part of T2-Engine.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    Tony<dev@t2engine.cn>
 * @copyright Tony<dev@t2engine.cn>
 * @link      https://www.t2engine.cn/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
declare(strict_types=1);

namespace App\Exception;

use T2\Request;
use T2\Response;
use Throwable;

class MissingInputException extends PageNotFoundException
{
    /**
     * @var string
     */
    protected string $template = '/app/view/400';

    /**
     * MissingInputException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = 'Missing input parameter :parameter', int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render an exception into an HTTP response.
     * @param Request $request
     * @return Response|null
     * @throws Throwable
     */
    public function render(Request $request): ?Response
    {
        $code    = $this->getCode() ?: 404;
        $debug   = config($request->plugin ? "plugin.$request->plugin.app.debug" : 'app.debug');
        $data    = $debug ? $this->data : ['parameter' => ''];
        $message = $this->trans($this->getMessage(), $data);
        if ($request->expectsJson()) {
            $json = ['code' => $code, 'msg' => $message, 'data' => $data];
            return new Response(200, ['Content-Type' => 'application/json'],
                json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        return new Response($code, [], $this->html($message));
    }
}