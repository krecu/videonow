<?php

namespace VideoNowBundle\EventListener;

use VideoNowBundle\Service\ProxyService;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class ProxyEventListener
 * @package VideoNowBundle\EventListener
 */
class ProxyEventListener
{
    /**
     * List routing rule
     *
     * @var ProxyService
     */
    protected $_proxy;

    /**
     * ProxyEventListener constructor.
     * @param ProxyService $_proxy
     */
    public function __construct(ProxyService $_proxy)
    {
        $this->_proxy = $_proxy;
    }

    /**
     * @inheritdoc
     */
    public function onKernelController(FilterControllerEvent $event)
    {

        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        $contents = $this->_proxy->execute($event->getRequest());

        // делаем доступным полученные данные в вызываемом котроллере
        $event->getRequest()->attributes->set('_overload', $contents);
    }
}