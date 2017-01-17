## Установка ##

Предпологаеться что имееться установленый symfony 3.*

Добавить записи в composer.json
```json
// composer.json
{
    "require": {
        "krecu/videonow": "dev-master"
    },
    "repositories": [
            {
                "type": "vcs",
                "url":  "git@github.com:krecu/videonow.git"
            }
    ]
}
```

Зарегестрировать bundle
```php
// app/AppKernel.php
new VideoNowBundle\VideoNowBundle(),
```

## Настройка ##
Добавьте запись `video_now` в config.yml

```yml
video_now:
    routing:
      example_1:
        pattern_uri: "~^/example1(.*)$~i"
        proxy_uri: "http://google.com"
        invoke: VideoNowBundle\RuleInvoke\ExampleRuleInvoke
      example_2:
        pattern_uri: "~^/example2(.*)$~i"
        proxy_uri: "http://google.com"
        invoke: VideoNowBundle\RuleInvoke\ExampleRuleInvoke
      example_3_1:
        pattern_uri: "~^/example3(.*)$~i"
        proxy_uri: "http://google.com"
        invoke: VideoNowBundle\RuleInvoke\ExampleRuleInvoke
      example_3_2:
        pattern_uri: "~^/example3(.*)$~i"
        proxy_uri: "https://jsonplaceholder.typicode.com/posts/1"
        invoke: VideoNowBundle\RuleInvoke\ExampleRuleInvoke
```

##### pattern_uri
Паттерн релятивного URL текущего домена 
пример:
```yml
pattern_uri: "~^/example1(.*)$~i"
```
##### proxy_uri
Абсолютный URL к каторому будет выполнен request
пример:
```yml
proxy_uri: "http://google.com"
```
##### invoke
Класс расширяющий стандартный метод отбора и поиска правил по регуляркам. Не обязательный параметр
пример:
```yml
invoke: VideoNowBundle\RuleInvoke\ExampleRuleInvoke
```

## Использование ##
Логика:
- Любой запрос перехватываеться листнером `ProxyEventListener`
- Если в списке нашлись необходимые правила то выполняем запрос по заданным `proxy_uri` и коллекционируем в атрибут `_overload` реквеста ответ в виде `ResponseInterface`
пример простого контроллера:
```php
class ProxyController extends Controller
{
    /**
     * @param Request $request
     * @param $route
     * @return JsonResponse
     */
    public function defaultAction(Request $request)
    {
        /** @var ResponseInterface[] $overload */
        $overload = $request->get('_overload');
        $content = [];
        if (!empty($overload)) {
            foreach ($overload as $key => $item) {
                $content[$key] = [
                    'content-type' => $item->getHeader('content-type'),
                    'size' => $item->getBody()->getSize()
                    // фактически мы можем как угодно обрабатывать полученные данные
            ];
            }
        }
        return new JsonResponse($content);
    }
}
```

демки в бандле:
- http://localhost:8000/example1 - найдено 1 правило
- http://localhost:8000/example3 - найдено несколько правило


## Методы сервиса ##
##### Метод `getRules(Request $request)`
Поиск подходящих правил, `допускаем что правило может быть не одно`
Результат:
* Массив правил, где
```php
[
    pattern_uri: string, // регулярка
    proxy_uri: string,   // http url
    invoke: null || RuleInvokeInterface,
```

Вызов:
```php
$rules = $this->getRules($request);
```

##### Метод `getParams(Request $request)`
Преобразование Symfony\Component\HttpFoundation\Request в массив
Результат:
```php
[
    'headers' => [],
    'query' => [],
    'request' => [],
    'attributes' => [],
    'files' => [],
    'server' => [],
    'connect_timeout' => int,
```

Вызов:
```php
$params = $this->getParams($request);
```

##### Метод `execute(Request $request)`
Отправка запроса по всем правилам