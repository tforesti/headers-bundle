# Batch Headers Bundle

A Symfony bundle to ease the configuration of global response headers. Instead of creating a response listener to add custom headers, use a configuration file:

```yaml
batch_headers:
  headers:
    # Apply a CSP on all the responses
    - Content-Security-Policy: default-src 'self'

    # Allow your API to be requested from all origins
    - name: Access-Control-Allow-Origin
      value: "*"
      condition: request.getPathInfo() matches '^/api'

    # Always cache images
    - name: Cache-Control
      value: max-age=31536000, public
      condition: response.headers.get('Content-Type') matches '^image/'
```

## Documentation

Read the documentation in [src/Resources/doc/](src/Resources/doc/index.md)
