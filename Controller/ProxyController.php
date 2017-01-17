<?php

namespace VideoNowBundle\Controller;

use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ProxyController extends Controller
{

    /**
     * @param Request $request
     * @param $route
     * @return JsonResponse
     */
    public function defaultAction(Request $request, $route)
    {

        /** @var ResponseInterface[] $overload */
        $overload = $request->get('_overload');
        $content = [];
        if (!empty($overload)) {
            foreach ($overload as $key => $item) {
                $content[$key] = [
                    'content-type' => $item->getHeader('content-type'),
                    'size' => $item->getBody()->getSize()
                    // ...
            ];
            }
        }

        return new JsonResponse($content);
    }
}
