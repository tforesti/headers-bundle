# Usage

## Global headers

You can add headers to all the responses with 3 different formats:

```yaml
batch_headers:
  headers:
    # As an array with `name` and `value` keys
    - name: Cache-Control
      value: no-cache

    # As an array with a unique key-value association
    - Cache-Control: no-store

    # As a semicolon-separated string
    - 'Cache-Control: no-transform'
```

**Note:** The headers are applied in the declaration order. The code above would set the `Cache-Control` header to `no-transform`.

## Conditional headers

Some headers need to be applied only to specific responses, a condition can be used to achieve this. [The condition is an expression](https://symfony.com/doc/current/components/expression_language/syntax.html) returning `true` if the header should be applied.

Inside the expression you can use the following variables:

- `request`: An instance of the `Symfony\Component\HttpFoundation\Request` class [(see Symfony's documentation)](https://symfony.com/doc/current/components/http_foundation.html#request).
- `response`: An instance of the `Symfony\Component\HttpFoundation\Response` class [(see Symfony's documentation)](https://symfony.com/doc/current/components/http_foundation.html#response).

```yaml
batch_headers:
  headers:
    # The header will be defined only if the request was made on a URL starting with "/api"
    - name: Access-Control-Allow-Origin
      value: '*'
      condition: request.getPathInfo() matches '^/api'

    # The header will be defined only if the response has a Content-Type starting with "image/"
    - name: Cache-Control
      value: max-age=31536000, public
      condition: response.headers.get('Content-Type') matches '^image/'
```
