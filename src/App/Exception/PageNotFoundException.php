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

class PageNotFoundException extends NotFoundException
{
    /**
     * @var string
     */
    protected string $template = '/app/view/404';

    /**
     * PageNotFoundException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '404 Not Found', int $code = 404, ?Throwable $previous = null)
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
        $data    = $this->data;
        $message = $this->trans($this->getMessage(), $data);
        if ($request->expectsJson()) {
            $json = ['code' => $code, 'msg' => $message, 'data' => $data];
            return new Response(200, ['Content-Type' => 'application/json'],
                json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        return new Response($code, [], $this->html($message));
    }

    /**
     * Get the HTML representation of the exception.
     * @param string $message
     * @return string
     * @throws Throwable
     */
    protected function html(string $message): string
    {
        $message = htmlspecialchars($message);
        if (is_file(base_path("$this->template.html"))) {
            return view($this->template, ['message' => $message])->rawBody();
        }
        return <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$message</title>
    <style>
        .center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1 class="center">$message</h1>
    <hr>
    <div class="center">webman</div>
</body>
</html>
EOF;
    }

}