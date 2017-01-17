<?php

namespace VideoNowBundle\Service;

use VideoNowBundle\RuleInvoke\RuleInvokeInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProxyService
 * @package VideoNowBundle\EventListener
 */
class ProxyService
{
    /**
     * List routing rule
     *
     * @var array
     */
    protected $_routing;

    /**
     * ProxyService constructor.
     * @param array $_routing
     */
    public function __construct(array $_routing)
    {
        $this->_routing = $_routing;
    }

    /**
     * Ищем подходящее правило проксирования
     *
     * @param $request
     * @return mixed
     */
    public function getRules(Request $request)
    {
        $rule = null;

        // парсим компоненты uri
        $partsUri = parse_url($request->getUri());

        // собираем релативный uri
        $relativeUri = $partsUri['path'] . (!empty($partsUri['query']) ? '?'.$partsUri['query'] : '');

        // фильтруем правила на соотведствия
        return array_filter($this->_routing, function($r) use($relativeUri, $request) {

            $invoke = true;

            if (!empty($r['invoke']) && class_exists($r['invoke'])) {
                /** @var RuleInvokeInterface $class */
                $class = $r['invoke'];
                $invoke = $class::check($r, $request);
            }


            return preg_match($r['pattern_uri'], $relativeUri) && $invoke;
        });
    }

    /**
     * Формируем на основании реквеста параметры запроса
     *
     * @param Request $request
     * @return array
     */
    private function getParams(Request $request)
    {
        $headers = $request->headers->all();
        // потомучто хост нам неважен
        unset($headers['host']);

        $paramBag = [
            'headers' => $headers,
            'query' => $request->query->all(),
            'request' => $request->request->all(),
            'attributes' => $request->attributes->all(),
            //'cookies' => $request->cookies->all(),
            'files' => $request->files->all(),
            'server' => $request->server->all(),
            'connect_timeout' => 5,
        ];

        switch ($request->getContentType()) {
            // если это json то возможно есть body
            case 'json' :
                if (!empty($request->getContent())) {
                    $paramBag['json'] = json_decode($request->getContent());
                }
                break;
        }

        return $paramBag;
    }

    /**
     * Проксирование на ендпоинты из правил
     *
     * @param Request $request
     * @return array
     */
    public function execute(Request $request)
    {

        // получаем набор правил проксирования
        // допускаем что проксировать мы можем на несколько урлов сразу
        $rules = $this->getRules($request);

        $content = [];

        foreach ($rules as $key => $rule) {
            $res = null;
            try {
                $client = new \GuzzleHttp\Client();
                $content[$key] = $client->request($request->getMethod(), $rule['proxy_uri'], $this->getParams($request));
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                // как то логируем что запрос неудался но наверное нам важно что бы приложение неупало
                error_log($e->getMessage());
            }

        }

        return $content;
    }
}